<?php

/**
 * An Excel Spreadhseet (97/2003) Reading package based on http://code.google.com/p/php-excel-reader/
 * @package Excel
 */
namespace sb\Excel;

use \sb\Excel\Reader\Backend;

class Reader extends Backend
{

    /**
     * Loads the spreadsheet
     * @param string $file The file path
     * @param <type> $read_extended_info
     * @param string $outputEncoding The output encoding to use
     * @example
     * <code>
     * $data = new \sb\Excel_Reader('/path/to/excelfile.xls');
     * </code>
     */
    public function __construct($file = '', $read_extended_info = true, $outputEncoding = '')
    {

        $this->set_UTF_encoder('iconv');
        if ($outputEncoding != '') {
            $this->setOutputEncoding($outputEncoding);
        }
        for ($i = 1; $i < 245; $i++) {
            $name = strtolower(( (($i - 1) / 26 >= 1) ? chr(($i - 1) / 26 + 64) : '') . chr(($i - 1) % 26 + 65));
            $this->colnames[$name] = $i;
            $this->colindexes[$i] = $name;
        }
        $this->read_extended_info = $read_extended_info;
        if ($file != "") {
            $this->read($file);

            $this->data = $this->getWorkbook();
            $this->parse();
        }
    }

    /**
     * Gets the value of a specific cell
     * @param int $row
     * @param int $col
     * @param int $sheet
     * @return string
     */
    public function value($row, $col, $sheet = 0)
    {
        return $this->val($row, $col, $sheet);
    }

    /**
     * Fires a function per cell
     * @param function $func The function to fire for each cell, passes (value, rownum, colnum) as arguments
     * @param integer $sheet The sheet to read use when going through cell
     * @example
     * <code>
     * $data = new \sb\Excel_Reader('/path/to/excelfile.xls');
     * $data->readCells(function($val, $row, $col) use($data) {
     *   echo $data->rowheight($row, 0);
     * });
     * </code>
     *
     */
    public function readCells($func, $sheet = 0)
    {
        $rows = $this->rowcount();
        $cols = $this->colcount();
        for ($r = 1; $r <= $rows; $r++) {
            for ($c = 1; $c < $cols; $c++) {
                $func($this->value($r, $c, $sheet), $r, $c);
            }
        }
    }

    /**
     * export to multi dimensional array
     * @param integer $sheet
     * @return array
     */
    public function toArray($sheet = 0)
    {
        $arr = array();
        for ($row = 1; $row <= $this->rowcount($sheet); $row++) {
            for ($col = 1; $col <= $this->colcount($sheet); $col++) {
                $arr[$row][$col] = $this->val($row, $col, $sheet);
            }
        }

        return $arr;
    }

    /**
     * Gets info for a specifc cell of a specific sheet
     * @param int $row
     * @param int $col
     * @param string $type
     * @param int $sheet
     * @return string
     */
    public function info($row, $col, $type = '', $sheet = 0)
    {
        $col = $this->getCol($col);
        if (array_key_exists('cellsInfo', $this->sheets[$sheet])
            && array_key_exists($row, $this->sheets[$sheet]['cellsInfo'])
            && array_key_exists($col, $this->sheets[$sheet]['cellsInfo'][$row])
            && array_key_exists($type, $this->sheets[$sheet]['cellsInfo'][$row][$col])) {
            return $this->sheets[$sheet]['cellsInfo'][$row][$col][$type];
        }
        return "";
    }

    public function type($row, $col, $sheet = 0)
    {
        return $this->info($row, $col, 'type', $sheet);
    }

    public function raw($row, $col, $sheet = 0)
    {
        return $this->info($row, $col, 'raw', $sheet);
    }

    public function rowspan($row, $col, $sheet = 0)
    {
        $val = $this->info($row, $col, 'rowspan', $sheet);
        if ($val == "") {
            return 1;
        }
        return $val;
    }

    public function colspan($row, $col, $sheet = 0)
    {
        $val = $this->info($row, $col, 'colspan', $sheet);
        if ($val == "") {
            return 1;
        }
        return $val;
    }

    public function rowcount($sheet = 0)
    {
        return $this->sheets[$sheet]['numRows'];
    }

    public function colcount($sheet = 0)
    {
        return $this->sheets[$sheet]['numCols'];
    }

    public function colwidth($col, $sheet = 0)
    {
        // Col width is actually the width of the number 0. So we have to estimate and come close
        return $this->colInfo[$sheet][$col]['width'] / 9142 * 200;
    }

    public function colhidden($col, $sheet = 0)
    {
        return !!$this->colInfo[$sheet][$col]['hidden'];
    }

    public function rowheight($row, $sheet = 0)
    {
        return $this->rowInfo[$sheet][$row]['height'];
    }

    public function rowhidden($row, $sheet = 0)
    {
        return !!$this->rowInfo[$sheet][$row]['hidden'];
    }
}

