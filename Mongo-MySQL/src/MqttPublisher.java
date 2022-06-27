import com.mongodb.client.FindIterable;
import com.mongodb.client.MongoCollection;
import org.bson.Document;
import org.bson.types.ObjectId;
import org.eclipse.paho.client.mqttv3.MqttClient;
import org.eclipse.paho.client.mqttv3.MqttConnectOptions;
import org.eclipse.paho.client.mqttv3.MqttException;
import org.eclipse.paho.client.mqttv3.MqttMessage;
import org.eclipse.paho.client.mqttv3.persist.MemoryPersistence;
import org.javatuples.Pair;
import org.javatuples.Triplet;

import java.sql.Timestamp;
import java.time.Instant;
import java.util.HashMap;
import java.util.Stack;

import static com.mongodb.client.model.Filters.*;

public class MqttPublisher extends Thread {

    private final Sensor sensor;
    private final double maxJump;
    private final int medicoesPorSensor;
    private final int tempoRequery;
    private final boolean verificarSalto;
    private final double alpha;

    private final String mqttTopic;
    private final String brokerUrl;
    private final String publisherId;
    private final int QoS_readings;
    private final int QoS_anomalies;

    private final MongoCollection<Document> labMedicoes;
    private final MongoCollection<Document> lastTimestamps;
    private MqttClient publisher;

    private Double lastLeitura = null;
    private ObjectId lastObjectId = new ObjectId("000000000000000000000000");
    private Double mediaMovel = null;

    public MqttPublisher(MongoCollection<Document> labMedicoes, MongoCollection<Document> lastTimestamps, Sensor sensor, int medicoesAoArrancar,
                         int tempoRequery, double maxJump, boolean verificarSalto, String mqttTopic, String brokerUrl,
                         String prefixPublisherId, int QoS_readings, int QoS_anomalies, double alpha) {
        this.labMedicoes = labMedicoes;
        this.lastTimestamps = lastTimestamps;
        this.sensor = sensor;
        this.medicoesPorSensor = medicoesAoArrancar;
        this.tempoRequery = tempoRequery;
        this.maxJump = maxJump;
        this.verificarSalto = verificarSalto;
        this.mqttTopic = mqttTopic;
        this.brokerUrl = brokerUrl;
        this.publisherId = sensor.getId() + "_" + prefixPublisherId;
        this.QoS_readings = QoS_readings;
        this.QoS_anomalies = QoS_anomalies;
        this.alpha = alpha;
    }

    private void connectToBroker() {
        MemoryPersistence persistence = new MemoryPersistence();
        try {
            publisher = new MqttClient(brokerUrl, publisherId, persistence);
            MqttConnectOptions connOpts = new MqttConnectOptions();
            connOpts.setCleanSession(true);
            System.out.println(sensor + " Connecting to broker: " + brokerUrl);
            publisher.connect(connOpts);
            System.out.println(sensor + " Connected to broker");
        } catch (MqttException e) {
            System.out.println(sensor + " Error while connecting to Mqtt Broker");
            System.out.println(sensor + "reason " + e.getReasonCode());
            System.out.println(sensor + "msg " + e.getMessage());
            System.out.println(sensor + "loc " + e.getLocalizedMessage());
            System.out.println(sensor + "cause " + e.getCause());
            System.out.println(sensor + "excep " + e);
            e.printStackTrace();
            System.exit(1);
        }
    }

    private void initializeData() {
        Document doc = lastTimestamps.find(eq("Sensor", sensor.getId())).first();
        if (doc != null) {
            lastObjectId = doc.getObjectId("lastTimestampOID");
            System.out.println(sensor + " Last ObjectId = " + lastObjectId.toString());
        } else
            System.out.println(sensor + ": No data from this sensor in collection \"lastTimestampsOID\"");
    }

    @Override
    public void run() {
        System.out.println(sensor + ": MQTTpublisher started");
        connectToBroker();
        initializeData();
        while (true) {
            try {
                FindIterable<Document> documents = labMedicoes.find(and(gt("_id", lastObjectId), eq("Sensor", sensor.getId()))).sort(new Document("_id", -1)).limit(medicoesPorSensor);
                Stack<Document> stack = new Stack<>();
                for (Document document : documents)
                    stack.push(document);
                while (!stack.isEmpty())
                    publishToMQTT(stack.pop());
                if (documents.first() != null)
                    lastObjectId = documents.first().getObjectId("_id");
                sleep(tempoRequery);
                //System.out.println(sensor+": Slept for "+tempoRequery/1000 +" seconds");
            } catch (Exception e) {
                System.out.println(sensor + ": Shutting down");
                break;
            }
        }
    }

    private void publishToMQTT(Document doc) {
        System.out.println(sensor + ": Json -> " + doc.toJson());
        Pair<Boolean, Integer> zona = getZona(doc);
        Pair<Boolean, Timestamp> dataHora = getData(doc);
        Timestamp dataHoraObjectId = new Timestamp(Long.parseLong(doc.getObjectId("_id").toString().substring(0, 8), 16) * 1000);
        Triplet<Boolean, Boolean, Double> leitura = getMedicao(doc);
        String json = null;
        boolean invalido = zona.getValue0() || dataHora.getValue0() || leitura.getValue0();
        boolean excluido = leitura.getValue1();

        if (invalido)
            json = doc.toJson();

        String content = zona.getValue1() + ";" + sensor + ";" + dataHora.getValue1() + ";" + dataHoraObjectId + ";" + calculoMediaMovel(leitura.getValue2()) + ";" + invalido + ";" + excluido + ";" + json;
        //System.out.println(content);

        MqttMessage message = new MqttMessage(content.getBytes());

        if (invalido)
            message.setQos(QoS_anomalies);
        else
            message.setQos(QoS_readings);

        try {
            publisher.publish(mqttTopic, message);

            if (!(invalido && excluido))
                lastLeitura = leitura.getValue2();

            lastObjectId = doc.getObjectId("_id");
            lastTimestamps.deleteOne(eq("Sensor", sensor.getId()));
            HashMap<String, Object> map = new HashMap<>();
            map.put("lastTimestampOID", lastObjectId);
            map.put("Sensor", sensor.getId());

            lastTimestamps.insertOne(new Document(map));

        } catch (MqttException e) {
            e.printStackTrace();
        }
    }

    private Double calculoMediaMovel(Double medicao) {
        if (medicao == null)
            return null;
        if (mediaMovel == null)
            return mediaMovel = medicao;
        return mediaMovel = alpha * medicao + (1 - alpha) * mediaMovel;
    }

    private Pair<Boolean, Integer> getZona(Document doc) {
        try {
            return new Pair<>(false, Character.getNumericValue(doc.getString("Zona").charAt(1)));
        } catch (Exception e) {
            return new Pair<>(true, null);
        }
    }

    private Pair<Boolean, Timestamp> getData(Document doc) {
        try {
            return new Pair<>(false, Timestamp.from(Instant.parse(doc.getString("Data"))));
        } catch (Exception e) {
            return new Pair<>(true, null);
        }
    }

    /**
     * Processes Parameter "Medicao"
     *
     * @param doc (JSON doc from MongoDB)
     * @return null if invalid
     */
    public Triplet<Boolean, Boolean, Double> getMedicao(Document doc) {
        Double leitura = null;
        try {
            leitura = Double.parseDouble(doc.getString("Medicao").replace(',', '.'));
            if (!leituraOutOfBounds(leitura)) {
                if (lastLeitura == null)
                    return new Triplet<>(false, true, leitura);
                return new Triplet<>(false, verificarSalto && (Math.abs(leitura - lastLeitura) > maxJump), leitura);
            }
        } catch (Exception e) {
        }
        return new Triplet<>(true, true, leitura);
    }

    private boolean leituraOutOfBounds(Double leitura) {
        return leitura > sensor.getLimiteSuperior() || leitura < sensor.getLimiteInferior();
    }

}
