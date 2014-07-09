<?php

/**
 * Description of Log
 *
 * @author Михаил Орехов
 */
class Log {

    private $cfg;
    private $error = array();

    //put your code here+
    public function __construct()
    {
        $this->cfg = Registry::get("Cfg");
    }

    public function log($msg, $err = FALSE)
    {
        $this->error_log($msg, $err);
        $doc_id = (Registry::get("doc_id") ? "doc_id : " . Registry::get("doc_id") : "");
        $msg = "[" . date("y.m d-h:i:s") . " ] $doc_id \t $msg \n";
        if ($this->cfg['DEBUGGING_PRINT'])
            print $msg;
        //Записать лог
        if ($this->cfg['DEBUGGING_LOG'])
        {
            $current = file_get_contents($this->cfg['DEBUGGING_LOG']);
            $current .= $msg;
            file_put_contents($this->cfg['DEBUGGING_LOG'], $current);
        }
    }

    /**
     * Логировать ошибку
     */
    private function error_log($msg, $err = FALSE)
    {
        if (empty($err))
            return;
        if (!$err_id = array_search($msg, $this->error))
        {
            $err_id = Registry::get("DbLink")->add_field("error", $msg);
            $this->error[$err_id] = $msg;
        }
        if (!empty(ParserTradeName::$doc_id))
        {
            Registry::get("DbLink")->add_doc_error(ParserTradeName::$doc_id, $err_id);
        }
    }

}

?>
