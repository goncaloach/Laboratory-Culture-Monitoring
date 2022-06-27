public class Sensor {

    private final String Id;
    private final double limiteSuperior;
    private final double limiteInferior;

    public Sensor(String id, double limiteSuperior, double limiteInferior) {
        this.Id = id;
        this.limiteSuperior = limiteSuperior;
        this.limiteInferior = limiteInferior;
    }

    public String getId() {
        return Id;
    }

    public double getLimiteSuperior() {
        return limiteSuperior;
    }

    public double getLimiteInferior() {
        return limiteInferior;
    }

    @Override
    public String toString() {
        return Id;
    }
}
