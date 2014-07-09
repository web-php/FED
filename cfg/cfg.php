<?php

return array(
    /** var string development testing production */
    'ENVIRONMENT' => "development",
    'VERSION' => 'v1.0',
    'AP_NAME' => 'FED',
    /** var array DB настройки подключения к базам */
    'DB' => array(
        'BASE' => array(
            'HOST' => '',
            'PORT' => '' , 
            'USER' => '',
            'BASE' => '',
            'PASS' => ''
        )
    ),
    
    'ERROR_LOG' => __DIR__ . "/../log/error_log",
    /** var string отображение ошибок */
    'DEBUGGING_PRINT' => TRUE,
    'DEBUGGING_LOG' => __DIR__ . '/../log/indexator_log.log',
    'ERROR_LOG' => __DIR__ . "/../log/error_log",
    'DEBUG_HTML' => __DIR__ . "/../log/html",
    /** curl */
    'HTTP_MAX_FAILCOUNT' => 3,
    'HTTP_CONNECT_TIMEOUT' => 15,
    'HTTP_EXEC_TIMEOUT' => 120 ,
    /** document */
    'MAX_TRADE_NAME' => 3000000 , 
    'RANGE' => array(
        'BEGIN' => 0 , 
        'END' => 0
    ) , 
    'THREAD' => 1 , 
    'DOC_ID' => ""
    
);
?>
