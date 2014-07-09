<?php

/**
 * Description of ParserFED 
 * разрабокта парсинга полного списка предприятий , 
 * принято решение о прекращение и выбор модели полного перебора сайта
 *
 * @author Михаил Орехов
 */
class ParserFED {

    private $Cfg;
    private $Dblink;
    private $parse_url = "http://www.fedresurs.ru/companies/";
    private $post_param = array(
        "__ASYNCPOS" => "", //Нет 
        "__EVENTARGUMENT" => "", //есть 
        "__EVENTTARGET" => "", //пустой на старте __EVENTTARGET // при клике устанавливается в ctl00$MainContent$ucTopDataPager$ctl00$ctl01 - 01 02 03 - страница
        "__EVENTVALIDATION" => "", //есть 
        "__VIEWSTATE" => "", //есть 
        "ctl00\$MainContent\$txtAddress" => "",
        "ctl00\$MainContent\$txtCode" => "",
        "ctl00\$MainContent\$txtName" => "",
        "ctl00\$ScriptManager1" => "", //	ctl00$MainContent$upnCompanyList|ctl00$MainContent$ucTopDataPager$ctl00$ctl02
        "ctl00\$tbCompanySearch" => ""
    );

    public function __construct()
    {
        $this->Cfg = Registry::get("Cfg");
        $this->Log = Registry::get("Log");
        $this->Dblink = Registry::get("DbLink");
        $this->Http = new HttpRequest();
        $this->ParseOrgList = new ParseOrgList();
    }

    public function run()
    {
        print_r($this->post_param);
        $this->begin_page();
    }

    private function begin_page()
    {
        $this->Log->log("Get begin page :");
        if (!$this->get_document($this->parse_url . "IsSearching", false, false, "begin"))
        {
            exit("error load document");
        }
        else
        {
            $list_item = $this->ParseOrgList->get_org_info($this->html);
            print_r($list_item) ; 
            //$this->Log->log($this->html);
        }
    }

    private function get_document($url, $mode = FALSE, $postdate = FALSE, $pr = "")
    {
        if ($html = $this->Http->get($url, $mode, $postdate))
        {
            if (!$this->request_analise($html))
            {
                $this->get_document($url, $mode, $postdate);
            }
            else
            {
                $this->html = $html;
            }
            return TRUE ; 
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
            if (preg_match($pattern , $html))
            {
                $this->Log->log($des);
                sleep(5);
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

    /** Установить параметры для POST запроса */
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
