<?php
/**
 * @author Tony Cashaw
 * @version 1.2 02-14-2008 12-08-2008
 *
 */

class sb_PDO_RecordPager{
	
	  /**
	   * The connection object to your database
	   *
	   * @var object sb_PDO
	   */
	  private $db;
	  
	  /**
	   * The SQL statment that you'd like to get pages of.
	   *
	   * @var string
	   */
	  public $sql;
	  
	  /**
	   * The number of records you'd like back per page
	   *
	   * @var integer
	   */
	  public $pagesize = 10;
	  
	  /**
	   * The object type of the objects returned which represent the rows - added 05/06/08
	   *
	   * @var string
	   */
	  public $object_type = null;
	  
	   /**
	   * The values passed for bound SQL parameters - added 05/06/08
	   *
	   * @var string
	   */
	  public $values = null;
	  
	  /**
	   * Creates the record paging object and accepts an sb_PDO database connection
	   *
	   * @param sb_PDO $db
	   */
	  public function __construct(sb_PDO $db){
	  		$this->db = $db;
	  }
	
	  /**
	   * Returns an object of type sb_PDORecordPage set to the page numberd $pagenum
	   * 
	   * Changed this 05/06/2008 Paul Visco added use of $this->values and $this->object_type to support additional sb_PDO->s2o() arguments
	   *
	   * @param integer $pagenum
	   * @return sb_PDORecordPage
	   * @example 
			 <code>
			 	
			 	//get the current requested page from an internet user
			 	$pnum = (isset($_REQUEST['page']))?$_REQUEST['page']:1;
			 	
			 	$pager = new sb_PDO_RecordPager($mysqlconn);
			 	$pager->sql = "SELECT * FROM user ORDER BY lname DESC;";
			 	$pager->pagesize = 20 //optional default is set to 10
			 	$res = $pager->get_page($punm);
			 	
			 	echo '<pre>' . print_r($res->rows) . '</pre>';
			 
			 
			 </code>
 	   * 
	   */
	  public function get_page($pagenum = 1){
	  				
			if((trim($this->sql) == '')){				
				
				throw(new Exception("The SQL statement '$this->sql' is not valid."));
					
			}else if( !(stristr(($this->sql), 'SELECT')) || (stristr(($this->sql), 'LIMIT'))){
				
				throw(new Exception("SQL must be a 'SELECT' statment with no 'LIMIT' clause"));	
			
			}else{
			
				//start return object
				$ret = new sb_PDORecordPage();	
				$this->sql = str_replace(";", "", $this->sql);
				
				$ret->requested_page = $pagenum;
				
				//get counts
				$count_sql = preg_replace("/SELECT(.*)FROM/", "SELECT COUNT(*) as 'count' FROM ", $this->sql) . ";";
				
				//$count_sql = preg_replace("/SELECT(.*)FROM/", "SELECT COUNT(*) as 'count' FROM ", $this->sql) . ";";
				$res = $this->db->s2o($count_sql, $this->values, $this->object_type);
				$ret->record_count = $res[0]->count;
				
				//page count			
				$temp = round($ret->record_count / $this->pagesize);
				$temp2 = $temp * $this->pagesize;
				$round_up = ($temp2 < $ret->record_count)?1:0;
				$ret->page_count = round($ret->record_count / $this->pagesize) + $round_up; 		
				
				if($pagenum < 1){					
					$ret->current_page = 1;
				}elseif($pagenum > $ret->page_count){
					$ret->current_page = $ret->page_count;
				}else{
					$ret->current_page = $pagenum;
				}
				
				//get limit clause
				$start = ($this->pagesize * ($ret->current_page - 1));		
				$limit_sql = $this->sql . " LIMIT $start, $this->pagesize; ";	
			
				$ret->rows = $this->db->s2o($limit_sql, $this->values, $this->object_type);
				
				//return
				return $ret;
			}		
			return 0;
		}
		
		
		/**
		 * After a sql statment has been set for this object this method will return
		 * a class of type sb_PDORecordPage that is the first page that meets the following
		 * search critieria:
		 * 		$field (the field to search)
		 * 		$value (the value of the specified field)
		 * NOTE: This functionaly is very slow to use.
		 *
		 * @author Tony Cashaw
		 * @param string $field
		 * @param string $value
		 * @return object sb_PDORecordPage or 0 if the value is not found
		 * 
		 * @example 
			 <code>
			 		//... continued from above
			 		
			 		if($flipped = $pager->flipto('lname', 'cashaw')){
			 			
			 			//prints the contents of the first page that contained a row with the column 
			 			//'lname' set to the value of 'cashaw'
			 			
			 			echo '<pre>' . print_r($res->rows) . '</pre>';
			 		}else{
			 			
			 		}
			 					 		
			 </code>
		 * 
		 */
		public function flipto($field, $value){
			
			$ret->found = 0;
			$ret->page = new stdClass();
			
			if(trim($this->sql) != ''){ 				
				//get the page count
				$temp = $this->get_page();
				$count = $temp->page_count;	
						
				for($pnum=1;$pnum<=$count;$pnum++){				
					
					//get the next page
					$page = $this->get_page($pnum);
					
					//look for the value
					foreach($page->rows as $rec){
						if(isset($rec->{$field})){
							if($rec->{$field} == $value){
								return $page;
							}
						}else{
							throw(new Exception("The field $field is not contained in the recordset you request to search"));
						}	
						
					}
				}
			}else{
				throw(new Exception("\$sql not set. Please set SQL before flipping to a page"));
			}
			
			return 0;
			
		}
}


/**
 * A data object with the details and records of a paged MySQL query
 *
 * @author Tony Cashaw
 * @version 1.0 2008-02-01
 */
class sb_PDORecordPage {	
	
	/**
	 * The page number that this object is set to
	 *
	 * @var integer unsigned
	 */
	public $current_page;
	
	/**
	 * The total number of pages possible
	 *
	 * @var integer
	 */
	public $page_count = -1;
	
	/**
	 * The number of total records in the super set of data
	 *
	 * @var integer
	 */
	public $record_count;
	
	/**
	 *  This array is the requested subset of data.
	 *  An array of of stdClass objects that reflect the rows of the statement. 
	 * 
	 * @var array
	 */
	public $rows = array();

}

?>