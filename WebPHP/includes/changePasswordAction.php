<?php

include 'class-autoload.php';

if (isset($_POST['submit'])) { 
    $password = $_POST['password'];
    // error_log($password); 

    //instantiate controller
    $controller = new LoginController();
    $controller->changePassword($password);

    //reload page
    header('location: ../public/homePage.php');
}
