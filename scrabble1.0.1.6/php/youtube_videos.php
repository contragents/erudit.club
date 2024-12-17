<?php
return [
    'videos' => [
        '5zNC1vZVRGY',
        'IQh0-cBtxFk',
        'Xh2LhXAuEvg',
        'z9ka_uKP8PU',
        'PSfLU-pNFOU',
        'FrV_hsUp9Ns',
        'DpIwF7rJq34',
        'SwTFmrJBXJU',
        'tb8hj5OXxUA',
        'qdzgj15af_I',
        'cE1adELPLi8',
        '2aAKf-6OfGA',
        'rGqg5kwQh6g',
        'eaxShqlIiTs',
        'BAXkZp7tMdQ',
        'hSDnKIhUIso',
        'VDsZxvRZvts',
    ],
    'wordsEn' => [
    ],
    'wordsRu' => [
        'Учим короткие слова с буквой <strong>Ц</strong>:<br /> 
            <strong>
            ци цы юц<br />
            рцы цам цеж цез цек цеп цех цоу цуг цук цян
            </strong>',

        'Учим короткие слова с буквой <strong>Ч</strong>:<br /> 
            <strong>
            бич гач кач кеч кич коч луч меч мяч сыч чад чай 
            чал чан час чат чек чёт чех чиж чий чин чип чир 
            чих чон чоп чох чуб чум чур ючи
            </strong>',

        'Учим короткие слова с буквой <strong>Ы</strong>:<br /> 
            <strong>
            лы цы ыр<br />
            азы бык быр быт выя дым дых еры иды 
            кыр мыс мыт овы пыж пыл рцы рык рым 
            сын сыр сыч тыл тын узы улы юты ясы
            </strong>',

        'Учим короткие слова с буквой <strong>Щ</strong>:<br /> 
            <strong>
            ща щи<br /> 
            лещ щит щуп щур<br /> 
            борщ вещи вещь гуща клещ куща моща мощи мощь нощь 
            овощ пища плащ плющ пращ прыщ пуща роща свищ
            </strong>',
    ],
    'query' => ['letter'=>"SELECT lower(slovo) FROM `dict`
WHERE slovo like '%z%'
AND
length <= 4
AND
NOT deleted
ORDER BY length, lower(slovo);",
        'abbr' => "SELECT lower(slovo) FROM `dict`
WHERE
(
length <= 5
AND
(
    comment like '%bbreviation%'
    AND slovo NOT like '% %'
    AND slovo NOT like '%-%'
    AND slovo NOT like '%;%'
)
)
AND
NOT deleted
ORDER BY length, lower(slovo);",
        'two-lett' => "SELECT lower(slovo) FROM `dict`
WHERE
lng = 2
AND length <= 2
AND NOT comment like '%bbreviation%'
AND NOT deleted
ORDER BY length, lower(slovo);"]
];
