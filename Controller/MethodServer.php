<?php
/**
 * Used to process requests of methods with @servable attributes
 */
class sb_Controller_MethodServer extends sb_Controller{

	/**
	 * Used to render the output through the filter_output method by calling the
	 * handler appropriate to request that has @servable set to true
	 * @return string
	 */
	public function render(){
		$method = isset($this->request->path_array[1]) ? $this->request->path_array[1] : 'index';
		
		if(method_exists($this, $method)){
			$reflection = new ReflectionMethod($this, $method);

			//check for phpdocs
			$docs = $reflection->getDocComment();
			$servable = false;
			if (!empty($docs)) {
				if (preg_match("~@servable (true|false)~", $docs, $match)) {
					$servable = $match[1] == 'true' ? true : false;
				}
			}

			if($servable){
				return $this->filter_output($this->$method());
			}
			
		}
		
		return $this->filter_output($this->not_found($method));
		
	}

}
?>