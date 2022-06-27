<?php
// include '../includes/class-autoload.php';


class CulturaModel extends Dbh
{

    protected function canUserAccessCultura($idUtilizador, $idCultura)
    {
        $stmt = $this->connect()->prepare('select * from cultura where IdUtilizador = ? and IdCultura = ?');
        $stmt->execute([$idUtilizador, $idCultura]);

        $queryResults = $stmt->fetchAll(PDO::FETCH_ASSOC);
        // echo "Nr culturas: ".sizeof($queryResults)."<br>";

        return sizeof($queryResults) == 0 ? false : true;
    }

    protected function getUserCulturas($idUtilizador)
    {
        $stmt = $this->connect()->prepare('select * from cultura where IdUtilizador = ?');
        $stmt->execute([$idUtilizador]);

        $queryResults = $stmt->fetchAll(PDO::FETCH_ASSOC);
        // echo "Nr culturas: ".sizeof($queryResults)."<br>";

        return $queryResults;
    }

    protected function getCultura($idCultura)
    {
        $stmt = $this->connect()->prepare('select * from cultura where IdCultura = ?');
        $stmt->execute([$idCultura]);

        $queryResults = $stmt->fetchAll(PDO::FETCH_ASSOC);
        // echo "Nr culturas: ".sizeof($queryResults)."<br>";

        return $queryResults[0];
    }

    protected function getTipoParametros()
    {
        $stmt = $this->connect()->prepare('select * from tipoparametro');
        $stmt->execute();
        $queryResults = $stmt->fetchAll(PDO::FETCH_ASSOC);
        // echo "Nr tipos: ".sizeof($queryResults)."<br>";

        return $queryResults;
    }

    protected function getTipoParametrosDeCultura($idCultura)
    {
        $stmt = $this->connect()->prepare('select CodigoParametro from parametrocultura where IdCultura = ?');
        $stmt->execute([$idCultura]);
        $queryResults = $stmt->fetchAll(PDO::FETCH_ASSOC);
        // echo "Nr tipo de params de cultura: ".sizeof($queryResults)."<br>";

        return $queryResults;
    }

    protected function getParametroDeCultura($idCultura, $codigoParametro)
    {
        $stmt = $this->connect()->prepare('select * from parametrocultura where IdCultura = :idCultura and CodigoParametro like :codigoParametro');
        $stmt->bindValue(':idCultura', $idCultura, PDO::PARAM_INT);
        $stmt->bindValue(':codigoParametro', $codigoParametro, PDO::PARAM_STR);
        $stmt->execute();
        $queryResults = $stmt->fetchAll(PDO::FETCH_ASSOC);
        // echo "Nr params de cultura: ".sizeof($queryResults)."<br>";
        return $queryResults[0];
    }

    protected function createParametroCultura($idCultura, $codigoParam, $min, $max, $minAviso, $minUrgente, $maxAviso, $maxUrgente)
    {
        try {

            // $stmt = $this->connect()->prepare('insert into parametrocultura (CodigoParametro, IdCultura, Min, Min_Urgente, Min_Aviso, Max, Max_Urgente, Max_Aviso) 
            //     values(:CodigoParametro, :IdCultura, NULLIF(:Min, \'\'), NULLIF(:Min_Urgente, \'\'), NULLIF(:Min_Aviso, \'\'), NULLIF(:Max, \'\'), NULLIF(:Max_Urgente, \'\'), NULLIF(:Max_Aviso, \'\'))');
            $stmt = $this->connect()->prepare('call CriarParametroCultura(:IdCultura, :CodigoParametro , NULLIF(:Min, \'\'), NULLIF(:Min_Urgente, \'\'), NULLIF(:Min_Aviso, \'\'), NULLIF(:Max, \'\'), NULLIF(:Max_Urgente, \'\'), NULLIF(:Max_Aviso, \'\'))');
            $stmt->bindValue(':CodigoParametro', $codigoParam, PDO::PARAM_STR);
            $stmt->bindValue(':IdCultura', $idCultura, PDO::PARAM_INT);
            $stmt->bindValue(':Min', $min, PDO::PARAM_STR);
            $stmt->bindValue(':Min_Urgente', $minUrgente, PDO::PARAM_STR);
            $stmt->bindValue(':Min_Aviso', $minAviso, PDO::PARAM_STR);
            $stmt->bindValue(':Max', $max, PDO::PARAM_STR);
            $stmt->bindValue(':Max_Urgente', $maxUrgente, PDO::PARAM_STR);
            $stmt->bindValue(':Max_Aviso', $maxAviso, PDO::PARAM_STR);
            $stmt->execute();
            $queryResults = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (strtolower(array_key_first($queryResults[0])) == strtolower("ERRO"))
                error_log(__METHOD__ . " Error on creating parametro de cultura. cultura: " . $idCultura . " parametro: " . $codigoParam);

            // echo "UserId: ".$_SESSION['userId']."<br>";
            return $queryResults;
        } catch (Exception $error) {
            error_log($error);
        }
    }

    protected function updateParametroCultura($idCultura, $codigoParam, $min, $max, $minAviso, $minUrgente, $maxAviso, $maxUrgente)
    {
        try {

            $stmt = $this->connect()->prepare('call AlterarParametroCultura(:idCultura, :CodigoParametro, NULLIF(:Min, \'\'), NULLIF(:Min_Urgente, \'\'), NULLIF(:Min_Aviso, \'\'), NULLIF(:Max, \'\'), NULLIF(:Max_Urgente, \'\'), NULLIF(:Max_Aviso, \'\'))');
            $stmt->bindValue(':idCultura', $idCultura, PDO::PARAM_INT);
            $stmt->bindValue(':CodigoParametro', $codigoParam, PDO::PARAM_STR);
            $stmt->bindValue(':Min', $min, PDO::PARAM_STR);
            $stmt->bindValue(':Min_Urgente', $minUrgente, PDO::PARAM_STR);
            $stmt->bindValue(':Min_Aviso', $minAviso, PDO::PARAM_STR);
            $stmt->bindValue(':Max', $max, PDO::PARAM_STR);
            $stmt->bindValue(':Max_Urgente', $maxUrgente, PDO::PARAM_STR);
            $stmt->bindValue(':Max_Aviso', $maxAviso, PDO::PARAM_STR);
            $stmt->execute();
            $queryResults = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (strtolower(array_key_first($queryResults[0])) == strtolower("ERRO"))
                error_log(__METHOD__ . " Error on updating parametro de cultura. cultura: " . $idCultura . " parametro: " . $codigoParam);

            // echo "UserId: ".$_SESSION['userId']."<br>";
            return $queryResults;
        } catch (Exception $error) {
            error_log($error);
        }
    }

    protected function deleteParametroCultura($idCultura, $codigoParam)
    {
        // error_log($idCultura);
        // error_log($codigoParam);
        $stmt = $this->connect()->prepare('call RemoverParametroCultura(:IdCultura, :CodigoParametro);');
        $stmt->bindValue(':CodigoParametro', $codigoParam, PDO::PARAM_STR);
        $stmt->bindValue(':IdCultura', $idCultura, PDO::PARAM_INT);
        $stmt->execute();
        $queryResults = $stmt->fetchAll(PDO::FETCH_ASSOC);
        // echo "UserId: ".$_SESSION['userId']."<br>";
        return $queryResults;
    }


    protected function updateCultura($idCultura, $nome, $minutos, $ativo)
    {
        try {

            $stmt = $this->connect()->prepare('call AlterarCultura(:IdCultura, :NomeCultura, :MinutosRealerta, :Ativo)');
            $stmt->bindValue(':IdCultura', $idCultura, PDO::PARAM_INT);
            $stmt->bindValue(':NomeCultura', $nome, PDO::PARAM_STR);
            $stmt->bindValue(':MinutosRealerta', $minutos, PDO::PARAM_INT);
            $stmt->bindValue(':Ativo', $ativo, PDO::PARAM_INT);
            $stmt->execute();
            $queryResults = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (sizeof($queryResults) > 0  && strtolower(array_key_first($queryResults[0])) == strtolower("ERRO"))
                error_log(__METHOD__ . " Error on updating cultura. idCultura: " . $idCultura . "; NomeCultura: " . $nome . "; MinutosRealerta: " . $minutos . "; Ativo: " . $ativo);

            // echo "UserId: ".$_SESSION['userId']."<br>";
            return $queryResults;
        } catch (Exception $error) {
            error_log($error);
        }
    }
}
