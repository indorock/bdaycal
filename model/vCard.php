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
            $ret[] = ['Name' => $contact->getFullname(), 'Birthday' => $contact->getBirthdate(), 'noyear' => $contact->getNoyear()];
        }
        return $ret;
    }

    public function parse()
    {
        $this->content = str_replace("\r\n ", '', $this->content);

        // Contacts
        preg_match_all('`BEGIN:VCARD(.+)END:VCARD`Us', $this->content, $m);
        foreach ($m[0] as $c) {
            if($this->bdays_only && !preg_match('`^BDAY:(.*)$`m', $c, $m))
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
    protected $noyear = false;

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
        if (preg_match('`^BDAY:(.*)$`m', $content, $m)){
            $date = trim($m[1]);
            if(preg_match('/\-\-[0-9]{4}/', $date, $matches)){
                $this->noyear = true;
                $this->birthdate = substr($date,2,2).'-'.substr($date,4,2);
            }else{
                $this->birthdate = substr($date,0,4).'-'.substr($date,4,2).'-'.substr($date,6,2);
            }
        }
        return $this;
    }}