<?php

namespace Base;


use Rollbar;
use Unirest;

class String
{
    
    public $return_data;

    /**
     * Displays an array in a easy-to-read format
     *
     * @param string $str
     * @param string $namn
     * @param string $beskrivning
     * @param bool $print
     * @return string
     */
    public static function print_pre($str = '', $namn = '', $beskrivning = '', $print = true)
    {
        $return = '';    
        $return .= '<div style="background-color:#f8f8f8;border:solid 1px #dcdcdc; margin:5px 0 5px 0;padding-left:10px;"><pre style="font-size:11px; font-family:Arial;">';
        if(!empty($namn)) {
            $return .= '<span style="font-size:15px; font-weight:bold; background-color:#d5ebf2;border:solid 1px #dcdcdc; margin-bottom:10px; padding:4px;"">'. $namn .'</span><br /><br />';
        }
        if(!empty($beskrivning)) {
            $return .= '<span style="font-size:12px; font-weight:normal; background-color:#e9fbff;border:solid 1px #ededed; margin-bottom:10px; padding:4px;"">'. $beskrivning .'</span><br /><br />';
        }
        if(!empty($str)) {
            $return .= print_r($str, true);
        }
            
       
        $return .= '</pre></div>';
        
         if($print) {
             echo $return;
         } else {
             return $return;
         }
    }



    /**
     * Returns the IP-address
     * @return string
     */
    public static function getIP()
    {

        if(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $retval = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $retval = $_SERVER['REMOTE_ADDR'];;
        }

        if($retval == '::1') {
            $retval = "127.0.0.1";
        }

        return $retval;

    }

    /**
     * Returns the external IP-address
     *
     * @return bool
     */
    public static function getExternalIP()
    {

        // Fetch datacenters
        $response = \Unirest\Request::get("https://www.ovpn.se/v1/api/ip");

        // Verify respons
        if($response->code != 200) {

            $response = \Unirest\Request::get("http://canihazip.com/s");

            if($response->code != 200) {
                \Base\Log::message($response->body->error);
                return false;
            } else {
                $ip = $response->raw_body;
            }
        } else {
            $ip = $response->body->ip;
        }

        return $ip;
    }


    /**
     * Beräkna tidsskillnad
     *
     * @param $start
     * @param bool $end
     * @internal param $sec
     * @return string
     */
    public static function print_time($start = false, $end)
    {

        if(!$start) {
            $date = new \DateTime('now', new \DateTimeZone('Europe/Stockholm'));
            $start = $date->getTimestamp();
        }

        $difference = $end-$start;

        if($difference < 59) {
            return $difference . ' ' . _('sekunder');
        } elseif($difference < 3600) {
            $val = round($difference/60);

            if($val == 1) {
                return $val . ' ' . _('minut');
            } else {
                return $val . ' ' . _('minuter');
            }

        } elseif($difference < 86400) {

            $val = round($difference/3600);

            if($val == 1) {
                return $val . ' ' . _('timma');
            } else {
                return $val . ' ' . _('timmar');
            }

        } else {
            $val = round($difference/86400);

            if($val == 1) {
                return $val . ' ' . _('dag');
            } else {
                return $val . ' ' . _('dagar');
            }
        }

    }

    /**
     * Removes HTML & javascript from the string.
     * 
     * @param string $str
     * @return string
     */
    public static function outputCleanString($str)
    {

        if(is_numeric($str)) {
            return $str;
        } else if(!is_string($str)) {
            return "";
        } else {
            $str = htmlentities($str, ENT_QUOTES, 'UTF-8');

            return $str;
        }

    }

    /**
     * Converts an abbreviation of an addon to readable format.
     *
     * @param $abbreviation
     * @return string
     */
    public static function addonAbbreviationToText($abbreviation)
    {

        if($abbreviation == "proxy") {
            $text = _('Proxy');
        } elseif($abbreviation == "filtering") {
            $text = _('Filtrering');
        } elseif($abbreviation == "public-ipv4") {
            $text = _('Publik IPv4');
        } elseif($abbreviation == "multihop") {
            $text = _('Multihop');
        } else {
            $text = '';
        }

        return $text;
    }

    /**
     * Removes duplicate values in an array
     *
     * @param $array
     * @param $field
     * @return array
     */
    public static function removeDuplicate($array, $field)
    {
        foreach ($array as $sub)
            $cmp[] = $sub[$field];

        $unique = array_unique(array_reverse($cmp,true));

        foreach ($unique as $k => $rien)
            $new[] = $array[$k];

        return $new;
    }

    /**
     * Parses the output of a process in order to display the time it has been running
     *
     * @param $uptime
     * @return string
     */
    public static function parseProcessUptime($uptime) {

        $explode = explode("-", $uptime);
        $retVal = array();

        if(count($explode) == 2) {

            $days = trim($explode[0]);

            if($days > 1) {
                $retVal['days'] = $days . ' dagar';
            } else {
                $retVal['days'] = $days . ' dag';
            }

            $rest = trim($explode[1]);
        } else {
            $rest = trim($explode[0]);
        }

        $format = explode(":", $rest);

        if($format[0] > 1 || $format[0] == 0) {
            $retVal['hours'] = round($format[0]) . ' timmar';
        } else {
            $retVal['hours'] = round($format[0]) . ' timma';
        }

        if($format[1] > 1 || $format[1] == 0)  {
            $retVal['minutes'] = round($format[1]) . ' minuter';
        } else {
            $retVal['minutes'] = round($format[1]) . ' minut';
        }

        if($format[2] > 1 || $format[2] == 0) {
            $retVal['seconds'] = round($format[2]) . ' sekunder';
        } else {
            $retVal['seconds'] = round($format[2]) . ' sekund';
        }

        return implode(', ', $retVal);
    }

}

?>