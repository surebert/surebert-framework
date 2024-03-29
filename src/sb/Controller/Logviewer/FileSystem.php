<?php

/**
 * @package Controller
 */
namespace sb\Controller\Logviewer;
use \sb\Controller\HTML\HTML5;
class FileSystem extends HTML5
{

    /**
     * Get the base url for the logs access url.  By default this is whatever
     * path the controller uses.  You could for example return '#'.$this->request->path
     * if you were using sb.hashHistory
     *
     * You would do this be overriding this method in your controller which extends this
     *
     * @return string
     */
    protected function getBaseUrl()
    {
        return $this->request->path;
    }

    /**
     * Sets the root where the logs are stored by default /private/logs
     * @return string
     */
    protected function getRoot()
    {
        return ROOT . '/private/logs/';
    }

    /**
     * Validates to make sure file path for export or view is sane
     *x
     * @param $path
     * @return array|string|string[]
     */
    protected function validatePath($path){

        //make sure real file path matches
        $valid = realpath($path) == $path;

        $valid = $valid && (is_file($path) || is_dir($path));

        //make sure real file path matches
        if(!$valid){
            $this->sendError(401);
            die("Files do not exist");
        }

    }

    /**
     * Loads the HTML navigation
     * @return string
     */
    protected function getNav()
    {
        return '<p style="float:right;"><a href="' . $this->getBaseUrl() . '">back to log home</a></p>';
    }

    /**
     * Converts all available log types to an html table
     * @param string $sort_by The column to sort by name, size
     * @return string  HTML
     */
    protected function logTypesToHtmlTable($sort_by = 'name', $reverse = false)
    {

        $directories = \sb\Files::getFiles($this->getRoot(), true);
        foreach ($directories as &$dir) {
            $dir['file_count'] = $dir['size']->file_count;
            $dir['size'] = $dir['size']->size;
        }

        $html = '<h1>Local Log Files</h1>';
        $html .= '<table><thead><tr>';
        foreach (Array('name', 'mtime', 'size', 'file_count') as $prop) {
            $html .= '<th><a href="' . $this->getBaseUrl() . '?sort_by=' . $prop;
            if ($prop == $sort_by && !$reverse) {
                $html .= '&reverse=1';
            } else {
                $html .= '&reverse=0';
            }
            $html .= '">' . $prop . '</a></th>';
        }

        $html .= '<th>Actions</th></tr></thead><tbody>';

        if (count($directories)) {
            usort($directories,
                function ($a, $b) use ($sort_by, $reverse) {

                    if (!isset($a[$sort_by]) || $a[$sort_by] == $b[$sort_by]) {
                        return 0;
                    }
                    if ($reverse) {
                        return ($a[$sort_by] < $b[$sort_by]) ? -1 : 1;
                    } else {
                        return ($a[$sort_by] > $b[$sort_by]) ? -1 : 1;
                    }
                });
        }

        foreach ($directories as  $data) {

            $html .= '<tr><td>' . htmlentities($data['name']) . '</td>';
            $html .= '<td>' . \date('m/d/Y', intval($data['mtime'])) . '</td>';
            $html .= '<td>' . \sb\Files::sizeToString($data['size']) . '</td>';
            $html .= '<td>' . $data['file_count'] . '</td>';
            $html .= '<td><a href="' . ($this->getBaseUrl())
                . '?command=get_dates&log_type=' . urlencode($data['name'])
                . '">view</a> | <a href="'
                . (\str_replace("#", "", $this->getBaseUrl()))
                . '/export?log_type=' . urlencode($data['name']) . '">export</a></td>';
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
    protected function datesToHtmlTable($log_type, $sort_by = 'name', $reverse = false)
    {

        $dir = $this->getRoot() . $log_type;
        $this->validatePath($dir);


        $files = Array();
        $iterator = new \DirectoryIterator($dir);

        foreach ($iterator as $file){

            if ( $file->isDir() && !$file->isDot() && !preg_match("~\.~", $file)) {
                $details = \sb\Files::getDirectorySize($file->getRealPath());
                $files[$file->getBasename()] = [
                    'type' => 'dir',
                    'path' => $file->getPath(),
                    'size' => \sb\Files::sizeToString($details->size),
                    'details' => $details,
                    'mtime' => $file->getMTime(),
                    'name' => $file->getBaseName()
                ];

            } else if ($file->isFile()){
                $files[] = [
                    'type' => 'file',
                    'path' => $file->getPath(),
                    'size' => \sb\Files::sizeToString(filesize($file->getRealPath())),
                    'mtime' => $file->getMTime(),
                    'name' => $file->getBaseName()
                ];
            }
        }

        rsort($files);
        $html = $this->getNav() . '<h1>Log: ' . $log_type . '</h1>';
        $html .= '<table><thead><tr>';
        foreach (Array('name', 'size', 'type') as $prop) {
            $html .= '<th><a href="' . $this->getBaseUrl()
                . '?command=get_dates&log_type=' . urlencode($log_type)
                . '&sort_by=' . urlencode($prop);
            if ($prop == $sort_by && !$reverse) {
                $html .= '&reverse=1';
            } else {
                $html .= '&reverse=0';
            }
            $html .= '">' . $prop . '</a></th>';
        }
        $html .= '<th>Actions</th></tr></thead><tbody>';


        if (count($files)) {
            usort($files,
                function ($a, $b) use ($sort_by, $reverse) {

                    if (!isset($a[$sort_by]) || $a[$sort_by] == $b[$sort_by]) {
                        return 0;
                    }
                    if ($reverse) {
                        return ($a[$sort_by] < $b[$sort_by]) ? -1 : 1;
                    } else {
                        return ($a[$sort_by] > $b[$sort_by]) ? -1 : 1;
                    }
                });
        }

        foreach ($files as $file) {
            $html .= '<tr>';
            $html .= '<td>' . str_replace(".log", "", $file['name']) . '</td>';

            $html .= '<td>' . $file['size'] . '</td>';

            $html .= '<td>'.$file['type'].'</td>';
            $html .= '<td>';

            if($file['type'] == 'file'){
                $html .= '<a href="' . ($this->getBaseUrl())
                    . '?command=view&log_type=' .urlencode( $log_type)
                    . '&date_file=' .urlencode( $file['name'])
                    . '">view</a> | <a href="'
                    . ($this->getBaseUrl())
                    . '?command=tail&n=100&log_type=' .urlencode( $log_type)
                    . '&date_file=' . $file['name']
                    . '">tail</a> |<a href="'
                    . (\str_replace("#", "", $this->getBaseUrl()))
                    . '/export?log_type=' . urlencode($log_type)
                    . '&date_file=' . urlencode($file['name'])
                    . '">export</a>';
            } else if($file['type'] == 'dir'){
                $html .= '<a href="' . ($this->getBaseUrl())
                    . '?command=get_dates&log_type=' .urlencode( $log_type.'/'.$file['name'])
                    . '">view</a> | <a href="'
                    . (\str_replace("#", "", $this->getBaseUrl()))
                    . '/export?log_type=' . urlencode($log_type.'/'.$file['name'])
                    . '">export</a>';
            }

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
    protected function fileToHtmlTextarea($path)
    {

        $this->validatePath($path);
        if (is_file($path)) {
            $contents = file_get_contents($path);
        } else {
            $contents = 'No data found!';
        }
        $html = $this->getNav() . '<h1>Log: ' . str_replace($this->getRoot(), "", $path) . '</h1>';
        $html .= '<textarea class="sb_log" style="width:95%;min-height:400px;" >' . $contents . '</textarea>';
        return $html;
    }

    /**
     * Convert a file's contents into an HTML textarea by reading the last N
     * number of lines only
     * @param string $path The path to the file
     * @param integer $lines The number of lines
     * @return string
     */
    protected function fileToHtmlTextareaTail($path, $lines = 500)
    {

        $this->validatePath($path);
        $handle = fopen($path, "r");
        $linecounter = $lines;
        $pos = -2;
        $beginning = false;
        $text = array();
        while ($linecounter > 0) {
            $t = " ";
            while ($t != "\n") {
                if (fseek($handle, $pos, SEEK_END) == -1) {
                    $beginning = true;
                    break;
                }
                $t = fgetc($handle);
                $pos--;
            }
            $linecounter--;
            if ($beginning) {
                rewind($handle);
            }
            $text[$lines - $linecounter - 1] = fgets($handle);
            if ($beginning) {
                break;
            }
        }
        fclose($handle);

        $html = $this->getNav() . '<h1>Log: ' . str_replace($this->getRoot(), "", $path) . '</h1>';
        $html .= '<p>Last ' . $lines . ' lines in reverse chronological order</p>';
        $html .= '<textarea class="sb_log" style="width:95%;min-height:400px;" >';
        foreach ($text as $c) {
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
    public function index()
    {

        $command = $this->getGet('command');
        $log_type = $this->getGet('log_type');
        $date_file = $this->getGet('date_file');
        $sort_by = $this->getGet('sort_by', 'name');
        $reverse = $this->getGet('reverse', false);

        $html = '';
        switch ($command) {

            case 'get_dates':
                $html .= $this->datesToHtmlTable($log_type, $sort_by, $reverse);
                break;

            case 'view':

                $date_file_path = $this->getRoot() . $log_type . '/' . $date_file;
                if (!$date_file || !is_file($date_file_path)) {
                    $html .= "File not found";
                } else {
                    $html .= $this->fileToHtmlTextarea($date_file_path);
                }
                break;

            case 'tail':

                $date_file_path = $this->getRoot() . $log_type . '/' . $date_file;
                if (!$date_file || !is_file($date_file_path)) {
                    $html .= "File not found";
                } else {
                    $n = $this->getGet('n', 100);
                    $html .= $this->fileToHtmlTextareaTail($date_file_path, $n);
                }
                break;

            default:
                $html .= $this->logTypesToHtmlTable($sort_by, $reverse);
                break;
        }

        return $html;
    }

    /**
     * Serves to handle export requests in zip format
     *
     * @return type
     * @servable true
     */
    public function export()
    {

        $log_type = $this->getGet('log_type');
        $date_file = $this->getGet('date_file');
        $date_file_path = $this->getRoot() . $log_type;
        if($date_file){
            $date_file_path .= '/' . $date_file;
        }

        $this->validatePath($date_file_path);
        return \sb\Files\ForceDownload::fileToZip($date_file_path);

    }
}
