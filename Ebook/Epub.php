<?php
/**
 *
 * $cssData = "body {\n  margin-left: .5em;\n  margin-right: .5em;\n  text-align: justify;\n}\n\np {\n  font-family: serif;\n  font-size: 10pt;\n  text-align: justify;\n  text-indent: 1em;\n  margin-top: 0px;\n  margin-bottom: 1ex;\n}\n\nh1, h2 {\n  font-family: sans-serif;\n  font-style: italic;\n  text-align: center;\n  background-color: #6b879c;\n  color: white;\n  width: 100%;\n}\n\nh1 {\n    margin-bottom: 2px;\n}\n\nh2 {\n    margin-top: -2px;\n    margin-bottom: 2px;\n}\n";
 *
 * $ebook = new sb_Ebook_Epub('hello world', 'Visco, Paul');

//$ebook->set_date(strtotime('01/22/1977 12:00PM'));

$ebook->add_global_css_file('test.css', $cssData);
$ebook->add_cover('cover.jpg');
$ebook->add_chapter(new sb_Ebook_Epub_Chapter('chapter 1', '<h1>Chapter 1</h1><p>blah blah</p>'));
$ebook->add_chapter(new sb_Ebook_Epub_Chapter('chapter 2', '<h1>Chapter 2</h1><p>blah blah</p>'));
$ebook->add_chapter(new sb_Ebook_Epub_Chapter('chapter 3', '<h1>Chapter 3</h1><p>blah blah</p>'));
$ebook->add_chapter(new sb_Ebook_Epub_Chapter('chapter 4', '<h1>Chapter 4</h1><p>blah blah</p>'));
$ebook->output();
?>
 */
class sb_Ebook_Epub{

	public $global_style_sheets = Array();

	/**
	 * The number of chapters
	 * @var number
	 */
	protected $chapter_count = 0;


	/**
	 * The zip file that will get exported
	 * @var String
	 */
	protected $zip;

	protected $date = null;

	/**
	 *
	 * @var sb_Ebook_Epub_OPF
	 */
	public $ocf;

	/**
	 *
	 * @var sb_Ebook_Epub_NCX
	 */
	public $ncx;

	function __construct($title='my_ebook', $author="") {

		$this->tmp_file = uniqid().'.epub';
		$this->zip = new ZipArchive();


		if ($this->zip->open($this->tmp_file, ZipArchive::CREATE)) {
			$this->debug("Opening archive: ".$this->tmp_file);
			$this->create_archive();
			$this->create_container_xml();
			$this->opf = new sb_Ebook_Epub_OPF();

			$this->ncx = new sb_Ebook_Epub_NCX();
			$this->set_title($title);
			$this->set_author($author);
		} else {
			throw(new Exception("Could not create archive: ".$this->tmp_file));
		}

	}


	protected function create_archive() {

		$this->zip->addFromString('mimetype', 'application/epub+zip');
		if($this->zip->addEmptyDir('META-INF/')) {
			$this->debug('Created META-INF directory');
		} else {
			$this->debug('META-INF directory exists');
		}

	}

	protected function create_container_xml(){

		$xml = new DOMDocument('1.0', 'UTF-8');
		$container = $xml->appendChild($xml->createElement('container'));
		$container->setAttribute('version', '1.0');
		$container->setAttribute('xmlns', 'urn:oasis:names:tc:opendocument:xmlns:container');
		$rootfiles = $container->appendChild($xml->createElement('rootfiles'));
		$rootfile = $rootfiles->appendChild($xml->createElement('rootfile'));
		$rootfile->setAttribute('full-path', 'book.opf');
		$rootfile->setAttribute('media-type', 'application/oebps-package+xml');
		$this->zip->addFromString('META-INF/container.xml', $xml->saveXML());
		$xml = null;
	}

	/**
	 *
	 * @param <type> $filename
	 * @param <type> $data
	 * @param <type> $file_id
	 * @param <type> $mime_type
	 * @return <type>
	 */
	public function add_global_css_file($filename,  $data='', $file_id=null, $mime_type=null) {

		$this->global_style_sheets[] = basename($filename);
		return $this->add_file($filename, $data, $file_id, 'text/css');
	}

	/**
	 * Wrapped this so that you could extend if not using framework
	 * @param string $filename
	 * @return <type>
	 */
	public function file_to_mime($filename){
		return sb_Files::file_to_mime($filename);
	}

	/**
	 *
	 * @param <type> $filename
	 * @param <type> $data
	 */
	public function add_file($filename,  $data='', $file_id=null, $media_type=null) {

		if(is_file($filename)){
			$data = file_get_contents($filename);
			$filename = basename($filename);
		}

		$media_type ?: $this->file_to_mime($filename);

		$file_id = $file_id ?: md5($filename);

		$this->zip->addFromString($filename, $data);

		$this->opf->add_file($filename, $media_type, $file_id);

		return $file_id;

	}

	protected function set_title($title){
		$this->title = $title;
		$this->opf->set_title($title);
		$this->ncx->set_title($title);
	}

	public function set_author($author, $sort_key=''){
		$this->opf->set_author($author, $sort_key);
		$this->ncx->set_author($author);
	}

	public function set_description($description){
		return $this->opf->set_descrtiption($description);
	}

	public function set_language($lang='en'){

		if (mb_strlen($language) != 2) {
			throw(new Exception("language must be two char language code e.g. en, de"));
		}
		return $this->opf->set_language($lang);
	}

	public function set_date($date=null){
		$this->date = $date ?: time();
		return $this->opf->set_date($this->date);
	}

	public function set_identifier($identifier, $identifier_type){
		if ($identifier_type != "URI" && $identifier_type != "ISBN" && $identifier_type != "UUID") {
			throw(new Exception("Identifier type must be ISBN, UUID, or URI"));
		}
		$this->identifier = $identifier;
		$this->identifier_type = $identifier_type;
	}

	public function add_chapter_raw($title, $contents='', $linear='yes', $autosplit=false){
		$this->chapter_count++;
		if(is_file($contents)){
			$src = basename($contents);
			$contents = file_get_contents($contents);
		} else {
			$src = $this->chapter_count.'.xhtml';
		}

		$this->zip->addFromString($src, $contents);
		$chapter_id = 'chapter_'.$this->chapter_count;
		$this->opf->add_file($src, 'application/xhtml+xml', $chapter_id);
		$this->opf->add_to_spine($chapter_id);
		$this->ncx->add_navpoint_to_navmap($chapter_id, $this->chapter_count, $title, $src);

	}

	public function add_chapter(sb_Ebook_Epub_Chapter $chapter, $linear='yes'){
		$chapter->add_css($this->global_style_sheets);
		$this->add_chapter_raw($chapter->title, $chapter->saveXML(), $linear);
		return $chapter;
	}

	public function add_cover($data){
		if(is_file($data)){
			$data = file_get_contents($data);
		}

		$this->add_file('cover.jpg', $data, 'cover', 'image/jpeg');
		$cover = new sb_Ebook_Epub_Chapter('Cover', '<div id="cover-image"><img src="cover.jpg" alt="Cover Image"/></div>');
		$this->add_chapter($cover, 'no');

	}

	public function output(){

		$this->date = $this->date ?: time();
		$this->opf->formatOutput = true;
		$this->opf->xml->formatOutput = true;
		$this->zip->addFromString("book.opf", $this->opf->saveXML());
		$this->zip->addFromString("book.ncx",  $this->ncx->saveXML());

		$this->zip->close();

		if (ini_get('zlib.output_compression')) {
			ini_set('zlib.output_compression', 'Off');
		}

		exit;

		header('Pragma: public');
		header('Last-Modified: ' . date('D, d M Y H:i:s T', $this->date));
		header('Expires: 0');
		header('Accept-Ranges: bytes');
		header('Connection: close');
		header('Content-Type: application/epub+zip');
		header('Content-Disposition: attachment; filename="' . str_replace(Array(" "), "_", $this->title) . '.epub";' );
		header('Content-Transfer-Encoding: binary');
		header('Content-Length: ' . strlen($this->tmp_file));

		sb_Files::read_chunked($this->tmp_file);
	}

	protected function debug($str){
		file_put_contents("php://stdout", $str."\n");
	}
}

?>