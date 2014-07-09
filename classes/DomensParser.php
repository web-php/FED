<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of DomensParser
 *
 * @author Михаил Орехов
 */
class DomensParser {

//put your code here

    private $urls = array(
        "ru_domains",
        "su_domains",
        "rf_domains"
    );
    private $domen_info = array(
        "domain",
        "reg_name",
        "registered",
        "registered_to",
        "expiration_date",
        "type"
    );
    private $data_length = 500000;

    public function __construct()
    {
        $this->Cfg = Registry::get("Cfg");
        //$this->Dblink = Registry::get("DbLink");
        //$this->DbDomens = Registry::get("DbDomens");
        $this->Http = new HttpRequest();
    }

    public function run()
    {
        Registry::get("DbLink")->action_keys("domains", "disable");
        foreach ($this->urls as $url)
        {
            $zone = substr($url, 0, 2);
            $path = "https://partner.r01.ru/zones/$url.gz";
            //Скачать базу доменов
            if (!$data = $this->Http->get($path, FALSE, FALSE, FALSE))
                throw new Exception("error load document . method : {$type}");
            //если все прошло успешно скопировать базу на сервер    
            if (file_put_contents("$url.gz", $data))
            {
                $data = array();
                $file = gzfile("$url.gz");

                Registry::set($zone, "doc_id", true);
                Registry::get("Log")->log(count($file));

                foreach ($file as $key => $parse_str)
                {
                    $array_value = explode("\t", $parse_str);
                    //отформатировать поля с датами
                    $date_arr = array(2, 3, 4);
                    array_walk($date_arr, function($i) use(&$array_value) {
                        $array_value[$i] = implode("-", array_reverse(explode(".", $array_value[$i])));
                    });
                    //Registry::get("Log")->log(print_r($array_value));
                    $data[] = array_combine($this->domen_info, $array_value);
                    //чистим исходный массив за собой 
                    $file[$key] = NULL;
                    //если данные достигли 
                    if (isset($data[$this->data_length]))
                    {
                        Registry::get("Log")->log("iteration : $key");
                        Registry::get("DbDomens")->add_domens($data, $zone, $this->domen_info);
                        $data = array();
                    }
                }
                if (count($data) > 0)
                {
                    Registry::get("Log")->log("Last iteration - count : " . count($data));
                    Registry::get("DbDomens")->add_domens($data, $zone , $this->domen_info);
                    $data = array();
                }
            }
        }
        Registry::get("DbLink")->action_keys("domains", "enable");
    }

}
