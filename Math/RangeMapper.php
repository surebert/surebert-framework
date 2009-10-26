<?php

/**
 * Maps ranges from one set of numbers to another
 *
 * @author Paul Visco 06/05/2007
 * @version 1.1 12/08/2008
 * @package sb_Math
 * 
 * $rangeMapper =new sb_Math_RangeMapper(Array(8,20), Array(10,100));
 * $rangeMapper->convert(50); //return 13.3 repeating
 * //basically 50 from the range of 10 to 100 is equivalent to 13.33 in the range of 8 to 20
 */
class sb_Math_RangeMapper{
 
	public function __construct($toRange, $fromRange){
		$this->toRange = $toRange;
		$this->fromRange = $fromRange;
		$toRangeDifference = $toRange[1]-$toRange[0];
		$fromRangeDifference = $fromRange[1]-$fromRange[0];
		
		$this->ratio = $toRangeDifference/$fromRangeDifference;
	}
	
	public function convert($fromRangeNumber){
		return (($fromRangeNumber-$this->fromRange[0])*$this->ratio)+$this->toRange[0];
	}
	
}
	



?>