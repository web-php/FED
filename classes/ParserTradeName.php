<?php

/**
 * Description of ParserTradeName
 *
 * @author Михаил Орехов
 */
class ParserTradeName {

    private $Cfg;
    private $Dblink;
    public static $doc_id;
    private $parse_url = "http://www.fedresurs.ru/companies/";
    private $field_array = array();

    public function __construct(array $range)
    {
        memory_get_usage();
        Registry::set(new Dblink());
        $this->Cfg = Registry::get("Cfg");
        $this->Log = Registry::get("Log");
        $this->Dblink = Registry::get("DbLink");
        $this->Http = new HttpRequest();
        $this->ParseOrg = new ParseOrg();
        $this->range = $range;
    }

    /**
     * Обработать диапазон документов , простой перебор
     */
    public function run()
    {
        $i = $this->range['begin'];
        $end = $this->range['end'];
        for (; $i <= $end; ++$i)
        {
            $item_list = $this->get_one_document($i);
        }
    }

    public function error_doc_handler()
    {
        $range = $this->range;
        $baseMemory = memory_get_usage();
        foreach($range as $key => $item)
        {
            $this->Dblink->del_doc_err( $item[0] );
            $this->get_one_document( $item[0] );
            
            if($key-5 == 0)
            {
                gc_collect_cycles();
                $this->Log->log( memory_get_usage() - $baseMemory );
            }
            unset( $range[$key] , $key , $item );
        }
        unset($this->range);
    }

    /**
     * получить одиночный документ , так же является отладочным методом для получения 1го документа . 
     */
    
    public function get_one_document($id)
    {
        //TODO : дублирующее запоминание переменной , требуется выяснить
        self::$doc_id = $id;
        Registry::set($id , "doc_id" , true);
        if (!$this->get_document($this->parse_url . $id, false, false))
        {
            $this->Log->log("document not load");
        }
        else
        {
            $this->Log->log("start parsing");
            $item_list = $this->ParseOrg->run($this->html);
            $this->process_data($id, $item_list);
        }
       // print_r($this->field_array);
    }

    /**
     * Кеш по повторяющимся полям
     */
    private function add_field($table_name, $value)
    {
        if (empty($this->field_array[$table_name]))
        {
            $this->field_array[$table_name] = array();
        }
        if (!$id = array_search($value, $this->field_array[$table_name]))
        {
            $id = $this->Dblink->add_field($table_name, $value);
            $this->field_array[$table_name][$id] = $value ; 
        }
        return $id;
    }

    /**
     * Обработать поля массива данных , добавить недостающие , изменить существующие , сохранить изменения .
     */
    private function process_data($id, array $item_list)
    {
        $item_list['org_id'] = (int) $id;
        $item_list['region'] = $this->add_field("region", $item_list['region']);
        $item_list['okved'] = $this->add_field("okved", $item_list['okved']);
        $item_list['org_status'] = $this->add_field("org_status", $item_list['org_status']);
        //если okopf не пустойразберем его на составляющие
        if (!empty($item_list['okopf']))
        {
            $okopf_array = explode(";", $item_list['okopf']);
            foreach ($okopf_array as $okopf)
            {
                $okopf_item[] = $this->add_field("okopf", $okopf);
            }
            $this->Dblink->add_org_okopf($id, $okopf_item);
            $item_list['okopf'] = 1;
        }
        else
            $item_list['okopf'] = NULL;
        //Создать хэш всех полей 
        $item_list['hash'] = md5(implode("", $item_list));
        //sleep(reand(0,3));
        if ($this->Dblink->add_org($item_list))
        {
            $this->Log->log("save organization complete");
        }
        else
        {
            $this->Log->log("error save");
        }
    }

    /**
     * Получить вылиденый документ
     */
    private function get_document($url, $mode = FALSE, $postdate = FALSE)
    {
        $info = $this->Http->get_header($url);
        if (preg_match("(404|403|302|301)", $info['http_code']))
        {
            $this->Log->log("not found (404|403|302|301)", "err");
            return FALSE;
        }


        if ($html = $this->Http->get($url, $mode, $postdate))
        {
            if (!$this->request_analise($html))
            {
                $this->get_document($url, $mode, $postdate);
            }
            else
            {
                $this->Log->log("document load complete");
                $this->html = $html;
            }
            return TRUE;
        }
        else
        {
            $this->Log->log("document not load");
            return FALSE;
        }
    }

    private function request_analise($html)
    {
        $patterns = array(
            "anti bot detected" => "#id=\"MainContent_antiBot_MessageLabel\"#i"
        );
        foreach ($patterns as $des => $pattern)
        {
            if (preg_match($pattern, $html))
            {
                $this->Log->log($des, "err");
                //sleep(5);
                return FALSE;
            }
        }
        return TRUE;
    }
    

    /**
     * Разбрать html на блоки предприятий 
     */
    function get_organization()
    {
        
    }

    /**
     * Установить параметры для POST запроса 
     */
    private function set_postdate()
    {

        foreach ($post_param as $key => $val)
        {
            $postdate[$key] = $val;
        }
        return $postdate;
    }

}

?>
