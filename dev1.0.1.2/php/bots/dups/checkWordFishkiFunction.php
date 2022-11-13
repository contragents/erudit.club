<?php

function makeRegexp($fishki)
{
    $regexp = '';
    $numZvezd = 0;
    foreach ($fishki as $num => $code)
        if ($code < 999)
            $regexp .= \Lang\Ru::$bukvy[$code][0];
        else $regexp .= zvezdaRegexp(++$numZvezd);

    return $regexp;
}

function zvezdaRegexp($numZvezd)
{
    if ($numZvezd <= 1)
        return 'эюшщфъчй';
    else
        return 'аимое';

}

function checkWordFishki(&$fishki, &$word, $lastLetter, &$lettersZvezd = [])
{
    $fishki1 = $fishki;
    $word = mb_strtolower($word, 'UTF-8');
    $lettersLastLetter = mb_str_split($lastLetter, 1, 'UTF-8');
    $lettersWord = mb_str_split($word, 1, 'UTF-8');
    $lettersZvezd = [];
    print 'Слово:';
    print_r($lettersWord);
    print ' Буквы на поле:';
    print_r($lettersLastLetter);
    print ' Фишки:';
    print_r($fishki1);
    foreach ($lettersWord as $numLetter => $letter) {
        foreach ($lettersLastLetter as $num => $lastLetter)
            if ($letter == $lastLetter) {
                $lettersLastLetter[$num] = '';
                $letter = '';
            }

        if ($letter !== '')
            foreach ($fishki1 as $num => $fishka)
                if ($letter == \Lang\Ru::$bukvy[($fishka < 999 ? $fishka : 32)][0]) {
                    unset($fishki1[$num]);
                    $letter = '';
                }

        if ($letter !== '')
            foreach ($fishki1 as $num => $fishka)
                if ($fishka >= 999) {
                    $lettersZvezd[$letter] = $fishka;
                    unset($fishki1[$num]);
                    //Признак что буква со звездочкой
                    $letter = '';
                    break;
                }

        if ($letter !== '') {
            print 'Лишняя буква: ' . $letter;
            print 'Слово:';
            print_r($lettersWord);
            print ' Буквы на поле:';
            print_r($lettersLastLetter);
            print ' Фишки:';
            print_r($fishki1);
            return false;
        }
    }
    print 'Слово:';
    print_r($lettersWord);
    print ' Буквы на поле:';
    print_r($lettersLastLetter);
    print ' Фишки:';
    print_r($fishki1);
    //$word = implode('',$lettersWord);
    $fishki = $fishki1;
    return true;
}