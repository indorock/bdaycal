<pre>
<?php
require('./model/iCal.php');

class Events{

    protected $file = null;
    protected $clean_labels = null;
    protected $output_year = null;
    protected $whitelist = [];

    public function __construct($output_year, $calendar_data) {
        $file = $calendar_data['file'];
        $this->clean_labels = isset($calendar_data['clean_labels']);
        if(isset($calendar_data['whitelist']))
            $this->whitelist = $calendar_data['whitelist'];

        if(!$output_year) throw new Exception('missing_year');
        if(!$file) throw new Exception('missing_input_file');
        $this->file = $file;
        $this->output_year = $output_year;
    }

    public function parse(){

        $is_url = preg_match('/https?:\/\//', $this->file, $m);
        $ret = [];
        $parts = explode('.', $this->file);
        if(!$is_url && count($parts) < 2)
            throw new Exception('filename_bad_format');

        $ext = end($parts);
        if($ext == 'csv'){
            $events = array_map('str_getcsv', file($this->file));
            array_walk($events, function(&$a) use ($events) {
                $a = array_combine($events[0], $a);
            });
            array_shift($contacts); # remove column header
        }elseif($ext == 'ics' || $is_url){
            $ical = new iCal($this->file, true);
            $events = $ical->asArray($this->output_year);
        }else{
            throw new Exception('unsupported_file_format');
        }
        foreach($events as $event){
            $date = $event['date'];
            if(substr($date,0,4) != $this->output_year)
                continue;
            if(!array_key_exists($date, $ret) || !is_array($ret[$date]))
                $ret[$date] = [];
            $name = $event['name'];
            if($this->clean_labels)
                $name = trim(preg_replace('/\([^()]+\)/','',$name));
            if(count($this->whitelist) && !in_array(strtolower($name), $this->whitelist))
                continue;
            if(isset($event['yearswed'])){
                $name = substr($name, 0, strpos($name,"Anniversary")) . $this->addOrdinalNumberSuffix($event['yearswed']) . ' Anniversary';
            }
            $ret[$date][] = $name;
            $ret[$date] = array_unique($ret[$date]);
        }
        return $ret;
    }


    protected function addOrdinalNumberSuffix($num) {
        if (!in_array(($num % 100),array(11,12,13))){
            switch ($num % 10) {
                // Handle 1st, 2nd, 3rd
                case 1:  return $num.'st';
                case 2:  return $num.'nd';
                case 3:  return $num.'rd';
            }
        }
        return $num.'th';
    }
}