//
/*
document.addEventListener('click', (event) => {
    if (event.target && event.target.closest(selectors.setNicknameBtn)) {
        event.preventDefault();
        const userId = document.querySelector(selectors.userIdInput).value;
        const value = document.querySelector(selectors.nicknameInput).value;
        savePlayerName(value, userId);
        return false;
    }
});

document.addEventListener('click', (event) => {
    if (event.target && event.target.closest(selectors.setProfileImageBtn)) {
        event.preventDefault();
        const userId = document.querySelector(selectors.userIdInput).value;
        const value = document.querySelector(selectors.profileImageInput).value;
        savePlayerAvatar(value, userId);
        return false;
    }
});
*/

// для копирования из input в буфер
function copyToClipboard(selector) {
    const element = document.querySelector(selector);
    element.select();
    element.setSelectionRange(0, 99999);
    document.execCommand('copy');
}

(function profileModal() {
    const selectors = {
        profileTabsId: 'profile-tabs',
        tabLink: '#profile-tabs a',
        tabContent: '.tab-content',
        tabContentWrap: '.tab-content-wrap',
        tabPane: '.tab-pane',
        copyBtn: '.js-btn-copy',
        setNicknameBtn: '.js-btn-set-nickname',
        setProfileImageBtn: '.js-btn-set-profile-image',
        nicknameInput: '#player_name',
        profileImageInput: '#player_avatar_file',
        userIdInput: '#user_id',
    };

    const setTabContentOffset = (tabsSelector) => {
        const targetId = document
            .querySelectorAll(`${selectors.tabLink}.active`)[0]
            .getAttribute('href');
        const tabContent = document.querySelector(targetId).closest(selectors.tabContent);
        const tabContentWrap = tabContent.closest(selectors.tabContentWrap);
        const tabPane = document.querySelector(targetId);
        tabContentWrap.style.height = tabContent.getBoundingClientRect().height + 'px';

        const index = [...tabContent.querySelectorAll(selectors.tabPane)].findIndex(
            (item) => {
                return item === tabPane;
            },
        );
        const width = tabContentWrap.getBoundingClientRect().width;

        const translateValue = index * -width;

        tabContent.style.cssText = `transform: translate(${translateValue}px, 0);`;
    };

    document.addEventListener('click', (event) => {
        if (event.target && event.target.closest(selectors.setNicknameBtn)) {
            event.preventDefault();
            const userId = document.querySelector(selectors.userIdInput).value;
            const value = document.querySelector(selectors.nicknameInput).value;
            savePlayerName(value, userId);
            return false;
        }
    });

    document.addEventListener('click', (event) => {
        if (event.target && event.target.closest(selectors.setProfileImageBtn)) {
            event.preventDefault();
            const userId = document.querySelector(selectors.userIdInput).value;
            const value = document.querySelector(selectors.profileImageInput).value;
            savePlayerAvatar(value, userId);
            return false;
        }
    });

    document.addEventListener('click', (event) => {
        if (event.target && event.target.closest(selectors.tabLink)) {
            event.preventDefault();
            document
                .querySelectorAll(selectors.tabLink)
                .forEach((item) => item.classList.remove('active'));
            event.target.classList.add('active');
            const targetId = event.target.getAttribute('href');

            setTabContentOffset(`#${selectors.profileTabsId}`);
        }
    });

    document.addEventListener('click', (event) => {
        if (event.target && event.target.closest(selectors.copyBtn)) {
            event.preventDefault();
            copyToClipboard(event.target.closest(selectors.copyBtn).getAttribute('href'));
        }
    });

    const onProfileModalLoaded = () => {
        const setWidthToPanes = () => {
            const tabPaneList = document.querySelectorAll(selectors.tabPane) || [];
            if (tabPaneList.length > 0) {
                const tabContentWrap = tabPaneList[0].closest(selectors.tabContentWrap);
                let width = tabContentWrap.getBoundingClientRect().width + 'px';

                for (let i = 0; i < tabPaneList.length; i++) {
                    tabPaneList[i].style.width = width;
                    const currentWidth = tabContentWrap.getBoundingClientRect().width + 'px';
                    if (currentWidth !== width) {
                        width = currentWidth;
                        i = 0;
                    }
                }
            }
        };

        setWidthToPanes();
        setTabContentOffset(`#${selectors.profileTabsId}`);

        if (!window.profileTabslistenerAttached) {
            window.addEventListener('resize', () => {
                setWidthToPanes();
                setTabContentOffset(`#${selectors.profileTabsId}`);
            });
            window.profileTabslistenerAttached = true;
        }

        window.dispatchEvent(new Event('resize'));
    };

    window.profileModal = { onProfileModalLoaded };
})();

const btnFAQClickHandler = () => {
    dialog = bootbox
        .alert({
            message: instruction,
            locale: 'ru',
        })
        .off('shown.bs.modal');

    return false;
};

document.addEventListener('click', (e) => {
    if (e.target && e.target.matches('#btn-faq')) {
        e.preventDefault();
        btnFAQClickHandler();
    }
});



