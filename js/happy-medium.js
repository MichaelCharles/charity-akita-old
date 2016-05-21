/* global $ */

function happyMedium(url, callback) {
    url = encodeURIComponent(url);

    var queryUrl = "https://query.yahooapis.com/v1/public/yql?q=select%20*%20from%20html%20where%20url%3D%22" + url + "%3Fformat%3Djson%22&callback=?";
        /* select * from html where url="http://medium.com/akita-association-of-jets?format=json" */
    $.getJSON(queryUrl, function(data) {
        trimQuery(data);
    });

    function trimQuery(data) {
        var dataString = data.results[0];
        var jsonString = dataString.substring(18, (dataString.length - 7));
        var json = JSON.parse(jsonString);
        callback(json);
    }
}