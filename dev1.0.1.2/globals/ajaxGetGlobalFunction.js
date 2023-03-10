//
async function fetchGlobal(script, param_name, param_data) {
    if (!requestToServerEnabled && script === STATUS_CHECKER_SCRIPT) {
        console.log('Request to server forbidden');
        return {message: "Ошибка связи с сервером. Пожалуйста, повторите", http_status: BAD_REQUEST, status: "error"};
    }

    requestToServerEnabled = false;
    requestToServerEnabledTimeout = setTimeout(function() {requestToServerEnabled = true;}, 500)

    if (pageActive != 'hidden') {
        requestSended = true;
        requestTimestamp = (new Date()).getTime();
    }

    if (useLocalStorage) {
        return await fetchGlobalYowser(script, param_name, param_data);
    } else {
        return await fetchGlobalNominal(script, param_name, param_data);
    }
}

async function fetchGlobalNominal(script, param_name, param_data) {
    const response = await fetch('//xn--d1aiwkc2d.club/<?=$dir?>/php/'
        + script
        + '?queryNumber=' + (queryNumber++)
        + '&lang=' + lang
        + (pageActive == 'hidden' ? '&page_hidden=true' : ''),
        {
            method: 'POST', // *GET, POST, PUT, DELETE, etc.
            mode: 'cors', // no-cors, *cors, same-origin
            cache: 'no-cache', // *default, no-cache, reload, force-cache, only-if-cached
            credentials: 'include', // include, *same-origin, omit
            headers: {
                //'Content-Type': 'application/json'
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            //redirect: 'follow', // manual, *follow, error
            //referrerPolicy: 'no-referrer', // no-referrer, *client
            body: (param_name != '' ? param_name + '=' + encodeURIComponent(JSON.stringify(param_data)) : param_data) //JSON.stringify(data) // body data type must match "Content-Type" header
        }
    );

    requestSended = false;

    if (response.status === BAD_REQUEST || response.status === PAGE_NOT_FOUND) {
        return {message: response.statusText, status: "error", http_status: response.status};
    }

    if (!response.ok) {
        console.log(`An error has occured: ${response.status}`);
        return {message: "Ошибка связи с сервером. Попробуйте еще раз...", status: "error"};
    }

    return await response.json(); // parses JSON response into native JavaScript objects
}

async function fetchGlobalYowser(script, param_name, param_data) {
    const response = await fetch('//xn--d1aiwkc2d.club/<?=$dir?>/php/yowser/index.php'
        + '?cooki='
        + localStorage.erudit_user_session_ID
        + '&script='
        + script
        + '&queryNumber='
        + (queryNumber++)
        + '&lang=' + lang
        + (pageActive == 'hidden' ? '&page_hidden=true' : ''),
        {
            method: 'POST',
            mode: 'cors',
            cache: 'no-cache',
            credentials: 'include',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: (param_name != ''
                ? param_name + '=' + encodeURIComponent(JSON.stringify(param_data))
                : param_data)
        }
    );

    requestSended = false;

    if (response.status === BAD_REQUEST || response.status === PAGE_NOT_FOUND) {
        return {message: response.statusText, status: "error", http_status: response.status};
    }

    if (!response.ok) {
        console.log(`An error has occured: ${response.status}`);
        return {message: "Ошибка связи с сервером. Попробуйте еще раз...", status: "error"};
    }

    return await response.json();
}