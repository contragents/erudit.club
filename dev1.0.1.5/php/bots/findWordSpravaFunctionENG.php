<?php

function findWordSprava($x, $y, &$desk, &$fishki, array &$slovaPlayed, $orientation = 'all')
{
    if (!count($fishki)) {
        return '';
    }
    //пробуем справа от xy
    $maxLen = maxToRight($x, $y, count($fishki), $desk);
    $maxWordLen = $maxLen;

    if (
        ($desk[$x][$y + 1][0] ?? false)
        ||
        ($desk[$x][$y - 1][0] ?? false)
    ) {
        return '';
    }
    //Не анализируем вертикальные примыкающие буквы
    $regexp = '';
    $lastLetter = '';
    $step = 1;
    while ($desk[$x - $step][$y][0]) {
        $lastLetter = \Lang\Eng::$bukvy[($desk[$x - $step][$y][1] < 999 ? $desk[$x - $step][$y][1] : $desk[$x - $step][$y][1] - 999 - 1)][0] . $lastLetter;
        $maxWordLen++;
        $step++;
    }
    //print '-' . $x . '-' . $y . '-' . $lastLetter . '-';
    $regexp = makeRegexp($fishki);

    $zapros = '';

    if ($maxLeftBeforeLen = maxToLeft($x - mb_strlen($lastLetter, 'UTF-8') - 1, $y, count($fishki), $desk)) {
        $zapros = "[$regexp]{0,$maxLeftBeforeLen}";
        $maxWordLen += $maxLeftBeforeLen;
    }

    if ($maxWordLen > 7) {
        $maxWordLen = 7;
    }//так работает индекс

    $zapros = "select slovo from dict where (slovo REGEXP \"^$zapros{$lastLetter}[$regexp]{0,$maxLen}";
    $zapros .= "$\") AND NOT deleted = 1  AND length<=$maxWordLen AND lng = 2 ORDER BY length ASC";

    //print $zapros . 'SPRAVA';
    $xLastLetter = $x - mb_strlen($lastLetter, 'UTF-8');
    if ($res = \Dadata\DB::queryArray($zapros)) {
        foreach ($res as $row) {
            if (!isset($slovaPlayed[$row['slovo'] = mb_strtolower($row['slovo'], 'UTF-8')]) && checkWordFishki(
                    $fishki,
                    $row['slovo'],
                    $lastLetter,
                    $lettersZvezd
                )) {
                $cells = $desk;
                $slovoNach = mb_strpos($row['slovo'], $lastLetter, 0, 'UTF-8');
                $lastLetterLen = mb_strlen($lastLetter, 'UTF-8');
                for ($k = 0; $k < $slovoNach; $k++) {
                    $cells[$xLastLetter - $k - 1][$y][0] = true;
                    $cells[$xLastLetter - $k - 1][$y][1] = \Lang\Eng::getLetterCode(
                        $letter = mb_substr($row['slovo'], $slovoNach - 1 - $k, 1, 'UTF-8')
                    );
                    if (isset($lettersZvezd[$letter])) {
                        $cells[$xLastLetter - $k - 1][$y][2] = $lettersZvezd[$letter];
                    }//Указали на занятую звездочку
                }
                for ($k = 0; $k <= mb_strlen($row['slovo'], 'UTF-8') - $slovoNach - $lastLetterLen - 1; $k++) {
                    $cells[$x + $k][$y][0] = true;
                    $cells[$x + $k][$y][1] = \Lang\Eng::getLetterCode(
                        $letter = mb_substr($row['slovo'], $slovoNach + $lastLetterLen + $k, 1, 'UTF-8')
                    );
                    if (isset($lettersZvezd[$letter])) {
                        $cells[$x + $k][$y][2] = $lettersZvezd[$letter];
                    }//Указали на занятую звездочку
                }
                printr($cells);
                $desk = $cells;
                return true;
                //return ['word'=>$row['slovo'],'lastLettersNum'=>mb_strlen($lastLetter,'UTF-8')];
            }
        }
    }

    return '';
}
