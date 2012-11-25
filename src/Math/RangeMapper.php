<?php

/**
 * Maps ranges from one set of numbers to another
 *
 * @author Voscp
 * @package Math
 * 
 * $rangeMapper =new \sb\Math\RangeMapper(Array(8,20), Array(10,100));
 * $rangeMapper->convert(50); //return 13.3 repeating
 * //basically 50 from the range of 10 to 100 is equivalent to 13.33 in the range of 8 to 20
 */
namespace sb\Math;

class RangeMapper{
 
    public function __construct($toRange, $fromRange)
    {
        $this->toRange = $toRange;
        $this->fromRange = $fromRange;
        $toRangeDifference = $toRange[1]-$toRange[0];
        $fromRangeDifference = $fromRange[1]-$fromRange[0];

        if($fromRangeDifference == 0){
            $this->ratio = 1;
        } else {
            $this->ratio = $toRangeDifference/$fromRangeDifference;
        }

    }
    
    public function convert($fromRangeNumber)
    {
        if($this->ratio == 1){
            return $this->toRange[1];
        }
        return (($fromRangeNumber-$this->fromRange[0])*$this->ratio)+$this->toRange[0];
    }
    
}
    



