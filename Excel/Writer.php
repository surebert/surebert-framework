<?php

/**
 * Writes simple excel files based on http://code.google.com/p/hexcel/ @copyright Adrian Duffell 2007
 * @package Excel
 *
 */
namespace sb\Excel;

class Writer
{

    /**
     * The beginning of file marker
     * @var string
     */
    protected $bof;

    /**
     * The end of file marker
     * @var string
     */
    protected $eof;

    /**
     * The contents of the file as it is being constructed
     * @var string
     */
    protected $contents;

    /**
     * Creates a new xls file for output or to save
     *
     * @example
     * <code>
     * $excel = new \sb\Excel_Writer();
     * $excel->setCell('A1', 'Hello World!');
     * $excel->setCell('D1', 'Hello World!');
     * $excel->setColumn('C', Array(1,2,3,4));

     * $excel->outputWithHeaders('hello.xls');
     * </code>
     */
    public function __construct()
    {
        $this->bof = \pack("s*", 0x809, 0x08, 0x00, 0x10, 0x0042, 0x04E4);
        $this->eof = \pack("s*", 0x0A, 0x00);
        $this->contents = $this->bof;
    }

    /**
     * Set the value of an individual cell
     *
     * @param mixed An excel cell reference such as A1, or an array in the 
     * format of ($row, $col) usign zero-based integers
     * @param mixed the value to put in this cell
     * @param the type of value (string|integer). Autodetects by default
     * @return boolean Success
     * */
    public function setCell($cell, $value, $type = "auto")
    {
        if ($type == "auto") {
            $type = \gettype($value);
        }

        if (\is_array($cell)) {
            $parts["row"] = $cell[0];
            $parts["col"] = $cell[1];
        } else {
            $parts = $this->refToArray($cell);
        }

        if (!\is_array($parts)) {
            \trigger_error("Cell reference should be in the format A1 or array(0,0).", E_USER_ERROR);
            return false;
        }

        $row = $parts["row"];
        $col = $parts["col"];

        switch ($type) {

            case "string" :

                /**
                 * @todo  it would be nice if we were able to keep that
                 * characters UTF-8 or unicode
                 */
                $value = \mb_convert_encoding($value, "Windows-1252", "UTF-8");
                $length = \mb_strlen($value, "Windows-1252");

                if ($length > 255) {
                    \trigger_error("String '$value' is too long. "
                        ."Please keep to a max of 255 characters.",
                        E_USER_ERROR);
                    return false;
                }

                $this->contents .= \pack("s*", 0x0204, 8 + $length, $row, $col, 0x00, $length);
                $this->contents .= $value;

                break;

            case "integer" :
                $this->contents .= \pack("s*", 0x0203, 14, $row, $col, 0x00);
                $this->contents .= \pack("d", $value);
                break;
        }

        return true;
    }

    /**
     * Set the values for a row
     *
     * @param integer The Excel row number to place these values
     * @param array An array of values
     * @return boolean Success
     * */
    public function setRow($row, $values)
    {

        if (!\is_array($values)) {
            \trigger_error("Values must be an array.", E_USER_ERROR);
            return false;
        }
        if (intval($row) < 1) {
            \trigger_error("Row number must be an integer greater than 1.", E_USER_ERROR);
            return false;
        }

        $i = 0;
        foreach ($values as $value) {
            $this->setCell(array($row - 1, $i), $value);
            $i++;
        }

        return true;
    }

    /**
     * undocumented function
     *
     * @param string The Excel column letter
     * @param array An array of values
     * @return Success
     * */
    public function setColumn($col, $values)
    {
        if (!\is_array($values)) {
            \trigger_error("Values must be an array.", E_USER_ERROR);
            return false;
        }
        if (\is_numeric($col)) {
            \trigger_error("Column must be a letter, eg column D.", E_USER_ERROR);
            return false;
        }

        //todo check array
        $i = 0;
        foreach ($values as $value) {
            $this->setCell($col . ($i + 1), $value);
            $i++;
        }

        return true;
    }

    /**
     * Stream this file over HTTP
     *
     * @return void
     * */
    public function outputWithHeaders($filename = 'output.xls')
    {
        \header("Expires: " . date("r", 0));
        \header("Last-Modified: " . gmdate("r") . " GMT");
        \header("Content-Type: application/x-msexcel");
        \header("Content-Disposition: attachment; filename=" . $filename);
        echo $this->__toString();
    }

    /**
     * Save the contents to file
     * @param string $filepath The filepath to save to
     * @return boolean
     */
    public function toFile($filepath)
    {
        if (\is_file($filepath)) {
            return \file_put_contents($filepath, $this->__toString());
        }
        return false;
    }

    /**
     * Retrieve the xls file contents
     *
     * @return an XLS string
     * */
    public function __toString()
    {
        return $this->contents . $this->eof;
    }

    /**
     * Convert a Excel Cell Reference to an array of component integers -- A5 becomes (6, 0);
     *
     * @param string An Excel cell reference such as A1, B7
     * @return array An associative array of row/column integers
     * */
    protected function refToArray($ref)
    {
        $offset = 64;

        $len = strlen($ref);

        $div = 0;
        for ($i = 0; $i < $len; $i++) {
            $char = substr($ref, $i, 1);
            if (is_numeric($char)) {
                $div = $i;
                break;
            }
        }
        if ($div < 1) {
            return false;
        }

        $row = substr($ref, $div);

        $place_col = 0;
        $col = 0;
        for ($i = $div - 1; $i >= 0; $i--) {
            $place_weight = pow(26, $place_col);
            $place_value = ord(substr($ref, $i, 1)) - $offset;
            $col += $place_value * $place_weight;
            $place_col++;
        }

        //return as 0 based
        return array("row" => $row - 1, "col" => $col - 1);
    }
}

