<?php
/**
 * Created by PhpStorm.
 * User: davidwibergh
 * Date: 15-05-19
 * Time: 20:06
 */

namespace Base;


class Log {

    /**
     * Log a message
     *
     * @param $string
     */
    public static function message($string, $type = 'error')
    {

        if(!in_array(strtolower($type),array('error', 'info', 'success'))) {
            return false;
        }

        $date = new \DateTime('now', new \DateTimeZone('Europe/Stockholm'));

        $file    = new \Shell\File();
        $content = $file->read('log.json');
        $log     = json_decode($content, true);

        $log[] = array(
            'timestamp' => $date->getTimestamp(),
            'message'   => $string
        );

        // Save the log file
        $file->write(
            array(
                'file' => 'config.json',
                'content' => json_encode($log, JSON_PRETTY_PRINT)
            )
        );
    }

} 