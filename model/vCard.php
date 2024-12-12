<?php

class vCard{

    protected $content = null;
    protected $contacts = [];
    protected $bdays_only = false;

    public function __construct($content = null, $bdays_only = false)
    {
        if ($content) {
            $isUrl  = strpos($content, 'http') === 0 && filter_var($content, FILTER_VALIDATE_URL);
            $isFile = strpos($content, "\n") === false && file_exists($content);
            if ($isUrl || $isFile) {
                $this->content = file_get_contents($content);
            }
            $this->bdays_only = $bdays_only;
        }
    }

    public function asGcal(){
        $ret = [];
        $this->parse();
        /** @var vCard_Contact $contact */
        foreach($this->contacts as $contact){
            $arr = ['Name' => $contact->getFullname(), 'Birthday' => $contact->getBirthdate()];
            if($contact->getNoyear())
                $arr['noyear'] = $contact->getNoyear();
            if($contact->getDeathdate())
                $arr['Deathdate'] = $contact->getDeathdate();
            $ret[] = $arr;
        }
        return $ret;
    }

    public function parse()
    {
        $this->content = str_replace("\r\n ", '', $this->content);
        // Contacts
        preg_match_all('`BEGIN:VCARD(.+)END:VCARD`Us', $this->content, $m);
        foreach ($m[0] as $c) {
            if($this->bdays_only && !preg_match(vCard_Contact::$bday_regexp, $c, $m))
                continue;
            $this->contacts[] = new vCard_Contact($c);
        }

        return $this;
    }

}

class vCard_Contact{

    protected $fullname = '';
    protected $email = '';
    protected $birthdate = '';
    protected $deathdate = '';
    protected $noyear = false;
    public static $bday_regexp = '`^BDAY(;X-APPLE-OMIT-YEAR=[0-9]{4})?:(.*)$`m';

    public function __construct($content = null)
    {
        if ($content) {
            $this->parse($content);
        }
    }

    public function getFullname(){
        return $this->fullname;
    }

    public function getBirthdate(){
        return $this->birthdate;
    }

    public function getDeathdate(){
        return $this->deathdate;
    }

    public function getNoyear(){
        return $this->noyear;
    }

    public function parse($content)
    {
        $content = str_replace("\r\n ", '', $content);

        // Full name
        if (preg_match('`^FN:(.*)$`m', $content, $m))
            $this->fullname = trim($m[1]);

        // Email
        if (preg_match('`^EMAIL([^:]+):(.*)$`m', $content, $m))
            $this->email= trim($m[2]);

        // Birthdate
        if (preg_match(self::$bday_regexp, $content, $m)){
            $date = trim($m[2]);
            if(preg_match('/X-APPLE-OMIT-YEAR/',$content)) {
                $this->noyear = true;
                $this->birthdate = substr($date,0,4).'-'.substr($date,5,2).'-'.substr($date,8,2);
            }elseif(preg_match('/\-\-[0-9]{4}/', $date, $matches)){
                $this->noyear = true;
                $this->birthdate = substr($date,2,2).'-'.substr($date,4,2);
            }else{
                $this->birthdate = substr($date,0,4).'-'.substr($date,5,2).'-'.substr($date,8,2);
            }
        }

        // Death
        if (preg_match('`^NOTE:death:(.*)$`m', $content, $m)){
            $this->deathdate = trim($m[1]);
        }

        return $this;
    }}