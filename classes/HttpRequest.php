<?php

/**
 * Скачать требуемую страницу
 */
require_once __DIR__ . "/../cfg/cfg.php";

class HttpRequest {

    private $Cfg;
    private $user_agent = array(
        "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)"
    );
    private $fail_timeout = 5;
    private $max_failcount;
    private $connect_timeout;
    private $exec_timeout;
    private $cookie_filename = "";
    private $basedir;
    private $proxy_host;
    private $proxy_port;
    private $proxy_socks;
    private $check = TRUE;
    private $header = array("Content-Language: ru", "Accept-Language: ru");

//----------------------------------------
    public function __construct()
    {
        $this->basedir = __DIR__ . "/..";
        $this->Cfg = Registry::get("Cfg");
        $this->max_failcount = $this->Cfg['HTTP_MAX_FAILCOUNT'];
        $this->connect_timeout = $this->Cfg['HTTP_CONNECT_TIMEOUT'];
        $this->exec_timeout = $this->Cfg['HTTP_EXEC_TIMEOUT'];
        $this->set_cookie_filename("fed");
    }

//----------------------------------------
    public function set_cookie_filename($cookie_filename)
    {
        $this->cookie_filename = $this->basedir . '/tmp/' . $cookie_filename;
    }

    /**
     * Удалить куки
     */
    public function clear_cookie()
    {
        if (file_exists($this->cookie_filename))
        {
            unlink($this->cookie_filename);
        }
    }

    //----------------------------------------
    public function set_google_useragent()
    {
        $this->user_agent = "Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)";
    }

    //----------------------------------------
    public function set_proxy($host, $port, $socks)
    {
        $this->proxy_host = $host;
        $this->proxy_port = $port;
        $this->proxy_socks = $socks;
    }

    /**
     * Получить заголовки страницы , без загрузки файла . используется в FipGrabber.php  для определения наличия изобржаения на сервере.
     * @param string $uri путь к получаемой странице
     */
    public function get_header($uri)
    {
        $user_agent = $this->user_agent[rand(0, count($this->user_agent) - 1)];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $uri);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->connect_timeout);
        curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
        // Only calling the head
        curl_setopt($ch, CURLOPT_HEADER, true); // header will be at output
        // curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'HEAD'); // HTTP request is 'HEAD'
        curl_setopt($ch, CURLOPT_NOBODY, TRUE);
        curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);
        return $info;
    }

    /**
     * Проверить получаемый файл на наличие закрывающих тегов </body>
     * Если теги отсутствуют увеличить время ожидания выполнения функции
     */
    public function check_integrity($html)
    {

        if (stristr($html, '</body>') === FALSE)
        {
            $this->exec_timeout += 90;
            Registry::get("Log")->log("page is not fully loaded" . curl_error($ch), "err");
            return FALSE;
        }
        else
        {
            $this->exec_timeout = $this->Cfg['HTTP_EXEC_TIMEOUT'];
            return $html;
        }
    }

    /**
     * Получить страницу полностью
     * @param string $uri путь к получаемой странице
     * @param string $mode режим получения документа  $mode = FALSE || TRUE 
     * @param string $postdate GET || POST 
     * @param bool $html_entity  Если ожидаем html то декодировать все символы в UTF представление
     */
    public function get($uri, $mode = FALSE, $postdate = FALSE, $html_entity = TRUE)
    {
        $contents = FALSE;
        $failcount = 0;
        $user_agent = $this->user_agent[rand(0, count($this->user_agent) - 1)];
        //print $user_agent . "\n";
        $ch = curl_init();
        while ($contents === FALSE) {
            curl_setopt($ch, CURLOPT_URL, $uri);
            //curl_setopt( $ch, CURLOPT_AUTOREFERER , FALSE );
            curl_setopt($ch, CURLOPT_HEADER, FALSE);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
            //curl_setopt( $ch, CURLOPT_MAXREDIRS , 5 );
            curl_setopt($ch, CURLOPT_HTTPHEADER, $this->header);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->connect_timeout);
            curl_setopt($ch, CURLOPT_TIMEOUT, $this->exec_timeout);
            curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
            if ($postdate)
            {
                curl_setopt($ch, CURLOPT_POST, TRUE);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postdate);
            }
            //В режиме FALSE , грабер сохраняет куки , проставляет прокси
            if ($mode == FALSE)
            {
                //Подключить куки 
                if (!empty($this->cookie_filename))
                {
                    curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookie_filename);
                    curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookie_filename);
                }
                //Ставим прокси    
                if (!empty($this->proxy_host))
                {
                    curl_setopt($ch, CURLOPT_PROXY, $this->proxy_host . ":" . $this->proxy_port);
                    if ($this->proxy_socks)
                    {
                        curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
                    }
                    else
                    {
                        curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
                    }
                }
            }

            $contents = curl_exec($ch);
            //проверим полученный HTML на целостность . если документ скачан не до конца отправим его на повторный сбор 
            if ($contents !== FALSE && $this->check == TRUE)
            {
                if (preg_match("#html#", $contents))
                {
                    $html_entity = TRUE;
                    $contents = $this->check_Integrity($contents);
                }
                if ($contents === FALSE)
                {
                    Registry::get("Log")->log("CURL: " . curl_error($ch), "err");
                    $failcount++;
                    if ($failcount == $this->max_failcount)
                        return FALSE;
                    sleep($this->fail_timeout);
                }
            }
            curl_close($ch);
            //file_put_contents($this->basedir . "/tmp/dump.html", $contents, FILE_APPEND);
            if ($html_entity)
                return html_entity_decode($contents, ENT_NOQUOTES, 'UTF-8');
            else
                return $contents;
        }
    }

}

?>