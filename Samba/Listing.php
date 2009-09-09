<?php
 /**
 *
 * @author: Anthony Cashaw
 * @version: 1.0 09/09/09
 *
 *Data object that  holds listing information for Windows share lisitngs
 */
	class sb_Samba_Share_Listing{
	
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
		public function fullpath(){			
			return sb_Samba_Share::winslashes((preg_match('/\w+\.\w*/', $this->path))?$this->path:"$this->path\\$this->name");
		}

                /**
                 * Returns the datemodified data as unix timestamp
                 * @return string
                 */
                public function unix_datemodified(){
                    return strtotime($this->datemodified);
                }

                public function unix_name(){
                    return strtolower($this->name);
                }
				
	};
?>