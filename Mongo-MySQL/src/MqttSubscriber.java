import org.eclipse.paho.client.mqttv3.*;
import org.eclipse.paho.client.mqttv3.persist.MemoryPersistence;
import org.ini4j.Wini;

import java.io.File;
import java.io.IOException;
import java.sql.*;

public class MqttSubscriber implements MqttCallback {

    //MySQL Local
    private String mysql_localServer;
    private String mysqllocal_username;
    private String mysqllocal_password;

    //MQTT
    private String brokerUrl;
    private String subscriberId;

    private Connection myLabConnection;

    public MqttSubscriber() {
        parseIni();
        connectToMySQL();
    }

    private void parseIni() {
        try {
            Wini ini = new Wini(new File("..\\DataTransfer.ini"));

            //MySQL Local
            String servidormysql_local = ini.get("MySQL Local", "servidormysql_local", String.class);
            mysqllocal_username = ini.get("MySQL Local", "username", String.class);
            mysqllocal_password = ini.get("MySQL Local", "password", String.class);
            String mysql_localDatabase = ini.get("MySQL Local", "database", String.class);
            mysql_localServer = "jdbc:mysql://" + servidormysql_local + "/" + mysql_localDatabase + "?jdbcCompliantTruncation=false";

            //MQTT
            brokerUrl = ini.get("MQTT", "brokerUrl", String.class);
            subscriberId = ini.get("MQTT", "SubscriberId", String.class);

        } catch (IOException e) {
            System.out.println("An error occured while trying to read the .ini file");
            e.printStackTrace();
            System.exit(1);
        }
    }

    private void connectToMySQL() {
        try {
            myLabConnection = DriverManager.getConnection(mysql_localServer, mysqllocal_username, mysqllocal_password);
        } catch (SQLException e) {
            System.out.println("Error while connecting to myLab");
            e.printStackTrace();
            System.exit(1);
        }
    }

    public void subscribe(String topic) {
        MemoryPersistence persistence = new MemoryPersistence();
        try {
            MqttClient sampleClient = new MqttClient(brokerUrl, subscriberId, persistence);
            MqttConnectOptions connOpts = new MqttConnectOptions();
            connOpts.setCleanSession(true);

            System.out.println("checking");
            System.out.println("Mqtt Connecting to broker: " + brokerUrl);

            sampleClient.connect(connOpts);
            System.out.println("Mqtt Connected");

            sampleClient.setCallback(this);
            sampleClient.subscribe(topic);

            System.out.println("Subscribed");
            System.out.println("Listening");

        } catch (MqttException e) {
            System.out.println(" Error while connecting to Mqtt Broker");
            System.out.println("reason "+e.getReasonCode());
            System.out.println("msg "+e.getMessage());
            System.out.println("loc "+e.getLocalizedMessage());
            System.out.println("cause "+e.getCause());
            System.out.println("excep "+e);
            e.printStackTrace();
            System.exit(1);
        }
    }

    public void connectionLost(Throwable arg0) {
    }

    public void deliveryComplete(IMqttDeliveryToken arg0) {
    }

    public void messageArrived(String topic, MqttMessage message) {
        System.out.println("Message Received: " +message.toString());

        String[] arrayContent = message.toString().split(";");
        Integer zona = arrayContent[0].equals("null")? null : Integer.parseInt(arrayContent[0]);
        String sensor = arrayContent[1];
        Timestamp dataHora = arrayContent[2].equals("null")? null : Timestamp.valueOf(arrayContent[2]);
        Timestamp dataHoraObjectId = Timestamp.valueOf(arrayContent[3]);
        Double leitura = arrayContent[4].equals("null")? null : Double.parseDouble(arrayContent[4]);
        boolean invalido = Boolean.parseBoolean(arrayContent[5]);
        boolean excluido = Boolean.parseBoolean(arrayContent[6]);
        String json = arrayContent[7].equals("null")? null : arrayContent[7];

        String query = "{CALL CriarMedicao(?,?,?,?,?,?,?,?)}";
        try {
            CallableStatement stmt = myLabConnection.prepareCall(query);
            stmt.setObject(1, zona, Types.INTEGER);
            stmt.setObject(2, sensor, Types.VARCHAR);
            stmt.setTimestamp(3, dataHora);
            stmt.setTimestamp(4, dataHoraObjectId);
            stmt.setObject(5, leitura, Types.DOUBLE);
            stmt.setBoolean(6, invalido);
            stmt.setBoolean(7, excluido);
            stmt.setObject(8, json, Types.LONGNVARCHAR);
            stmt.executeQuery();

        } catch (SQLException e) {
            System.out.println("Error while sending data to MyLab");
            e.printStackTrace();
        }
    }

    public static void main(String[] args) {
        System.out.println("Subscriber running");
        String mqttTopic = null;
        try {
            Wini ini = new Wini(new File("..\\DataTransfer.ini"));
            mqttTopic = ini.get("MQTT", "topic", String.class);
        } catch (IOException e) {
            System.out.println("Error while reading from ini");
            e.printStackTrace();
        }
        new MqttSubscriber().subscribe(mqttTopic);
    }
}
