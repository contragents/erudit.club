//
function isAndroidAppGlobal() {
    if (getCookieGlobal('DEVICE') === 'Android') {
        return true;
    }

    if (getCookieGlobal('PRODUCT') === 'RocketWeb') {
        return true;
    }

    return window.location.href.indexOf('app=1') > -1;
}


function isVerstkaTestGlobal() {
    return window.location.href.indexOf('verstka=1') > -1;
}

function isMobileDeviceGlobal() {
    const ua = navigator.userAgent;
    if (/(tablet|ipad|playbook|silk)|(android(?!.*mobi))/i.test(ua)) {
        return true;//tablet
    } else if (/Mobile|Android|iP(hone|od)|IEMobile|BlackBerry|Kindle|Silk-Accelerated|(hpw|web)OS|Opera M(obi|ini)/.test(ua)) {
        return true;//mobile
    }

    return false;//desktop
}

function isTabletDeviceGlobal() {
    const ua = navigator.userAgent;
    if (/(tablet|ipad|playbook|silk)|(android(?!.*mobi))/i.test(ua)) {
        return true;
    }

    return false;
}

function isVkAppGlobal() {
    if (document.referrer == 'https://vk.com/') {
        return true;
    }

    if (document.location.href.match('api_url')) {
        return true;
    }

    return false;
}

function isYandexAppGlobal() {
    if (document.location.href.match('yandex')) {
        return true;
    }

    if (window.location.href.indexOf('yandex') > -1) {
        return true;
    }

    return false;
}

function getCookieGlobal(cookieName) {
    var results = document.cookie.match('(^|;) ?' + cookieName + '=([^;]*)(;|$)');

    if (results)
        return (unescape(results[2]));
    else
        return false;
}

function isIOSDevice() {
    if (/iPhone|iPad|iPod/i.test(navigator.userAgent)) {
        return true;
    }

    return false;
}

// CLUB-383 Верстка профиля. пихнуть куданибудь

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


// для копирования из input в буфер
function copyToClipboard(selector) {
    const element = document.querySelector(selector);
    element.select();
    element.setSelectionRange(0, 99999);
    document.execCommand('copy');
}

(function profileModal() {
    // on modal loaded
    // document.addEventListener("DOMContentLoaded", onProfileModalLoaded);

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
        document
            .querySelectorAll(selectors.tabPane)
            .forEach(
                (item) =>
                    (item.style.width =
                        item.closest(selectors.tabContentWrap).getBoundingClientRect()
                            .width + 'px'),
            );

        if (!window.profileTabslistenerAttached) {
            window.addEventListener('resize', (event) => {
                document.querySelectorAll(selectors.tabPane).forEach((item) => {
                    const width =
                        item.closest(selectors.tabContentWrap).getBoundingClientRect()
                            .width + 'px';
                    item.style.width = width;
                });
                setTabContentOffset(`#${selectors.profileTabsId}`);
            });
            window.profileTabslistenerAttached = true;
        }

        window.dispatchEvent(new Event('resize'));
    };

    window.profileModal = { onProfileModalLoaded };

    // return {
    //     onProfileModalLoaded
    // }
})();



