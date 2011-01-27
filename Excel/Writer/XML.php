<?php

/**
 * Simple excel xml generator based on php-excel by Oliver Schwarz <oliver.schwarz@gmail.com>
 *
 * @package sb_Excel
 */
class sb_Excel_Writer_XML {

	/**
	 * Header (of document)
	 * @var string
	 */
	protected $header = <<<'HEADER'
<?xml version="1.0" encoding="%s"?>
<?mso-application progid="Excel.Sheet"?>
<Workbook
   xmlns="urn:schemas-microsoft-com:office:spreadsheet"
   xmlns:o="urn:schemas-microsoft-com:office:office"
   xmlns:x="urn:schemas-microsoft-com:office:excel"
   xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"
   xmlns:html="http://www.w3.org/TR/REC-html40">
HEADER;
	/**
	 * Footer (of document)
	 * @var string
	 */
	protected $footer = "</Workbook>";
	/**
	 * Lines to output in the excel document
	 * @var array
	 */
	protected $lines = array();
	/**
	 * Used encoding
	 * @var string
	 */
	protected $encoding = 'UTF-8';
	/**
	 * Convert variable types
	 * @var boolean
	 */
	protected $convert_types = false;
	/**
	 * Worksheet title
	 * @var string
	 */
	protected $worksheet_title;

	/**
	 * The author of the document
	 * @var string
	 */
	protected $author = '';

	/**
	 * The last author of the document
	 * @var string
	 */
	protected $last_author ='';

	/**
	 * Unix timestamp of the doc creation date, defaults to now
	 * @var integer
	 */
	protected $created = '';

	/**
	 * The company the document is associated with
	 * @var string
	 */
	protected $company;

	/**
	 * The version number of the document, defaults to 1.0
	 * @var string
	 */
	protected $version = '1.0';

	/**
	 * Constructor
	 *
	 * The constructor allows the setting of some additional
	 * parameters so that the library may be configured to
	 * one's needs.
	 *
	 * On converting types:
	 * When set to true, the library tries to identify the type of
	 * the variable value and set the field specification for Excel
	 * accordingly. Be careful with article numbers or postcodes
	 * starting with a '0' (zero)!
	 *
	 * @param string $worksheet_title Title for the worksheet
	 * @param boolean $convert_types Convert variables to field specification
	 * @example
	 * <code>
	 *
	 * </code>
	 */
	public function __construct($worksheet_title = 'Table1', $convert_types = false) {
		$this->convert_types = $convert_types;
		$this->set_worksheet_title($worksheet_title);
		$this->created = time();
	}

	/**
	 * Sets the author of the document
	 * @param string $author
	 * @param string $last_author
	 * @param string $company
	 */
	public function set_author($author, $last_author, $company){
		$this->author = $author;
		$this->last_author = $last_author;
		$this->company = $company;
	}

	/**
	 * Sets the version and timestamp of the document
	 * @param string $version
	 * @param integer $created Either the unix timestamp or anything that strtotime takes
	 */
	public function set_version($version, $created){
		$this->version = $version;
		$this->created = preg_match("~^\d+$", $created) ? $created : strtotime($created);
	}

	/**
	 * Set encoding
	 * @param string Encoding type to set e.g. UTF-8
	 */
	public function set_encoding($encoding) {
		$this->encoding = $encoding;
	}

	/**
	 * Set worksheet title
	 *
	 * Strips out not allowed characters and trims the
	 * title to a maximum length of 31.
	 *
	 * @param string $title Title for worksheet
	 */
	public function set_worksheet_title($title) {
		$title = preg_replace("/[\\\|:|\/|\?|\*|\[|\]]/", "", $title);
		$title = substr($title, 0, 31);
		$this->worksheet_title = $title;
	}

	/**
	 * Add row
	 *
	 * Adds a single row to the document. If set to true, self::convert_types
	 * checks the type of variable and returns the specific field settings
	 * for the cell.
	 *
	 * @param array $array One-dimensional array with row content
	 */
	public function add_row($array, $style='') {
		$array = is_array($array) ? $array : func_get_args();

		$cells = "";
		foreach ($array as $k => $v) {
			$type = 'String';
			if ($this->convert_types === true && is_numeric($v)) {
				$type = 'Number';
			}
			$v = htmlentities($v, ENT_COMPAT, $this->encoding);
			$cells .= "<Cell".($style ? ' ss:StyleID="'.$style.'"' : '')."><Data ss:Type=\"$type\">" . $v . "</Data></Cell>\n";
		}
		$this->lines[] = "<Row>\n" . $cells . "</Row>\n";
	}

	/**
	 * Add rows
	 *
	 * Adds multiple rows to the document. If set to true, self::convert_types
	 * checks the type of variable and returns the specific field settings
	 * for the cell.
	 *
	 * @param $row Array, any number of arrays as rows
	 */
	public function add_rows() {
		$rows = func_get_args();
		foreach($rows as $row){
			$this->add_row($row);
		}
	}

	/**
	 * Add row
	 *
	 * Adds a single row to the document. If set to true, self::convert_types
	 * checks the type of variable and returns the specific field settings
	 * for the cell.
	 *
	 * @param array $array One-dimensional array with row content
	 */
	public function add_headers($array) {
		$array = is_array($array) ? $array : func_get_args();
		return $this->add_row($array, 'bold');
	}

	/**
	 * Add an array to the document
	 * @param array 2-dimensional array
	 */
	public function add_array($array) {
		foreach ($array as $k => $v) {
			$this->add_row($v);
		}
	}

	/**
	 *
	 * @param string $filename Name of excel file to generate (...xls) default worksheet.xls
	 */
	public function output_with_headers($filename='worksheet') {
		$filename = preg_replace('/[^aA-zZ0-9\_\-]/', '', $filename);

		header("Content-Type: application/msexcel; charset=" . $this->encoding);
		header("Content-Disposition: inline; filename=\"" . $filename . ".xlsx\"");
		echo $this->__toString();
	}

	/**
	 * Generate the excel file
	 */
	public function __toString() {

		$str = stripslashes(sprintf($this->header, $this->encoding));
		$str .= $this->document_properties_to_string();
		$str .= <<<STR
<Styles>
<Style ss:ID="bold">
  <Font x:Family="Swiss" ss:Bold="1" />
</Style>
</Styles>
STR;
		$str .= "\n<Worksheet ss:Name=\"" . $this->worksheet_title . "\">\n<Table>\n";
		foreach ($this->lines as $line){
			$str .= $line;
		}

		$str .= "</Table>\n</Worksheet>\n";
		$str .= $this->footer;
		return $str;
	}

	/**
	 * Converts document properties into XML
	 * @return string
	 */
	protected function document_properties_to_string(){
		$str = '<DocumentProperties xmlns="urn:schemas-microsoft-com:office:office">';
		$str .= '<Author>'.$this->author.'</Author>';
		$str .= '<LastAuthor>'.$this->last_author.'</LastAuthor>';
		$str .= '<Created>'.date("Y-m-d\TH:i:sP", $this->created).'</Created>';
		$str .= '<Company>'.$this->company.'</Company>';
		$str .= '<Version>'.$this->version.'</Version>';
		$str .= '</DocumentProperties>';
		return $str;
	}

}

?>