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
        \Shell\System::setReadWrite();

        // Let's make sure the file exists and is writable first.
        if (is_writable($data['file'])) {

            // Attempt to open the file
            if (!$handle = fopen($data['file'], 'w')) {
                \Base\Log::message('Could not open file (' . $data['file'] . ')');

                // Make the filesystem readonly
                \Shell\System::setReadOnly();
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
            \Shell\System::setReadOnly();
            return true;

        } else {
            error_log('File was not writeable (' . $data['file'] . ')');
            // Make the filesystem readonly
            \Shell\System::setReadOnly();
            return false;
        }
    }

    /**
     * Creates a file
     *
     * @param $filename
     * @return bool
     */
    public function create($filename)
    {

        // Check if the file name is the full path or not
        if(strpos($filename, '/') === false) {
            $filename = DOCUMENT_ROOT . '/' . $filename;
        }

        // Check if the file already exists
        if(file_exists($filename)) {

            // The file already exists.
            \Base\Log::message('File already exists (' . $filename . ')');
            return false;
        }

        // Make the filesystem writeable
        \Shell\System::setReadWrite();

        // Create file
        $fp = fopen($filename,"w");
        fclose($fp);
        chmod($filename, '0777');

        // Make the filesystem read-only
        \Shell\System::setReadOnly();

        // Verify that the file was successfully created
        if(!file_exists($filename)) {

            // Log the error
            \Base\Log::message('Failed to create file (' . $filename . ')');
            return false;
        }

        return true;
    }
} 