//
async function openWindowGlobal(word) {
    const response = await fetch(BASE_URL + WORD_SCRIPT + '?word=' + word, {
        method: 'GET',
        mode: 'cors', // no-cors, *cors, same-origin
        cache: 'no-cache', // *default, no-cache, reload, force-cache, only-if-cached
        credentials: 'include',
        headers: {
            //'Content-Type': 'application/json',
            'Content-Type': 'application/x-www-form-urlencoded',
        },
    });

    // Проверяем успешность запроса
    if (!response.ok) {
        throw new Error(`Ошибка запроса: ${response.status}`);
    }

    const returndata = await response.json();
    // Получаем JSON

    if ('result' in returndata) {
        let dialogWord = bootbox.alert({
            message: returndata.result,
            className: 'modal-settings modal-profile text-white',
            locale: lang.toLowerCase(),
        }).off("shown.bs.modal");
    }
}


