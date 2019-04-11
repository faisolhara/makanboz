<?php
return array(
    'host'    => '192.168.107.9',
    'port'    => 9312,
    // 'port'    => 9301,
    'timeout' => 30,
    // 'indexes' => array(
        // 'products' => array('table' => 'products', 'column' => 'id'),
    // ),
    'indexes' => array (
        'users_sph' => FALSE,
        'products_sph' => FALSE,
    ),
    // 'mysql_server' => array(
    //     'host' => '192.168.107.9',
    //     'port' => 9306
    // )
     'mysql_server' => array(
        'host' => env('DB_HOST'),
        'port' => env('DB_PORT'),
        'username' => env('DB_USERNAME'),
        'dbname' => env('DB_DATABASE'),
        'passwd' => env('DB_PASSWORD'),
    )
); 
