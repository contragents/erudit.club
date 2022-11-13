<?php
/**
 * В случае одинакового числа повторов символа результат непредсказуем,
 * например у ТОП1 символа число повторов равно ТОП2 символу и равно ТОП3 символу
 * пример - слово milklim - символы m,i,l имеют одинаковое число повторов
 */

// API version
if (isset($_GET['string'])) {
    print json_encode(['status' => 'success', 'result' => pred_max($_GET['string'])], JSON_UNESCAPED_UNICODE);
    exit();
}

$stroka = 'abccdceffgihhhhj';

function pred_max(string $stroka)
{
    $maxes = [
        1 => ['char' => false, 'repeats' => 0],
        2 => ['char' => false, 'repeats' => 0]
    ];

    $povtors = [];

    foreach (str_split($stroka) as $char) {
        $povtors[$char] = isset($povtors[$char]) ? ++$povtors[$char] : 0;

        // Сравниваем число повторов символа с 1-м местом
        if ($povtors[$char] > $maxes[1]['repeats']) {
            $maxes[2] = $maxes[1];
            $maxes[1] = ['char' => $char, 'repeats' => $povtors[$char]];
        } elseif ($povtors[$char] > $maxes[2]['repeats']) {
            // Сравниваем число повторов символа с 2-м местом
            $maxes[2] = ['char' => $char, 'repeats' => $povtors[$char]];
        }
    }

    return $maxes[2]['char'];
}

print pred_max($stroka);