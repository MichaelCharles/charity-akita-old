/* global $ */
/* global happyMedium */

$(document).ready(function() {
    //happyMedium("medium.com/free-code-camp", function(data){
    happyMedium("https://medium.com/akita-association-of-jets", function(data) {
        var postData = $.map(data.payload.references.Post, function(el) {
            return el
        });
        console.log(postData);
        var buildMediumPosts = function(data, count) {

            if (count === undefined) {
                count = 0;
            }

            var thisPost = data[count];

            if (thisPost) {
                var $postLink = $("<a href='https://medium.com/p/" + thisPost.id + "'></a>");
                var $articleCard = $("<div class='article-card'></div>");
                thisPost.previewContent.bodyModel.paragraphs.forEach(function(preview) {
                    if (preview.type === 1) {
                        var $postDesc = $("<p>" + preview.text + "</p>");
                        $articleCard.append($postDesc);
                    }
                    else if (preview.type === 13) {
                        var $postSubtitle = $("<h2>" + preview.text + "</h2>");
                         $articleCard.append($postSubtitle);
                    }
                    else if (preview.type === 4) {
                        var $postImage = $("<div class='media' style='background-image: url(https://cdn-images-1.medium.com/max/500/" + preview.metadata.id + ")'></div>");
                         $articleCard.append($postImage);
                    }
                    else if (preview.type === 3) {
                        var $postTitle = $("<h1>" + preview.text + "</h1>");
                         $articleCard.append($postTitle);
                    }
                    else if (preview.type === 2) {
                        var $postPreviewTitle = $("<h1>" + preview.text + "</h1>");
                         $articleCard.append($postPreviewTitle);
                    }
                    else {
                        console.log("Unrecognized preview content type: " + preview.type);
                    }
                });
                $postLink.append($articleCard);
                $("#medium-articles").append($postLink);

                buildMediumPosts(data, count + 1);
            }
        }

        buildMediumPosts(postData);
    });
});