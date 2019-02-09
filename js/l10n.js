function l10n_get(keys, arrayToFill, callback) {
    jQuery.post('locales/lang.php',
        {keys: keys},
        function(response) {
            if (arrayToFill) {
                window[arrayToFill]=response;
            }
            callback && callback(response);
        }
    );
}