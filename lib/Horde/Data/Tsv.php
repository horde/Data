<?php
/**
 * Horde_Data implementation for tab-separated data (TSV).
 *
 * Copyright 1999-2010 The Horde Project (http://www.horde.org/)
 *
 * See the enclosed file COPYING for license information (LGPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/lgpl.html.
 *
 * @author   Jan Schneider <jan@horde.org>
 * @author   Chuck Hagenbuch <chuck@horde.org>
 * @category Horde
 * @package  Data
 */
class Horde_Data_Tsv extends Horde_Data_Base
{
    /**
     * File extension.
     *
     * @var string
     */
    protected $_extension = 'tsv';

    /**
     * MIME content type.
     *
     * @var string
     */
    protected $_contentType = 'text/tab-separated-values';

    /**
     * Convert data file contents to list of data records.
     *
     * @param string $contents   Data file contents.
     * @param boolean $header    True if a header row is present.
     * @param string $delimiter  Field delimiter.
     *
     * @return array  List of data records.
     */
    public function importData($contents, $header = false, $delimiter = "\t")
    {
        if ($_SESSION['import_data']['format'] == 'pine') {
            $contents = preg_replace('/\n +/', '', $contents);
        }

        $contents = explode("\n", $contents);
        $data = array();
        if ($header) {
            $head = explode($delimiter, array_shift($contents));
        }

        foreach ($contents as $line) {
            if (trim($line) == '') {
                continue;
            }
            $line = explode($delimiter, $line);
            if (!isset($head)) {
                $data[] = $line;
            } else {
                $newline = array();
                for ($i = 0; $i < count($head); $i++) {
                    $newline[$head[$i]] = empty($line[$i]) ? '' : $line[$i];
                }
                $data[] = $newline;
            }
        }

        return $data;
    }

    /**
     * Builds a TSV file from a given data structure and returns it as a
     * string.
     *
     * @param array $data      A two-dimensional array containing the data set.
     * @param boolean $header  If true, the rows of $data are associative
     *                         arrays with field names as their keys.
     *
     * @return string  The TSV data.
     */
    public function exportData($data, $header = false)
    {
        if (!is_array($data) || count($data) == 0) {
            return '';
        }
        $export = '';
        $head = array_keys(current($data));
        if ($header) {
            $export = implode("\t", $head) . "\n";
        }
        foreach ($data as $row) {
            foreach ($head as $key) {
                $cell = $row[$key];
                if (!empty($cell) || $cell === 0) {
                    $export .= $cell;
                }
                $export .= "\t";
            }
            $export = substr($export, 0, -1) . "\n";
        }

        return $export;
    }

    /**
     * Builds a TSV file from a given data structure and triggers its download.
     * It DOES NOT exit the current script but only outputs the correct headers
     * and data.
     *
     * @param string $filename  The name of the file to be downloaded.
     * @param array $data       A two-dimensional array containing the data
     *                          set.
     * @param boolean $header   If true, the rows of $data are associative
     *                          arrays with field names as their keys.
     */
    public function exportFile($filename, $data, $header = false)
    {
        $export = $this->exportData($data, $header);
        $this->_browser->downloadHeaders($filename, 'text/tab-separated-values', false, strlen($export));
        echo $export;
    }

    /**
     * Takes all necessary actions for the given import step, parameters and
     * form values and returns the next necessary step.
     *
     * @param integer $action  The current step. One of the IMPORT_* constants.
     * @param array $param     An associative array containing needed
     *                         parameters for the current step.
     *
     * @return mixed  Either the next step as an integer constant or imported
     *                data set after the final step.
     * @throws Horde_Data_Exception
     */
    public function nextStep($action, $param = array())
    {
        switch ($action) {
        case Horde_Data::IMPORT_FILE:
            parent::nextStep($action, $param);

            if ($_SESSION['import_data']['format'] == 'mulberry' ||
                $_SESSION['import_data']['format'] == 'pine') {
                $_SESSION['import_data']['data'] = $this->importFile($_FILES['import_file']['tmp_name']);
                $format = $_SESSION['import_data']['format'];
                if ($format == 'mulberry') {
                    $appKeys  = array('alias', 'name', 'email', 'company', 'workAddress', 'workPhone', 'homePhone', 'fax', 'notes');
                    $dataKeys = array(0, 1, 2, 3, 4, 5, 6, 7, 9);
                } elseif ($format == 'pine') {
                    $appKeys = array('alias', 'name', 'email', 'notes');
                    $dataKeys = array(0, 1, 2, 4);
                }
                foreach ($appKeys as $key => $app) {
                    $map[$dataKeys[$key]] = $app;
                }
                $data = array();
                foreach ($_SESSION['import_data']['data'] as $row) {
                    $hash = array();
                    if ($format == 'mulberry') {
                        if (preg_match("/^Grp:/", $row[0]) || empty($row[1])) {
                            continue;
                        }
                        $row[1] = preg_replace('/^([^,"]+),\s*(.*)$/', '$2 $1', $row[1]);
                        foreach ($dataKeys as $key) {
                            if (array_key_exists($key, $row)) {
                                $hash[$key] = stripslashes(preg_replace('/\\\\r/', "\n", $row[$key]));
                            }
                        }
                    } elseif ($format == 'pine') {
                        if (count($row) < 3 || preg_match("/^#DELETED/", $row[0]) || preg_match("/[()]/", $row[2])) {
                            continue;
                        }
                        $row[1] = preg_replace('/^([^,"]+),\s*(.*)$/', '$2 $1', $row[1]);
                        /* Address can be a full RFC822 address */
                        try {
                            $addr_arr = Horde_Mime_Address::parseAddressList($row[2]);
                        } catch (Horde_Mime_Exception $e) {
                            continue;
                        }
                        if (empty($addr_arr[0]->mailbox)) {
                            continue;
                        }
                        $row[2] = $addr_arr[0]->mailbox . '@' . $addr_arr[0]->host;
                        if (empty($row[1]) && !empty($addr_arr[0]->personal)) {
                            $row[1] = $addr_arr[0]->personal;
                        }
                        foreach ($dataKeys as $key) {
                            if (array_key_exists($key, $row)) {
                                $hash[$key] = $row[$key];
                            }
                        }
                    }
                    $data[] = $hash;
                }
                $_SESSION['import_data']['data'] = $data;
                $_SESSION['import_data']['map'] = $map;
                $ret = $this->nextStep(Horde_Data::IMPORT_DATA, $param);
                return $ret;
            }

            /* Move uploaded file so that we can read it again in the next step
               after the user gave some format details. */
            try {
                $this->_browser->wasFileUploaded('import_file', _("TSV file"));
            } catch (Horde_Browser_Exception $e) {
                throw new Horde_Data_Exception($e);
            }
            $file_name = Horde_Util::getTempFile('import', false);
            if (!move_uploaded_file($_FILES['import_file']['tmp_name'], $file_name)) {
                throw new Horde_Data_Exception('The uploaded file could not be saved.');
            }
            $_SESSION['import_data']['file_name'] = $file_name;

            /* Read the file's first two lines to show them to the user. */
            $_SESSION['import_data']['first_lines'] = '';
            $fp = @fopen($file_name, 'r');
            if ($fp) {
                $line_no = 1;
                while ($line_no < 3 && $line = fgets($fp)) {
                    $newline = Horde_String::length($line) > 100 ? "\n" : '';
                    $_SESSION['import_data']['first_lines'] .= substr($line, 0, 100) . $newline;
                    $line_no++;
                }
            }
            return Horde_Data::IMPORT_TSV;

        case Horde_Data::IMPORT_TSV:
            $_SESSION['import_data']['header'] = $this->_vars->header;
            $import_data = $this->importFile($_SESSION['import_data']['file_name'],
                                             $_SESSION['import_data']['header']);
            $_SESSION['import_data']['data'] = $import_data;
            unset($_SESSION['import_data']['map']);
            return Horde_Data::IMPORT_MAPPED;
        }

        return parent::nextStep($action, $param);
    }

}
