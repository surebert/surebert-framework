<?php

/**
 * @author Tony Cashaw
 * @package PDO
 */
namespace sb\PDO;

class RecordPager
{

    /**
     * The connection object to your database
     *
     * @var object PDO
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
     * Set to 1 to allow a blank object to return accurate pagenumbers
     *
     * @var integer
     */
    public $page_null = 0;

    /**
     * Set a max limit of records to page
     *
     * @var integer
     */
    public $max_limit = null;

    /**
     * The values passed for bound SQL parameters - added 05/06/08
     *
     * @var array
     */
    public $values = array();

    /**
     * Creates the record paging object and accepts an \sb\PDO database connection
     *
     * @param PDO $db
     */
    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Returns an object of type PDORecordPage set to the page numberd $pagenum
     *
     * Changed this 05/06/2008 Paul Visco added use of $this->values and 
     * $this->object_type to support additional \sb\PDO->s2o() arguments
     *
     * @param integer $pagenum
     * @return \sb\PDORecordPage
     *
     * <code>
     * 
     * //get the current requested page from an internet user
     * $pnum = (isset($_REQUEST['page']))?$_REQUEST['page']:1;
     * 
     * $pager = new \sb\PDO_RecordPager($mysqlconn);
     * $pager->sql = "SELECT * FROM user ORDER BY lname DESC;";
     * $pager->pagesize = 20 //optional default is set to 10
     * $res = $pager->getPage($punm);
     * 
     * echo '<pre>' . print_r($res->rows) . '</pre>';
     *
     *
     * </code>
     *
     */
    public function getPage($pagenum = 1, PDO_RecordPage $ret = null)
    {

        $pagenum = ($pagenum < 1) ? 1 : $pagenum;

        if ((trim($this->sql) == '')) {

            throw(new \Exception("The SQL statement '$this->sql' is not valid."));
        } elseif (!(stristr(($this->sql), 'SELECT')) || (stristr(($this->sql), 'LIMIT'))) {

            throw(new \Exception("SQL must be a 'SELECT' statment with no 'LIMIT' clause"));
        } else {

            //start return object
            if (!$ret) {
                $ret = new PDO_RecordPage();
            }

            $this->sql = str_replace(";", "", $this->sql);

            $ret->requested_page = $pagenum;

            //get counts
            $sql = $this->sql;
            if ($this->max_limit) {
                $sql .= " LIMIT " . $this->max_limit;
            }
            $count_sql = "SELECT COUNT(*) AS 'count' FROM (" . $sql . ") sb65a";

            $res = $this->db->s2o($count_sql, $this->values);
            $ret->record_count = isset($res[0]) ? $res[0]->count : 0;

            //page count
            $temp = round($ret->record_count / $this->pagesize);
            $temp2 = $temp * $this->pagesize;
            $round_up = ($temp2 < $ret->record_count) ? 1 : 0;
            $ret->page_count = round($ret->record_count / $this->pagesize) + $round_up;
            $ret->page_count = ($ret->page_count < 1) ? 1 : $ret->page_count;

            //current page
            $ret->current_page = ($pagenum > $ret->page_count) ? $ret->page_count : $pagenum;

            //get limit clause
            $start = ($this->pagesize * ($ret->current_page - 1));
            $start = ($start < 0) ? 0 : $start;
            $limit_sql = $this->sql . " LIMIT $start, $this->pagesize; ";

            //debug
            //echo $limit_sql; exit;

            $ret->rows = $this->db->s2o($limit_sql, $this->values, $this->object_type);

            //return
            return $ret;
        }
        return 0;
    }

    /**
     * After a sql statment has been set for this object this method will return
     * a class of type \sb\PDORecordPage that is the first page that meets the following
     * search critieria:
     *         $field (the field to search)
     *         $value (the value of the specified field)
     * NOTE: This functionaly is very slow to use.
     *
     * @author Tony Cashaw
     * @param string $field
     * @param string $value
     * @return object \sb\PDORecordPage or 0 if the value is not found
     *
     * <code>
     * //... continued from above
     *
     * if($flipped = $pager->flipTo('lname', 'cashaw')){
     * 
     *    //prints the contents of the first page that contained a row with the column
     *    //'lname' set to the value of 'cashaw'
     * 
     *    echo '<pre>' . print_r($res->rows) . '</pre>';
     * }else{
     * 
     * }
     *
     * </code>
     */
    public function flipTo($field, $value)
    {

        $ret->found = 0;
        $ret->page = new \stdClass();

        if (trim($this->sql) != '') {
            //get the page count
            $temp = $this->getPage();
            $count = $temp->page_count;

            for ($pnum = 1; $pnum <= $count; $pnum++) {

                //get the next page
                $page = $this->getPage($pnum);

                //look for the value
                foreach ($page->rows as $rec) {
                    if (isset($rec->{$field})) {
                        if ($rec->{$field} == $value) {
                            return $page;
                        }
                    } else {
                        throw(new \Exception(
                        "The field $field is not contained in the recordset you request to search"
                        ));
                    }
                }
            }
        } else {
            throw(new \Exception("\$sql not set. Please set SQL before flipping to a page"));
        }

        return 0;
    }
}

