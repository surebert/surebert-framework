<?php 
class sb_Controller_Logviewer_FileSystem extends sb_Controller_HTML5{

	protected function get_root(){
		return ROOT.'/private/logs/';
	}
	protected function get_files($dir, $get_directories=false, $get_max_file_date=''){
		$arr = Array();
		$iterator = new DirectoryIterator($dir);
		
        foreach ($iterator as $file){

		  if ($get_directories && $file->isDir() && !$file->isDot() && !preg_match("~\.~", $file)) {
             $arr[$file->getBasename()] = Array(
				 'path' => $file->getPath(),
				 'size' => $this->get_dir_size($file->getRealPath()),
				 'mtime' => $file->getMTime(),
				 'name' => $file->getBaseName());
			 
		  } else if (!$get_directories && $file->isFile()){
			  $arr[] = $file->getBasename();
			}
		}
		
		$get_directories ? ksort($arr) : sort($arr);
		return $arr;
	}
	
	protected function get_dir_size($path) {
		
		$size_in_bytes = 0;
		$files = scandir($path);
		$path = rtrim($path, '/'). '/';
		
		foreach($files as $file) {
			if ($file != "." && $file != ".." && $file != ".svn") {
				$x = $path . $file;
				if (is_dir($x)) {
					$size_in_bytes +=$this->get_dir_size($x);
				} else {
					$size_in_bytes += filesize($x);
				}
			}   
		}

		return $size_in_bytes;
	}
	
	protected function get_base_url(){
		return $this->request->path;
	}
	
	protected function get_nav(){
		return '<p style="float:right;"><a href="'.$this->get_base_url().'">back to log home</a></p>';
	}
	/**
	 * 
	 */
	protected function log_types_to_html_list($sort_by='name'){
		
		$directories = $this->get_files($this->get_root(), true);
		$html = '<h1>Local Log Files</h1>';
		$html .= '<table><thead><tr><th><a href="'.$this->get_base_url().'">Name</a></th><th><a href="'.$this->get_base_url().'?sort_by=mtime">Last Modified</a></th><th><a href="'.$this->get_base_url().'?sort_by=size">Size</a></th><th>Actions</th></tr></thead><tbody>';
	
		if($sort_by != 'name' && count($directories)){
			usort($directories, function ($a, $b) use($sort_by) {
				
				if(!isset($a[$sort_by]) || $a[$sort_by] == $b[$sort_by]){
					return 0;
				}
				return ($a[$sort_by] > $b[$sort_by]) ? -1 : 1;
			});

		}
		
		foreach($directories as $dir=>$data){
			$html .= '<tr><td>'.$data['name'].'</td>';
			$html .= '<td>'.date('m/d/Y', $data['mtime']).'</td>';
			$html .= '<td>'.sb_Files::size_to_string($data['size']).'</td>';
			$html .= '<td><a href="'.($this->get_base_url()).'?command=get_dates&log_type='.$data['name'].'">view</a> | <a href="'.(str_replace("#", "", $this->get_base_url())).'?command=export&log_type='.$data['name'].'">export</a></td>';
			$html .= '</tr>';
			
		}
		
		$html .= '</tbody></table>';
		return $html;
	}
	
	protected function dates_to_html_list($log_type){
		$log_type = preg_replace("~[^\w]~", "", $log_type);
		$files = $this->get_files($this->get_root().$log_type, false);
		rsort($files);
		$html = $this->get_nav().'<h1>Log: '.$log_type.'</h1>';
		$html .= '<table><thead><tr><th>Name</th><th>Size</th><th>Actions</th></tr></thead><tbody>';
		
		foreach($files as $file){
			$html .= '<tr>';
			$html .= '<td>'.str_replace(".log", "", $file).'</td>';
			
			$html .= '<td>'.sb_Files::size_to_string(filesize($this->get_root().$log_type.'/'.$file)).'</td>';
			$html .= '<td>';
			$html .= '<a href="'.($this->get_base_url()).'?command=view&log_type='.$log_type.'&date_file='.$file.'">view</a> | <a href="'.($this->get_base_url()).'?command=tail&n=100&log_type='.$log_type.'&date_file='.$file.'">tail</a> |<a href="'.(str_replace("#", "", $this->get_base_url())).'?command=export&log_type='.$log_type.'&date_file='.$file.'">export</a>';
			$html .= '</td>';
			$html .= '</tr>';
			
		}
		
		$html .= '<tbody></table>';
		return $html;
	}
	
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
	
	protected function file_to_zip($path){
		if(is_file($path) || is_dir($path)){
			$zip = new ZipArchive;
			$zip_file = ROOT.'/private/cache/'.md5(microtime(true));
			
			if ($zip->open($zip_file, ZipArchive::CREATE) === TRUE) {
				if(is_dir($path)){
					$iterator = new DirectoryIterator($path);
					
					foreach ($iterator as $file){
					  if ($file->isFile()){
						  $zip->addFile($file->getPath().'/'.$file->getBasename(), basename($file->getBasename()));
						}
					}
					
				} else {
					$zip->addFile($path, basename($path));
				}
				
				if($zip->close()){
					sb_Files_ForceDownload::send($zip_file, str_replace("/", "_", basename($path)).'.zip');
					unlink($zip_file);
				}
			} else {
				echo 'failed to create zip file';
			}
		} else {
			$contents = 'No data found!';
		}
		
	}
	/**
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
			
				$html .= $this->dates_to_html_list($log_type);
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
					$html .= $this->file_to_zip($date_file_path);
				} else if(is_dir($date_file_path)){
					$html .= $this->file_to_zip($date_file_path);
				}
				
				break;
				
			default:
				$sort_by = $this->get_get('sort_by', 'name');
				$html .= $this->log_types_to_html_list($sort_by);
				break;
				
		}
		
		return $html;
		
	}
	
}
?>