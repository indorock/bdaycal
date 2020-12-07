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
            $bday = trim($contact['Birthday']);
            if(!$bday)
                continue;
            if(strpos($bday, '--') === 0 || (isset($contact['noyear']) && $contact['noyear'])){
                $dt_bday_now = new DateTime($this->output_year.'-'.substr($bday,2));
                $age = null;
            }else{
                $dt_bday = new DateTime($bday);
                $dt_bday_now = new DateTime($this->output_year.'-'.$dt_bday->format('m-d'));
                $age = $dt_bday_now->diff($dt_bday)->y;
            }
            $str_bday_now = $dt_bday_now->format('Y-m-d');
            if(!array_key_exists($str_bday_now, $ret) || !is_array($ret[$str_bday_now]))
                $ret[$str_bday_now] = [];
            $ret[$str_bday_now][] = ['name' => $contact['Name'], 'age' => $age];
        }
        return $ret;
    }
}