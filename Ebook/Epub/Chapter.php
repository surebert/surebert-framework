<?php
/**
 * Used to model and Epub Chapter
 * . "<html xmlns=\"http://www.w3.org/1999/xhtml\">\n"
	. "<head>"
	. "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />\n"
	. "<link rel=\"stylesheet\" type=\"text/css\" href=\"styles.css\" />\n"
	. "<title>Test Book</title>\n"
	. "</head>\n"
	. "<body>\n";
 * @author paul.visco@roswellpark.org
 * @package sb_Epub
 */
class sb_Ebook_Epub_Chapter{

	/**
	 *
	 * @var DOMDocument
	 */
	public $html;
	public $head;
	public $body;

	public $title;

	public function  __construct($title, $html='', $css='') {
		$this->title = $title;

		$this->xml = new DomDocument('1.0', 'UTF-8');

		$this->create_html();
		$this->create_head($title);
		$this->create_body();
		if(!empty($html)){
			$this->set_body_innerHTML($html);
		}
		$this->add_css($css);


	}

	protected function create_html(){
		$this->html = $this->xml->appendChild($this->xml->createElement('html'));
		$this->html->setAttribute('xmlns', 'http://www.w3.org/1999/xhtml');
	}

	protected function create_head($title_txt){
		$this->head = $this->html->appendChild($this->xml->createElement('head'));
		$title = $this->head->appendChild($this->xml->createElement('title'));
		$title->appendChild($this->xml->createTextNode($title_txt));
	}

	protected function create_body(){
		$this->body = $this->html->appendChild($this->xml->createElement('body'));
	}

	public function add_css($href=''){

		if($href){
			if(is_string($href)){
				$href = Array($href);
			}

			if(is_array($href)){
				foreach($href as $h){

					$link = $this->head->appendChild($this->xml->createElement('link'));
					$link->setAttribute('rel', 'stylesheet');
					$link->setAttribute('type', 'text/css');
					$link->setAttribute('href', $h);
				}
			}
		}

	}

	public function set_body_innerHTML($html){
		$fragment = $this->xml->createDocumentFragment();
		$fragment->appendXML($html);
		$this->body->appendChild($fragment);

	}
	public function saveXML(){
		return $this->xml->saveXML();
	}

}
?>