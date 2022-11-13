//
function changeFishkiGlobal(fishkiRequest) {
    fetchGlobal('change_fishki.php','',fishkiRequest)
            .then((data) => {
                commonCallback(data);
            });
            
    return;
}