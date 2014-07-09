<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ParserTmdn
 *
 * @author Mikhail
 */
class ParserTmdn {

    private $Cfg;
    private $Dblink;
    private $count_all = 0;
    private $lang = array(
        "AT", "BG", "BX", "CY", "CZ", "DE", "DK", "EE", "ES",
        "FI", "FR", "GB", "GR", "HR", "HU", "IE", "IT", "KR",
        "LT", "LV", "MA", "MT", "MX", "NO", "PL", "PT", "RO",
        "RU", "SE", "SI", "SK", "TR", "US", "EM", "WO",);
    //Карта запросов с типами и параметрами
    private $maping_url = array(
        //Валидатор запроса , должен вернуть OK
        array(
            "url" => "https://www.tmdn.org/tmview/validate-search-query",
            "type" => "POST",
            "query" => array(
                "q" => "lang",
            ),
            "return" => "string"
        ),
        //Предположительно обработчик запроса , ворнет массив JSON c кэш ключем
        array(
            "url" => "https://www.tmdn.org/tmview/search-tmv",
            "type" => "POST",
        ),
        //Пока не понял зачем ээтот запрос вернуть OK
        array(
            "url" => "https://www.tmdn.org/tmview/totalResult",
            "type" => "POST",
            "query" => array(
                "logaction" => "doSearch",
                "totalResultsNo" => 0
            ),
            "return" => "string"
        ),
        //запрос возвращающий основной ответ 
        array(
            "url" => "https://www.tmdn.org/tmview/get-results-tmv",
            "type" => "GET",
            "query" => array(
                "_search" => "false",
                "nd" => 1396684866755,
                "rows" => 450,
                "page" => 1,
                "sidx" => "tm",
                "pageSize" => 1000000, //Устанавливаем максимум 
                "cacheKey" => "cacheKey", //выбрать из запроса https://www.tmdn.org/tmview/search-tmv [cacheKey]
                "expandWipo" => "false",
                "providerList" => "null",
                "expandedOffices" => "null",
                "selectedRowRefNumber" => "null"
            ),
            "return" => "json"
        )
    );
    public static $field = array(
        'tm' => '',
        'anm2' => '',
        'tm2' => '',
        'MarkVerbalElementText' => '',
        'sc' => '',
        'ST13' => '',
        'ty' => '',
        'oc' => '',
        'nc' => '',
        'vc' => '',
        'an' => '',
        'MarkImageURI' => '',
        'ad' => '',
        'anm' => '',
        'OperationCode' => '',
        'ApplicantName' => '',
        'timestamp' => '',
        'ipr' => '',
        'RegistrationNumber' => '',
        'ExpiryDate' => '',
        'RegistrationDate' => ''
    );

    public function __construct() {
        $this->Cfg = Registry::get("Cfg");
        $this->Log = Registry::get("Log");
        $this->Dblink = Registry::get("DbLink");
        $this->Http = new HttpRequest();
    }

    public function run() {
        //print_r($this->post_param);
        //Цикл дней    
        for ($x = 1; $x <= 366; $x++) {
            $date = new DateTime();
            $date->setDate(2005, 2, 10);
            $date->modify('-' . $x . ' day');
            $date_parse = $date->format("Y-m-d");
            Registry::get("Log")->log("range:" . $x . " - " . $date_parse);
            //цикл агенств 
            foreach ($this->lang as $lang) {
                $count = $this->query($lang, $date_parse);
                //если результат выше 450 то проходим все 45 классов отдельно
                if ($count >= 450) {
                    $file = __DIR__ . "/../over_load_row.log";
                    $buf = file_get_contents($file);
                    file_put_contents($file, $buf . "date-set: $date_parse lang : $lang total-row: $count \n");
                    $total = 0;
                    //цикл мкту классов
                    for ($i = 1; $i <= 45; $i++) {
                        $total = + $this->query($lang, $date_parse, $i);
                        if ($total >= $count)
                            break;
                    }
                }
            }
        }
        Registry::get("Log")->log("count all :" . $this->count_all);
        //$count_all ; 
    }

    /**
     * Сформировать и выполнить запрос нап получение массива данных
     */
    private function query($lang, $date_parse, $class = '') {
        //формируем строку запроса
        $query = array("q" => "oc:$lang " . ($class ? "AND nc:$class" : "") . " AND ad:$date_parse..$date_parse");

        print_r($query);
        //установить выборку всех достпных документов (450)
        $this->maping_url[3]['query']['expandedOffices'] = $lang;

        $data = $this->set_global_connect(
                $this->maping_url[0]['type'], $this->maping_url[0]['url'], $query);

        Registry::get("Log")->log($data);

        $data = $this->set_global_connect(
                $this->maping_url[1]['type'], $this->maping_url[1]['url'], $query);
        Registry::get("Log")->log($data);

        $data = json_decode($data);
        $this->maping_url[3]['query']['cacheKey'] = $data->cacheKey;

        $data = $this->set_global_connect(
                $this->maping_url[3]['type'], $this->set_getdata($this->maping_url[3]));
        if (empty($data))
            return;
        $arr = (json_decode($data));

        Registry::get("Log")->log("language:" . $lang);
        Registry::get("Log")->log("class:" . $class);
        Registry::get("Log")->log("total page:" . $arr->total);
        Registry::get("Log")->log("count rows:" . count($arr->rows));
        Registry::get("Log")->log("count total rows:" . ($arr->providerTotalNumberResults->$lang ? $arr->providerTotalNumberResults->$lang : 0));

        if ($arr->total == 0)
            return 0;
        $this->Dblink->insert_tmdn($arr->rows);
        $this->count_all += count($arr->rows);
        return $arr->providerTotalNumberResults->$lang;
    }

    private function set_global_connect($type, $url, $postdata = false) {
        switch ($type) 
        {
            case "GET" :
                if (!$data = $this->Http->get($url))
                    throw new Exception("error load document . method : {$type}");
                break;
            case "POST" :
                if (!$data = $this->Http->get($url, FALSE, $postdata))
                    throw new Exception("error load document .  method : {$type}");
                break;
        }
        return $data;
    }

    /**
     * Сформировать GET запрос
     */
    private function set_getdata($data) {
        $get = "";
        foreach ($data['query'] as $k => $v)
            $get .= "$k=" . urlencode($v) . "&";
        $url = $data['url'] . "?" . substr($get, 0, -1);
        //print($url);
        //exit;
        return $url;
    }

}
