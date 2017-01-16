/* global $ */

$("document").ready(function() {
    if (window.location.hash == "") {
        $("#about-content").show();
    }
    else {
        var elName = window.location.hash + "-content";
        $(elName).show();
    }

    function openContent(page) {
        $(".switchable").hide();
        var elName = "#" + page + "-content";
        $(elName).fadeIn(200);
    }

    $(".c-switcher").click(function() {
        openContent(this.href.trim().toLowerCase().substring(this.href.indexOf('#')+1));
    });
});
