<?php
/**
 * Diff Merge
 * 
 * Merge A and B selecting types and lines
 * 
 * @author Andreas Seifert
 * @copyright (c) 2013 Andreas Seifert
 * @license New BSD License http://www.opensource.org/licenses/bsd-license.php
 * @link http://github.com/cobrafast/php-diff
 */

class Diff_Merge
{
    private $diff;
    
    public static $MERGEOPT_USE_INSERT = 1;
    public static $MERGEOPT_USE_REPLACE = 2;
    public static $MERGEOPT_USE_DELETE = 4;
    
    public function __construct(Diff $diff)
    {
        $this->diff = $diff;
    }
    
    /**
     * Creates a merged file using only groups of a certain type.
     * @param int $options Options for merging. Use Diff_Merge::MERGEOPT_USE_*
     * @return array Returns merged array of lines
     */
    public function Merge($options = 7, $use_lines = false)
    {
        $use_ins = $options & 1;
        $use_rep = $options & 2;
        $use_del = $options & 4;
        
        $a = $this->diff->getA();
        $b = $this->diff->getB();
        $c = array();
        
        $opCodes = $this->diff->getGroupedOpcodes();
        $last = 0;
        foreach ($opCodes as $group)
        {
            foreach ($group as $code)
            {
                list($tag, $a1, $a2, $b1, $b2) = $code;
                
                if ($b1 > $last)
                {
                    for ($i = $last; $i < $b1; $i++)
                        $c[] = $b[$i];
                    $last = $b1;
                }
                
                if ($tag == 'equal')
                {
                    for ($i = $a1; $i < $a2; $i++)
                        $c[] = $a[$i];
                }
                else if ($tag == 'insert' && $use_ins)
                {
                    for ($i = $b1; $i < $b2; $i++)
                        $c[] = $b[$i];
                }
                else if ($tag == 'insert' && !$use_ins && ($r = $this->inRange($b1, $b2, $use_lines)) !== false)
                {
                    foreach ($r as $n)
                        $c[] = $b[$n];
                }
                else if ($tag == 'delete' && !$use_del)
                {
                    if (($r = $this->inRange($a1, $a2, $use_lines)) !== false)
                    {
                        var_dump($r);
                        for ($i = $last; $i < $a2; $i++)
                        {
                            var_dump($i);
                            if (!in_array($i, $r))
                                $c[] = $a[$i];
                        }
                    }
                    else
                    {
                        for ($i = $a1; $i < $a2; $i++)
                            $c[] = $a[$i];
                    }
                }
                else if ($tag == 'replace')
                {
                    if ($use_rep)
                    {
                        for ($i = $b1; $i < $b2; $i++)
                            $c[] = $b[$i];
                    }
                    else
                    {
                        if (($r = $this->inRange($a1, $a2, $use_lines)) !== false)
                        {
                            for ($i = $b1; $i < $b2; $i++)
                            {
                                if (in_array($i, $r))
                                    $c[] = $b[$i];
                                else
                                    $c[] = $a[$i];
                            }
                        }
                        else
                        {
                            for ($i = $a1; $i < $a2; $i++)
                               $c[] = $a[$i];
                        }
                    }
                }
                $last = $b2;
            }
        }
        
        return $c;
    }
    
    private function inRange($min, $max, $n)
    {
        if ($n === false)
            return false;
        
        if (is_array($n))
        {
            $range = array();
            foreach ($n as $i)
            {
                if ($i >= $min && $i <= $max)
                    $range[] = $i;
            }
            if (empty($range))
                return false;
            
            return $range;
        }
        else if (is_numeric($n))
            return ($n >= $min && $n <= $max);
        return false;
    }
}