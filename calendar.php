<?php
require __DIR__.'/vendor/autoload.php';
require('./model/cal.php');
//        ini_set('display_errors', 0);

function show_calendar(){
    $as_pdf = isset($_GET['pdf']);
    $month = null;
    $year = null;

    if(isset($_GET['y']))
        $year = (int)$_GET['y'];
    if(isset($_GET['m']))
        $month = (int)$_GET['m'];

    if(isset($_GET['now'])) {
        $month = (int)date('m');
        $year = (int)date('Y');
    }

    $cal = new Cal($year);
    if(!$as_pdf)
        include('./templates/head.php');
    echo $cal->show($as_pdf, $month);
    if(!$as_pdf)
        include('./templates/end.php');
}