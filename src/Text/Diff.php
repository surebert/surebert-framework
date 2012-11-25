<?php

/**
 * Used to diff between two texts
 * Adapted from https://github.com/paulgb/simplediff/blob/5bfe1d2a8f967c7901ace50f04ac2d9308ed3169/simplediff.php
 * 
 * @author paul.visco@roswellpark.org
 * @package Text
 */
namespace sb\Text;

class Diff 
    {

    public static function compare($old, $new) 
    {
        
        $maxlen = 0;
        foreach ($old as $oindex => $ovalue) {
            $nkeys = array_keys($new, $ovalue);
            foreach ($nkeys as $nindex) {
                $matrix[$oindex][$nindex] = isset($matrix[$oindex - 1][$nindex - 1]) ? $matrix[$oindex - 1][$nindex - 1] + 1 : 1;
                if ($matrix[$oindex][$nindex] > $maxlen) {
                    $maxlen = $matrix[$oindex][$nindex];
                    $omax = $oindex + 1 - $maxlen;
                    $nmax = $nindex + 1 - $maxlen;
                }
            }
        }

        if ($maxlen == 0){
            return array(array('d' => $old, 'i' => $new));
        }

        return array_merge(
                self::compare(array_slice($old, 0, $omax), array_slice($new, 0, $nmax)),
                array_slice($new, $nmax, $maxlen),
                self::compare(array_slice($old, $omax + $maxlen), array_slice($new, $nmax + $maxlen)));
    }

    public static function html($old, $new) 
    {
        
        $html = '';
        $old = str_replace("\n", "\n ", $old);
        $new = str_replace("\n", "\n ", $new);
        $arr = self::compare(explode(' ', $old), explode(' ', $new));
        foreach($arr as $a) {
            if(is_array($a)) {
                if(isset($a['d'])){
                    $html .= "<del>" . implode(' ', $a['d']) . "</del>";
                }
                if(isset($a['i'])){
                    $html .=  "<ins>" . implode(' ', $a['i']) . "</ins> ";
                }
            } else {
                $html .= $a.' ';
            }
        }
        return str_replace("\n ", "\n", $html);
    }
    

}

