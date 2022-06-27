<?php

if(!session_id())
{
     session_start();
}

ini_set("log_errors", 1); // Enable error logging
ini_set("error_log", "../tmp/php-error.log"); // set error path
// error_log( "Hello, errors!" ); // log a test error

spl_autoload_register(function ($className)
{
    $url = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    $extension = '.php';

    $classesFolders = ['includes/', 'public/', 'src/controller/', 'src/model/', 'src/view/'];

    if (strpos($url, 'includes') !== false || strpos($url, 'public') !== false) {
        $prePath = '../';
     } else if (strpos($url, 'controller') !== false || strpos($url, 'model') !== false || strpos($url, 'view') !== false){
        $prePath = '../../';
    }

    foreach ($classesFolders as $folder) {
        if (file_exists($prePath . $folder . $className . $extension)){
            $path = $prePath . $folder;
            break;
        }
    }

    require_once $path . $className . $extension;
});

// function autoLoader($className)
// {
//     $url = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
//     $extension = '.php';

//     $classesFolders = ['includes/', 'public/', 'src/controller/', 'src/model/', 'src/view/'];

//     if (strpos($url, 'includes') !== false || strpos($url, 'public') !== false) {
//         $prePath = '../';
//      } else if (strpos($url, 'controller') !== false || strpos($url, 'model') !== false || strpos($url, 'view') !== false){
//         $prePath = '../../';
//     }

//     foreach ($classesFolders as $folder) {
//         if (file_exists($prePath . $folder . $className . $extension)){
//             $path = $prePath . $folder;
//             break;
//         }
//     }

//     require_once $path . $className . $extension;
// }


// function consoleLog($msg) {
//     echo '<script type="text/javascript">' .
//       'console.log(' . $msg . ');</script>';
// } 