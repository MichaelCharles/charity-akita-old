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


    if (window.location.href.indexOf("/blog/") > -1 || window.location.href.indexOf("archive.charityakita.com") > -1 ) {
            window.location.replace("http://charityakita.com/archive");
    }
});