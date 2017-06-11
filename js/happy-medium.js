/* global $ */

function happyMedium(url, callback) {
    "use strict";
    url = encodeURIComponent(url);
    var queryUrl = "https://query.yahooapis.com/v1/public/yql?q=select%20*%20from%20htmlstring%20where%20url%3D%22" + url + "%3Fformat%3Djson%22&env=store%3A%2F%2Fdatatables.org%2Falltableswithkeys&callback=?";

    /* select * from html where url="http://medium.com/akita-association-of-jets?format=json" */
    $.getJSON(queryUrl, function (data) {
        
        var dataString = data.results[0];
        //var jsonString = dataString.substring(18, (dataString.length - 7));
        var jsonString = dataString.substring(70, (dataString.length - 36));
        //var $displayMe = $('<textarea style="position:fixed;top:0;left:0;height:100vh;width:100vw;z-index:3921231;background:white"></textarea>');
        //$displayMe.html(jsonString);
        //$("body").append($displayMe);
        var json = JSON.parse(jsonString);

        callback(json);
    });
}
