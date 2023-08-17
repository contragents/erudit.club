<?php

use Lang\Eng;

function findWordSleva($x, $y, &$desk, &$fishki, array &$slovaPlayed, $orientation = 'all')
{
    if (!count($fishki)) {
        return '';
    }

    //пробуем влево от xy
    $maxLen = maxToLeft($x, $y, count($fishki), $desk);
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
    while ($desk[$x + $step][$y][0]) {
        $lastLetter .= Eng::$bukvy[($desk[$x + $step][$y][1] < 999 ? $desk[$x + $step][$y][1] : $desk[$x + $step][$y][1] - 999 - 1)][0];
        $maxWordLen++;
        $step++;
    }
    //print '-' . $x . '-' . $y . '-' . $lastLetter . '-';
    $regexp = makeRegexp($fishki);

    $zapros = "select slovo from dict where (slovo REGEXP \"^[$regexp]{0,$maxLen}$lastLetter";

    if (($maxRightAfterLen = maxToRight(
            $x + mb_strlen($lastLetter, 'UTF-8') + 1,
            $y,
            count($fishki),
            $desk
        )) || (($x == 7) && ($y == 7))) {
        $zapros .= "[$regexp]{0,$maxRightAfterLen}";
        $maxWordLen += $maxRightAfterLen;
    }

    if ($maxWordLen > 7) {
        $maxWordLen = 7;
    }//так работает индекс
    /*Как идея ограничивать мах длину слова, но она будет почти всегда 8+
    if ( ($maxWordLen - $maxRightAfterLen - $maxLen + count($fishki)) < $maxWordLen )
        $maxWordLen = $maxWordLen - $maxRightAfterLen - $maxLen + count($fishki);
    */

    $zapros .= "$\") AND NOT deleted = 1 AND slovo != '$lastLetter' AND length<=$maxWordLen AND lng = 2 ORDER BY length ASC";
    //print $zapros . 'SLEVA'; //sleep (5);

    if ($res = \Dadata\DB::queryArray($zapros)) {
        foreach ($res as $row) {
            if (!isset($slovaPlayed[$row['slovo'] = mb_strtolower($row['slovo'], 'UTF-8')])
                &&
                checkWordFishki($fishki, $row['slovo'], $lastLetter, $lettersZvezd)) {
                $cells = $desk;
                $slovoNach = ($lastLetter === '' ? 0 : mb_strpos($row['slovo'], $lastLetter, 0, 'UTF-8'));
                $lastLetterLen = ($lastLetter === '' ? 0 : mb_strlen($lastLetter, 'UTF-8'));
                for ($k = 0; $k < $slovoNach; $k++) {
                    $cells[$x - $k][$y][0] = true;
                    $cells[$x - $k][$y][1] = Eng::getLetterCode(
                        $letter = mb_substr($row['slovo'], $slovoNach - 1 - $k, 1, 'UTF-8')
                    );
                    if (isset($lettersZvezd[$letter])) {
                        $cells[$x - $k][$y][2] = $lettersZvezd[$letter];
                    }//Указали на занятую звездочку
                }

                if (($x == 7) && ($y == 7)) {
                    $delta = -1;
                } else {
                    $delta = 0;
                }

                for ($k = $slovoNach + $lastLetterLen; $k < mb_strlen($row['slovo'], 'UTF-8'); $k++) {
                    $cells[$x + $k - $slovoNach + 1 + $delta][$y][0] = true;
                    $cells[$x + $k - $slovoNach + 1 + $delta][$y][1] = Eng::getLetterCode(
                        $letter = mb_substr($row['slovo'], $k, 1, 'UTF-8')
                    );
                    if (isset($lettersZvezd[$letter])) {
                        $cells[$x + $k - $slovoNach + 1 + $delta][$y][2] = $lettersZvezd[$letter];
                    } // Указали на занятую звездочку
                }
                printr($cells);
                //sleep(2);
                $desk = $cells;

                return true;
                //return ['word'=>$row['slovo'],'lastLettersNum'=>mb_strlen($lastLetter,'UTF-8')];
            }
        }
    }

    return '';
}