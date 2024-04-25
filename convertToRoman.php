<?php

function convertToRoman($num) {
    $map = array(
        'M' => 1000,
        'CM' => 900,
        'D' => 500,
        'CD' => 400,
        'C' => 100,
        'XC' => 90,
        'L' => 50,
        'XL' => 40,
        'X' => 10,
        'IX' => 9,
        'V' => 5,
        'IV' => 4,
        'I' => 1
    );

    $result = '';

    foreach ($map as $roman => $arabic) {
        // Divide the number by the current value of the Roman numeral
        $matches = intval($num / $arabic);
        // Concatenate the Roman numeral the corresponding number of times
        $result .= str_repeat($roman, $matches);
        // Subtract the value of the Roman numeral from the number
        $num %= $arabic;
    }

    return $result;
}

?>
