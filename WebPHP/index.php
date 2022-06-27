<?php

include("$_SERVER[DOCUMENT_ROOT]/includes/class-autoload.php");
 

if (!isset($_SESSION['isLoggedIn']) || (isset($_SESSION['isLoggedIn']) && $_SESSION['isLoggedIn'] == false)) {
    header('location: public/startPage.php');
} else {
    header('location: public/homePage.php');
}
