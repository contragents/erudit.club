//
function changeFishkiGlobal(fishkiRequest) {
    fetchGlobal(CHANGE_FISHKI_SCRIPT,'',fishkiRequest)
            .then((data) => {
                commonCallback(data);
            });
            
    return;
}