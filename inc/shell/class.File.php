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
                \Base\Log::message('Could not open file (' . $file . ')');
                return false;
            }

            // Read content from file
            $content = fread($handle, filesize($file));

            // Verify that the read was successful
            if(!$content) {
                \Base\Log::message('Could not read content from file (' . $file . ')');
                return false;
            }

            fclose($handle);

            $_SESSION['file'][$file] = $content;
            return $content;

        } else {
            \Base\Log::message('File was not readable (' . $file . ')');
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

        // Make the filesystem writeable
        shell_exec('mount -o rw /');

        // Let's make sure the file exists and is writable first.
        if (is_writable($data['file'])) {

            // Attempt to open the file
            if (!$handle = fopen($data['file'], 'w')) {
                \Base\Log::message('Could not open file (' . $data['file'] . ')');

                // Make the filesystem readonly
                shell_exec('mount -o ro /');
                return false;
            }

            // Write the content to our opened file.
            if (fwrite($handle, $data['content']) === FALSE) {
                \Base\Log::message('Could not write to file (' . $data['file'] . ')');
                \Base\Log::message('Content that failed to be written: ' . $data['content']);
            }

            // Unset the file 'cache'
            unset($_SESSION['file'][$data['file']]);

            // Close the file
            fclose($handle);

            // Make the filesystem readonly
            shell_exec('mount -o ro /');
            return true;

        } else {
            error_log('File was not writeable (' . $data['file'] . ')');
            shell_exec('mount -o ro /');
            return false;
        }
    }
} 