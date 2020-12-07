<?php
require('./model/vCard.php');

class Contacts{

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
            $contacts = array_map('str_getcsv', file($this->file));
            array_walk($contacts, function(&$a) use ($contacts) {
                $a = array_combine($contacts[0], $a);
            });
            array_shift($contacts); # remove column header
        }elseif($ext == 'vcf'){
            $vcard = new vCard($this->file, true);
            $contacts = $vcard->asGcal();
        }else{
            throw new Exception('unsupported_file_format');
        }
        foreach($contacts as $contact){
            $ret = $this->getDateData($contact,$ret);
            $ret = $this->getDateData($contact,$ret, 'Deathdate');
        }
        return $ret;
    }

    protected function getDateData($contact, &$ret, $key = 'Birthday'){
        if(!isset($contact[$key]))
            return $ret;
        $day = trim($contact[$key]);
        $age = null;
        if(strpos($day, '--') === 0 || (isset($contact['noyear']) && $contact['noyear'])){
            $dt_now = new DateTime($this->output_year.'-'.substr($day,2));
        }else{
            $dt_day = new DateTime($day);
            $dt_now = new DateTime($this->output_year.'-'.$dt_day->format('m-d'));
            $dt_diff = $dt_day->diff($dt_now);
            $age = $dt_diff->format('%r%y');
        }
        if($key == 'Deathdate' && $age<0)
            return $ret;

        $str_now = $dt_now->format('Y-m-d');
        $arr = ['name' => $contact['Name'], 'age' => $age, strtolower($key) => $str_now];
        if(isset($contact['Deathdate'])){
            $dt_death = new DateTime($contact['Deathdate']);
            if($dt_now > $dt_death)
                $arr['deceased'] = true;
        }
        if(!array_key_exists($str_now, $ret) || !is_array($ret[$str_now]))
            $ret[$str_now] = [];
        $ret[$str_now][] = $arr;

        return $ret;

    }
}