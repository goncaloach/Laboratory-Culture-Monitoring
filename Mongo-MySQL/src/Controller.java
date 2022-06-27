import com.mongodb.MongoClient;
import com.mongodb.MongoClientURI;
import com.mongodb.client.FindIterable;
import com.mongodb.client.MongoCollection;
import org.bson.Document;
import org.ini4j.Wini;
import org.javatuples.Triplet;

import java.io.File;
import java.io.IOException;
import java.sql.*;
import java.util.*;

import static com.mongodb.client.model.Filters.eq;

public class Controller {

    //Mongo Local
    private String localServer;
    private String localDatabase;
    private String localCollection;
    private String lastTimestampsCollection;
    private String MySqlBackupSensorCollection;

    //MySQL Cloud
    private String mysql_cloudServer;
    private String mysqlcloud_username;
    private String mysqlcloud_password;
    private String mysql_cloudDatabase;
    private String mysql_cloudTable;

    //MySQL Local
    private String mysql_localServer;
    private String mysqllocal_username;
    private String mysqllocal_password;
    private String mysql_localDatabase;

    //Parametros
    private int medicoesPorSensor;
    private int tempoRequery; //milliseconds
    private int tempo_checkThreads;
    private double maxJumpT;
    private double maxJumpL;
    private double maxJumpH;
    private boolean verificarSaltoT;
    private boolean verificarSaltoL;
    private boolean verificarSaltoH;
    private Double Talpha = null;
    private Double Lalpha = null;
    private Double Halpha = null;

    //MQTT
    private boolean migracaoMQTT;
    private String mqttTopic;
    private String brokerUrl;
    private String prefixPublisherId;
    private int QoS_readings;
    private int QoS_anomalies;

    private MongoClient mongoClient;
    private MongoCollection<Document> labMedicoes;
    private MongoCollection<Document> lastTimestamps;
    private Connection myLabConnection;
    private ArrayList<Sensor> sensors = new ArrayList<>();
    private ArrayList<Migrator> migrators = new ArrayList<>();
    private AnomalyMigrator anomalyMigrator;

    public Controller() {
        parseIni();
        connectToMongo();
        connectToCloudMySQL();
        if (!migracaoMQTT) {
            System.out.println("Using direct migration");
            connectToMySQL();
            initializeMigrators();
            new insertCommandsFromConsole().start();
            controlMigrators();
        } else{
            System.out.println("Using MQTT migration");
            initializeMqttMigrators();
        }

    }

    private void parseIni() {
        try {
            Wini ini = new Wini(new File("..\\DataTransfer.ini"));

            //Mongo Local
            boolean using_replica_set = ini.get("Mongo Local", "using_replica_set", boolean.class);
            String servidor_local = ini.get("Mongo Local", "servidor_local", String.class);
            localServer = "mongodb://" + servidor_local;
            if (using_replica_set) {
                String replicaSet = ini.get("Mongo Local", "replica_set", String.class);
                localServer = localServer + "/?replicaSet=" + replicaSet;
            }
            localDatabase = ini.get("Mongo Local", "database", String.class);
            localCollection = ini.get("Mongo Local", "collection", String.class);
            MySqlBackupSensorCollection = ini.get("Mongo Local", "MySqlBackupSensorCollection", String.class);

            //Parametros
            medicoesPorSensor = ini.get("Parametros", "medicoesPorSensor", int.class);
            tempoRequery = ini.get("Parametros", "tempo_requery", int.class);
            tempo_checkThreads = ini.get("Parametros", "tempo_checkThreads", int.class);
            maxJumpT = ini.get("Parametros", "maxJumpT", double.class);
            maxJumpL = ini.get("Parametros", "maxJumpL", double.class);
            maxJumpH = ini.get("Parametros", "maxJumpH", double.class);
            verificarSaltoT = ini.get("Parametros", "verificarSaltoT", boolean.class);
            verificarSaltoL = ini.get("Parametros", "verificarSaltoL", boolean.class);
            verificarSaltoH = ini.get("Parametros", "verificarSaltoH", boolean.class);

            //MySQL Cloud
            String servidormysql_cloud = ini.get("MySQL Cloud", "servidormysql_cloud", String.class);
            mysqlcloud_username = ini.get("MySQL Cloud", "username", String.class);
            mysqlcloud_password = ini.get("MySQL Cloud", "password", String.class);
            mysql_cloudDatabase = ini.get("MySQL Cloud", "database", String.class);
            mysql_cloudServer = "jdbc:mysql://" + servidormysql_cloud + "/" + mysql_cloudDatabase;
            mysql_cloudTable = ini.get("MySQL Cloud", "table", String.class);

            //MySQL Local
            String servidormysql_local = ini.get("MySQL Local", "servidormysql_local", String.class);
            mysqllocal_username = ini.get("MySQL Local", "username", String.class);
            mysqllocal_password = ini.get("MySQL Local", "password", String.class);
            mysql_localDatabase = ini.get("MySQL Local", "database", String.class);
            mysql_localServer = "jdbc:mysql://" + servidormysql_local + "/" + mysql_localDatabase + "?jdbcCompliantTruncation=false";

            //MQTT
            migracaoMQTT = ini.get("MQTT", "migracaoMQTT", boolean.class);
            if (migracaoMQTT) {
                mqttTopic = ini.get("MQTT", "topic", String.class);
                brokerUrl = ini.get("MQTT", "brokerUrl", String.class);
                prefixPublisherId = ini.get("MQTT", "PrefixPublisherId", String.class);
                QoS_readings = ini.get("MQTT", "QoS_readings", int.class);
                QoS_anomalies = ini.get("MQTT", "QoS_anomalies", int.class);
                lastTimestampsCollection = ini.get("Mongo Local", "lastTimestampsCollection", String.class);
                Talpha = ini.get("Parametros", "Talpha", double.class);
                Lalpha = ini.get("Parametros", "Lalpha", double.class);
                Halpha = ini.get("Parametros", "Halpha", double.class);
            }

        } catch (IOException e) {
            System.out.println("An error occured while trying to read the .ini file");
            e.printStackTrace();
            System.exit(1);
        }
    }

    private void connectToMongo() {
        try {
            mongoClient = new MongoClient(new MongoClientURI(localServer));
            labMedicoes = mongoClient.getDatabase(localDatabase).getCollection(localCollection);
            labMedicoes.find().first();
            if (migracaoMQTT) {
                lastTimestamps = mongoClient.getDatabase(localDatabase).getCollection(lastTimestampsCollection);
                lastTimestamps.find().first();
            }
        } catch (Exception e) {
            e.printStackTrace();
            System.out.println("Error while connecting to labDB");
            System.exit(1);
        }
    }

    /**
     * Gets a resultSet (list of sensors from MySQLCloud)
     */
    private void connectToCloudMySQL() {
        MongoCollection<Document> mySqlBackupSensor = mongoClient.getDatabase(localDatabase).getCollection(MySqlBackupSensorCollection);
        try {
            Connection cloudMySQLConnection = DriverManager.getConnection(mysql_cloudServer, mysqlcloud_username, mysqlcloud_password);
            Statement statement = cloudMySQLConnection.createStatement();
            ResultSet resultSet = statement.executeQuery("select * from " + mysql_cloudDatabase + "." + mysql_cloudTable);
            mySqlBackupSensor.drop();
            getSensorsMySQCloud(resultSet, mySqlBackupSensor);
        } catch (Exception e) {
            System.out.println("Error while connecting to " + mysql_cloudDatabase);
            e.printStackTrace();
            System.out.println("Getting sensor backed information from " + localDatabase);
            getBackedSensorInformation(mySqlBackupSensor);
        }
    }

    private void getBackedSensorInformation(MongoCollection<Document> mySqlBackupSensor) {
        FindIterable<Document> documents = mySqlBackupSensor.find();
        if (documents.first() == null) {
            System.out.println("No backed sensor information available");
            System.exit(1);
        }
        for (Document doc : documents) {
            String sensor = doc.getString("Sensor");
            double limiteinferior = doc.getDouble("LimiteInferior");
            double limitesuperior = doc.getDouble("LimiteSuperior");
            sensors.add(new Sensor(sensor, limitesuperior, limiteinferior));
        }
    }

    /**
     * Initializes List of sensors
     *
     * @param resultSet connectToCloudMySQL()
     * @throws SQLException resultSet empty
     */
    private void getSensorsMySQCloud(ResultSet resultSet, MongoCollection<Document> mySqlBackupSensor) throws SQLException {
        while (resultSet.next()) {
            String sensor = resultSet.getString("tipo") + resultSet.getInt("idsensor");
            double limiteinferior = resultSet.getDouble("limiteinferior");
            double limitesuperior = resultSet.getDouble("limitesuperior");

            HashMap<String, Object> map = new HashMap<>();
            map.put("Sensor", sensor);
            map.put("LimiteInferior", limiteinferior);
            map.put("LimiteSuperior", limitesuperior);
            mySqlBackupSensor.insertOne(new Document(map));

            sensors.add(new Sensor(sensor, limitesuperior, limiteinferior));
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

    private void initializeMigrators() {
        for (Sensor sensor : sensors) {
            Triplet<Boolean, Double, Double> params = associateParameters(sensor.getId());
            Migrator m = new Migrator(labMedicoes, myLabConnection, sensor,
                    medicoesPorSensor, tempoRequery, params.getValue1(), params.getValue0());
            migrators.add(m);
            m.start();
        }
        anomalyMigrator = new AnomalyMigrator(labMedicoes, myLabConnection, new Sensor("AM", 0, 0),
                medicoesPorSensor, tempoRequery, sensors);
        anomalyMigrator.start();
    }

    private void initializeMqttMigrators() {
        for (Sensor sensor : sensors) {
            Triplet<Boolean, Double, Double> params = associateParameters(sensor.getId());
            new MqttPublisher(labMedicoes, lastTimestamps, sensor, medicoesPorSensor, tempoRequery, params.getValue1(),
                    params.getValue0(), mqttTopic, brokerUrl, prefixPublisherId, QoS_readings, QoS_anomalies, params.getValue2()).start();
        }
    }

    private Triplet<Boolean, Double, Double> associateParameters(String sensor) {
        switch (sensor.charAt(0)) {
            case 'T' -> {
                return new Triplet<>(verificarSaltoT, maxJumpT, Talpha);
            }
            case 'L' -> {
                return new Triplet<>(verificarSaltoL, maxJumpL, Lalpha);
            }
            case 'H' -> {
                return new Triplet<>(verificarSaltoH, maxJumpH, Halpha);
            }
        }
        return new Triplet<>(null, null, null);
    }


    private void controlMigrators() {
        while (true) {
            for (Migrator migrator : migrators) {
                if (!migrator.isAlive()) {
                    System.out.println("Controller: " + migrator.getSensor() + " is down");
                    Migrator newMigrator = new Migrator(labMedicoes, myLabConnection, migrator.getSensor(),
                            medicoesPorSensor, tempoRequery, migrator.getMaxJump(), migrator.getVerificarSalto());
                    migrators.add(newMigrator);
                    newMigrator.start();
                    System.out.println("Controller: " + migrator.getSensor() + " restarted");
                    migrators.remove(migrator);
                    break;
                }
            }
            if (!anomalyMigrator.isAlive()) {
                System.out.println("Controller: AnomalyMigrator is down");
                anomalyMigrator = new AnomalyMigrator(labMedicoes, myLabConnection,
                        new Sensor("AM", 0, 0), medicoesPorSensor, tempoRequery, sensors);
                anomalyMigrator.start();
                System.out.println("Controller: anomalyMigrator restarted");
            }
            try {
                Thread.sleep(tempo_checkThreads);
            } catch (InterruptedException e) {
                e.printStackTrace();
            }
        }
    }


    private class insertCommandsFromConsole extends Thread { //syntax = SHUTDOWN <sensor>  || ADD <sensor>

        @Override
        public void run() {
            System.out.println("Controller: Listening for commands...");
            Scanner scanner = new Scanner(System.in);
            while (scanner.hasNextLine()) {
                String line = scanner.nextLine();
                String[] args = line.split("\\s+");
                if (checkArguments(args)) {
                    if (args[0].equals("SHUTDOWN"))
                        shutdownMigrator(args[1]);
                    if (args[0].equals("START"))
                        startMigrator(args[1]);
                }
            }
        }

        private boolean checkArguments(String[] args) {
            if (args.length != 2 || !(args[0].equals("SHUTDOWN") || args[0].equals("START")) || args[1].length() != 2) {
                System.out.println("Wrong Arguments");
                return false;
            }
            return true;
        }

        private void shutdownMigrator(String sensorSTR) {
            for (Migrator migrator : migrators) {
                if (migrator.getSensor().getId().equals(sensorSTR)) {
                    migrator.interrupt();
                    migrators.remove(migrator);
                    sensors.removeIf(sensor -> sensor.getId().equals(sensorSTR));
                    return;
                }
            }
            System.out.println("Invalid Sensor");
        }

        private void startMigrator(String sensorSTR) {
            for (Sensor sens : sensors) {
                if (sens.getId().equalsIgnoreCase(sensorSTR)) {
                    System.out.println("This Migrator is already running");
                    return;
                }
            }
            try {
                Connection cloudMySQLConnection = DriverManager.getConnection(mysql_cloudServer, mysqlcloud_username, mysqlcloud_password);
                Statement statement = cloudMySQLConnection.createStatement();
                String query = "select * from " + mysql_cloudDatabase + "." + mysql_cloudTable +
                        " WHERE idsensor = '" + sensorSTR.charAt(1) + "' AND tipo = '" + sensorSTR.charAt(0) + "'";
                ResultSet resultSet = statement.executeQuery(query);
                if (resultSet.next()) {
                    double limiteinferior = resultSet.getDouble("limiteinferior");
                    double limitesuperior = resultSet.getDouble("limitesuperior");
                    Sensor s = new Sensor(sensorSTR, limitesuperior, limiteinferior);
                    sensors.add(s);
                    Triplet<Boolean, Double, Double> params = associateParameters(s.getId());
                    Migrator m = new Migrator(labMedicoes, myLabConnection, s, medicoesPorSensor, tempoRequery,
                            params.getValue1(), params.getValue0());
                    migrators.add(m);
                    m.start();
                } else
                    System.out.println("Invalid Sensor");
            } catch (Exception e) {
                System.out.println("Error while connecting to " + mysql_cloudDatabase);
                e.printStackTrace();

                System.out.println("Getting sensor backed information from "+localDatabase);
                MongoCollection<Document> mySqlBackupSensor = mongoClient.getDatabase(localDatabase).getCollection(MySqlBackupSensorCollection);
                Document doc = mySqlBackupSensor.find(eq("Sensor",sensorSTR)).first();
                if(doc==null)
                    System.out.println("Invalid sensor");
                else {
                    double limiteSuperior = doc.getDouble("LimiteSuperior");
                    double limiteInferior = doc.getDouble("LimiteInferior");
                    Sensor s = new Sensor(sensorSTR, limiteSuperior, limiteInferior);
                    sensors.add(s);
                    Triplet<Boolean, Double, Double> params = associateParameters(s.getId());
                    Migrator m = new Migrator(labMedicoes, myLabConnection, s, medicoesPorSensor, tempoRequery,
                            params.getValue1(), params.getValue0());
                    migrators.add(m);
                    m.start();
                }
            }
        }
    }


}