<?php
function findWordSverhu($x, $y, &$desk, &$fishki, array &$slovaPlayed, $orientation = 'all')
{
    if (!count($fishki)) return '';
    //пробуем вверх от xy
    $maxLen = maxToUp($x, $y, count($fishki), $desk);
    $maxWordLen = $maxLen;
    $lettersZvezd = [];//Массив звезд, который определяется в методе checkWordFishki

    if ($desk[$x + 1][$y][0] || $desk[$x - 1][$y][0]) return '';
    //Не анализируем горизонтальные примыкающие буквы
    $regexp = '';
    $lastLetter = '';
    $step = 1;
    while ($desk[$x][$y + $step][0]) {
        $lastLetter .= \Lang\Ru::$bukvy[($desk[$x][$y + $step][1] < 999 ? $desk[$x][$y + $step][1] : $desk[$x][$y + $step][1] - 999 - 1)][0];
        $maxWordLen++;
        $step++;
    }
    //Собрали буквы, к которым примыкаем слово сверху
    print '-' . $x . '-' . $y . '-' . $lastLetter . '-';
    $regexp = makeRegexp($fishki);
    $zapros = "select slovo from dict where (slovo REGEXP \"^[$regexp]{0,$maxLen}$lastLetter";
    if (($maxDownAfterLen = maxToDown($x, $y + mb_strlen($lastLetter, 'UTF-8') + 1, count($fishki), $desk)) || (($x == 7) && ($y == 7))) {
        $zapros .= "[$regexp]{0,$maxDownAfterLen}";
        $maxWordLen += $maxDownAfterLen;
    }

    if ($maxWordLen > 7)
        $maxWordLen = 7;//так работает индекс

    $zapros .= "$\") AND NOT deleted = 1 AND length<=$maxWordLen AND lng = 1 ORDER BY length ASC";
    print $zapros . 'SVERHU'; //sleep (5);

    if ($res = \Dadata\DB::queryArray($zapros)) {
        foreach ($res as $row)
            if (!isset($slovaPlayed[$row['slovo'] = mb_strtolower($row['slovo'], 'UTF-8')])
                &&
                checkWordFishki($fishki, $row['slovo'], $lastLetter, $lettersZvezd)) {
                $cells = $desk;
                $slovoNach = ($lastLetter === '' ? 0 : mb_strpos($row['slovo'], $lastLetter));
                $lastLetterLen = ($lastLetter === '' ? 0 : mb_strlen($lastLetter, 'UTF-8'));

                for ($k = 0; $k < $slovoNach; $k++) {
                    $cells[$x][$y - $k][0] = true;
                    $letter = mb_substr($row['slovo'], $slovoNach - 1 - $k, 1, 'UTF-8');
                    $cells[$x][$y - $k][1] = \Lang\Ru::getLetterCode($letter);

                    if (isset($lettersZvezd[$letter]) && count($lettersZvezd[$letter])) {
                        $cells[$x][$y - $k][2] = array_pop($lettersZvezd[$letter]);
                        //Указали на занятую звездочку
                        $cells[$x][$y - $k][1] += (999 + 1);
                        //Поставили звездочку
                    }
                }

                for ($k = $slovoNach + $lastLetterLen; $k < mb_strlen($row['slovo'], 'UTF-8'); $k++) {
                    $cells[$x][$y + $k - $slovoNach + 1][0] = true;
                    $letter = mb_substr($row['slovo'], $k, 1, 'UTF-8');
                    $cells[$x][$y + $k - $slovoNach + 1][1] = \Lang\Ru::getLetterCode($letter);

                    if (isset($lettersZvezd[$letter]) && count($lettersZvezd[$letter])) {
                        $cells[$x][$y + $k - $slovoNach + 1][2] = array_pop($lettersZvezd[$letter]);
                        //Указали на занятую звездочку
                        $cells[$x][$y + $k - $slovoNach + 1][1] += (999 + 1);
                        //Поставили звездочку
                    }
                }

                printr($cells);
                $desk = $cells;
                return true;
                //return ['word'=>$row['slovo'],'lastLettersNum'=>mb_strlen($lastLetter,'UTF-8')];
            }
    }

    return '';
}