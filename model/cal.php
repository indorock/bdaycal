<?php

require('./model/contacts.php');
use Spipu\Html2Pdf\Html2Pdf;

class Cal{

    protected $output_year = null;
    protected $contacts = null;

    public function __construct($output_year){
        if(!$output_year) throw new Exception('missing_year');
        $this->output_year = $output_year;
        $file = './data/contacts.csv';
        $this->contacts = new Contacts($output_year, $file);
    }

    public function show($as_pdf= false){
        $ret = '';
        if($as_pdf)
            $ret .= '<link href="css/styles_pdf.css" rel="stylesheet" type="text/css" />';
        $bdays = $this->contacts->parse();

        $dt_start = new DateTime($this->output_year.'-01-01');
        $dt_end = new DateTime(($this->output_year+1).'-01-01');
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
                                $ret .= '<div class="birthday"><div>'. $bday['name'] .' turns '. $bday['age'] ."</div></div>\r\n";
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
    }

}
