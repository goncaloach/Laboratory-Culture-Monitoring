<?php

include 'class-autoload.php';

if (isset($_POST['submit'])) {
    $idCultura = $_POST['idCultura'];
    $codigoParam = $_POST['codigoParam'];

    $minimo = $_POST['minimo'];
    $maximo = $_POST['maximo'];
    $minimoAviso = $_POST['minimoAviso'];
    $minimoUrgencia = $_POST['minimoUrgencia'];
    $maximoAviso = $_POST['maximoAviso'];
    $maximoUrgencia = $_POST['maximoUrgencia']; 
    // error_log($idCultura);
    // error_log($codigoParam);

    //instantiate controller
    $controller = new CulturaController();
    $controller->addParametroCultura($idCultura, $codigoParam, $minimo, $maximo, $minimoAviso, $minimoUrgencia, $maximoAviso, $maximoUrgencia);

    //reload page
    header('location: ../public/culturaPage.php?idCultura=' . $idCultura);
}
