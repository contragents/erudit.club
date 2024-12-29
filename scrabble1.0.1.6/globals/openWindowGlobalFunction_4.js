//
async function openWindowGlobal(word){
    /*const response = await fetch(BASE_URL + '<?=$dir?>/php/word.php?ingame=yes&word='+word, {
    method: 'POST',
    mode: 'cors',
    cache: 'no-cache',
    credentials: 'include',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded',
    },
    body: '12=12'
  });

  return await response.text(); // parses JSON response into native JavaScript objects
  */

    fetchGlobal(WORD_SCRIPT, '', 'ingame=yes&word=' + word)
        .then((resp) => {
            if ('result' in resp) {
               let dialogWord = bootbox.alert({
                    message: resp.result,
                    className: 'modal-settings modal-profile text-white',
                    locale: lang === 'RU' ? 'ru' : 'en',
                }).off("shown.bs.modal");
            }
        });
}

