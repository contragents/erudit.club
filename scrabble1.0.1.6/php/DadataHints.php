<?php

namespace Dadata;

use \Cache;
use AchievesModel;
use Erudit\Game;
use PlayerModel;
use T;

class Hints
{
    public const TYPE_WORDS_QUERY = 'words';

    private static $p;
    private static $gameState;
    private static $User;

    const HINT_DAILY_SHOW = 2;
    const HINT_CACHE_TTL = 12 * 60 * 60;
    const HINT_USER_CACHE_KEY = "erudit.hint_";
    const LAMP_IMG_URL = "img/idea.png";
    const YANDEX_RATING_URL = "https://yandex.ru/ugcpub/object-digest?app_id=yandex-games&otype=Soft&object=%2Fontoid%2Fygs393661&show_rating=1&view=games";

    const TG_GROUP_LINK = [
        'mobile' => 'https://t.me/eruditclub',
        'desktop' => 'https://web.telegram.org/#/im?p=@eruditclub'
    ];

    public static $VIDEOS;
    public static $EXT_ASSETS;

    const SVG_IMAGES = [
        'share' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M17.25 9.25C16.2471 9.25 15.3428 8.82822 14.7047 8.15237L9.58284 10.9282C9.69138 11.266 9.75 11.6261 9.75 12C9.75 12.3754 9.6909 12.737 9.5815 13.076L14.7035 15.8489C15.3417 15.1723 16.2465 14.75 17.25 14.75C19.183 14.75 20.75 16.317 20.75 18.25C20.75 20.183 19.183 21.75 17.25 21.75C15.317 21.75 13.75 20.183 13.75 18.25C13.75 18.0405 13.7684 17.8354 13.8037 17.636L8.43855 14.7315C7.83919 15.2123 7.07819 15.5 6.25 15.5C4.317 15.5 2.75 13.933 2.75 12C2.75 10.067 4.317 8.5 6.25 8.5C7.07991 8.5 7.84235 8.78885 8.44228 9.27149L13.804 6.36568C13.7685 6.1658 13.75 5.96006 13.75 5.75C13.75 3.817 15.317 2.25 17.25 2.25C19.183 2.25 20.75 3.817 20.75 5.75C20.75 7.683 19.183 9.25 17.25 9.25ZM18.75 5.75C18.75 4.92157 18.0784 4.25 17.25 4.25C16.4216 4.25 15.75 4.92157 15.75 5.75C15.75 6.57843 16.4216 7.25 17.25 7.25C18.0784 7.25 18.75 6.57843 18.75 5.75ZM17.25 19.75C18.0784 19.75 18.75 19.0784 18.75 18.25C18.75 17.4216 18.0784 16.75 17.25 16.75C16.4216 16.75 15.75 17.4216 15.75 18.25C15.75 19.0784 16.4216 19.75 17.25 19.75ZM7.75 12C7.75 11.1716 7.07843 10.5 6.25 10.5C5.42157 10.5 4.75 11.1716 4.75 12C4.75 12.8284 5.42157 13.5 6.25 13.5C7.07843 13.5 7.75 12.8284 7.75 12Z" fill="black"></path>
                        </svg>'
    ];

    const EXCEPTIONS = [
        'Android' => [//Подсказки, зависящие от приложения Андроид
            'share' => 'isAndroidApp',
            '<strong>Внимание!</strong><br /> Вышло обновление Игры. Для применения изменений, пожалуйста, обновите кеш приложения:<br />Нажать шестерёнку справа вверху<br />Выбрать пункт Приложения<br />В списке приложений найти Эрудит, нажать на него<br />Выбрать пункт меню Память<br />Нажать Очистить кэш справа внизу. Только кэш, НЕ данные' => 'IsNotAndroidApp',
            'Оставьте Ваш отзыв о приложении - мы ценим мнение каждого игрока и постоянно улучшаем Игру - <strong><a href="https://play.google.com/store/apps/details?id=club.erudite.app">Оценить</a></strong>' => 'IsNotAndroidApp',
            'video' => 'isAndroidApp',
            '<strong>Внимание!</strong><br /> Вышло обновление Игры. Для применения изменений, пожалуйста, обновите кеш браузера - <strong>Shift&nbsp;F5</strong>' => 'isAndroidApp',
            '<strong>Внимание!</strong><br /> Теперь можно в Личном Кабинете <strong>загрузить свой Аватар</strong> на наш сервер. Для применения изменений, пожалуйста, обновите кеш браузера - <strong>Shift&nbsp;F5</strong>' => 'isAndroidApp',
        ],
        Game::MY_TURN_STATUS => [//Подсказки для myTurn
            'share' => 'isMyTurn',
            //не просить поделиться во время хода игрока
            'video' => 'isMyTurn',
            //не показывать видео во время хода игрока
            'Оставьте Ваш отзыв о приложении - мы ценим мнение каждого игрока и постоянно улучшаем Игру - <strong><a href="https://play.google.com/store/apps/details?id=club.erudite.app">Оценить</a></strong>' => 'isMyTurn',
            '<strong>Внимание!</strong><br /> Вышло обновление Игры. Для применения изменений, пожалуйста, обновите кеш браузера - <strong>Shift&nbsp;F5</strong>' => 'isMyTurn',
        ],
        'Yandex' => [//подсказки, зависящие от Яндекса
            'Ссылка на наш <a target="_blank" href="https://www.youtube.com/channel/UCipptDPm5oRX_VCo5TaTHaQ">Youtube-канал</a> - откроется в новом окне' => 'isYandexApp',
            'video' => 'isYandexApp',
        ]
    ];

    const HINTS = [
        1 => [
            '<strong>Внимание!</strong><br /> Теперь можно в Личном Кабинете <strong>загрузить свой Аватар</strong> на наш сервер. Для применения изменений, пожалуйста, обновите кеш браузера - <strong>Shift&nbsp;F5</strong>',
            '<strong>Внимание!</strong><br /> Вышло обновление Игры. Для применения изменений, пожалуйста, обновите кеш приложения:<br />Нажать шестерёнку справа вверху<br />Выбрать пункт Приложения<br />В списке приложений найти Эрудит, нажать на него<br />Выбрать пункт меню Память<br />Нажать Очистить кэш справа внизу. Только кэш, НЕ данные',
            '<strong>Внимание!</strong><br /> Вышло обновление Игры. Для применения изменений, пожалуйста, обновите кеш приложения:<br />Нажать шестерёнку справа вверху<br />Выбрать пункт Приложения<br />В списке приложений найти Эрудит, нажать на него<br />Выбрать пункт меню Память<br />Нажать Очистить кэш справа внизу. Только кэш, НЕ данные',
            '<strong>Внимание!</strong><br /> Вышло обновление Игры. Для применения изменений, пожалуйста, обновите кеш приложения:<br />Нажать шестерёнку справа вверху<br />Выбрать пункт Приложения<br />В списке приложений найти Эрудит, нажать на него<br />Выбрать пункт меню Память<br />Нажать Очистить кэш справа внизу. Только кэш, НЕ данные',
            '<strong>Внимание!</strong><br /> Вышло обновление Игры. Для применения изменений, пожалуйста, обновите кеш браузера - <strong>Shift&nbsp;F5</strong>',
            'Появляющийся значок <img height="24px" src="img/no-network-logo.png" /> означает, что Вы находитесь в зоне <strong>слабого приема Интернета</strong>. Сервер отвечает с задержкой',
            'При выборе игры <strong>на Английском инструкция изменится</strong> - ознакомьтесь с ней в процессе игры',
            'При выборе игры <strong>на Английском</strong> подбор по рейтингу ВРЕМЕННО не учитывается',
            'В режиме <strong>Английского</strong> все <strong>рейтинги</strong> и <strong>достижения</strong> учитываются как обычно',
            'Рекомендуем в <strong>Английской</strong> версии выбирать время <strong>на ход 2 минуты</strong>',
            'В <strong>Английскую</strong> версию включены популярные <strong>аббревиатуры</strong> - ознакомьтесь со списком в новой инструкции',
            'support',
            'video',
            'Сохраните КЛЮЧ от учетной записи - доступен в ЛИЧНОМ КАБИНЕТЕ',
            'Ссылка на наш <a target="_blank" href="https://www.youtube.com/channel/UCipptDPm5oRX_VCo5TaTHaQ">Youtube-канал</a> - откроется в новом окне',
            'wordsRuHint',
            'wordsEnHint',
        ],
        1800 => [
            '<strong>Внимание!</strong><br /> Теперь можно в Личном Кабинете <strong>загрузить свой Аватар</strong> на наш сервер. Для применения изменений, пожалуйста, обновите кеш браузера - <strong>Shift&nbsp;F5</strong>',
            '<strong>Внимание!</strong><br /> Вышло обновление Игры. Для применения изменений, пожалуйста, обновите кеш приложения:<br />Нажать шестерёнку справа вверху<br />Выбрать пункт Приложения<br />В списке приложений найти Эрудит, нажать на него<br />Выбрать пункт меню Память<br />Нажать Очистить кэш справа внизу. Только кэш, НЕ данные',
            '<strong>Внимание!</strong><br /> Вышло обновление Игры. Для применения изменений, пожалуйста, обновите кеш браузера - <strong>Shift&nbsp;F5</strong>',
            'Зеленое поле <span style="background-color: chartreuse;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span> увеличивает очки за БУКВУ в 2 раза',
            'Желтое поле <span style="background-color: #fd0;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span> увеличивает очки за БУКВУ в 3 раза',
            'Синее поле <span style="background-color: blue;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span> увеличивает очки за СЛОВО в 2 раза',
            'Красное поле <span style="background-color: firebrick;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span> увеличивает очки за СЛОВО в 3 раза',
            '<strong>Первый ход</strong> играем через <strong>центральную</strong> клетку поля',
            'Используйте меню ЛОГ, чтобы посмотреть истоию ходов в игре',
            'Кнопка ПРОВЕРИТЬ позволяет убедиться в правильности собранной комбинации',
            'Вы можете <strong>забрать звездочку с поля</strong>, если у вас есть соответствующая буква <span style="white-space: nowrap">
            <strong>А<->А*</strong>
            </span> Просто перетащите свою букву на букву со звездочкой на поле',
            '<strong>Ъ</strong> труднее всего пристроить на поле. Совет - меняйте',
            'ИНСТРУКЦИЮ лучше изучить ДО начала игры',
            'В наш Эрудит можно играть как со смартфона, так и с ПК',
            //'<strong>Подбор слов</strong> по словарю Эрудита доступен в <a target="_blank" href="https://t.me/erudit_club_bot">Телеграм-боте</a>',
        ],
        1900 => [
            //'<strong>Подбор слов</strong> по словарю Эрудита доступен в <a target="_blank" href="https://t.me/erudit_club_bot">Телеграм-боте</a>',
            '<strong>Внимание!</strong><br /> Теперь можно в Личном Кабинете <strong>загрузить свой Аватар</strong> на наш сервер. Для применения изменений, пожалуйста, обновите кеш браузера - <strong>Shift&nbsp;F5</strong>',
            '<strong>Внимание!</strong><br /> Вышло обновление Игры. Для применения изменений, пожалуйста, обновите кеш приложения:<br />Нажать шестерёнку справа вверху<br />Выбрать пункт Приложения<br />В списке приложений найти Эрудит, нажать на него<br />Выбрать пункт меню Память<br />Нажать Очистить кэш справа внизу. Только кэш, НЕ данные',
            '<strong>Внимание!</strong><br /> Вышло обновление Игры. Для применения изменений, пожалуйста, обновите кеш приложения:<br />Нажать шестерёнку справа вверху<br />Выбрать пункт Приложения<br />В списке приложений найти Эрудит, нажать на него<br />Выбрать пункт меню Память<br />Нажать Очистить кэш справа внизу. Только кэш, НЕ данные',
            '<strong>Внимание!</strong><br /> Вышло обновление Игры. Для применения изменений, пожалуйста, обновите кеш приложения:<br />Нажать шестерёнку справа вверху<br />Выбрать пункт Приложения<br />В списке приложений найти Эрудит, нажать на него<br />Выбрать пункт меню Память<br />Нажать Очистить кэш справа внизу. Только кэш, НЕ данные',
            '<strong>Внимание!</strong><br /> Вышло обновление Игры. Для применения изменений, пожалуйста, обновите кеш браузера - <strong>Shift&nbsp;F5</strong>',
            'Оцените Игру по <a href="' . self::YANDEX_RATING_URL . '" target="_blank">ссылке</a> - откроется в новом окне',
            'share',
            'Буква <strong>В</strong> - самая бесполезная. Стоит 1 очко. С ней можно составить только одно слово из 2-х букв - <strong>ВА</strong>',
            'Буква <strong>Ч</strong> - самая неудобная. Стоит 5 очков. С ней нельзя составить слово из 2-х букв',
            'При замене букв нет никакого смысла менять звездочку - это и так самая лучшая фишка',
            'При замене фишек все буквы УЖЕ отмечены на обмен (кроме <strong>*</strong>)',
            'В среднем, за большое количество коротких слов можно получить больше очков, чем за длинные слова',
            'Слово, составленное через <strong>две синие</strong> клетки, получает <strong>в 4 раза</strong> больше очков',
            'Выиграв у сильного соперника, вы получите больше очков рейтинга, чем потеряли бы, проиграв ему',
            'Ценные буквы - <strong>Ш, Щ, Ф, Ю, Э</strong> - старайтесь ставить на цветные клетки',
            'Используйте меню <strong>ЧАТ для общения</strong> с противниками',
            'Ссылка на <strong>связь с поддержкой</strong> доступна в меню ЧАТ',
            'Нажимая на кнопку НОВАЯ ИГРА раньше ее окончания, вы признаете поражение',
            'Кнопка СТЕРЕТЬ возвращает все передвинутые буквы на исходную позицию',
            'Пользуйтесь кнопкой ПРОВЕРИТЬ всегда <strong>перед отправкой</strong> хода',
            'Меняйте буквы, если не можете составить ни одного слова',
            'Если у вас на руках <strong>одни гласные</strong> или <strong>только согласные</strong> буквы - меняйте, если их некуда пристроить',
            'Возможность выбрать соперника по рейтингу доступна для игроков с рейтингом от 1800',
            'Игра до 300 очков занимает дольше времени, но этот режим предпочитают сильные игроки',
            'По результатам игры <strong>вы можете ПРИГЛАСИТЬ</strong> соперника на реванш, но <strong>он не обязан ПРИНИМАТЬ</strong> приглашение',
            'Старайтесь запомнить как можно больше слов из 2-х букв',
            'Очки за слово считаются по сумме всех букв с учетом <span style="background-color: chartreuse;">букваХ2</span>, <span style="background-color: #fd0;">букваХ3</span>
            , а затем к полученному числу применяются модификаторы СИНИХ <span style="background-color: blue; color: white;">словоХ2</span> и КРАСНЫХ <span style="background-color: firebrick; color: white;">словоХ3</span> клеток',
            'Оставьте Ваш отзыв о приложении - мы ценим мнение каждого игрока и постоянно улучшаем Игру - <strong><a href="https://play.google.com/store/apps/details?id=club.erudite.app">Оценить</a></strong>',
            'recordsHint',
        ],
        2100 => [
            //'<strong>Подбор слов</strong> по словарю Эрудита доступен в <a target="_blank" href="https://t.me/erudit_club_bot">Телеграм-боте</a>',
            '<strong>Внимание!</strong><br /> Теперь можно в Личном Кабинете <strong>загрузить свой Аватар</strong> на наш сервер. Для применения изменений, пожалуйста, обновите кеш браузера - <strong>Shift&nbsp;F5</strong>',
            '<strong>Внимание!</strong><br /> Вышло обновление Игры. Для применения изменений, пожалуйста, обновите кеш приложения:<br />Нажать шестерёнку справа вверху<br />Выбрать пункт Приложения<br />В списке приложений найти Эрудит, нажать на него<br />Выбрать пункт меню Память<br />Нажать Очистить кэш справа внизу. Только кэш, НЕ данные',
            '<strong>Внимание!</strong><br /> Вышло обновление Игры. Для применения изменений, пожалуйста, обновите кеш приложения:<br />Нажать шестерёнку справа вверху<br />Выбрать пункт Приложения<br />В списке приложений найти Эрудит, нажать на него<br />Выбрать пункт меню Память<br />Нажать Очистить кэш справа внизу. Только кэш, НЕ данные',
            '<strong>Внимание!</strong><br /> Вышло обновление Игры. Для применения изменений, пожалуйста, обновите кеш приложения:<br />Нажать шестерёнку справа вверху<br />Выбрать пункт Приложения<br />В списке приложений найти Эрудит, нажать на него<br />Выбрать пункт меню Память<br />Нажать Очистить кэш справа внизу. Только кэш, НЕ данные',
            '<strong>Внимание!</strong><br /> Вышло обновление Игры. Для применения изменений, пожалуйста, обновите кеш браузера - <strong>Shift&nbsp;F5</strong>',
            'Оцените Игру по <a href="' . self::YANDEX_RATING_URL . '" target="_blank">ссылке</a> - откроется в новом окне',
            'share',
            'При замене фишек, возможно, стоит сохранить ценные буквы - <strong>Ф,Ш,Щ,Ю,Э</strong>',
            'Заменив <strong>Ъ</strong> как можно скорее, Вы, возможно, подкинете его противнику',
            'Слово, составленное через две красные клетки, получает в 9 раз больше очков',
            'Старайтесь <strong>не оставлять</strong> открыми буквы <strong>А, И, У, С</strong> рядом с <strong>цветными</strong> клетками',
            'В первую очередь старайтесь избавиться от неудобных букв - <strong>Ч, Ц, Ы, В</strong>',
            'Ценные буквы - <strong>Ш, Щ, Ф, Ю, Э</strong> - старайтесь использовать 2 раза за ход',
            'Задайте Ник в ЛИЧНОМ КАБИНЕТЕ',
            'Аватар можно задать в ЛИЧНОМ КАБИНЕТЕ. Пока только по ссылке (URL)',
            'Старайтесь запомнить как можно больше слов из 3-х букв',
            'Оцените Игру по <a href="' . self::YANDEX_RATING_URL . '" target="_blank">ссылке</a> - откроется в новом окне',
            'recordsHint',
        ],
        2300 => [
            //'<strong>Подбор слов</strong> по словарю Эрудита доступен в <a target="_blank" href="https://t.me/erudit_club_bot">Телеграм-боте</a>',
            '<strong>Внимание!</strong><br /> Теперь можно в Личном Кабинете <strong>загрузить свой Аватар</strong> на наш сервер. Для применения изменений, пожалуйста, обновите кеш браузера - <strong>Shift&nbsp;F5</strong>',
            '<strong>Внимание!</strong><br /> Вышло обновление Игры. Для применения изменений, пожалуйста, обновите кеш приложения:<br />Нажать шестерёнку справа вверху<br />Выбрать пункт Приложения<br />В списке приложений найти Эрудит, нажать на него<br />Выбрать пункт меню Память<br />Нажать Очистить кэш справа внизу. Только кэш, НЕ данные',
            '<strong>Внимание!</strong><br /> Вышло обновление Игры. Для применения изменений, пожалуйста, обновите кеш приложения:<br />Нажать шестерёнку справа вверху<br />Выбрать пункт Приложения<br />В списке приложений найти Эрудит, нажать на него<br />Выбрать пункт меню Память<br />Нажать Очистить кэш справа внизу. Только кэш, НЕ данные',
            '<strong>Внимание!</strong><br /> Вышло обновление Игры. Для применения изменений, пожалуйста, обновите кеш приложения:<br />Нажать шестерёнку справа вверху<br />Выбрать пункт Приложения<br />В списке приложений найти Эрудит, нажать на него<br />Выбрать пункт меню Память<br />Нажать Очистить кэш справа внизу. Только кэш, НЕ данные',
            '<strong>Внимание!</strong><br /> Вышло обновление Игры. Для применения изменений, пожалуйста, обновите кеш браузера - <strong>Shift&nbsp;F5</strong>',
            'Оцените Игру по <a href="' . self::YANDEX_RATING_URL . '" target="_blank">ссылке</a> - откроется в новом окне',
            'share',
            'Иногда выгоднее усложнить позицию сопернику, чем набрать много очков',
            'Если Вам не хватает очков, чтобы выиграть партию, а у противника почти выигрыш - попытайтесь максимально испортить ему позицию',
            'Под конец игры стоимость хода в среднем возрастает',
            'Не держите долго <strong>Ъ</strong> - меняйте сразу, если вероятность собрать с ним слово в следующем ходу мала',
            'Сохраните КЛЮЧ от учетной записи - доступен в ЛИЧНОМ КАБИНЕТЕ',
            'Оцените Игру по <a href="' . self::YANDEX_RATING_URL . '" target="_blank">ссылке</a> - откроется в новом окне',
            'wordsEnHint',
            'Оставьте Ваш отзыв о приложении - мы ценим мнение каждого игрока и постоянно улучшаем Игру - <strong><a href="https://play.google.com/store/apps/details?id=club.erudite.app">Оценить</a></strong>',
            'recordsHint',
            'recordsHint',
        ],
    ];

    private static function checkCache($User, &$gameStatus, $hint)
    {
        self::$p = Cache::getInstance();
        $showsCount = self::$p->redis->get(self::HINT_USER_CACHE_KEY . $User . $hint);

        if (!$showsCount || $showsCount < self::HINT_DAILY_SHOW) {
            self::$p->redis->setex(
                self::HINT_USER_CACHE_KEY . $User . $hint,
                self::HINT_CACHE_TTL,
                $showsCount ? ++$showsCount : 1
            );
            $gameStatus['users'][$gameStatus[$User]]['hints'][$gameStatus['turnNumber']] = $hint;

            return false;
        } else {
            return true;
        }
    }

    private static function randKey($keyCount)
    {
        return rand(0, 100000) % $keyCount;
    }

    private static function randExValue(array $exRates)
    {
        $allRates = array_keys(self::HINTS);
        $reducedRates = array_diff($allRates, $exRates);
        return self::randValue(self::HINTS[array_values($reducedRates)[rand(0, count($reducedRates) - 1)]]);
    }

    private static function randValue(array $arr)
    {
        $keyCount = count($arr);
        $value = $arr[rand(0, 100000) % $keyCount];

        foreach (self::EXCEPTIONS as $ex) {
            if (isset($ex[$value]) && self::{$ex[$value]}()) {
                return '';
            }
        }

        if (is_callable("self::$value")) {
            self::$EXT_ASSETS = include 'youtube_videos.php';
            return self::$value();
        }

        switch ($value) {
            case 'share':
                return '';//self::shareHint();
            case 'support':
                return self::supportHint();
            case 'video':
                self::$EXT_ASSETS = include 'youtube_videos.php';
                return self::videoHint();
            default:
                return $value;
        }
    }

    public static function getHint($User, &$gameStatus, $rating = false)
    {
        if (\Game::$gameName !== \Game::ERUDIT) {
            return '';
        }

        self::$gameState = $gameStatus;
        self::$User = $User;

        $doCheckCache = true;
        if (isset($gameStatus['users'][$gameStatus[$User]]['hints'][$gameStatus['turnNumber']])) {
            $hint = $gameStatus['users'][$gameStatus[$User]]['hints'][$gameStatus['turnNumber']];
            $doCheckCache = false;
        } elseif (!$rating) {
            $category = array_keys(self::HINTS)[self::randKey(count(self::HINTS))];
            $hint = self::randValue(self::HINTS[$category]);
        } else {
            foreach (self::HINTS as $rate => $rateHints) {
                if (($rating < $rate) && ($rate > 1)) {
                    $hint = self::randValue($rateHints);
                    break;
                }
            }
        }

        $hint = $hint ?? self::randValue($rateHints);

        if ($doCheckCache) {
            if (self::checkCache($User, $gameStatus, $hint)) {
                $hint = self::randValue(self::HINTS[1]);//2й прогон
                if (self::checkCache($User, $gameStatus, $hint)) {
                    $hint = self::randExValue([1, $rate]);//3й прогон
                    if (self::checkCache($User, $gameStatus, $hint)) {
                        return '';
                    }
                }
            }
        }

        if ($hint === '') {
            return '';
        } else {
            return "
<br />
<br />
<p style=\"font-size: 10pt;\">
<img style='background-color:#ffffff;margin-top: -3px;' src='"
                . self::LAMP_IMG_URL
                . "' height='14px'/>
&nbsp;$hint
</p>
";
        }
    }

    private static function supportHint()
    {
        if(self::isYandexApp()) {
            return '';
        }

        $result = 'Получить <strong>поддержку</strong> и оставить <strong>сообщение об ошибках</strong> Вы можете в нашей '
            . self::link(
                self::isMobileDevice()
                    ? self::TG_GROUP_LINK['mobile']
                    : self::TG_GROUP_LINK['desktop'],
                'группе'
            )
            . ' Telegram. Советуем подписаться заранее';

        return $result;
    }

    private static function link($url, $anchor)
    {
        return "<a href=\"$url\" target=\"_blank\">$anchor</a>";
    }

    public static function getWordHint(string $word): array
    {
        try {
            $words = json_decode(file_get_contents(self::getBotWordRequestURL() . $word . '&game=' . Game::$gameName), true);
        } catch(\Throwable $e) {
            $words = ['Ошибка сервера'];
        }

        if (!is_array($words)) {
            $words = [];
        }

        if(count($words) == 5) {
            $words[] = T::S('Only 5 words are shown in random order')
                . '<br>'
                . T::S('connect_bot');
        }

        $res = ['message' => implode('<br>', $words)];

        return $res;
    }

    private static function recordsHint()
    {
        $record = Prizes::getRandomRecord();
        $recorderCommonID = Players::getCommonIDByCookie($record['cookie']);
        $recorderPlayerID = Players::getUserIDByCookie($record['cookie']);
        $recordPlayerName = PlayerModel::getPlayerName( // CLUB-440 Players::getPlayerName(
            $recorderPlayerID
                ? ['ID' => $record['cookie'], 'common_id' => $recorderCommonID,]
                : ['ID' => $record['cookie'], 'common_id' => $recorderCommonID, 'userID' => $recorderPlayerID]
        );
        $recordPlayerAvatarUrl = PlayerModel::getAvatarUrl($recorderCommonID);//Players::getAvatarUrl();


        return self::renderRecordsView(
            array_merge(
                $record,
                [
                    'CommonID' => $recorderCommonID,
                    'PlayerID' => $recorderPlayerID,
                    'PlayerName' => $recordPlayerName,
                    'AvatarUrl' => $recordPlayerAvatarUrl,
                ]
            )
        );
    }


    private static function renderRecordsView(array $recordData)
    {
        return
            "
<strong><span style=\"color: #00ff00; \">" . T::S('Congratulations to Player') . "</span> {$recordData['PlayerName']}&nbsp;!!!</strong>&nbsp;
<img style=\"border-radius: 5px 5px 5px 5px; margin-left:20px;padding-top:0;\" alt=\"😰\" src=\"{$recordData['AvatarUrl']}\" height=\"75px\" max-width=\"100px\" />
<br />
Новое достижение - <strong>" . AchievesModel::PRIZE_TITLES[$recordData['type']] . "</strong> <br />"
            . ($recordData['word']
                ? "Составленное слово: <strong>{$recordData['word']}</strong> <br />"
                : ''
            )
            . "Результат: <strong>{$recordData['value']} "
            . (strpos($recordData['type'], '_len')
                ? '&nbsp;букв'
                : '')
            . "</strong> <br />"

            . "
Получен жетон <img style=\"
						cursor: pointer; 
						margin-left: 0px; padding: 0;
						margin-top: -10px;
						z-index: 50;
				\" 
				title=\"Кликните для увеличения изображения\" 
				id=\"{$recordData['type']}\" 
				onclick=\"showFullImage('{$recordData['type']}', 500, 100);\" 
				src=\"{$recordData['link']}\" width=\"100px\" /> <br />
Дата установления достижения: <strong>" . date("d.m.Y H:i", $recordData['record_date']) . "</strong>";
    }

    private static function wordsRuHint()
    {
        return self::wordsEnHint('wordsRu');
    }

    private static function wordsEnHint($key = 'wordsEn')
    {
        $engWords = self::$EXT_ASSETS[$key];
        return $engWords[rand(0, count($engWords) - 1)];
    }

    private static function shareHint()
    {
        return '';
    }

    public
    static function videoHint()
    {
        return '';
    }

    public
    static function isMobileDevice()
    {
        return preg_match(
            "/(android|avantgo|blackberry|bolt|boost|cricket|docomo
|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i"
            ,
            $_SERVER["HTTP_USER_AGENT"]
        );
    }

    public
    static function isDesktopDevice()
    {
        return !self::isMobileDevice();
    }

    public
    static function IsNotAndroidApp()
    {
        return !self::isAndroidApp();
    }

    public
    static function isAndroidApp()
    {
        if (isset($_COOKIE['DEVICE']) && $_COOKIE['DEVICE'] == 'Android') {
            return true;
        }

        if (isset($_COOKIE['PRODUCT']) && $_COOKIE['PRODUCT'] == 'RocketWeb') {
            return true;
        }

        if (strpos($_SERVER['HTTP_REFERER'] ?? '', 'app=1')) {
            return true;
        }

        return false;
    }

    public
    static function isVkApp()
    {
        if (isset($_SERVER['HTTP_REFERER']) && (strpos($_SERVER['HTTP_REFERER'], 'api.vk.com') !== false)) {
            return true;
        }

        return false;
    }

    public
    static function isYandexApp(): bool
    {
        return \BaseController::isYandexApp();
    }

    public
    static function isClubApp()
    {
        return !(self::isAndroidApp() || self::isVkApp() || self::isYandexApp());
    }

    public
    static function isMyTurn()
    {
        if (self::$gameState['users'][self::$gameState[self::$User]]['status'] == Game::MY_TURN_STATUS) {
            return true;
        }

        return false;
    }

    protected static function getBotWordRequestURL()
    {
        return \Config::$config['domain'] . '/bot/words?word=';
    }
}
