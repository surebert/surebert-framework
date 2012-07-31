<?php
/**
 * Data object that  holds listing information for Windows share lisitngs
 *
 * @author Paul Visco, Anthony Cashaw
 * @package Samba
 *
 */
namespace sb;
class Samba_Listing {

	/**
	 * The name of the windows resource
	 * @var string
	 */
	public $name;

	/**
	 * The remote directory where this file resides
	 * @var unknown_type
	 */
	public $path;

	/**
	 * The type of resource this listing is
	 * @var char
	 */
	public $type;

	/**
	 * This size of the windows resource in bytes
	 * @var integer
	 */
	public $size;

	/**
	 * The date that the windows resoruce was last modified
	 * @var string
	 */
	public $datemodified;

	/**
	 * Prints the full path of the resource
	 * @return string
	 */
	public function fullpath() {
		return Samba_Connection::winslashes((preg_match('/\w+\.\w*/', $this->path))?$this->path:"$this->path\\$this->name");
	}

	/**
	 * Returns the datemodified data as unix timestamp
	 * @return string
	 */
	public function unix_datemodified() {
		return strtotime($this->datemodified);
	}

	/**
	 * Normalizes name as windows is case insesntive
	 * @return string
	 */
	public function normalize_name() {
		return strtolower($this->name);
	}

};
?>