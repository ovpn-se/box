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

        // Open the log file
        $file    = new \Shell\File();
        $content = $file->read('log.json');

        \Shell\System::setReadWrite();

        // Check if file was successfully opened
        if(!$content) {

            error_log('Could not read log file. Creating file instead.');

            // File was not opened. Attempt to create the log file
            $create = $file->create('log.json');

            // Check if file was successfully created
            if(!$create) {

                // Failed to create file.

                error_log('Failed to create file');
                \Shell\System::setReadOnly();
                return false;
            }
        }

        // Decode the log file
        $log = json_decode($content, true);

        // If the json_decode didn't work lets scrap all existing data and create a new array
        if(!$log) {
            $log = array();
        }

        // Create log entry
        $log[] = array(
            'timestamp' => $date->getTimestamp(),
            'message'   => $string
        );

        // Save the log file
        $write = $file->write(
            array(
                'file' => 'log.json',
                'content' => json_encode($log, JSON_PRETTY_PRINT)
            )
        );

        // Verify that the write was successful
        if(!$write) {
            \Shell\System::setReadWrite();
            error_log('Failed to log entry (' . $string . ')');
            \Shell\System::setReadOnly();
            return false;
        }
        \Shell\System::setReadOnly();
        return true;
    }

} 