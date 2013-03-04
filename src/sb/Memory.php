<?php
/**
 * Used to debug memory usage
 */
namespace sb;

class Memory{
    
    /**
     * Determines the peak memory usage
     * @param boolean $system Set this to TRUE to get the real size of memory allocated from system
     * @param boolean $peak Get peak memory if true, otherwise current memory
     * @return string The value in b, KB, or MB depending on size
     */
    public static function getUsage($system=false, $peak = true) {
        if ($peak) {
            $mem = memory_get_peak_usage($system);
        } else {
            $mem = memory_get_usage($system);
        }

       $unit=array('b','kb','mb','gb','tb','pb');
       return round($mem/pow(1024,($i=floor(log($mem,1024)))),2).' '.$unit[$i];
    }
}
