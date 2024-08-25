//
function bootBoxIsOpenedGlobal() {
    if (!canOpenDialog) {
        return true;
    }

    if (dialog) {
        if (dialog[0].clientHeight > 0) {
            return true;
        } else if ('ariaHidden' in dialog[0]) {
            if (dialog[0].ariaHidden !== "true") {
                return true;
            }
        }
    }

    if (dialogTurn) {
        if (dialogTurn[0].clientHeight > 0) {
            return true;
        } else if ('ariaHidden' in dialogTurn[0]) {
            if (dialogTurn[0].ariaHidden !== "true") {
                return true;
            }
        }
    }

    return false;
}