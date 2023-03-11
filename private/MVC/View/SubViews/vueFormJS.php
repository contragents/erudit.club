<script>
    const newline = "<br />";
    const Lot = {
        brand: '<?=$Lot['brand']?>',
        name: '<?=$Lot['name']?>',
        color: '<?=$Lot['color']?>',
        pictures: ['<?=$Lot['pic1_url']?>', '<?=$Lot['pic2_url']?>', '<?=$Lot['pic3_url']?>', '<?=$Lot['pic4_url']?>'],
        price: '<?=number_format($Lot['client_price_ruble'], 0, '', ' ')?>',
        category: '<?=$Lot['category']?>',
        sub_category: '<?=$Lot['sub_category']?>',
        composition: '<?=$Lot['composition']?>',
        depth: '<?=$Lot['depth_cm']?>',
        height: '<?=$Lot['height_cm']?>',
        strap: '<?=$Lot['strap_cm']?>',
        width: '<?=$Lot['width_cm']?>',
        descr: '<?=$Lot['description']?>',
        shipDate: '<?=$Lot['promiseShipDate']?>'
    };

    const email = '<?=$user['email']?>';
    const name = '<?=$user['name']?>';
    const phone = '<?=$user['phone']?>';

    const index = '<?=$address['index']?>';
    const address = '<?=$address['address']?>';

    const app = new Vue({
        el: "#app",
        data: {
            Lot: Lot,
            offerTextError: false,
            offerCheckBox: false || (email !== '' ? true : false),
            offerAgreed: false || (email !== '' ? true : false),
            verifyCodeFormHidden: true,
            emailPresent: email !== '' ? true : false,
            namePresent: name != '' ? true : false,
            phonePresent: phone != '' ? true : false,
            indexPresent: index != '' ? true : false,
            addressPresent: address != '' ? true : false,
            addressVerified: false,
            emailWrongFormatHidden: true,
            verifyButtonDisabled: true,
            checkButtonDisabled: true,
            codeVerified: false,
            email: email,
            name: name,
            phone: phone,
            index: index,
            dadataIndex: '',
            address: address,
            long: '',
            lat: '',
            payment: '',
            options: [
                {text: 'Выберите способ оплаты', value: ''},
                <?php foreach($paymentMethods as $method) {?>
                {text: '<?=$method['text']?>', value: '<?=$method['value']?>'},
                <?php } ?>
                /*{text: 'На карту ВТБ', value: 'cardnum_Vtb'},
                {text: 'На карту Сбербанк', value: 'cardnum_Sber'},
                {text: 'По номеру телефона (СБП)', value: 'sbp'},
                {text: 'По реквизитам', value: 'account'}*/
            ],
            pvz_price: 0,
            pvz_address: '',
            pvz_tel: '',
            pvz_code: false,
            pvz1_price: 0,
            pvz1_address: '',
            pvz1_tel: '',
            pvz1_code: false,
            courier_price: 0,
            courier_address: '',
            pvz_value: '',
            pvz1_value: '',
            courier_value: '',
            deliverySelector: false
        }
    });


    const emailElement = document.getElementById("email");

    emailElement.addEventListener("input", function (event) {
        console.log(emailElement.checkValidity());
        if (emailElement.checkValidity()) {
            app.verifyButtonDisabled = false;
            //emailElement.setCustomValidity("");
        } else {
            app.verifyButtonDisabled = true;
            //emailElement.setCustomValidity("I am expecting an e-mail address!");
        }
    });

    function sendOrder() {
        let errors = '';
        let warnings = '';
        //let newline = "\n";

        if (app.name === '') {
            errors += "Не заполнено поле Имя и Фамилия" + newline;
        }

        if (app.phone === '') {
            errors += "Не заполнено поле Номер телефона" + newline;
        }

        if (app.index === '') {
            errors += "Не заполнено поле ИНДЕКС" + newline;
        } else if (app.index != app.dadataIndex && app.dadataIndex != '') {
            warnings += "<strong>Предупреждение!</strong> Введенное значение Индекс не совпадает с полученным от сервера <strong>"
                + app.dadataIndex
                + "</strong>! "
                + newline
                + "Проверьте введенные данные"
                + newline;
        }

        if (app.address === '') {
            errors += "Не заполнено поле АДРЕС" + newline;
        }

        if (app.payment === '') {
            errors += "Не выбран метод оплаты" + newline;
        }

        if (!app.deliverySelector) {
            errors += "Не выбран способ доставки" + newline;
        }


        if (errors !== '') {
            bootAlert('<strong>Внимание!</strong>'
                + newline
                + errors
                + (warnings != '' ? newline + warnings : '')
                + newline
                + 'Пожалуйста, для отправки заказа заполните пропущенные поля'
            );
            return false;
        }

        if (warnings !== '') {
            bootConfirm(warnings + newline + "Отправить как есть?", function () {
                realSendOrder();
            });
        } else {
            realSendOrder();
        }
    }

    function realSendOrder() {
        let postData = 'email=' + encodeURIComponent(app.email) +
            '&name=' + encodeURIComponent(app.name) +
            '&phone=' + encodeURIComponent(app.phone) +
            '&payment=' + encodeURIComponent(app.payment) +
            '&index=' + encodeURIComponent(app.index) +
            '&address=' + encodeURIComponent(app.address) +
            '&long=' + encodeURIComponent(app.long) +
            '&lat=' + encodeURIComponent(app.lat) +
            '&delivery=' + app.deliverySelector;

        fetchGlobal('sendOrder', '', postData)
            .then((data) => {
                    if ('message' in data) {
                        bootAlert(data['message']);
                    } else {
                        bootAlert('При отправке заказа возникла ошибка! Пожалуйста, попробуйте снова');
                    }
                }
            );
    }

    function beginCheckValidation() {
        const checkElement = document.getElementById("check");

        checkElement.addEventListener("input", function (event) {
            console.log(checkElement.checkValidity());
            if (checkElement.checkValidity()) {
                app.checkButtonDisabled = false;
                //checkElement.setCustomValidity("");
            } else {
                app.checkButtonDisabled = true;
                //checkElement.setCustomValidity("I am expecting a 6 digit code!");
            }
        });
    }

    function beginRestValidation() {
        //validation stuff
        return true;
    }

    function checkAddress() {
        if (!document.getElementById('contacts_form').checkValidity()) {
            return true;
        }

        if (app.index != app.dadataIndex && app.dadataIndex != '') {
            let warning = "Предупреждение! Введенное значение Индекс не совпадает с полученным от сервера ("
                + app.dadataIndex
                + ")!"
                + newline
                + "Проверьте введенные данные";
            bootConfirm(warning, function () {
                realCheckAddress();
            });
        } else {
            realCheckAddress();
        }

        return false;
    }

    function realCheckAddress() {
        fetchGlobal('verifyAddress', '',
            'index='
            + app.index
            + '&address='
            + encodeURIComponent(app.address)
            + '&long='
            + app.long
            + '&lat='
            + app.lat
        )
            .then((data) => {
                if (data['result']) {
                    if ('pvz' in data['delivery']) {
                        app.pvz_price = data['delivery']['pvz']['price'];
                        app.pvz_address = data['delivery']['pvz']['address'];
                        app.pvz_tel = data['delivery']['pvz']['tel'];
                        app.pvz_code = data['delivery']['pvz']['pvz_code'];
                        app.pvz_value = app.pvz_code + "_" + data['delivery']['pvz']['raw_price'];
                    }
                    if ('pvz1' in data['delivery']) {
                        app.pvz1_price = data['delivery']['pvz1']['price'];
                        app.pvz1_address = data['delivery']['pvz1']['address'];
                        app.pvz1_tel = data['delivery']['pvz1']['tel'];
                        app.pvz1_code = data['delivery']['pvz1']['pvz_code'];
                        app.pvz1_value = app.pvz1_code + "_" + data['delivery']['pvz']['raw_price'];
                    }
                    app.courier_price = data['delivery']['courier']['price'];
                    app.courier_address = data['delivery']['courier']['address'];
                    app.courier_value = "COURIER_" + data['delivery']['courier']['raw_price'];

                    app.addressVerified = true;

                    if ('checkedIndex' in data) {
                        app.dadataIndex = data['checkedIndex'];
                    }
                    //console.log(data['dadata_response']);
                }
            });
    }

    function checkEmailCode() {
        fetchGlobal('checkCode', 'code', $('#check').val() + '()' + $('#email').val())
            .then((data) => {
                    if (data['result']) {
                        app.codeVerified = true;
                        app.checkButtonDisabled = true;
                        console.log(data['client']['name'], data['client']['phone']);
                        if ('client' in data) {
                            app.name = data['client']['name'];
                            app.phone = data['client']['phone'];
                            app.namePresent = app.name != '' ? true : false;
                            app.phonePresent = app.phone != '' ? true : false;
                        }

                        if ('address' in data) {
                            app.index = data['address']['index'];
                            app.address = data['address']['address'];
                        }

                        setTimeout(function () {
                            beginRestValidation() //пока не знаю что валидировать
                        }, 1000);
                    }

                    setTimeout(function () {
                        bootAlert(data['message']);
                    }, 100);
                }
            );
    }

    function checkOffer() {
        if (!app.offerCheckBox) {
            app.offerTextError = true;//"Для продолжения заказа необходимо подтвердить согласие с офертой";
            return false;
        } else {
            app.offerTextError = false;
            app.offerAgreed = true;
            return true;
        }
    }

    function VerifyEmail() {
        if (!checkOffer()) {
            return false;
        }

        fetchGlobal('checkMail', 'email', $('#email').val())
            .then((data) => {
                    if (data['result']) {
                        app.verifyButtonDisabled = true;
                        app.verifyCodeFormHidden = false;
                        setTimeout(function () {
                            beginCheckValidation()
                        }, 1000);
                    } else {
                        setTimeout(function () {
                            bootAlert(data['message']);
                        }, 100);
                    }
                }
            );
    }

    async function fetchGlobal(action, param_name, param_data) {
        const response = await fetch('?action=' + action,
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
                body: (param_name != '' ? param_name + '=' + encodeURIComponent(param_data) : param_data) //JSON.stringify(data) // body data type must match "Content-Type" header
            }
        );

        if (!response.ok) {
            console.log(`An error has occured: ${response.status}`);
            return {message: "Ошибка связи с сервером. Попробуйте еще раз...", status: "error"};
        }

        return await response.json(); // parses JSON response into native JavaScript objects
    }
</script>
<script src="js/app.min.js?_v=20220210205829"></script>
<script src="js/dadata.js"></script>
<!--<script src="https://cdn.jsdelivr.net/npm/suggestions-jquery@21.12.0/dist/js/jquery.suggestions.min.js"></script>-->

<script>
    $("#address").suggestions({
        token: "c40c6179e234fca2dd1b9463d267d117a801cc5c",
        type: "ADDRESS",
        /* Вызывается, когда пользователь выбирает одну из подсказок */
        onSelect: function (suggestion) {
            app.address = suggestion.value;
            if (suggestion.data.postal_code !== null) {
                app.dadataIndex = suggestion.data.postal_code;
                if (app.index == '') {
                    app.index = app.dadataIndex;
                }
            }

            if (suggestion.data.geo_lat !== null) {
                app.lat = suggestion.data.geo_lat;
                app.long = suggestion.data.geo_lon;
            }
            //console.log(suggestion.data.postal_code);
            //console.log(suggestion);
        }
    });
</script>

