<?php
/**
 * Created by PhpStorm.
 * User: davidwibergh
 * Date: 15-04-16
 * Time: 09:44
 */

namespace Shell;


class File {

    /**
     * Läser en fil
     *
     * @param $file
     * @return bool|string
     */
    public function read($file)
    {

        if(isset($_SESSION['file'][$file])) {
            return $_SESSION['file'][$file];
        }

        // Check if the file name is the full path or not
        if(strpos($file, '/') === false) {
            $file = DOCUMENT_ROOT . '/' . $file;
        }

        // Let's make sure the file exists and is writable first.
        if (is_readable($file)) {

            // Attempt to open the file
            if (!$handle = fopen($file, 'r')) {
                error_log('Could not open file (' . $file . ')');
                return false;
            }

            // Read content from file
            $content = fread($handle, filesize($file));

            // Verify that the read was successful
            if(!$content) {
                error_log('fread() failed for (' . $file . ')');
                return false;
            }

            fclose($handle);

            $_SESSION['file'][$file] = $content;
            return $content;

        } else {
            error_log('File was not readable (' . $file . ')');
            return false;
        }
    }

    /**
     * Write content to a file
     *
     * @param $data
     * @return bool
     */
    public function write($data)
    {
        if(!isset($data['file']) || !isset($data['content'])) {
            return false;
        }

        // Check if the file name is the full path or not
        if(strpos($data['file'], '/') === false) {
            $data['file'] = DOCUMENT_ROOT . '/' . $data['file'];
        }

        //error_log($data['file']);

        // Let's make sure the file exists and is writable first.
        if (is_writable($data['file'])) {

            // Attempt to open the file
            if (!$handle = fopen($data['file'], 'w')) {
                //error_log('Could not open file (' . $data['file'] . ')');
                return false;
            }

            // Write the content to our opened file.
            if (fwrite($handle, $data['content']) === FALSE) {
                //error_log('fwrite() failed for (' . $data['file'] . ')');
                //error_log('Failed content: ' . $data['content']);
            }

            unset($_SESSION['file'][$data['file']]);

            fclose($handle);

            return true;

        } else {
            error_log('File was not writeable (' . $data['file'] . ')');
            return false;
        }
    }
} 