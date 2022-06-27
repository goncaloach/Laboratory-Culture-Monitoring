<?php

include 'class-autoload.php';

if (isset($_POST['submit'])) {
    $idCultura = $_POST['idCultura'];
    $codigoParam = $_POST['codigoParam'];
    // error_log($idCultura);
    // error_log($codigoParam);
    //instantiate controller
    $controller = new CulturaController();

    $controller->removeParametroCultura($idCultura, $codigoParam);

    //reload page
    header('location: ../public/culturaPage.php?idCultura=' . $idCultura);
}
