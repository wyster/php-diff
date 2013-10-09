<?php

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
    public function Merge($options = 7)
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
                else if ($tag == 'delete' && !$use_del)
                {
                    for ($i = $a1; $i < $a2; $i++)
                        $c[] = $a[$i];
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
                        for ($i = $a1; $i < $a2; $i++)
                           $c[] = $a[$i];
                    }
                }
                $last = $b2;
            }
        }
        
        return $c;
    }
}