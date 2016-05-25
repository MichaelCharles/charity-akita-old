$(document).ready(function () {
    function ifURLExists(url, callback) {
        $.ajax({
            type: 'HEAD',
            url: url,
            success: function () {
                callback();
            },
            error: function () {
                // Do nothing.
            }
        });
    }


    if (window.location.href.indexOf("/blog/") > -1) {
        var newURL = window.location.href.replace("/blog/", "/archive");
        
        ifURLExists(newURL, function() {
            window.location.replace(newURL);
        });
    }
});