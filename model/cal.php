<?php

require('./model/contacts.php');
require('./model/events.php');
use Spipu\Html2Pdf\Html2Pdf;

class Cal{

    protected $output_year = null;
    protected $contacts = null;

    public function __construct($output_year){
        $this->output_year = $output_year ?? date('Y');
        $this->contacts = new Contacts($output_year, './data/contacts-moeder.vcf');
        $calendars = [
          ['name' => 'events-moeder', 'file' => './data/events-moeder.ics', 'whitelist' => []],
          ['name' => 'canada-holidays', 'file' => 'https://www.officeholidays.com/ics-clean/canada', 'clean_labels' => true, 'whitelist' => ["st. patrick's day","victoria day","canada day","thanksgiving","remembrance day"]],
          ['name' => 'nl-holidays', 'file' => './data/nl-feestdagen.ics', 'whitelist' => []]
        ];
        foreach($calendars as $cal)
            $this->calendars[] = new Events($output_year, $cal);
    }

    public function show($as_pdf= false, $now = false){
        $ret = '';
        if($as_pdf)
            $ret .= '<link href="css/styles_pdf.css" rel="stylesheet" type="text/css" />';
        $bdays = $this->contacts->parse();
        $events = [];
        foreach($this->calendars as $calendar)
            $events = array_merge_recursive($events, $calendar->parse());
        if($now){
            $dt_start = new DateTime('first day of this month');
            $dt_end = clone $dt_start;
            $dt_end->modify('+1 month');
        }else{
            $dt_start = new DateTime($this->output_year.'-01-01');
            $dt_end = new DateTime(($this->output_year+1).'-01-01');
        }
        $period = new DatePeriod($dt_start, new DateInterval('P1M'), $dt_end);

        foreach($period as $dt_month){
            $month = $dt_month->format('m');
            $monthname = $dt_month->format('F');
            $daysinmonth = (int)$dt_month->format('t');
            $date = 1;
            $first_day_of_month = (int)$dt_month->format('N');
            $weeksinmonth = ceil(($first_day_of_month+$daysinmonth-1)/7);
            if($as_pdf)
                $ret .= "<page>";
            $ret .= <<<EOT
            <table class="month">
                <thead>
                <tr><th class="monthname" colspan="7">$monthname $this->output_year</th></tr>
                <tr class="daysofweek"><th>Monday</th><th>Tuesday</th><th>Wednesday</th><th>Thursday</th><th>Friday</th><th>Saturday</th><th>Sunday</th></tr>
                </thead>
                <tbody>
EOT;

            foreach(range(1,$weeksinmonth) as $week){
                $ret .= "<tr>\r\n";
                foreach(range(1,7) as $dayofweek){
                    $ret .= "<td>\r\n";
                    if($date <= $daysinmonth && ($week > 1 || $dayofweek >= $first_day_of_month)){
                        $fulldate = $this->output_year.'-'.$month.'-'.str_pad($date, 2, '0', STR_PAD_LEFT);
                        $ret .= '<div class="day"><span class="date">'.$date."</span>\r\n";
                        if(array_key_exists($fulldate, $bdays)){
                            foreach($bdays[$fulldate] as $bday){
                                $name = preg_replace('/([A-Za-z]+) ([A-Za-z])(.+)/','$1 $2.',$bday['name']);
                                if(isset($bday['birthday'])){
                                    $ret .= '<div class="birthday"><div>'. $name;
                                    if($bday['age']){
                                        if(isset($bday['deceased']) && $bday['deceased'] == true)
                                            $ret .= ' '. $bday['age'];
                                        else
                                            $ret .= ' turns '. $bday['age'];
                                    }
                                }elseif($bday['deathdate']){
                                    $ret .= '<div class="deathdate"><div>'. $name ."† ";
                                    if($bday['age'])
                                        $ret .= $bday['age'] .' years ago';
                                }
                                $ret .= "</div></div>\r\n";
                            }
                        }
                        if(array_key_exists($fulldate, $events)){
                            foreach($events[$fulldate] as $event){
                                $ret .= '<div class="event"><div>'. $event."</div></div>\r\n";
                            }
                        }
                        $ret .= "</div>\r\n";
                        $date++;
                    }
                    $ret .= "</td>\r\n";
                }
                $ret .= "</tr>\r\n";
            }
            $ret .= "</tbody></table>\r\n";
            if($as_pdf)
                $ret .= "</page>";
        }
        if(!$as_pdf)
            return $ret;

        $html2pdf = new Html2Pdf('L','A4', 'en', true,'UTF-8', [0,0,0,0]);
        $html2pdf->writeHTML($ret);
        $html2pdf->output();
        return false;
    }

}
