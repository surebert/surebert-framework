<?php
/**
 * Generates a random lower/upper letter + numbers password of a specified length
 * Makes sure at least one char is cpaitalize
 *
 * Makes sure at least one
 *
 * @author paul.visco@roswellpark.org
 * @package sb_Password
 */
class sb_Password_Random {
	/**
	 * The length of password to generate
	 * @var integer
	 */
	protected $length = 6;

	/**
	 * The password that is generated
	 * @var string
	 */
	protected $password = '';

	/**
	 * Create a password of a certain length
	 *
	 * <code>
	 * echo new sb_Password_Random(6);
	 * </code>
	 * 
	 * @param integer $length The length of the password to generate
	 * @param boolean $mixed_case Determines if mixed case is used
	 */
	function __construct($length, $mixed_case = true) {

		if(preg_match("~^\d$~", $length)){
			$this->length = $length;
		} else {
			throw(new Exception("Length must an integer"));
		}

		$this->generate();

		if($mixed_case){
			$this->randomly_capitalize();
		}
	}

	/**
	 * Generates the password randomly to include at least on letter
	 */
	protected function generate(){

		$this->password = '';

		$letters = range('a', 'z');

		//get rid of l and o as they can be confused with 1 and 0
		unset($letters[11]);
		unset($letters[14]);

		$letters = array_values($letters);

		$letters_length = count($letters);

		foreach(range(1,$this->length) as $char){
			
			$char = '';
		
			$letter = $letters[rand(0, $letters_length-1)];
			$integer = rand(2, 9);

			if(!preg_match("~[a-kmnp-z]~", $this->password)){
				$char = $letter;
			} else if(!preg_match("~[2-9]~", $this->password)){
				$char = $integer;
			} else {
				$char = rand(0, 1) ? $letter : $integer;
			}

			$this->password .= $char;
		}

	}

	/**
	 * Randomly capitalize one of the letters in the password
	 */
	protected function randomly_capitalize(){
		$chars = str_split ($this->password);
		$letter_positions = Array();
		foreach($chars as $key=>$char){
			if(!is_numeric($char)){
				$letter_positions[] = $key;
			}
		}


		$random_letter = $letter_positions[array_rand($letter_positions)];
		
		$chars[$random_letter] = ucwords($chars[$random_letter]);
	
		$this->password = implode('', $chars);
	}

	public function __toString(){
		return $this->password;
	}
}

?>