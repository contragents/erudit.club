//
async function openWindowGlobal(word){
    const response = await fetch(BASE_URL + '<?=$dir?>/php/word.php?ingame=yes&word='+word, {
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
}

