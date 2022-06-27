<?php

include 'class-autoload.php';

if (isset($_POST['submit'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    //instantiate controller
    $loginCtrl = new LoginController($username, $password);

    //running error handlers and login
    $loginCtrl->loginUser();

    //go to home page if successful (index handles it)
    header('location: ../index.php');

}
