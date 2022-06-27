<?php

include 'class-autoload.php';

if (isset($_POST['submit'])) {
    $idCultura = $_POST['idCultura'];
    $nome = $_POST['nome'];
    $minutos = $_POST['minutos'];
    $ativo = isset($_POST['ativo']);
    error_log($ativo?1:-1);
    error_log($ativo);
    //instantiate controller
    $controller = new CulturaController();
    $controller->editCultura($idCultura, $nome, $minutos, $ativo ? 1 : -1);

    //reload page
    header('location: ../public/culturaPage.php?idCultura=' . $idCultura);
}