<?php
/** 
 * разобрать поля страницы
 */
class ParseOrg {
    private $html ;
    private $item_list = array(); 
    public static $match_list = array(
        "org_name" => "Полное фирменное наименование:" ,
        "inn" => "ИНН:" , 
        "org_brief_name" => "Сокращённое фирменное наименование:" ,
        "ogrn" => "ОГРН:" ,
        "org_address" => "Юридический адрес \(по данным ЕГРЮЛ\):" , 
        "okved" => "Основная отрасль:" , 
        "postal_address" => "Почтовый адрес \(по данным компании\):" , 
        "kpp" => "КПП:" , 
        "okopf" => "ОКОПФ:" ,
        "region" => "Регион:" , 
        "init_capital" => "Уставный капитал \(по данным ЕГРЮЛ\):" , 
        "org_cost" => "Стоимость чистых активов:" , 
        "org_status" => "Cтатус юридического лица:" , 
        "licvidation_date" => "Дата внесения в ЕГРЮЛ записи о нахождении в стадии ликвидации:" ,
        "msg_count" => "Количество сообщений:" , 
        //"msg" => "" , 
        //"doc" => ""
    );

    /**
     * Разобрать документ на состовляющие поля
     */
    public function run($html)
    {
        $this->html = str_replace("\n","",$html) ; 
    
        foreach(self::$match_list as $item => $match)
        {
            $this->item_list[$item] = $this->get_field($match) ; 
        }
        return $this->item_list ; 
    }
    
    //Получить искомую строку 
    private function get_field($match)
    {
        if(preg_match("#<td.*?>.*?($match).*?</td>.*?<td.*?>(?P<field>.*?)</td>#i" , $this->html , $res)) 
            return trim(strip_tags($res['field'])); 
        return '' ;
    }
   
}

?>
