<?php

/**
 * A data object with the details and records of a paged MySQL query
 *
 * @author Tony Cashaw
 * @package PDO
 */
namespace sb\PDO;

class RecordPage
{

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

    public function prevPage()
    {
        if ($this->page_null == 1) {
            return 0;
        }
        return ($this->current_page <= 1) ? 1 : $this->current_page - 1;
    }

    public function nextPage()
    {
        if ($this->page_null == 1) {
            return 0;
        }
        return ($this->current_page >= $this->page_count) ? $this->page_count : $this->current_page + 1;
    }
}

