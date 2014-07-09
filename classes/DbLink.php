<?php

/**
 * Модель для работы с базой ссылок (LINKS)
 */
class DbLink {

    const ERROR = 0;

    private $pdo;
    private $table_name = array(
        "error" => 1,
        "region" => 1,
        "okved" => 1,
        "org_status" => 1,
        "okopf" => 1,
        "org_okopf" => 1);

    //----------------------------------------
    public function __construct()
    {
        $Cfg = Registry::get("Cfg");
        $this->pdo = new Db($Cfg['DB']['BASE']);
    }

    /**
     * Отключение включение ключей для больших вставок данных
     * @param type $bd_prefix
     * @param type $table_name
     * @param type $action
     */
    public function action_keys($table_name, $action)
    {
        Registry::get("Log")->log("$action keys");
        $this->pdo->query("alter table $table_name $action keys");
    }

    /** Функция обслуживания однотипных таблиц */
    public function add_field($table_name, $value)
    {
        $this->table_valid($table_name);
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

    /**
     * закрепить за организацией ОКПОФ
     */
    public function add_org_okopf($id, array $okopf_item)
    {
        $sql = "INSERT INTO org_okopf 
                    (org_id , okopf_id) 
                VALUES 
                    (:org_id , :okopf_id) 
                ON DUPLICATE KEY UPDATE 
                    `id` = LAST_INSERT_ID(id) ; ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':org_id', $id);
        $stmt->bindParam(':okopf_id', $okopf_id);
        foreach ($okopf_item as $okopf_id)
        {
            $stmt->execute();
        }
    }

    /**
     *  Добавить организацию
     */
    public function add_org(array $item_list)
    {
        $variable = array();
        $field_name = array_keys($item_list);
        $sql = "INSERT INTO organization
                    (" . implode(",", $field_name) . ") 
                VALUES 
                    (:" . implode(", :", $field_name) . ") 
                ON DUPLICATE KEY UPDATE 
                    `id` = LAST_INSERT_ID(id) ; ";

        $stmt = $this->pdo->prepare($sql);
        foreach ($item_list as $name => $val)
        {
            $stmt->bindParam(':' . $name, $variable[$name]);
            $variable[$name] = (!empty($val) ? $val : NULL);
        }
        if (!$stmt->execute())
        {
            Registry::get("Log")->log(implode(" , ", $stmt->errorInfo()), "err");
            return FALSE;
        }
        else
            return $this->pdo->lastInsertId();
    }

    public function get_error_document()
    {
        static $dbres = NULL;
        if ($dbres == NULL)
        {
            $sql = "SELECT org_id FROM doc_error WHERE error <> 1 ;";
            $dbres = $this->pdo->query($sql);
        }
        if (!($row = $dbres->fetchAll(PDO::FETCH_NUM)))
        {
            $dbres = NULL;
        }
        return $row;
    }

    public function del_doc_err($org_id)
    {
        $sql = "DELETE FROM `doc_error` WHERE (org_id=:org_id) ;";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':org_id', $org_id);
        $stmt->execute();
    }

    public function add_doc_error($doc_id, $err_id)
    {
        $sql = "INSERT INTO doc_error 
                   (org_id , error , attempt , parsed_date , state) 
                VALUES 
                    (   :doc_id , 
                        :err_id , 
                        (doc_error.attempt+1) , 
                        NOW() , 
                        0   ) 
                ON DUPLICATE KEY UPDATE 
                    `parsed_date` = NOW() , attempt = (attempt+1) ;";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':doc_id', $doc_id);
        $stmt->bindParam(':err_id', $err_id);
        $stmt->execute();
    }

    private function table_valid($table_name)
    {
        if (!isset($this->table_name[$table_name]))
            throw new Exception("Table $table_name not found =( ");
    }

    /** Функция обслуживания однотипных таблиц */
    public function insert_tmdn($values)
    {
        $key = array_keys(ParserTmdn::$field);

        $sql = "INSERT IGNORE INTO tmdn_base 
                    ( " . implode(" , ", $key) . " ) 
                VALUES 
                    ( :" . implode(" , :", $key) . " )";
        //print_r($sql);
        $stmt = $this->pdo->prepare($sql);
        //Замыкание на добавление новго запроса в транзакцию

        $call = function($value) use ($stmt, $key) {
            //print_r($value);
            foreach ($key as $field)
            {
                $stmt->bindValue(':' . $field, (!empty($value->$field) ?
                                (is_array($value->$field) ?
                                        serialize($value->$field) : $value->$field ) : NULL));
            }
            $stmt->execute();
        };
        //старт транзакции
        $dbh = $this->pdo->beginTransaction();
        array_walk($values, $call);
        $this->pdo->commit();
    }

}
