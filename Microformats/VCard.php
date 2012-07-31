<?php
/**
 * @package Microformats
 *
 * Adapted from http://code.google.com/p/collabtiv/source/browse/include/class.vCard.php?r=1
 */
namespace sb;

class Microformats_VCard 
    {
    private $output;
    private $output_format;
    private $first_name;
    private $middle_name;
    private $last_name;
    private $edu_title;
    private $addon;
    private $nickname;
    private $company;
    private $organisation;
    private $department;
    private $job_title;
    private $note;
    private $tel_work1_voice;
    private $tel_work2_voice;
    private $tel_home1_voice;
    private $tel_home2_voice;
    private $tel_cell_voice;
    private $tel_car_voice;
    private $tel_pager_voice;
    private $tel_additional;
    private $tel_work_fax;
    private $tel_home_fax;
    private $tel_isdn;
    private $tel_preferred;
    private $tel_telex;
    private $work_street;
    private $work_zip;
    private $work_city;
    private $work_region;
    private $work_country;
    private $home_street;
    private $home_zip;
    private $home_city;
    private $home_region;
    private $home_country;
    private $postal_street;
    private $postal_zip;
    private $postal_city;
    private $postal_region;
    private $postal_country;
    private $url_work;
    private $role;
    private $birthday;
    private $email;
    private $rev;
    private $lang;
    private $photo;

    private $phones_home = Array();
    private $phones_word = Array();

    function __construct($lang = '') {
        $this->card_filename = (string) time() . '.vcf';
        $this->rev = (string) date('Ymd\THi00\Z', time());
        $this->set_language($lang);
    }
    private function set_string($var, $value = '') 
    {
        if (strlen(trim($value)) > 0) {
            $this->$var = (string) $value;
        }
    }
    public function set_first_name($string = '') 
    {
        $this->set_string('first_name', $string);
    }
    public function set_middle_name($string = '') 
    {
        $this->set_string('middle_name', $string);
    }
    public function set_last_name($string = '') 
    {
        $this->set_string('last_name', $string);
    }
    public function set_education_title($string = '') 
    {
        $this->set_string('edu_title', $string);
    }
    public function set_addon($string = '') 
    {
        $this->set_string('addon', $string);
    }
    public function set_nick_name($string = '') 
    {
        $this->set_string('nickname', $string);
    }
    public function set_company($string = '') 
    {
        $this->set_string('company', $string);
    }
    public function set_organisation($string = '') 
    {
        $this->set_string('organisation', $string);
    }
    public function set_department($string = '') 
    {
        $this->set_string('department', $string);
    }
    public function set_job_title($string = '') 
    {
        $this->set_string('job_title', $string);
    }
    public function set_note($string = '') 
    {
        $this->set_string('note', $string);
    }
    public function set_telephone_work($string = '') 
    {
        $this->phones_work[] = $string;
    }
    public function set_telephone_home($string = '', $num=1) 
    {
        $this->phones_home[] = $string;
    }
    public function set_cell_phone($string = '') 
    {
        $this->set_string('tel_cell_voice', $string);
    }
    public function set_carphone($string = '') 
    {
        $this->set_string('tel_car_voice', $string);
    }
    public function set_pager($string = '') 
    {
        $this->set_string('tel_pager_voice', $string);
    }
    public function set_additional_telephone($string = '') 
    {
        $this->set_string('tel_additional', $string);
    }
    public function set_fax_work($string = '') 
    {
        $this->set_string('tel_work_fax', $string);
    }
    public function set_fax_home($string = '') 
    {
        $this->set_string('tel_home_fax', $string);
    }
    public function set_ISDN($string = '') 
    {
        $this->set_string('tel_isdn', $string);
    }
    public function set_preferred_telephone($string = '') 
    {
        $this->set_string('tel_preferred', $string);
    }
    public function set_telex($string = '') 
    {
        $this->set_string('tel_telex', $string);
    }
    public function set_work_street($string = '') 
    {
        $this->set_string('work_street', $string);
    }
    public function set_work_ZIP($string = '') 
    {
        $this->set_string('work_zip', $string);
    }
    public function set_work_city($string = '') 
    {
        $this->set_string('work_city', $string);
    }
    public function set_work_region($string = '') 
    {
        $this->set_string('work_region', $string);
    }
    public function set_work_country($string = '') 
    {
        $this->set_string('work_country', $string);
    }
    public function set_home_street($string = '') 
    {
        $this->set_string('home_street', $string);
    }
    public function set_home_ZIP($string = '') 
    {
        $this->set_string('home_zip', $string);
    }
    public function set_home_city($string = '') 
    {
        $this->set_string('home_city', $string);
    }
    public function set_home_region($string = '') 
    {
        $this->set_string('home_region', $string);
    }
    public function set_home_country($string = '') 
    {
        $this->set_string('home_country', $string);
    }
    public function set_postal_street($string = '') 
    {
        $this->set_string('postal_street', $string);
    }
    public function set_postal_ZIP($string = '') 
    {
        $this->set_string('postal_zip', $string);
    }
    public function set_postal_city($string = '') 
    {
        $this->set_string('postal_city', $string);
    }
    public function set_postal_region($string = '') 
    {
        $this->set_string('postal_region', $string);
    }
    public function set_postal_country($string = '') 
    {
        $this->set_string('postal_country', $string);
    }
    public function set_URL_work($string = '') 
    {
        $this->set_string('url_work', $string);
    }
    public function set_role($string = '') 
    {
        $this->set_string('role', $string);
    }
    public function set_email($string = '') 
    {
        $this->set_string('email', $string);
    }
    private function set_language($isocode = '') 
    {
        $this->lang = (string) (($this->is_valid_language_code($isocode) == true) ? ';LANGUAGE=' . $isocode : '');
    }
    public function set_birthday($timestamp) 
    {
        $this->birthday = (int) date('Ymd', $timestamp);
    }
    public function set_photo($type, $photo) 
    {
        $this->photo = "PHOTO;TYPE=$type;ENCODING=BASE64:" . base64_encode($photo);
    }

    private function quoted_printable_encode($quotprint) 
    {

        $quotprint = (string) str_replace('\r\n', chr(13) . chr(10), $quotprint);
        $quotprint = (string) str_replace('\n', chr(13) . chr(10), $quotprint);
        $quotprint = (string) preg_replace("~([\x01-\x1F\x3D\x7F-\xFF])~e", "sprintf('=%02X', ord('\\1'))", $quotprint);
        $quotprint = (string) str_replace('\=0D=0A', '=0D=0A', $quotprint);
        return (string) $quotprint;
    }
    public static function is_valid_language_code($code) 
    {
        return (boolean) ((preg_match('(^([a-zA-Z]{2})((_|-)[a-zA-Z]{2})?$)', trim($code)) > 0) ? true : false);
    }
    private function generate_card_output($format) 
    {
        $this->output_format = (string) $format;
        if ($this->output_format == 'vcf') {
            $this->output = (string) "BEGIN:VCARD\r\n";
            $this->output .= (string) "VERSION:2.1\r\n";
            $this->output .= (string) "N;ENCODING=QUOTED-PRINTABLE:" . $this->quoted_printable_encode($this->last_name . ";" . $this->first_name . ";" . $this->middle_name . ";" . $this->addon) . "\r\n";
            $this->output .= (string) "FN;ENCODING=QUOTED-PRINTABLE:" . $this->quoted_printable_encode($this->first_name . " " . $this->middle_name . " " . $this->last_name . " " . $this->addon) . "\r\n";
            if (strlen(trim($this->nickname)) > 0) {
                $this->output .= (string) "NICKNAME;ENCODING=QUOTED-PRINTABLE:" . $this->quoted_printable_encode($this->nickname) . "\r\n";
            }
            $this->output .= (string) "ORG" . $this->lang . ";ENCODING=QUOTED-PRINTABLE:" . $this->quoted_printable_encode($this->organisation) . ";" . $this->quoted_printable_encode($this->department) . "\r\n";
            if (strlen(trim($this->job_title)) > 0) {
                $this->output .= (string) "TITLE" . $this->lang . ";ENCODING=QUOTED-PRINTABLE:" . $this->quoted_printable_encode($this->job_title) . "\r\n";
            }
            if (isset($this->note)) {
                $this->output .= (string) "NOTE" . $this->lang . ";ENCODING=QUOTED-PRINTABLE:" . $this->quoted_printable_encode($this->note) . "\r\n";
            }

            foreach($this->phones_work as $phone) {
                $this->output .= (string) "TEL;WORK;VOICE:" . $phone . "\r\n";
            }

            foreach($this->phones_home as $phone) {
                $this->output .= (string) "TEL;HOME;VOICE:" . $phone . "\r\n";
            }

            if (isset($this->tel_cell_voice)) {
                $this->output .= (string) "TEL;CELL;VOICE:" . $this->tel_cell_voice . "\r\n";
            }
            if (isset($this->tel_car_voice)) {
                $this->output .= (string) "TEL;CAR;VOICE:" . $this->tel_car_voice . "\r\n";
            }
            if (isset($this->tel_additional)) {
                $this->output .= (string) "TEL;VOICE:" . $this->tel_additional . "\r\n";
            }
            if (isset($this->tel_pager_voice)) {
                $this->output .= (string) "TEL;PAGER;VOICE:" . $this->tel_pager_voice . "\r\n";
            }
            if (isset($this->tel_work_fax)) {
                $this->output .= (string) "TEL;WORK;FAX:" . $this->tel_work_fax . "\r\n";
            }
            if (isset($this->tel_home_fax)) {
                $this->output .= (string) "TEL;HOME;FAX:" . $this->tel_home_fax . "\r\n";
            }

            if (isset($this->tel_isdn)) {
                $this->output .= (string) "TEL;ISDN:" . $this->tel_isdn . "\r\n";
            }
        
            if (isset($this->tel_preferred)) {
                $this->output .= (string) "TEL;PREF:" . $this->tel_preferred . "\r\n";
            }
            $this->output .= (string) "ADR;WORK:;" . $this->company . ";" . $this->work_street . ";" . $this->work_city . ";" . $this->work_region . ";" . $this->work_zip . ";" . $this->work_country . "\r\n";
            $this->output .= (string) "LABEL;WORK;ENCODING=QUOTED-PRINTABLE:" . $this->quoted_printable_encode($this->company) . "=0D=0A" . $this->quoted_printable_encode($this->work_street) . "=0D=0A" . $this->quoted_printable_encode($this->work_city) . ", " . $this->quoted_printable_encode($this->work_region) . " " . $this->quoted_printable_encode($this->work_zip) . "=0D=0A" . $this->quoted_printable_encode($this->work_country) . "\r\n";
            $this->output .= (string) "ADR;HOME:;" . $this->home_street . ";" . $this->home_city . ";" . $this->home_region . ";" . $this->home_zip . ";" . $this->home_country . "\r\n";
            $this->output .= (string) "LABEL;HOME;ENCODING=QUOTED-PRINTABLE:" . $this->quoted_printable_encode($this->home_street) . "=0D=0A" . $this->quoted_printable_encode($this->home_city) . ", " . $this->quoted_printable_encode($this->home_region) . " " . $this->quoted_printable_encode($this->home_zip) . "=0D=0A" . $this->quoted_printable_encode($this->home_country) . "\r\n";
            $this->output .= (string) "ADR;POSTAL:;" . $this->postal_street . ";" . $this->postal_city . ";" . $this->postal_region . ";" . $this->postal_zip . ";" . $this->postal_country . "\r\n";
            $this->output .= (string) "LABEL;POSTAL;ENCODING=QUOTED-PRINTABLE:" . $this->quoted_printable_encode($this->postal_street) . "=0D=0A" . $this->quoted_printable_encode($this->postal_city) . ", " . $this->quoted_printable_encode($this->postal_region) . " " . $this->quoted_printable_encode($this->postal_zip) . "=0D=0A" . $this->quoted_printable_encode($this->postal_country) . "\r\n";
            if (isset($this->url_work)) {
                $this->output .= (string) "URL;WORK:" . $this->url_work . "\r\n";
            }
            if (isset($this->role)) {
                $this->output .= (string) "ROLE" . $this->lang . ":" . $this->role . "\r\n";
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
                if(isset($this->photo)) {
                $this->output .= (string) $this->photo . "\r\n";
            }
            $this->output .= (string) "REV:" . $this->rev . "\r\n";
            $this->output .= (string) "END:VCARD\r\n";
        }
    }
    public function get_card_output($format = "vcf") 
    {
        if (!isset($this->output) || $this->output_format != $format) {
            $this->generate_card_output($format);
        }
        return (string) $this->output;
    }
    public function output_file($format = 'vcf') 
    {
        if ($format == 'vcf') {
            header('Content-Type: text/x-vcard');
            header('Content-Disposition: attachment; filename=vCard_' . date('Y-m-d_H-m-s') . '.vcf');
            echo $this->get_card_output('vcf');
        }
    }
    private function get_card_file_path() 
    {
        $path_parts = pathinfo($_SERVER['SCRIPT_NAME']);
        $port = (string) (($_SERVER['SERVER_PORT'] != 80) ? ':' . $_SERVER['SERVER_PORT'] : '');
        return (string) 'http://' . $_SERVER['SERVER_NAME'] . $port . $path_parts["dirname"] . '/' . $this->download_dir . '/' . $this->card_filename;
    }
}

?>