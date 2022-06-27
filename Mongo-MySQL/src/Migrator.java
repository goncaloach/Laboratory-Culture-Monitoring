import com.mongodb.client.FindIterable;
import com.mongodb.client.MongoCollection;
import org.bson.Document;
import org.bson.types.ObjectId;
import org.javatuples.Pair;
import org.javatuples.Triplet;

import java.sql.*;
import java.time.Instant;
import java.util.Stack;

import static com.mongodb.client.model.Filters.*;

public class Migrator extends Thread {

    private final Sensor sensor;
    private final double maxJump;
    private final int medicoesPorSensor;
    private final int tempoRequery;
    private final boolean verificarSalto;

    private final MongoCollection<Document> labMedicoes;
    private final Connection myLabConnection;

    private Double lastLeitura = null;
    private ObjectId lastObjectId = new ObjectId("000000000000000000000000");

    public Migrator(MongoCollection<Document> labMedicoes, Connection myLabConnection,
                    Sensor sensor, int medicoesAoArrancar, int tempoRequery, double maxJump, boolean verificarSalto) {
        this.labMedicoes = labMedicoes;
        this.myLabConnection = myLabConnection;
        this.sensor=sensor;
        this.medicoesPorSensor = medicoesAoArrancar;
        this.tempoRequery = tempoRequery;
        this.maxJump = maxJump;
        this.verificarSalto = verificarSalto;
    }

    @Override
    public void run() {
        System.out.println(sensor + ": Migrator started");
        initializeData();
        while (true) {
            try {
                FindIterable<Document> documents = filterDocuments();
                Stack<Document> stack = new Stack<>();
                for (Document document : documents)
                    stack.push(document);
                while(!stack.isEmpty())
                    sendDataToMySQL(stack.pop());
                if (documents.first() != null)
                    lastObjectId = documents.first().getObjectId("_id");
                sleep(tempoRequery);
                //System.out.println(sensor+": Slept for "+tempoRequery/1000 +" seconds");
            } catch (Exception e) {
                System.out.println(sensor + ": Shutting down");
                break;
            }
        }
        System.out.println(sensor + ": Terminated");
    }

    public FindIterable<Document> filterDocuments() {
        return labMedicoes.find(and(gt("_id",lastObjectId), eq("Sensor", sensor.getId()))).sort(new Document("_id", -1)).limit(medicoesPorSensor);
    }

    /**
     * Goes to MySQL and gets last DataHoraObjectId
     */
    private void initializeData() {
        try {
            Statement statement = myLabConnection.createStatement();
            String query = "SELECT DataHoraObjectId FROM medicao WHERE Sensor = '" + sensor.getId() + "' ORDER BY DataHoraObjectId DESC LIMIT 1";
            ResultSet resultSet = statement.executeQuery(query);
            resultSet.next();
            Timestamp timestamp = resultSet.getTimestamp("DataHoraObjectId");
            lastObjectId = new ObjectId(Long.toHexString(timestamp.getTime() /1000L) + "FFFFFFFFFFFFFFFF");
            System.out.println(sensor + ": Last timestamp of measurement = " + timestamp);
            System.out.println(sensor + ": Last objectId of measurement = " + lastObjectId.toString());
        } catch (SQLException e) { //First time launch
            System.out.println(sensor + ": No data from this sensor in table \"medicao\"");
        }
    }

    /**
     * Processes data from doc and sends it to MySQL
     * @param doc (JSON doc from MongoDB)
     */
    public void sendDataToMySQL(Document doc) {
        System.out.println(sensor + ": Json -> " + doc.toJson());
        Pair<Boolean, Integer> zona = getZona(doc);
        Pair<Boolean, Timestamp> dataHora = getData(doc);
        Timestamp dataHoraObjectId = new Timestamp(Long.parseLong(doc.getObjectId("_id").toString().substring(0, 8), 16) * 1000);
        Triplet<Boolean,Boolean,Double> leitura = getMedicao(doc);
        String json = null;
        boolean invalido = zona.getValue0() || dataHora.getValue0() || leitura.getValue0();
        boolean excluido = leitura.getValue1();

        if (invalido)
            json = doc.toJson();

        String query = "{CALL CriarMedicao(?,?,?,?,?,?,?,?)}";
        try {
            CallableStatement stmt = myLabConnection.prepareCall(query);
            stmt.setObject(1, zona.getValue1(), Types.INTEGER);
            stmt.setObject(2, sensor.getId(), Types.VARCHAR);
            stmt.setTimestamp(3, dataHora.getValue1());
            stmt.setTimestamp(4, dataHoraObjectId);
            stmt.setObject(5, leitura.getValue2(), Types.DOUBLE);
            stmt.setBoolean(6, invalido);
            stmt.setBoolean(7, excluido);
            stmt.setObject(8, json, Types.LONGNVARCHAR);
            stmt.executeQuery();

            if ( !(invalido && excluido))
                lastLeitura = leitura.getValue2();

        } catch (SQLException e) {
            System.out.println(sensor+ " :Error while sending data to MyLab");
            e.printStackTrace();
        }
    }

    private Pair<Boolean,Integer> getZona(Document doc) {
        try {
            return new Pair<>(false,Character.getNumericValue(doc.getString("Zona").charAt(1)));
        } catch (Exception e) {
            return new Pair<>(true,null);
        }
    }

    private Pair<Boolean,Timestamp> getData(Document doc) {
        try {
            return new Pair<>(false,Timestamp.from(Instant.parse(doc.getString("Data"))));
        } catch (Exception e) {
            return new Pair<>(true,null);
        }
    }

    /**
     * Processes Parameter "Medicao"
     * @param doc (JSON doc from MongoDB)
     * @return null if invalid
     */
    public Triplet<Boolean,Boolean,Double> getMedicao(Document doc) {
        Double leitura = null;
        try {
            leitura = Double.parseDouble(doc.getString("Medicao").replace(',','.'));
            if (!leituraOutOfBounds(leitura)){
                if(lastLeitura==null)
                    return new Triplet<>(false,true,leitura);
                return new Triplet<>(false, verificarSalto && (Math.abs(leitura - lastLeitura) > maxJump),leitura);
            }
        } catch (Exception e) {}
        return new Triplet<>(true,true,leitura);
    }

    private boolean leituraOutOfBounds(Double leitura) {
        return leitura > sensor.getLimiteSuperior() || leitura < sensor.getLimiteInferior();
    }

    public Sensor getSensor() {
        return sensor;
    }

    public double getMaxJump() {
        return maxJump;
    }

    public ObjectId getLastObjectId() {
        return lastObjectId;
    }

    public MongoCollection<Document> getLabMedicoes() {
        return labMedicoes;
    }

    public int getMedicoesPorSensor() {
        return medicoesPorSensor;
    }

    public boolean getVerificarSalto() {
        return verificarSalto;
    }
}