<?php
function getMidColors($rgb1, $rgb2, $nb) {
    $rgb_mid=array();
    for ($j = 1; $j <= $nb; $j++) {
        $rgb_mid[$j]=array();
        for ($i = 0; $i < 3; $i++) {
            if ($rgb1[$i] < $rgb2[$i]) {
                $rgb_mid[$j][]= round(((max($rgb1[$i], $rgb2[$i]) - min($rgb1[$i], $rgb2[$i])) / ($nb + 1)) * $j + min($rgb1[$i], $rgb2[$i]));
            } else {
                $rgb_mid[$j][]= round(max($rgb1[$i], $rgb2[$i]) - ((max($rgb1[$i], $rgb2[$i]) - min($rgb1[$i], $rgb2[$i])) / ($nb + 1)) * $j);
            }
        }
    }
    return $rgb_mid;
}

?>
