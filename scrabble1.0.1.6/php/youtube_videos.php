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
        'Учим <strong>Английские</strong> короткие слова с буквой <strong>Z</strong>:<br /><br />  
            <strong>
            zen zip zoo<br />
            buzz czar daze fizz gaze haze jazz lazy maze
            nazi quiz size zeal zest zinc zone
            </strong>',

        'Учим <strong>Английские</strong> короткие слова с буквой <strong>Q</strong>:<br /><br /> 
            <strong>
            hq iq qc <br />
            faq <br />
            quay quid quip quit quiz <br />
            equal pique quack quail quake qualm quart 
            queen query quest queue quiet quilt quirk 
            quota quote squad squat squid
            </strong>
            <br /><br /> 
            Слово может являться аббревиатурой',

        'Учим <strong>Английские</strong> короткие слова с буквой <strong>J</strong>:<br /><br /> 
            <strong>
            dj jp <br />
            jab jam jar jaw jet jew jig job jog joy jug <br />
            hajj jack jade jail jazz jeep jeer jerk jest jibe jinx join joke jolt jpeg judo july jump june junk jury
            </strong>
            <br /><br /> 
            Слово может являться аббревиатурой',

        'Учим Английские <strong>аббревиатуры</strong>:<br /><br /> 
            <strong>
            ad cd fa fm hq iq jp ma mp pt qc tb tv uk wc<br />
            bbc bse ceo diy dna efl elt gmt iou ipa itv
            mba mep mpv msc mtv pda pin pow ufo usa vat
            www xml<br />
            call corp gcse mrsa nato sars sgml
            tefl<br />
            tesol
            </strong>',

        'Учим Английские слова <strong>из 2-х букв</strong>:<br /><br /> 
            <strong>
            ba cv dj do er ex gi go gp if it iv lp mr ms ox pc pe pr
            </strong>
            <br /><br /> 
            Слово может являться аббревиатурой, не обозначенной в словаре',

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
