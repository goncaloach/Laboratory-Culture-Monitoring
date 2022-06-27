<?php

// include 'class-autoload.php';

session_start();
session_unset();
session_destroy();

//go to home page (index handles it)
header('location: ../index.php');
