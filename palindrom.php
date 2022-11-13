<?php
/**
 * Палиндромом считаем "симметричную" строку с учетов симметричности всех спецсимволов,
 * например "23 09мм90 32" - палиндром
 * 'mil klim' - не палиндром
 */

// API version
if (isset($_GET['string'])) {
    if (palindrom($_GET['string'])) {
        print json_encode(['status' => 'success', 'result' => $_GET['string'] . ' - Палиндром'], JSON_UNESCAPED_UNICODE);
    } else {
        print json_encode(['status' => 'error', 'result' => $_GET['string'] . ' - Не палиндром'], JSON_UNESCAPED_UNICODE);
    }

    exit();
}

$stroka1 = 'milklim';
$stroka2 = 'otvet';

function palindrom($stroka)
{
    for ($i = 0; $i < strlen($stroka) / 2; $i++) {
        if ($stroka[$i] != $stroka[strlen($stroka) - 1 - $i]) {
            return false;
        }
    }

    return true;
}

print $stroka2 . palindrom($stroka2) ? ' - Палиндром' : ' - Не палиндром';
print $stroka1 . palindrom($stroka1) ? ' - Палиндром' : ' - Не палиндром';
