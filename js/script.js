/* global $ */
/* global happyMedium */

$(document).ready(function () {
    "use strict";
    happyMedium("https://medium.com/akita-association-of-jets", function (data) {

        var postData = $.map(data.payload.references.Post, function (el) {
            return el;
        });
        //console.log(postData);
        var buildMediumPosts = function (data, count) {

            if (count === undefined) {
                count = 0;
            }

            var thisPost = data[count];

            if (thisPost && count < 5) {
                $(".medium-loading").hide();
                var $articleCard = $("<a href='https://medium.com/p/" + thisPost.id + "' class='article-card'></div>");
                var $postImage = $("<div class='article-media'></div>");
                var $postContent = $("<div class='article-content'></div>");
                $articleCard.append($postImage);
                $articleCard.append($postContent);
                var $articleDate = $("<p class='date'></p>");
                var pD = new Date(thisPost.firstPublishedAt);
                var monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
                $articleDate.html("Published on " + monthNames[pD.getMonth()] + " " + pD.getDate() + ", " + pD.getFullYear());
                thisPost.previewContent.bodyModel.paragraphs.forEach(function (preview) {
                    if (preview.type === 1) {
                        var $postDesc = $("<p>" + preview.text + "</p>");
                        $postContent.append($postDesc);
                    }
                    else if (preview.type === 13) {
                        var $postSubtitle = $("<p>" + preview.text + "</p>");
                        $postContent.append($postSubtitle);
                    }
                    else if (preview.type === 4) {
                        $postImage.css({
                            "background-image": "url('https://cdn-images-1.medium.com/max/500/" + preview.metadata.id +"')"
                        });
                    }
                    else if (preview.type === 3) {
                        var $postTitle = $("<h3>" + preview.text + "</h1>");
                        $postContent.append($postTitle);
                    }
                    else if (preview.type === 2) {
                        var $postPreviewTitle = $("<p class='preview-title'>" + preview.text + "</p>");
                        $postContent.append($postPreviewTitle);
                    }
                    else {
                        throw new Error("Unrecognized preview content type: " + preview.type);
                    }
                });
                $postContent.append($articleDate);
                $("#medium-articles").append($articleCard);

                buildMediumPosts(data, count + 1);
            }
            else {
                var $moreLink = $("<div class='read-more-card'><a href='https://medium.com/akita-association-of-jets'>Read more articles on Medium.com</a></div>");
                $("#medium-articles").append($moreLink);
            }
        };

        buildMediumPosts(postData);
    });





    $.ajaxSetup({
        cache: true
    });
    $.getScript('//connect.facebook.net/en_US/sdk.js', function () {
        FB.init({
            appId: '1925408434352308',
            version: 'v2.5' /* or v2.0, v2.1, v2.2, v2.3 */
        });
        FB.api(
            "/923345024404557/events/?access_token=1925408434352308%7C-LqKZs2921LCzVaRg4VMbCHojW4",
            function (data) {



                function parseFacebookDate(date) {
                    date = date.split("T");
                    return date[0];
                }


                if (data && !data.error) {
                    /* handle the result */
                    $(".facebook-loading").hide();

                    var buildEvents = function (data, count) {
                        if (!count) {
                            count = 0;
                        }

                        var thisEvent = data[count];

                        if (thisEvent && count < 3) {

                            var $eventCard = $('<a href="https://www.facebook.com/events/' + thisEvent.id + '" class="event-card"></a>');
                            var $eventContent = $('<div class="event-content"></div>');
                            var $eventTitle = $('<h3></h3>');
                            var $eventDate = $('<p class="event-date"></p>');

                            var eD = new Date(parseFacebookDate(thisEvent.start_time));

                            var monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
                            $eventDate.html(monthNames[eD.getMonth()] + " " + eD.getDate() + ", " + eD.getFullYear());
                            $eventTitle.html(thisEvent.name);
                            var $eventDescription = $('<p></p>');
                            $eventDescription.html(thisEvent.description.substring(0, 100) + "...");
                            $eventContent.append($eventTitle);
                            $eventContent.append($eventDate);
                            $eventContent.append($eventDescription);

                            $eventCard.append($eventContent);
                            $("#facebook-events").append($eventCard);

                            buildEvents(data, (count + 1));
                        }
                        else {
                            var $moreLink = $("<div class='read-more-card read-more-events'><a href='https://facebook.com/charityakita/events'>See more events on Facebook</a></div>");
                            $("#facebook-events").append($moreLink);
                        }
                    };

                    buildEvents(data.data);

                }
                else {
                    throw new Error("Error: " + data.error);
                }
            }
        );
    });



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

    ifURLExists("http://archive.charityakita.com", function () {
        $(".archive-notice").show();
    });
});
