<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of RouterMode
 *
 * @author Михаил Орехов
 */
class RouterMode {

    //put your code here

    public static function execute($options)
    {
        //Удалить все потоки
        if (!empty($options['k']))
        {
            ForkInit::kill_all();
        }
        //диапазоны
        else if(!empty($options['r']))
        {
            $range = explode("-",$options['r']);
            Registry::set($range , "Range");
        }
        //обработать плохие документы
        else if(!empty($options['u']))
        {
            ForkInit::error_doc_handler($options['u']);
        }
        //Запустить отдельный документ в парсинг
        else if (!empty($options['d']))
        {
            $ParserFED = new ParserTradeName(array());
            $ParserFED->get_one_document($options['d']);
        }
        
        //Колличество потоков
        if (!empty($options['t']))
        {
            ForkInit::default_handler($options['t']);
        }
    }

}

?>
