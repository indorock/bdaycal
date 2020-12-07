<?php

class Contacts{

    protected $file = null;
    protected $output_year = null;

    public function __construct($output_year, $file) {
        if(!$output_year) throw new Exception('missing_year');
        if(!$file) throw new Exception('missing_input_fike');
        $this->file = $file;
        $this->output_year = $output_year;
    }

    public function parse(){

        $ret = [];
        $now = new DateTime();

        $contacts = array_map('str_getcsv', file($this->file));
        array_walk($contacts, function(&$a) use ($contacts) {
            $a = array_combine($contacts[0], $a);
        });
        array_shift($contacts); # remove column header



        foreach($contacts as $contact){
            if(!$contact['Birthday'])
                continue;
            $dt_bday = new DateTime($contact['Birthday']);
            $bday = $dt_bday->format('Y-m-d');
            $dt_bday_now = new DateTime($this->output_year.'-'.$dt_bday->format('m-d'));
            $str_bday_now = $dt_bday_now->format('Y-m-d');
            $age = $dt_bday_now->diff($dt_bday);
            if(!array_key_exists($str_bday_now, $ret) || !is_array($ret[$str_bday_now]))
                $bdays[$str_bday_now] = [];
            $ret[$str_bday_now][] = ['name' => $contact['Name'], 'age' => $age->y];
        }

        return $ret;
    }
}