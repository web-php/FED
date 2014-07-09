<?php

/**
 *  Отчеты работы программы
 */
class RobotLog {

    private $pdo;
    private $time_begin; //начало работы
    private $start_date; //Старт работы 
    private $end_date;   //Конец работы 
    private $w_time;     //время выполнения

    public function __construct()
    {
        $this->pdo = Registry::get("Db");
        $this->time_begin = time();
    }

    /**
     * Сформировать отчет о времени работы программы
     */
    public function get_date_report()
    {
        $report = "Начала работу:      " . $this->start_date . "\n";
        $report .= "Завершила работу:   " . $this->end_date . "\n";
        $report .= "Время работы:       " . $this->w_time . "\n\n";

        return $report;
    }

    /**
     * Получить время работы программы
     */
    public function get_runtime_programm()
    {
        $this->init_date_info();
        return $this->get_date_report();
    }

    /**
     * Сформировать переменные с временем выполнения скрипта 
     * Установит start_date начало работы
     *           end_date конец работы
     *           w_time общее время выполнения
     */
    private function init_date_info()
    {
        $end_time = time();

        $time_total = $end_time - $this->time_begin;

        $hours_total = floor($time_total / 3600);
        $min_total = floor(($time_total - $hours_total * 3600) / 60);
        $sec_total = $time_total - $hours_total * 3600 - $min_total * 60;

        $report = "";

        $this->start_date = date("y.m.d H:i:s", $this->time_begin);
        $this->end_date = date("y.m.d H:i:s", $end_time);
        $this->w_time = "$hours_total:$min_total:$sec_total";
    }

    //----------------------------------------
    public function get_time_report()
    {
        $time_end = time();
        $time_total = $time_end - $this->time_begin;
        $hours_total = floor($time_total / 3600);
        $min_total = floor(($time_total - $hours_total * 3600) / 60);
        $sec_total = $time_total - $hours_total * 3600 - $min_total * 60;

        $report = "";
        $report .= "Начала работу:      " . date("y.m.d H:i:s", $this->time_begin) . "\n";
        $report .= "Завершила работу:   " . date("y.m.d H:i:s", $time_end) . "\n";
        $report .= "Время работы:       $hours_total:$min_total:$sec_total\n\n";

        return $report;
    }

    public function get_range_document()
    {
        $sql = "SELECT count(*) , min(org_id) , max(org_id) FROM organization 
                where org_id > :start AND org_id <= :end 
                ORDER BY org_id ASC ";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':start', $start);
        $stmt->bindParam(':end', $end);
        $stmt->execute();
        //Registry::get("Log")->log("Начало работы");
        
        $cfg = Registry::get("Cfg");
        $iteration = 200 ; 
        $step = ceil($cfg['MAX_TRADE_NAME'] / $iteration);
        $end = $step-1;
        $j = 1;
        $start = 0;
        for (; $j <= $iteration ;  $j++)
        {
            $stmt->execute();
            print("\nrange : ".Helpers::triada($start)." - ".Helpers::triada($end)." \n");
            print_r($stmt->fetch(PDO::FETCH_ASSOC));
            $start = $end;
            $end += $step-1;
        }
    }

}