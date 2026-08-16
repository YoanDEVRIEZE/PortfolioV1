document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('#flash-messages .alert').forEach(function (alert) {
        window.setTimeout(function () {
            if (!alert.isConnected) {
                return;
            }

            alert.classList.add('admin-flash-is-leaving');
            window.setTimeout(function () {
                if (window.bootstrap && window.bootstrap.Alert) {
                    window.bootstrap.Alert.getOrCreateInstance(alert).close();
                } else {
                    alert.remove();
                }
            }, 260);
        }, alert.classList.contains('alert-danger') ? 7000 : 5000);
    });
});
