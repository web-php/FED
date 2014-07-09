<?php

/**
 * простой реестр для доступа к обьектам
 */
class Registry {

    private function __construct()
    {
        
    }

    private function __clone()
    {
        
    }

    private static $instance = array();

    /**
     * Добавить обьект в реестр 
     * @param $obj добавляемый обьект
     * @param $name если требуется свое имя для обьекта
     * @param $reload если требуется перезаписать обьект
     */
    public static function set($obj, $name = '', $reload = '')
    {
        if (empty($name))
        {
            if (!$name = get_class($obj))
                throw new Exception("\n object is not exist \n");
        } else
        {
            if (!is_string($name))
                throw new Exception("\n name variable fail \n");
        }
        if (empty(self::$instance[$name]) || !empty($reload))
        {
            self::$instance[$name] = $obj;
        }
    }

    /** Получить обькт из реестра */
    public static function get($name)
    {
        if (!empty(self::$instance[$name]))
        {
            return self::$instance[$name];
        }
        else
        {
            //print_r(self::$instance);
            return FALSE; 
            //throw new Exception("\n object $name does not exist in the registry \n");
        }
    }
}
?>
