<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of DbTmdn
 *
 * @author Mikhail
 */
class DbTmdn {
    //put your code here
    const ERROR = 0;

    private $pdo;
   

    //----------------------------------------
    public function __construct()
    {
        $Cfg = Registry::get("Cfg");
        $this->pdo = Registry::get("Db");
    }

    /*** Функция обслуживания однотипных таблиц */
    public function add_field($table_name, $value)
    {
        $sql = "INSERT INTO $table_name 
                    ($table_name) 
                VALUES 
                    (:$table_name) 
                ON DUPLICATE KEY UPDATE 
                    `id` = LAST_INSERT_ID(id) ;";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':' . $table_name, $value);
        $stmt->execute();
        return $this->pdo->lastInsertId();
    }
}
