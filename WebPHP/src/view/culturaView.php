<?php
include("$_SERVER[DOCUMENT_ROOT]/includes/class-autoload.php");

class CulturaView extends CulturaModel
{
    public function __construct()
    {
    }

    public function getCurrentUserCulturas()
    {
        error_log(__METHOD__);
        error_log(isset($_SESSION['userId']));
        error_log('UserId = '.$_SESSION['userId']);
        // echo "UserId: ".$_SESSION['userId']."<br>";
        return $this->getUserCulturas($_SESSION['userId']);
    }

    public function redirectIfCantAccessCultura($idCultura)
    { 
        error_log(__METHOD__);
        error_log('idCultura = '.$idCultura);
        if(!(is_int($idCultura) || is_numeric($idCultura)))
        {
            header("location: ../../index.php");
        }

        if(!$this->canUserAccessCultura($_SESSION['userId'], $idCultura))
        {
            header("location: ../../index.php");
        }
    }

    public function getCulturaInfo($idCultura)
    { 
        error_log(__METHOD__);
        $this->redirectIfCantAccessCultura($idCultura);
        return $this->getCultura($idCultura);
    }

    public function getTipoParametrosToRender($idCultura)
    {
        error_log(__METHOD__);
        $this->redirectIfCantAccessCultura($idCultura);
        $tipos = $this->getTipoParametros();
        $tiposCultura = $this->getTipoParametrosDeCultura($idCultura);

        foreach ($tipos as  $key => $fields) {
            $codigo = $fields['CodigoParametro'];
            switch ($codigo) {
                case 'H':
                    $tipos[$key]['NomeParametro'] = 'Humidade';
                    if ($this->tipoParametroExistsInCultura($codigo, $tiposCultura))
                        $tipos[$key]['Exists'] = true;
                    break;
                case 'L':
                    $tipos[$key]['NomeParametro'] = 'Luminosidade';
                    if ($this->tipoParametroExistsInCultura($codigo, $tiposCultura))
                        $tipos[$key]['Exists'] = true;
                    break;
                case 'T':
                    $tipos[$key]['NomeParametro'] = 'Temperatura';
                    if ($this->tipoParametroExistsInCultura($codigo, $tiposCultura))
                        $tipos[$key]['Exists'] = true;
                    break;
            }
        }

        return $tipos;
    }

    public function getParametroCulturaInfo($idCultura, $codigoParametro)
    {
        error_log(__METHOD__);
        $this->redirectIfCantAccessCultura($idCultura);
        $parametroCultura = $this->getParametroDeCultura($idCultura, $codigoParametro);
 
        return $parametroCultura;
    }

    private function tipoParametroExistsInCultura($tipo, $tiposInCultura)
    {
        error_log(__METHOD__);
        foreach ($tiposInCultura as  $key => $fields) {
            if ($fields['CodigoParametro'] === $tipo) {
                return true;
            }
        }

        return false;
    }
}
