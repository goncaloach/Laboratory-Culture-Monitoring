import com.mongodb.*;
import com.mongodb.MongoClient;
import com.mongodb.client.*;
import org.bson.Document;

import static com.mongodb.client.model.Filters.gt;

import org.bson.types.ObjectId;
import org.ini4j.Wini;

import java.io.File;
import java.io.IOException;
import java.util.LinkedList;

public class Cloud_Mongo {

    static int medicoesAoArrancar;
    static int tempoRequery;
    static MongoCollection<Document> medicoesCloud;
    static MongoCollection<Document> medicoesLocal;

    static String cloudServer;
    static String cloudDatabase;
    static String cloudCollection;

    static String localServer;
    static String localDatabase;
    static String localCollection;

    public static void main(String[] args) throws InterruptedException {
        /*PARSE INI FILE*/
        parseIni();
        //Connect to the remote MongoDB
        connectCloud();
        //Connect to the local MongoDB
        connectLocal();
        //start data Download
        downloader c = new downloader(medicoesCloud, medicoesLocal);
        c.start();
        while (true) {
            c.join();
            c = new downloader(medicoesCloud, medicoesLocal);
            c.start();
        }
    }

    private static class downloader extends Thread {
        private final MongoCollection<Document> medicoesCloud;
        private final MongoCollection<Document> medicoesLocal;

        public downloader(MongoCollection<Document> medicoesCloud, MongoCollection<Document> medicoesLocal) {
            this.medicoesCloud = medicoesCloud;
            this.medicoesLocal = medicoesLocal;
        }

        /**
        Migrate docs from cloud to local DB
        */
        @Override
        public void run() {
            Document lastDoc = medicoesLocal.find().sort(new Document("_id", -1)).first();
            FindIterable<Document> docList;
            LinkedList<Document> docLL = new LinkedList<>();
            /**
             ON START: if local is empty copy number of the latest docs set in variable medicoesAoArrancar from cloud
             */
            if (lastDoc == null) {
                docList = medicoesCloud.find().sort(new Document("_id", -1)).limit(medicoesAoArrancar);
                for (Document document: docList)
                    docLL.addFirst(document);//TODO - não sendo necessário inverter, teriamos sempre de iterar

                if(!docLL.isEmpty()) {
                    medicoesLocal.insertMany(docLL);
                    System.out.println(docLL);//TODO - Remover
                }
                lastDoc = docList.first();
            }

            ObjectId lastId = lastDoc.getObjectId("_id");

            while (true) {
                docList = medicoesCloud.find(gt("_id", lastId)).sort(new Document("_id", -1)).limit(medicoesAoArrancar);//TODO - Fará sentido limit?
                docLL = new LinkedList<>();
                for (Document document: docList) {
                    docLL.addFirst(document);//TODO - não sendo necessário inverter, teriamos sempre de iterar
                }
                if(!docLL.isEmpty()) {
                    medicoesLocal.insertMany(docLL);
                    System.out.println(docLL);//TODO - Remover
                }
                if(docList.first()!=null)
                    lastId = docList.first().getObjectId("_id");
                else
                    System.out.println("No new Documents found!");//TODO - Remover
                try {
                    sleep(tempoRequery);
                } catch (InterruptedException e) {
                    e.printStackTrace();
                }
            }
        }
    }

    private static void parseIni() {
        try {
            Wini ini = new Wini(new File("..\\DataTransfer.ini"));
            medicoesAoArrancar = ini.get("Parametros", "medicoesAoArrancar", int.class);
            tempoRequery = ini.get("Parametros", "tempo_requery", int.class);

            String servidor_cloud = ini.get("Mongo Cloud", "servidor_cloud", String.class);
            String username = ini.get("Mongo Cloud", "username", String.class);
            String password = ini.get("Mongo Cloud", "password", String.class);
            cloudServer = "mongodb://" + username + ":" + password + "@" + servidor_cloud + "/?authSource=admin&authMechanism=SCRAM-SHA-1";
            cloudDatabase = ini.get("Mongo Cloud", "database", String.class);
            cloudCollection = ini.get("Mongo Cloud", "collection", String.class);

            boolean using_replica_set = ini.get("Mongo Local", "using_replica_set", boolean.class);
            String servidor_local = ini.get("Mongo Local", "servidor_local", String.class);
            localServer = "mongodb://" + servidor_local;
            if(using_replica_set) {
                String replicaSet = ini.get("Mongo Local", "replica_set", String.class);
                localServer = localServer + "/?replicaSet=" + replicaSet;
            }
            localDatabase = ini.get("Mongo Local", "database", String.class);
            localCollection = ini.get("Mongo Local", "collection", String.class);
            System.out.println(".ini file parsed!");
        } catch (IOException e) {
            System.out.println("Não foi possivel ler o ficheiro .ini!");
            System.exit(1);
        }
    }

    static private void connectCloud() {
        try {
            System.out.println("A estabelecer ligação com o servidor remoto...");
            MongoClient mongoClient = new MongoClient(new MongoClientURI(cloudServer));
            medicoesCloud = mongoClient.getDatabase(cloudDatabase).getCollection(cloudCollection);
            medicoesCloud.find().first();
            System.out.println("Ligação ao servidor remoto estabelecida!");
        } catch (Exception e) {
            System.out.println("Não foi possivel conectar com o servidor remoto!");
            System.exit(1);
        }
    }

    static private void connectLocal() {
        try {
            System.out.println("A estabelecer ligação com o servidor local...");
            MongoClient mongoClient = new MongoClient(new MongoClientURI(localServer));
            medicoesLocal = mongoClient.getDatabase(localDatabase).getCollection(localCollection);
            medicoesLocal.find().first();
            System.out.println("Ligação ao servidor local estabelecida!");
        } catch (Exception e) {
            System.out.println("Não foi possivel conectar com o servidor local!");
            System.exit(1);
        }
    }
}
