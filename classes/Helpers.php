<?php

/**
 * Вспомогательный класс проекта 
 * @version 1.0
 * @author Mikhail Orekhov <mikhail@edwaks.ru>
 */

class Helpers {

    //Конвертер байтов
    static public function byte_convert($bytes = NULL)
    {
        if (!$bytes)
            return 0;
        $symbol = array('B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
        $exp = 0;
        $converted_value = 0;
        if ($bytes > 0)
        {
            $exp = floor(log($bytes) / log(1024));
            $converted_value = ( $bytes / pow(1024, floor($exp)) );
        }

        return sprintf('%.2f ' . $symbol[$exp], $converted_value);
    }
    //разбить число на триады
    static public function triada($num)
    {
        return preg_replace("~(\d(?=(?:\d{3})+(?!\d)))~s", "\\1,", $num); 
    }

}

?>
