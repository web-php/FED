<?php

/**
 * Разделить предпологаемый обьем документов на потоки 
 */

class ForkInit {

    /**
     * Режим запуска по умочанию , делит максимальное количество документов на количество пооков
     */
    private static function default_range($thread)
    {
        $cfg = Registry::get("Cfg");
        return self::get_range($cfg['MAX_TRADE_NAME'], $thread);
    }

    private static function get_range($count, $thread)
    {
        $step = ceil($count / $thread);
        return (array(
            "begin" => 0, "end" => $step, "step" => $step));
    }

    /**
     * Обработка указаного диапазона документов
     */
    private static function set_range($thread)
    {
        $range = Registry::get("Range");
        if (!empty($range[0]) AND !empty($range[1]))
        {
            if ($range[0] > $range[1])
                $range = array_reverse($range);
            $step = ceil(($range[1] - $range[0]) / $thread);
            return (array("begin" => $range[0], "end" => $range[0] + $step, "step" => $step));
        }
        return self::default_range($thread);
    }
    
    /**
     * Обработка документов с ошибками
     */
    public static function error_doc_handler($thread)
    {
        $Dblink = new Dblink();
        $error_doc = $Dblink->get_error_document() ;
        $count = count($error_doc) ;
        $range = self::get_range($count, $thread) ;
        self::iterator( "error_doc_handler" , $range , $thread , $error_doc ) ; 
        //TODO :  дублирование кода , следует переработать в полиморфизм
    }
    
    /**
     * Стандартный запуск 
     */
    public static function default_handler($thread)
    {
        $range = (!Registry::get("Range")) ? self::default_range($thread) : self::set_range($thread);
        self::iterator( "run" , $range , $thread ) ; 
    }
    
    
    private static function iterator( $method_name , $range , $thread , $array = '' )
    {
        for ($j = 1; $j <= (int) $thread; ++$j)
        {
            //Установить требуемый массив на передачу парсеру , 
            $param = (!empty($array)) ? array_slice($array, $range['begin'], $range['end']) : $range;

            $pid = pcntl_fork();
            if ($pid == 0)
            {
                //Registry::get("Log")->log("range : " . $range['begin'] . " - " . $range['end']);
                $ParserFED = new ParserTradeName( $param );
                if (method_exists($ParserFED , $method_name ))
                {
                    $ParserFED->$method_name();
                    sleep(5);
                    exit;
                }
                else 
                    throw Exception ("Method $method_name not exist") ; 
                
            }
            $range['begin'] = $range['end'];
            $range['end'] += $range['step'];
        }
        
    }

    /**
     * Убить все порожденные процессы
     */
    public static function kill_all()
    {
        $str_kill = "";
        $cmd = 'ps -e -o pid,user,cmd | grep "`whoami`" | grep fed_parser_mt_cli.php | grep -v "grep"';
        exec($cmd, $output);
        foreach ($output as $process)
        {
            $process = trim($process);
            if ($process == "stdin: is not a tty" OR $process == "")
                continue;
            $pid = explode(" ", $process);
            $str_kill .= $pid[0] . " ";
        }
        Registry::get("Log")->log("kill : $str_kill" . count($output));
        exec("kill -kill $str_kill", $output);
    }

}

?>
