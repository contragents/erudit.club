<?php

include_once(__DIR__ . '/../autoload.php');
$isFirstRun = !DictModel::find()
    ->where([['field_name' => DictModel::WORD_FIELD, 'condition' => 'IS NOT', 'value' => 'NULL', 'raw' => true]])
    ->one();

try {
    while (($each = DictModel::find()
        ->where(
            $isFirstRun
                ? []
                : [
                [
                    'field_name' => DictModel::WORD_SCORE_FIELD,
                    'condition' => 'IS',
                    'value' => 'NULL',
                    'raw' => true
                ]
            ]
        )
        ->order(DictModel::ID_FIELD)
        ->each())->current()) {
        $isFirstRun = false;

        foreach (
            $each as $dictModel
        ) {
            print PHP_EOL . $dictModel->_slovo . ($dictModel->_word_score ?? 'NULL') . PHP_EOL;
            $langClass = $dictModel->_lng === 1
                ? Ru::class
                : Eng::class;

            $letterScore = 0;

            foreach (mb_str_split($dictModel->_slovo) as $letter) {
                $letterScore += $langClass::getLetterScore($letter);
                print "$letter:$letterScore"; // Выводит нарастающим итогом
            }

            $dictModel->_word_score = $letterScore;
            print $dictModel->save() ? 'SAVED' : 'FAILED';
        }
    }
} catch (Exception $e) {
    echo $e->getMessage();
}