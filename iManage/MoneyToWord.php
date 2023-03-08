<?php 
define("MAJOR", 'AND');
define("MINOR", 'CENTS');
class MoneyToWord
{
    var $pounds;
    var $pence;
    var $major;
    var $minor;
    var $words = '';
    var $number;
    var $magind;
    var $units = array('', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine');
    var $teens = array('ten', 'eleven', 'twelve', 'thirteen', 'fourteen', 'fifteen', 'sixteen', 'seventeen', 'eighteen', 'nineteen');
    var $tens = array('', 'ten', 'twenty', 'thirty', 'forty', 'fifty', 'sixty', 'seventy', 'eighty', 'ninety');
    var $mag = array('', 'thousand', 'million', 'billion', 'trillion');

    function MoneyToWord($amount, $major = MAJOR, $minor = MINOR)
    {
        $this->__MoneyToWord__((int)($amount), $major);
        $whole_number_part = $this->words;
        #$right_of_decimal = (int)(($amount-(int)$amount) * 100);
        $strform = number_format($amount,2);
        $right_of_decimal = (int)substr($strform, strpos($strform,'.')+1);
        $this->__MoneyToWord__($right_of_decimal, $minor);
        $this->words = $whole_number_part . ' ' . $this->words;
    }

    function __MoneyToWord__($amount, $major)
    {
        $this->major  = $major;
        #$this->minor  = $minor;
        $this->number = number_format($amount, 2);
        list($this->pounds, $this->pence) = explode('.', $this->number);
        $this->words = "$this->major";
        if ($this->pounds == 0)
            $this->words = "Zero $this->words";
        else {
            $groups = explode(',', $this->pounds);
            $groups = array_reverse($groups);
            for ($this->magind = 0; $this->magind < count($groups); $this->magind++) {
                if (($this->magind == 1) && (strpos($this->words, 'hundred') === false) && ($groups[0] != '000'))
                    $this->words = ' and ' . $this->words;
                $this->words = $this->_build($groups[$this->magind]) . $this->words;
            }
        }
    }

    function _build($n)
    {
        $res = '';
        $na  = str_pad("$n", 3, "0", STR_PAD_LEFT);
        if ($na == '000')
            return '';
        if ($na{0} != 0)
            $res = ' ' . $this->units[$na{0}] . ' hundred';
        if (($na{1} == '0') && ($na{2} == '0'))
            return $res . ' ' . $this->mag[$this->magind];
        $res .= $res == '' ? '' : ' and';
        $t = (int) $na{1};
        $u = (int) $na{2};
        switch ($t) {
            case 0:
                $res .= ' ' . $this->units[$u];
                break;
            case 1:
                $res .= ' ' . $this->teens[$u];
                break;
            default:
                $res .= ' ' . $this->tens[$t] . ' ' . $this->units[$u];
                break;
        }
        $res .= ' ' . $this->mag[$this->magind];
        return $res;
    }
    
    function get(){
        return strtoupper(str_replace(array('  ',' '),array(' ',' '),$this->words));
    }
}
function currency_to_cheque($num){ 
    $decones = array( 
                '01' => "One", 
                '02' => "Two", 
                '03' => "Three", 
                '04' => "Four", 
                '05' => "Five", 
                '06' => "Six", 
                '07' => "Seven", 
                '08' => "Eight", 
                '09' => "Nine", 
                10 => "Ten", 
                11 => "Eleven", 
                12 => "Twelve", 
                13 => "Thirteen", 
                14 => "Fourteen", 
                15 => "Fifteen", 
                16 => "Sixteen", 
                17 => "Seventeen", 
                18 => "Eighteen", 
                19 => "Nineteen" 
                );
    $ones = array( 
                0 => " ",
                1 => "One",     
                2 => "Two", 
                3 => "Three", 
                4 => "Four", 
                5 => "Five", 
                6 => "Six", 
                7 => "Seven", 
                8 => "Eight", 
                9 => "Nine", 
                10 => "Ten", 
                11 => "Eleven", 
                12 => "Twelve", 
                13 => "Thirteen", 
                14 => "Fourteen", 
                15 => "Fifteen", 
                16 => "Sixteen", 
                17 => "Seventeen", 
                18 => "Eighteen", 
                19 => "Nineteen",
                '01' => "One",     
                '02' => "Two", 
                '03' => "Three", 
                '04' => "Four", 
                '05' => "Five", 
                '06' => "Six", 
                '07' => "Seven", 
                '08' => "Eight", 
                '09' => "Nine", 
                ); 
    $tens = array( 
                0 => "",
                2 => "Twenty", 
                3 => "Thirty", 
                4 => "Forty", 
                5 => "Fifty", 
                6 => "Sixty", 
                7 => "Seventy", 
                8 => "Eighty", 
                9 => "Ninety" 
                ); 
    $hundreds = array( 
                "Hundred", 
                "Thousand", 
                "Million", 
                "Billion", 
                "Trillion", 
                "Quadrillion" 
                ); 
    $num = number_format($num,2,".",","); 
    $num_arr = explode(".",$num); 
    $wholenum = $num_arr[0]; 
    $decnum = $num_arr[1]; 
    $whole_arr = array_reverse(explode(",",$wholenum)); 
    krsort($whole_arr); 
    $rettxt = ""; 
    
    $cents = "";
    if($decnum > 0){ 
        $cents .= "  and cents "; 
        if($decnum < 20){ 
            $cents .= $decones[$decnum]; 
        }
        elseif($decnum < 100){ 
            $cents .= $tens[substr($decnum,0,1)]; 
            $cents .= " ".$ones[substr($decnum,1,1)]; 
        }
    } 
    $cents = trim($cents);
    foreach($whole_arr as $key => $i){ 
        if($i < 20){ 
            $rettxt .= $ones[$i]; 
        }
        elseif($i < 100){ 
            $fc_zero = $i[0] == 0;
            $i = $fc_zero ? substr($i,1) : $i;
            $rettxt .= ($fc_zero ? ' and ':'').$tens[substr($i,0,1)]; 
            $rettxt .= " ".$ones[substr($i,1,1)]; 
        }
        else{ 
            $rettxt .= ' '.$ones[substr($i,0,1)]." ".$hundreds[0]; 
            $_tenth = $tens[substr($i,1,1)];
            if(empty($_tenth)){
                $rettxt .= (empty($cents) && $ones[substr($i,1)] ? " and ":' ').$ones[substr($i,1)]; 
            }else{
                $rettxt .= (empty($cents) && !empty($_tenth) ? " and ":' ').$_tenth; 
                $rettxt .= " ".$ones[substr($i,2,1)]; 
            }
        } 
        if($key > 0){ 
            $rettxt .= " ".$hundreds[$key]; 
        } 
    
    } 
    $rettxt = "RINGGIT MALAYSIA : ".$rettxt." ".$cents;
    
    return strtoupper(trim(str_replace("  ","",$rettxt)))." ONLY";
        
}
?>