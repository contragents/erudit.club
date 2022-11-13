<?php
preg_match('/((release|yandex|dev)(\d\.\d\.\d\.\d))/',__DIR__,$matches);
$dir=$matches[1];
//Определяем каталог версии разработки
?>
<?php
if (stripos($_SERVER['HTTP_USER_AGENT'], 'yowser') === false)
{
?>
    async function fetchGlobal(script, param_name, param_data) {
        const response = await fetch('//xn--d1aiwkc2d.club/<?=$dir?>/php/'
            + script
            + '?queryNumber='+(queryNumber++)
            + '&lang='+lang
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
        return await response.json(); // parses JSON response into native JavaScript objects
    }
<?php
} else {
?>
    async function fetchGlobal(script, param_name, param_data)
    {
        const response = await fetch('//xn--d1aiwkc2d.club/<?=$dir?>/yowser/'
            + (localStorage.erudit_user_session_ID ? localStorage.erudit_user_session_ID : 'EMPTY_SESSION_ID')
            + '/'
            + script
            + '?queryNumber='
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
        return await response.json();
    }
<?php
}
