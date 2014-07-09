<?php

/**
 * Description of DbDomens
 *
 * @author Михаил Орехов
 */
class DbDomens {

    //put your code here
    const ERROR = 0;

    private $pdo;

    //----------------------------------------
    public function __construct()
    {
        $Cfg = Registry::get("Cfg");
        $this->pdo = Registry::get("Db");
    }

    //Добавить домен в базу 
    public function add_domens(array $data, $zone, $domain_info)
    {

        $update = "";
        $pdo = $this->pdo;
        $domain_info[] = "hash";
        foreach ($domain_info as $val)
            $update .= "$val = :$val, ";

        $sql = "INSERT INTO domains"
                . " (" . implode(" , ", array_values($domain_info)) . " , zone ) "
                . " VALUES "
                . " (:" . implode(" , :", array_values($domain_info)) . " , :zone )"
                . "ON DUPLICATE KEY UPDATE
                                $update
                                zone = :zone";
        
        
        $stmt = $pdo->prepare($sql);
        $call = function($data) use ( $stmt ) {
            foreach ($data as $f_name => $f_value)
            {
                //Registry::get("Log")->log("$f_name : $f_value");
                $stmt->bindValue(":" . $f_name, (!empty($f_value) ? $f_value : NULL));
            }
            $stmt->bindValue(":hash", md5($data['domain']));
            $stmt->execute();
        };
        Registry::get("Log")->log("beginTransaction");
        $pdo->beginTransaction();
        $stmt->bindValue(":zone", $zone);
        array_walk($data, $call);
        $pdo->commit();
    }

    public function insert_data_migration(array $maping, array $data, $reestr_id)
    {
        $update = "";
        $pdo = $this->pdo;


        foreach ($maping as $key => $val)
        {
            $update .= "$key = :$key, ";
        }
        $sql = "INSERT INTO doc_data"
                . " (" . implode(",", array_keys($maping)) . " , reestr_id ) "
                . " VALUES "
                . " (:" . implode(" , :", array_keys($maping)) . " , :reestr_id )"
                . "ON DUPLICATE KEY UPDATE
                                $update
                                `reestr_id` = :reestr_id";
        $stmt = $pdo->prepare($sql);
        $call = function($data) use ( $stmt, $maping ) {
            foreach ($maping as $f_name => $f_value)
            {
                $stmt->bindValue(":" . $f_name, (!empty($data[$f_value]) ? $data[$f_value] : NULL));
                //, (is_string($f_value) ? PDO::PARAM_STR : PDO::PARAM_INT)
            }
            $stmt->execute();
        };
        Registry::get("Log")->log("beginTransaction");
        $pdo->beginTransaction();
        $stmt->bindValue(":reestr_id", $reestr_id, PDO::PARAM_INT);
        array_walk($data, $call);
        $pdo->commit();
    }

}
