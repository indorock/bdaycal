<?php
require __DIR__.'/vendor/autoload.php';
require('./model/cal.php');
//        ini_set('display_errors', 0);

function show_calendar(){
    $as_pdf = isset($_GET['pdf']);
    $now = isset($_GET['now']);
    $year = isset($_GET['y']) && !$now ? (int)$_GET['y'] : (int)date('Y');
    $cal = new Cal($year);
    if(!$as_pdf)
        include('./templates/head.php');
    echo $cal->show($as_pdf, $now);
    if(!$as_pdf)
        include('./templates/end.php');
}