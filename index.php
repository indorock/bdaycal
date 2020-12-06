<style type="text/css">
    *,html,body{
        font-family: Sans-serif;
        margin:0;
        padding:0;
    }

    table.month{
        border: 2px solid;
        border-collapse:collapse;
        width:277mm;
        height:190mm;
        margin:10mm;
    }

    table.month thead th{
        height:10mm;
        text-align: center;
    }

    table.month th.monthname{
        border: 2px solid;
        font-weight: bold;
        font-size:20px;
    }

    table.month tr.daysofweek td{
        border: 1px solid;
        font-weight: bold;
        font-size:16px;
        border-collapse:collapse;
    }

    table.month tbody td{
        border: 1px solid;
        border-collapse:collapse;
        padding:0px;
        font-size:14px;
        width:39mm;
        height:36mm;
        text-align:left;
        vertical-align: top;
    }

    table.month td div.day{
        text-align:left;
    }

    table.month td div.day span.date{
        font-weight: bold;
        margin:10px;
        font-size:20px;
        display:block;
    }

    table.month td div.day div.birthday{
        background:#99aadd;
        font-size:12px;
        display:block;
        width:100%;
    }

    table.month td div.day div.birthday div{
        padding:5px;
    }



</style>
<pre>

<?php

$output_year = 2021;


$dt_start = new DateTime($output_year.'-01-01');
$dt_end = new DateTime(($output_year+1).'-01-01');

$file = './data/contacts.csv';

$contacts = array_map('str_getcsv', file($file));
array_walk($contacts, function(&$a) use ($contacts) {
    $a = array_combine($contacts[0], $a);
});
array_shift($contacts); # remove column header

$bdays = [];
$now = new DateTime();


foreach($contacts as $contact){
    if(!$contact['Birthday'])
        continue;
    $dt_bday = new DateTime($contact['Birthday']);
    $bday = $dt_bday->format('Y-m-d');
    $dt_bday_now = new DateTime($output_year.'-'.$dt_bday->format('m-d'));
    $str_bday_now = $dt_bday_now->format('Y-m-d');
    $age = $dt_bday_now->diff($dt_bday);
    if(!is_array($bdays[$str_bday_now]))
        $bdays[$str_bday_now] = [];
    $bdays[$str_bday_now][] = ['name' => $contact['Name'], 'age' => $age->y];
}

//print_r($bdays);

?>
</pre>

<?php
$period = new DatePeriod($dt_start, new DateInterval('P1M'), $dt_end);

foreach($period as $dt_month){
    $month = $dt_month->format('m');
    $monthname = $dt_month->format('F');
    $daysinmonth = $dt_month->format('t');
    $date = 1;
    $first_day_of_month = $dt_month->format('N');
    $weeksinmonth = ceil(($first_day_of_month+$daysinmonth-1)/7);
?>
<table class="month">
    <thead>
        <tr>
            <th class="monthname" colspan="7"><?php echo $monthname ?> <?php echo $output_year ?></th>
        </tr>
        <tr class="daysofweek">
            <th>Monday</th>
            <th>Tuesday</th>
            <th>Wednesday</th>
            <th>Thursday</th>
            <th>Friday</th>
            <th>Saturday</th>
            <th>Sunday</th>
        </tr>
    </thead>
    <tbody>
<?php
    foreach(range(1,$weeksinmonth) as $week){
?>
      <tr>
<?php
        foreach(range(1,7) as $dayofweek){
?>
            <td>
<?php
            if($date <= $daysinmonth && ($week > 1 || $dayofweek >= $first_day_of_month)){
                $fulldate = $output_year.'-'.$month.'-'.str_pad($date, 2, '0', STR_PAD_LEFT);
?>
                <div class="day">
                    <span class="date"><?php echo $date ?></span>
<?php
                if(array_key_exists($fulldate, $bdays)){
                    foreach($bdays[$fulldate] as $bday){
?>
                        <div class="birthday"><div><?php echo $bday['name'] ?> turns <?php echo $bday['age'] ?></div></div>

<?php
                    }
                }
?>

                </div>
<?php
                $date++;
            }
?>
            </td>
<?php
        }
?>
      </tr>
<?php
    }
?>
    </tbody>

    </table>
<?php
}




//print_r($out);

