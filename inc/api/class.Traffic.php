<?php
/**
 * Created by PhpStorm.
 * User: davidwibergh
 * Date: 15-04-21
 * Time: 23:20
 */

namespace API;


use Base\String;
use Slim\Slim;

class Traffic {

    public function get()
    {

        $app = Slim::getInstance();

        // Get the url to darkstat
        $file    = new \Shell\File();
        $content = $file->read('config.json');
        $OVPNconfig  = json_decode($content);

        if(!$content || !$OVPNconfig) {
            \Base\Log::message(_('Misslyckades att läsa config.json eller så var filen i ett felaktigt format'));
            $app->halt(500, json_encode(array('status' => false, 'error' => 'Ett tekniskt fel har inträffat.')));
        }

        $response =  \Unirest\Request::get(
            $OVPNconfig->darkstat . 'graphs.xml',
            array(
                "Accept" => "text/xml"
            )
        );

        // Verify respons
        if($response->code != 200) {
            \Base\Log::message($response->body->error);
            $app->halt($response->code, json_encode(array('status' => false, 'error' => $response->body->error)));
        }

        $trafficData = new \SimpleXMLElement($response->raw_body);
        $count = count($trafficData->seconds->e)-2;

        // Return success
        $app->response->status(200);
        $app->response->body(
            json_encode(
                array(
                    'status' => true,
                    'traffic' => array(
                        'input' => round($trafficData->seconds->e[$count]['i']*0.000008,2),
                        'output' => round($trafficData->seconds->e[$count]['o']*0.000008,2),
                    )
                )
            )
        );
        $app->stop();
    }

    /**
     * Fetches historical traffic data
     */
    public function getHistorical($timespan)
    {

        $app = Slim::getInstance();

        // Verify that timespan is an allowed value
        if(!in_array($timespan, array('minutes', 'hours', 'days'))) {
            \Base\Log::message(_('Otillåtet värde skickades för historik (' . $timespan . ')'));
            $app->halt(400, json_encode(array('status' => false, 'error' => 'Felaktigt värde skickat som parameter')));
        }

        // Get the url to darkstat
        $file    = new \Shell\File();
        $content = $file->read('config.json');
        $OVPNconfig  = json_decode($content);

        if(!$content || !$OVPNconfig) {
            \Base\Log::message(_('Misslyckades att läsa config.json eller så var filen i ett felaktigt format'));
            $app->halt(500, json_encode(array('status' => false, 'error' => 'Ett tekniskt fel har inträffat.')));
        }

        $response =  \Unirest\Request::get(
            $OVPNconfig->darkstat . 'graphs.xml',
            array(
                "Accept" => "text/xml"
            )
        );

        // Verify respons
        if($response->code != 200) {
            \Base\Log::message($response->body->error);
            $app->halt($response->code, json_encode(array('status' => false, 'error' => $response->body->error)));
        }

        // Get current timestamp
        $date = new \DateTime('now', new \DateTimeZone('Europe/Stockholm'));
        $dateData = array(
            'minute' => $date->format('i'),
            'hour'   => $date->format('G'),
            'day'    => $date->format('j'),
            'month'  => $date->format('n'),
            'year'   => $date->format('Y')
        );

        $xml = new \SimpleXMLElement($response->raw_body);
        $rawTrafficData = $xml->$timespan;
        $count = count($rawTrafficData->e)-1;

        $trafficData = array();

        $totalInput  = 0;
        $totalOutput = 0;

        $x = 0;

        while($x <= $count) {

            $entry = $rawTrafficData->e[$x];
            $timeValue = (string)$entry['p'];

            if($timespan == 'minutes') {

                if($timeValue > $dateData['minute']) {
                    $date->modify('-1 hour');
                    $date->setTime($date->format('G'), $timeValue, 0);
                } else {
                    $date->setTime($dateData['hour'], $timeValue, 0);
                }

            } elseif($timespan == 'hours') {

                if($timeValue > $dateData['hour']) {
                    $date->modify('-1 day');
                }

                $date->setTime($timeValue, 0, 0);

            } elseif($timespan == 'days') {

                if($timeValue > $dateData['day']) {
                    $date->modify('-1 month');
                }

                $date->setDate($date->format('Y'), $date->format('n'), $timeValue);
                $date->setTime(0, 0, 0);

            }

            $trafficData[] = array(
                'input' => round($entry['i']*0.000000954,2),
                'output' => round($entry['o']*0.000000954,2),
                'date'   => $date->format('D M d Y H:i:s O'),
                'timestamp' => $date->getTimestamp()
            );

            $totalInput+=$entry['i'];
            $totalOutput+=$entry['o'];

            $x++;

            $date = new \DateTime('now', new \DateTimeZone('Europe/Stockholm'));

        }

        // Sort the array based on IP
        foreach ($trafficData as $key => $row) {

            $timeData[$key]  = $row['timestamp'];
        }

        array_multisort($timeData, SORT_ASC, $trafficData);

        // Return success
        $app->response->status(200);
        $app->response->body(
            json_encode(
                array(
                    'status' => true,
                    'summary' => array(
                        'input' => round($totalInput*0.000000954,2),
                        'output' => round($totalOutput*0.000000954,2),
                        'total'  => round(($totalInput+$totalOutput)*0.000000954,2)
                    ),
                    'traffic' => $trafficData
                )
            )
        );
        $app->stop();
    }

    public function externalIP()
    {

        $app = Slim::getInstance();

        // Get external IP
        $ip = \Network\IP::external();

        if(!$ip) {
            \Base\Log::message(_('Misslyckades att hämta den externa IP-adressen.'));
            $app->halt(500, json_encode(array('status' => false, 'error' => 'Ett tekniskt fel har inträffat.')));
        }

        // Return success
        $app->response->status(200);
        $app->response->body(
            json_encode(
                array(
                    'status' => true,
                    'ip' => $ip
                )
            )
        );
        $app->stop();
    }

} 