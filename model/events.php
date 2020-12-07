<?php
require('./model/iCal.php');

class Events{

    protected $file = null;
    protected $output_year = null;

    public function __construct($output_year, $file) {
        if(!$output_year) throw new Exception('missing_year');
        if(!$file) throw new Exception('missing_input_file');
        $this->file = $file;
        $this->output_year = $output_year;
    }

    public function parse(){

        $ret = [];
        $parts = explode('.', $this->file);
        if(count($parts) < 2)
            throw new Exception('filename_bad_format');
        $ext = end($parts);
        if($ext == 'csv'){
            $events = array_map('str_getcsv', file($this->file));
            array_walk($events, function(&$a) use ($events) {
                $a = array_combine($events[0], $a);
            });
            array_shift($contacts); # remove column header
        }elseif($ext == 'ics'){
            $ical = new iCal($this->file, true);
            $events = $ical->asArray();
        }else{
            throw new Exception('unsupported_file_format');
        }
        foreach($events as $event){
            $date = $event['date'];
            if(substr($date,0,4) != $this->output_year)
                continue;
            if(!array_key_exists($date, $ret) || !is_array($ret[$date]))
                $ret[$date] = [];
            $ret[$date][] = ['name' => $event['name']];
            $ret[$date] = array_unique($ret[$date]);
        }
        return $ret;
    }
}