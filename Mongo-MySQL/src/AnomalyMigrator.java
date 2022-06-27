import com.mongodb.client.FindIterable;
import com.mongodb.client.MongoCollection;
import org.bson.Document;
import org.bson.conversions.Bson;
import org.javatuples.Triplet;

import java.sql.Connection;
import java.util.ArrayList;

import static com.mongodb.client.model.Filters.*;

public class AnomalyMigrator extends Migrator{

    private ArrayList<Bson> eqSensors = new ArrayList<>();

    public AnomalyMigrator(MongoCollection<Document> labMedicoes, Connection myLabConnection,
                           Sensor sensor, int medicoesAoArrancar, int tempoRequery, ArrayList<Sensor> sensors){
        super(labMedicoes, myLabConnection, sensor, medicoesAoArrancar, tempoRequery, 0,false);
        for (Sensor sens: sensors)
            eqSensors.add(eq("Sensor", sens.getId()));
    }

    @Override
    public FindIterable<Document> filterDocuments(){
        return super.getLabMedicoes().find(and(gt("_id", super.getLastObjectId()), nor(eqSensors))).sort(new Document("_id", -1)).limit(super.getMedicoesPorSensor());
    }

    @Override
    public Triplet<Boolean,Boolean,Double> getMedicao(Document doc) {
        Double leitura = null;
        try {
            leitura = Double.parseDouble(doc.getString("Medicao").replace(',','.'));
        } catch (Exception e) {}
        return new Triplet<>(true,true,leitura);
    }

}
