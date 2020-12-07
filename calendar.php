<?php
require __DIR__.'/vendor/autoload.php';
require('./model/cal.php');

function show_calendar($as_pdf = false){
    $cal = new Cal(2021);
    if(!$as_pdf)
        include('./templates/head.php');
    echo $cal->show($as_pdf);
    if(!$as_pdf)
        include('./templates/end.php');
}