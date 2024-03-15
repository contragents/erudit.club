<?php

return [
'turnTime' => [2 => 120, 3 => 90, 4 => 60],
'winScore' => 200,
'ratingGameWaitLimit' => 360,
'gameWaitLimit' => Cache::llen('erudit.bot_games') > 5
    ? 15 
    : (Cache::llen('erudit.bot_games') == 0
        ? 60 
        : 30),
'cacheTimeout' => 3000,
'ratingsCacheTimeout' => 200,
'turnDeltaTime' => 10,//Разрешенное превышение длительности хода
'activityTimeout' => 30,
'chisloFishek' => 7,
'advMessage' => '<a href="https://youtu.be/p0U2jDZg0zM" target="_blank" title="Новое видео на канале Эрудит.CLUB!">Видео -  Как работает система начисления очков рейтинга</a>',
'botNames' => [0 => 'Васёк', 1 => 'Малый', 2 => 'Томмиган', 3 => 'Петровна', 4 => 'Русофил', 5 => 'Вахмур', 6 => 'Иваныч', 7 => 'Бес слов', 8 => 'RealMan', 9=> 'Мятежник',
10 => 'Босой',
11 => 'Машков',
12 => 'Ершов Н.',
13 => 'Борис Танк',
14 => 'Наталья Орлова',
15 => 'Вяземский П.С.',
16 => 'Одутловатый',
17 => 'Нагибатор',
18 => 'Неудержимый',
19 => 'Чоткий',
20 => 'Бройлер',
21 => 'Боди+',
22 => 'Няшечкина',
23 => 'Скудоумов Игорь',
24 => 'Валежников',
25 => 'Кар-Фу',
26 => 'Бигль',
27 => 'Трус-Ы',
28 => 'Честных',
29 => 'Павел Бродских',
30 => 'Пабгер',
31 => 'Ютуберша',
32 => 'хз',
33 => 'MagicLover',
34 => 'Победювсехна',
35 => 'Стручок',
36 => 'ТОПчик',
37 => '33й',
38 => 'Кыся',
39 => 'Дима Москва',
40 => 'Жбан-Жан',
41 => 'Спросименякак',
42 => 'Аленяка',
43 => 'Аленеводка',
44 => 'ЗК7568731',
45 => 'О.В. Кучкуйло',
46 => 'Чужой']
];