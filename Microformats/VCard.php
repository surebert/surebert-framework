<?php

/**
 * @package Microformats
 *
 * Adapted from http://code.google.com/p/collabtiv/source/browse/include/class.vCard.php?r=1
 */
namespace sb\Microformats;

class VCard
{

    protected $output;
    protected $output_format;
    protected $first_name;
    protected $middle_name;
    protected $last_name;
    protected $edu_title;
    protected $addon;
    protected $nickname;
    protected $company;
    protected $organisation;
    protected $department;
    protected $job_title;
    protected $note;
    protected $tel_work1_voice;
    protected $tel_work2_voice;
    protected $tel_home1_voice;
    protected $tel_home2_voice;
    protected $tel_cell_voice;
    protected $tel_car_voice;
    protected $tel_pager_voice;
    protected $tel_additional;
    protected $tel_work_fax;
    protected $tel_home_fax;
    protected $tel_isdn;
    protected $tel_preferred;
    protected $tel_telex;
    protected $work_street;
    protected $work_zip;
    protected $work_city;
    protected $work_region;
    protected $work_country;
    protected $home_street;
    protected $home_zip;
    protected $home_city;
    protected $home_region;
    protected $home_country;
    protected $postal_street;
    protected $postal_zip;
    protected $postal_city;
    protected $postal_region;
    protected $postal_country;
    protected $url_work;
    protected $role;
    protected $birthday;
    protected $email;
    protected $rev;
    protected $lang;
    protected $photo;
    protected $phones_home = Array();
    protected $phones_word = Array();

    public function __construct($lang = '')
    {
        $this->card_filename = (string) time() . '.vcf';
        $this->rev = (string) date('Ymd\THi00\Z', time());
        $this->setLanguage($lang);
    }

    protected function setString($var, $value = '')
    {
        if (strlen(trim($value)) > 0) {
            $this->$var = (string) $value;
        }
    }

    public function setFirstName($string = '')
    {
        $this->setString('first_name', $string);
    }

    public function setMiddleName($string = '')
    {
        $this->setString('middle_name', $string);
    }

    public function setLastName($string = '')
    {
        $this->setString('last_name', $string);
    }

    public function setEducationTitle($string = '')
    {
        $this->setString('edu_title', $string);
    }

    public function setAddon($string = '')
    {
        $this->setString('addon', $string);
    }

    public function setNickName($string = '')
    {
        $this->setString('nickname', $string);
    }

    public function setCompany($string = '')
    {
        $this->setString('company', $string);
    }

    public function setOrganization($string = '')
    {
        $this->setString('organisation', $string);
    }

    public function setDepartment($string = '')
    {
        $this->setString('department', $string);
    }

    public function setJobTitle($string = '')
    {
        $this->setString('job_title', $string);
    }

    public function setNote($string = '')
    {
        $this->setString('note', $string);
    }

    public function setTelephoneWork($string = '')
    {
        $this->phones_work[] = $string;
    }

    public function setTelephoneHome($string = '', $num = 1)
    {
        $this->phones_home[] = $string;
    }

    public function setCellPhone($string = '')
    {
        $this->setString('tel_cell_voice', $string);
    }

    public function setCarPhone($string = '')
    {
        $this->setString('tel_car_voice', $string);
    }

    public function setPager($string = '')
    {
        $this->setString('tel_pager_voice', $string);
    }

    public function setAdditionalTelephone($string = '')
    {
        $this->setString('tel_additional', $string);
    }

    public function setFaxWork($string = '')
    {
        $this->setString('tel_work_fax', $string);
    }

    public function setFaxHome($string = '')
    {
        $this->setString('tel_home_fax', $string);
    }

    public function setIsdn($string = '')
    {
        $this->setString('tel_isdn', $string);
    }

    public function setPreferredTelephone($string = '')
    {
        $this->setString('tel_preferred', $string);
    }

    public function setTelex($string = '')
    {
        $this->setString('tel_telex', $string);
    }

    public function setWorkStreet($string = '')
    {
        $this->setString('work_street', $string);
    }

    public function setWorkZip($string = '')
    {
        $this->setString('work_zip', $string);
    }

    public function setWorkCity($string = '')
    {
        $this->setString('work_city', $string);
    }

    public function setWorkRegion($string = '')
    {
        $this->setString('work_region', $string);
    }

    public function setWorkCountry($string = '')
    {
        $this->setString('work_country', $string);
    }

    public function setHomeStreet($string = '')
    {
        $this->setString('home_street', $string);
    }

    public function setHomeZip($string = '')
    {
        $this->setString('home_zip', $string);
    }

    public function setHomeCity($string = '')
    {
        $this->setString('home_city', $string);
    }

    public function setHomeRegion($string = '')
    {
        $this->setString('home_region', $string);
    }

    public function setHomeCountry($string = '')
    {
        $this->setString('home_country', $string);
    }

    public function setPostalStreet($string = '')
    {
        $this->setString('postal_street', $string);
    }

    public function setPostalZip($string = '')
    {
        $this->setString('postal_zip', $string);
    }

    public function setPostalCity($string = '')
    {
        $this->setString('postal_city', $string);
    }

    public function setPostalRegion($string = '')
    {
        $this->setString('postal_region', $string);
    }

    public function setPostalCountry($string = '')
    {
        $this->setString('postal_country', $string);
    }

    public function setUrlWork($string = '')
    {
        $this->setString('url_work', $string);
    }

    public function setRole($string = '')
    {
        $this->setString('role', $string);
    }

    public function setEmail($string = '')
    {
        $this->setString('email', $string);
    }

    public function setLanguage($isocode = '')
    {
        $this->lang = (string) (($this->isValidLanguageCode($isocode) == true) ? ';LANGUAGE=' . $isocode : '');
    }

    public function setBirthday($timestamp)
    {
        $this->birthday = (int) date('Ymd', $timestamp);
    }

    public function setPhoto($type, $photo)
    {
        $this->photo = "PHOTO;TYPE=$type;ENCODING=BASE64:" . base64_encode($photo);
    }

    protected function quotedPrintableEncode($quotprint)
    {

        $quotprint = (string) str_replace('\r\n', chr(13) . chr(10), $quotprint);
        $quotprint = (string) str_replace('\n', chr(13) . chr(10), $quotprint);
        $quotprint = (string) preg_replace("~([\x01-\x1F\x3D\x7F-\xFF])~e",
            "sprintf('=%02X', ord('\\1'))", $quotprint);
        $quotprint = (string) str_replace('\=0D=0A', '=0D=0A', $quotprint);
        return (string) $quotprint;
    }

    public static function isValidLanguageCode($code)
    {
        return (boolean) ((preg_match('(^([a-zA-Z]{2})((_|-)[a-zA-Z]{2})?$)', trim($code)) > 0) ? true : false);
    }

    protected function generateCardOutput($format)
    {
        $this->output_format = (string) $format;
        if ($this->output_format == 'vcf') {
            $this->output = (string) "BEGIN:VCARD\r\n";
            $this->output .= (string) "VERSION:2.1\r\n";
            $this->output .= (string) "N;ENCODING=QUOTED-PRINTABLE:"
                . $this->quotedPrintableEncode($this->last_name
                    . ";" . $this->first_name . ";" . $this->middle_name . ";" . $this->addon) . "\r\n";
            $this->output .= (string) "FN;ENCODING=QUOTED-PRINTABLE:"
                . $this->quotedPrintableEncode($this->first_name . " "
                    . $this->middle_name . " " . $this->last_name
                    . " " . $this->addon) . "\r\n";
            if (strlen(trim($this->nickname)) > 0) {
                $this->output .= (string) "NICKNAME;ENCODING=QUOTED-PRINTABLE:"
                    . $this->quotedPrintableEncode($this->nickname) . "\r\n";
            }
            $this->output .= (string) "ORG"
                . $this->lang . ";ENCODING=QUOTED-PRINTABLE:"
                . $this->quotedPrintableEncode($this->organisation)
                . ";" . $this->quotedPrintableEncode($this->department)
                . "\r\n";
            if (strlen(trim($this->job_title)) > 0) {
                $this->output .= (string) "TITLE" . $this->lang
                    . ";ENCODING=QUOTED-PRINTABLE:"
                    . $this->quotedPrintableEncode($this->job_title)
                    . "\r\n";
            }
            if (isset($this->note)) {
                $this->output .= (string) "NOTE" . $this->lang
                    . ";ENCODING=QUOTED-PRINTABLE:"
                    . $this->quotedPrintableEncode($this->note)
                    . "\r\n";
            }

            foreach ($this->phones_work as $phone) {
                $this->output .= (string) "TEL;WORK;VOICE:" . $phone . "\r\n";
            }

            foreach ($this->phones_home as $phone) {
                $this->output .= (string) "TEL;HOME;VOICE:" . $phone . "\r\n";
            }

            if (isset($this->tel_cell_voice)) {
                $this->output .= (string) "TEL;CELL;VOICE:"
                    . $this->tel_cell_voice . "\r\n";
            }
            if (isset($this->tel_car_voice)) {
                $this->output .= (string) "TEL;CAR;VOICE:"
                    . $this->tel_car_voice . "\r\n";
            }
            if (isset($this->tel_additional)) {
                $this->output .= (string) "TEL;VOICE:"
                    . $this->tel_additional . "\r\n";
            }
            if (isset($this->tel_pager_voice)) {
                $this->output .= (string) "TEL;PAGER;VOICE:"
                    . $this->tel_pager_voice . "\r\n";
            }
            if (isset($this->tel_work_fax)) {
                $this->output .= (string) "TEL;WORK;FAX:"
                    . $this->tel_work_fax . "\r\n";
            }
            if (isset($this->tel_home_fax)) {
                $this->output .= (string) "TEL;HOME;FAX:"
                    . $this->tel_home_fax . "\r\n";
            }

            if (isset($this->tel_isdn)) {
                $this->output .= (string) "TEL;ISDN:"
                    . $this->tel_isdn . "\r\n";
            }

            if (isset($this->tel_preferred)) {
                $this->output .= (string) "TEL;PREF:"
                    . $this->tel_preferred . "\r\n";
            }
            $this->output .= (string) "ADR;WORK:;" . $this->company . ";"
                . $this->work_street . ";" . $this->work_city . ";"
                . $this->work_region . ";" . $this->work_zip . ";"
                . $this->work_country . "\r\n";
            $this->output .= (string) "LABEL;WORK;ENCODING=QUOTED-PRINTABLE:"
                . $this->quotedPrintableEncode($this->company) . "=0D=0A"
                . $this->quotedPrintableEncode($this->work_street) . "=0D=0A"
                . $this->quotedPrintableEncode($this->work_city) . ", "
                . $this->quotedPrintableEncode($this->work_region) . " "
                . $this->quotedPrintableEncode($this->work_zip) . "=0D=0A"
                . $this->quotedPrintableEncode($this->work_country) . "\r\n";
            $this->output .= (string) "ADR;HOME:;" . $this->home_street . ";"
                . $this->home_city . ";" . $this->home_region . ";"
                . $this->home_zip . ";" . $this->home_country . "\r\n";
            $this->output .= (string) "LABEL;HOME;ENCODING=QUOTED-PRINTABLE:"
                . $this->quotedPrintableEncode($this->home_street)
                . "=0D=0A" . $this->quotedPrintableEncode($this->home_city)
                . ", " . $this->quotedPrintableEncode($this->home_region)
                . " " . $this->quotedPrintableEncode($this->home_zip)
                . "=0D=0A" . $this->quotedPrintableEncode($this->home_country)
                . "\r\n";
            $this->output .= (string) "ADR;POSTAL:;"
                . $this->postal_street . ";"
                . $this->postal_city . ";"
                . $this->postal_region . ";"
                . $this->postal_zip . ";"
                . $this->postal_country . "\r\n";
            $this->output .= (string) "LABEL;POSTAL;ENCODING=QUOTED-PRINTABLE:"
                . $this->quotedPrintableEncode($this->postal_street)
                . "=0D=0A" . $this->quotedPrintableEncode($this->postal_city)
                . ", " . $this->quotedPrintableEncode($this->postal_region)
                . " " . $this->quotedPrintableEncode($this->postal_zip)
                . "=0D=0A" . $this->quotedPrintableEncode($this->postal_country)
                . "\r\n";
            if (isset($this->url_work)) {
                $this->output .= (string) "URL;WORK:" . $this->url_work . "\r\n";
            }
            if (isset($this->role)) {
                $this->output .= (string) "ROLE"
                    . $this->lang . ":"
                    . $this->role . "\r\n";
            }
            if (isset($this->birthday)) {
                $this->output .= (string) "BDAY:" . $this->birthday . "\r\n";
            }
            if (isset($this->email)) {
                $this->output .= (string) "EMAIL;PREF;INTERNET:" . $this->email . "\r\n";
            }
            if (isset($this->tel_telex)) {
                $this->output .= (string) "EMAIL;TLX:" . $this->tel_telex . "\r\n";
            }
            if (isset($this->photo)) {
                $this->output .= (string) $this->photo . "\r\n";
            }
            $this->output .= (string) "REV:" . $this->rev . "\r\n";
            $this->output .= (string) "END:VCARD\r\n";
        }
    }

    public function getCardOutput($format = "vcf")
    {
        if (!isset($this->output) || $this->output_format != $format) {
            $this->generateCardOutput($format);
        }
        return (string) $this->output;
    }

    public function outputFile($format = 'vcf')
    {
        if ($format == 'vcf') {
            header('Content-Type: text/x-vcard');
            header('Content-Disposition: attachment; filename=vCard_'
                . date('Y-m-d_H-m-s') . '.vcf');
            echo $this->getCardOutput('vcf');
        }
    }

    protected function getCardFilePath()
    {
        $path_parts = pathinfo($_SERVER['SCRIPT_NAME']);
        $port = (string) (($_SERVER['SERVER_PORT'] != 80) ? ':' . $_SERVER['SERVER_PORT'] : '');
        return (string) 'http://' . $_SERVER['SERVER_NAME']
            . $port . $path_parts["dirname"] . '/'
            . $this->download_dir . '/' . $this->card_filename;
    }
}

