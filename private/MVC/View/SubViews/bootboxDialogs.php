<script>
    function bootAlert(message) {
        dialog = bootbox.alert({
            title: '',
            message: message,
            locale: 'ru',
            size: 'small',
            buttons: {
                ok: {
                    label: 'Понятно',
                    className: 'order__button contacts__btn active'
                }
            }
        });
    }
    function bootConfirm(message, callback) {
        return bootbox.confirm({
            message: message,
            buttons: {
                confirm: {
                    label: 'Да, продолжить',
                    className: 'btn-success'
                },
                cancel: {
                    label: 'Отменить',
                    className: 'btn-danger'
                }
            },
            callback: function (result) {
                if (result) {
                    callback();
                }
            }
        });
    }
</script>

