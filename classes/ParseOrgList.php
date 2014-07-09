<?php

/**
 * Description of ParseOrgList
 * Разобрать список организаций представленных на странице
 * @author Михаил Орехов
 */
class ParseOrgList {

    //put your code here
    private $html;
    private $separator = array();
    private $vcard_pattern = array();
    private $list_item = array();

    public function __construct()
    {
        //устоновить резделители для страницы 
        $this->separator = array(
            "<div id=\"MainContent_upnCompanyList\">",
            "#class=(\"vcard.*?\")#i"
        );
        //Установить регулярные выражения для каждого типа пунктов предприятия
        $this->vcard_pattern = array(
            "url" => "window.location.assign\(\'(.*?)\'\)" , 
            "title" => "<span class=\"fn org\".*?>(.*?)</span>" , 
            "inn" => "ИНН (.*?) </span>" , 
            "ogrn" => "ОГРН (.*?)\)" , 
            "state" => "<div style=\"display: block\" >(.*?)</div>" , 
            "address" => "<span class=\"street-address\">(.*?)</span>"
        );
    }

    public function get_org_info($html)
    {
        $this->html = $html;
        return $this->get_block();
    }
    /** Блоки компаний на состовляющие */
    private function get_block()
    {
        $html_block = preg_split($this->separator[0], $this->html);
        if (!empty($html_block[1]))
        {
            if ($org_block = preg_split($this->separator[1], $html_block[1]))
            {
                unset($org_block[0]);
                foreach($org_block as $key => $block)
                {
                    foreach($this->vcard_pattern as $title => $pattern)
                    {
                        preg_match("#$pattern#i", $block , $match) ; 
                        //print_r($match) ; 
                        $this->list_item[$key][$title] = @$match[1];
                    }
                }
            }
        }
        return $this->list_item ; 
    }

}

?>
