<?php

class CulturaController extends CulturaModel
{

    public function editCultura($idCultura, $nome, $minutos, $ativo)
    {
        $this->updateCultura($idCultura, $nome, $minutos, $ativo);
    }

    public function addParametroCultura($idCultura, $codigoParam, $min, $max, $minAviso, $minUrgente, $maxAviso, $maxUrgente)
    {
        $this->createParametroCultura($idCultura, $codigoParam, $min, $max, $minAviso, $minUrgente, $maxAviso, $maxUrgente);
    }

    public function editParametroCultura($idCultura, $codigoParam, $min, $max, $minAviso, $minUrgente, $maxAviso, $maxUrgente)
    {
        $this->updateParametroCultura($idCultura, $codigoParam, $min, $max, $minAviso, $minUrgente, $maxAviso, $maxUrgente);
    }

    public function removeParametroCultura($idCultura, $codigoParam)
    {
        $this->deleteParametroCultura($idCultura, $codigoParam);
    }
}
