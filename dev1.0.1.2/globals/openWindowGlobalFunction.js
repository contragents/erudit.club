//
async function openWindowGlobal(word){
    const response = await fetch('//xn--d1aiwkc2d.club/<?=$dir?>/php/word.php?ingame=yes&word='+word, {
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
    body: '12=12' //JSON.stringify(data) // body data type must match "Content-Type" header
  });

  return await response.text(); // parses JSON response into native JavaScript objects
}

