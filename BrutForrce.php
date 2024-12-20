<?php

$target_password = "abc";

function brute_force($target) {
    $characters = 'abcdefghijklmnopqrstuvwxyz0123456789';
    $target_length = strlen($target);

    for ($length = 1; $length <= $target_length; $length++) {
        $combinations = generate_combinations($characters, $length);

        foreach ($combinations as $guess) {
            echo "Пробую: $guess\n";
            if ($guess === $target) {
                echo "Пароль найден: $guess\n";
                return $guess;
            }
        }
    }
}

function generate_combinations($characters, $length) {
    $combinations = [];
    $charactersArray = str_split($characters);
    $maxIndex = count($charactersArray) - 1;

 
    $totalCombinations = pow(count($charactersArray), $length); 
    for ($i = 0; $i < $totalCombinations; $i++) {
        $combination = '';
        $temp = $i;
        
       
        for ($j = 0; $j < $length; $j++) {
            $combination = $charactersArray[$temp % count($charactersArray)] . $combination;
            $temp = (int)($temp / count($charactersArray));
        }
        $combinations[] = $combination;
    }

    return $combinations;
}

brute_force($target_password);

?>
