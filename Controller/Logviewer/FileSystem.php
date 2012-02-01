<?php 
/**
 * @package sb_Controller 
 */
class sb_Controller_Logviewer_FileSystem extends sb_Controller_HTML5{

	/**
	 * Get the base url for the logs access url.  By default this is whatever
	 * path the controller uses.  You could for example return '#'.$this->request->path
	 * if you were using sb.hashHistory
	 * 
	 * You would do this be overriding this method in your controller which extends this
	 * 
	 * @return string
	 */
	protected function get_base_url(){
		return $this->request->path;
	}
	
	/**
	 * Sets the root where the logs are stored by default /private/logs
	 * @return string
	 */
	protected function get_root(){
		return ROOT.'/private/logs/';
	}
	
	/**
	 * Loads the HTML navigation
	 * @return string 
	 */
	protected function get_nav(){
		return '<p style="float:right;"><a href="'.$this->get_base_url().'">back to log home</a></p>';
	}
	
	/**
	 * Converts all available log types to an html table
	 * @param string $sort_by The column to sort by name, size
	 * @return string  HTML
	 */
	protected function log_types_to_html_table($sort_by='name', $reverse=false){
		
		$directories = sb_Files::get_files($this->get_root(), true);
		foreach($directories as &$dir){
			$dir['file_count'] = $dir['size']->file_count;
			$dir['size'] = $dir['size']->size;
		}
		
		$html = '<h1>Local Log Files</h1>';
		$html .= '<table><thead><tr>';
		foreach(Array('name', 'mtime', 'size', 'file_count') as $prop){
			$html .= '<th><a href="'.$this->get_base_url().'?sort_by='.$prop;
			if($prop == $sort_by && !$reverse){
				$html .= '&reverse=1';
			} else {
				$html .= '&reverse=0';
			}
			$html .= '">'.$prop.'</a></th>';
		}
		
		$html .= '<th>Actions</th></tr></thead><tbody>';
	
		if(count($directories)){
			usort($directories, function ($a, $b) use($sort_by, $reverse) {
				
				if(!isset($a[$sort_by]) || $a[$sort_by] == $b[$sort_by]){
					return 0;
				}
				if($reverse){
					return ($a[$sort_by] < $b[$sort_by]) ? -1 : 1;
				} else {
					return ($a[$sort_by] > $b[$sort_by]) ? -1 : 1;
				}
				
			});

		}
		
		foreach($directories as $dir=>$data){
		
			$html .= '<tr><td>'.$data['name'].'</td>';
			$html .= '<td>'.date('m/d/Y', intval($data['mtime'])).'</td>';
			$html .= '<td>'.sb_Files::size_to_string($data['size']).'</td>';
			$html .= '<td>'.$data['file_count'].'</td>';
			$html .= '<td><a href="'.($this->get_base_url()).'?command=get_dates&log_type='.$data['name'].'">view</a> | <a href="'.(str_replace("#", "", $this->get_base_url())).'?command=export&log_type='.$data['name'].'">export</a></td>';
			$html .= '</tr>';
			
		}
		
		$html .= '</tbody></table>';
		return $html;
	}
	
	/**
	 * Converts dates for a specific log type into a table
	 * @param string $log_type the name of the log type as specified by the logger
	 * methods used to write it
	 * @return string HTML
	 */
	protected function dates_to_html_table($log_type, $sort_by='name', $reverse=false){
		$log_type = preg_replace("~[^\w]~", "", $log_type);
		$files = sb_Files::get_files($this->get_root().$log_type, false);
		rsort($files);
		$html = $this->get_nav().'<h1>Log: '.$log_type.'</h1>';
		$html .= '<table><thead><tr>';
		foreach(Array('name',  'size') as $prop){
			$html .= '<th><a href="'.$this->get_base_url().'?command=get_dates&log_type='.$log_type.'&sort_by='.$prop;
			if($prop == $sort_by && !$reverse){
				$html .= '&reverse=1';
			} else {
				$html .= '&reverse=0';
			}
			$html .= '">'.$prop.'</a></th>';
		}
		$html .= '<th>Actions</th></tr></thead><tbody>';
		
		$f = Array();
		foreach($files as $file){
			$f[] = Array('name' => $file, 'size' => filesize($this->get_root().$log_type.'/'.$file));
		}
		
		$files = $f;
		$f = null;
		
		if(count($files)){
			usort($files, function ($a, $b) use($sort_by, $reverse) {
				
				if(!isset($a[$sort_by]) || $a[$sort_by] == $b[$sort_by]){
					return 0;
				}
				if($reverse){
					return ($a[$sort_by] < $b[$sort_by]) ? -1 : 1;
				} else {
					return ($a[$sort_by] > $b[$sort_by]) ? -1 : 1;
				}
				
			});

		}
		
		foreach($files as $file){
			$html .= '<tr>';
			$html .= '<td>'.str_replace(".log", "", $file['name']).'</td>';
			
			$html .= '<td>'.sb_Files::size_to_string($file['size']).'</td>';
			$html .= '<td>';
			$html .= '<a href="'.($this->get_base_url()).'?command=view&log_type='.$log_type.'&date_file='.$file['name'].'">view</a> | <a href="'.($this->get_base_url()).'?command=tail&n=100&log_type='.$log_type.'&date_file='.$file['name'].'">tail</a> |<a href="'.(str_replace("#", "", $this->get_base_url())).'?command=export&log_type='.$log_type.'&date_file='.$file['name'].'">export</a>';
			$html .= '</td>';
			$html .= '</tr>';
			
		}
		
		$html .= '<tbody></table>';
		return $html;
	}
	
	/**
	 * Convert a file's contents into an HTML textarea
	 * @param string $path
	 * @return string 
	 */
	protected function file_to_html_textarea($path){
		if(is_file($path)){
			$contents = file_get_contents($path);
		} else {
			$contents = 'No data found!';
		}
		$html = $this->get_nav().'<h1>Log: '.str_replace($this->get_root(), "", $path).'</h1>';
		$html .= '<textarea class="sb_log" style="width:95%;min-height:400px;" >'.$contents.'</textarea>';
		return $html;
	}
	
	/**
	 * Convert a file's contents into an HTML textarea by reading the last N 
	 * number of lines only
	 * @param string $path The path to the file
	 * @param integer $lines The number of lines
	 * @return string 
	 */
	protected function file_to_html_textarea_tail($path, $lines=50){
		
		$handle = fopen($path, "r");
		$linecounter = $lines;
		$pos = -2;
		$beginning = false;
		$text = array();
		while ($linecounter > 0) {
			$t = " ";
			while ($t != "\n") {
				if(fseek($handle, $pos, SEEK_END) == -1) {
					$beginning = true; 
					break; 
				}
				$t = fgetc($handle);
				$pos --;
			}
			$linecounter --;
			if ($beginning) {
				rewind($handle);
			}
			$text[$lines-$linecounter-1] = fgets($handle);
			if ($beginning) break;
		}
		fclose ($handle);
		
		$html = $this->get_nav().'<h1>Log: '.str_replace($this->get_root(), "", $path).'</h1>';
		$html .= '<p>Last '.$lines.' lines in reverse chronological order</p>';
		$html .= '<textarea class="sb_log" style="width:95%;min-height:400px;" >';
		foreach($text as $c){
			$html .= $c;
		}
		$html .= '</textarea>';
		return $html;
	}
	

	/**
	 * Serves as the gateway to accept requests
	 * 
	 * examples include get_dates, view, tail, export
	 * 
	 * @return type 
	 * @servable true
	 */
	public function index(){
		
		$command = $this->get_get('command');
		$html = '';
		switch($command){
			
			case 'get_dates':
				$log_type = $this->get_get('log_type');
				$sort_by = $this->get_get('sort_by', 'name');
				$reverse = $this->get_get('reverse', false);
				$html .= $this->dates_to_html_table($log_type, $sort_by, $reverse);
				break;
			
			case 'view':
				$log_type = $this->get_get('log_type');
				$date_file = $this->get_get('date_file');
				$date_file_path = $this->get_root().$log_type.'/'.$date_file;
				
				if(!$date_file || !is_file($date_file_path)){
					$html .= "File not found";
				} else {
					$html .= $this->file_to_html_textarea($date_file_path);
				}
				break;
				
			case 'tail':
				$log_type = $this->get_get('log_type');
				$date_file = $this->get_get('date_file');
				$date_file_path = $this->get_root().$log_type.'/'.$date_file;
				
				if(!$date_file || !is_file($date_file_path)){
					$html .= "File not found";
				} else {
					$n = $this->get_get('n', 100);
					$html .= $this->file_to_html_textarea_tail($date_file_path, $n);
				}
				break;
				
			case 'export':
				$log_type = $this->get_get('log_type');
				$date_file = $this->get_get('date_file');
				$date_file_path = $this->get_root().$log_type.'/'.$date_file;
				if($date_file && is_file($date_file_path)){
					$html .= sb_Files_ForceDownload::file_to_zip($date_file_path);
				} else if(is_dir($date_file_path)){
					$html .= sb_Files_ForceDownload::file_to_zip($date_file_path);
				}
				
				break;
				
			default:
				$sort_by = $this->get_get('sort_by', 'name');
				$reverse = $this->get_get('reverse', false);
				$html .= $this->log_types_to_html_table($sort_by, $reverse);
				break;
				
		}
		
		return $html;
		
	}
	
}
?>