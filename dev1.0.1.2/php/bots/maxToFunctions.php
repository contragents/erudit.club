<?php
function maxToLeft($x,$y,$countFishki,&$desk) {
    $max=0;
    //if ($x == 0) return 1;
    for ($i=$x;($i>0 ? (!$desk[$i-1][$y][0]) : true) &&  (!$desk[$i][$y][0]) && (!$desk[$i][$y+1][0]) && (!$desk[$i][$y-1][0]) && ($i>0 ? (!$desk[$i-1][$y-1][0]) : true) && (!$desk[$i+1][$y-1][0]) && ($i >= 0); $i--) {
        $max++;
    }
    
    return ($max>$countFishki ? $countFishki : $max);
}

function maxToUp($x,$y,$countFishki,&$desk) {
    //if ( $y == 0 ) return 1;
    $max=0;
    
    for ($j=$y;(($j>0) && ($desk[$x][$j-1][0] == false)) && (!$desk[$x][$j][0]) && (!$desk[$x+1][$j][0]) && (!$desk[$x-1][$j][0]) && ($j>0 ? (!$desk[$x-1][$j-1][0]) : true) && (!$desk[$x+1][$j-1][0]) && ($j >= 0); $j--) {
        $max++;
    }
    
    return ($max>$countFishki ? $countFishki : $max);
}

function maxToRight($x,$y,$countFishki,&$desk) {
    //if ($x == 14) return 1;
    $max=0;
    
    for ($i=$x;($i<14 ? (!$desk[$i+1][$y][0]) : true) && (!$desk[$i][$y][0]) && (!$desk[$i+1][$y][0]) && (!$desk[$i][$y+1][0]) && (!$desk[$i][$y-1][0]) && (!$desk[$i+1][$y+1][0]) && (!$desk[$i+1][$y-1][0]) && ($i <=14); $i++) {
        $max++;
    }
    
    return ($max>$countFishki ? $countFishki : $max);
}

function maxToDown($x,$y,$countFishki,&$desk) {
    $max=0;
    
    for ($j=$y;($j<14 ? (!$desk[$x][$j+1][0]) : true) && (!$desk[$x][$j][0]) && (!$desk[$x+1][$j][0]) && (!$desk[$x-1][$j][0]) && (!$desk[$x-1][$j+1][0]) && (!$desk[$x+1][$j+1][0]) && (!$desk[$x+1][$j+1][0]) && (!$desk[$x-1][$j+1][0]) && ($j <=14); $j++) {
        $max++;
    }
    
    return ($max>$countFishki ? $countFishki : $max);
}
