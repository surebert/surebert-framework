<?php
/**
 * Used to create views which force download of specific files
 *
 * You can add additional properties on the fly
 * @author visco
 * @version 1.0 12/08/08 12/08/08
 * @package sb_View
 */
class sb_Files_ForceDownload{

	/**
	 * Send headers and begins force-download
	 *
	 * @param string $file
	 *
	 */

	public static function send($file, $display_file_name=''){
		$display_file_name = $display_file_name ? $display_file_name : basename($file);
		header("HTTP/1.1 200 OK");
		header("Status: 200 OK");
		header("Pragma: private");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Content-Transfer-Encoding: Binary");
		header('Content-Type: application/force-download');
		header('Content-disposition: attachment; filename='.$display_file_name);
		sb_Files::read_chunked($file);
	}

}

?>