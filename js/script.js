/* global $ */
/* global happyMedium */

$(document).ready(function() {
    happyMedium("medium.com/free-code-camp", function(data){
    //happyMedium("https://medium.com/akita-association-of-jets", function(data) {
        var postData = $.map(data.payload.references.Post, function(el) {
            return el
        });
        
        var buildMediumPosts = function(data, count) {

            if (count === undefined) {
                count = 0;
            }

            var thisPost = data[count];

            if (thisPost && count < 4) {
                $(".medium-loading").hide();
                var $articleCard = $("<a href='https://medium.com/p/" + thisPost.id + "' class='article-card'></div>");
                var $postImage = $("<div class='article-media'></div>");
                var $postContent = $("<div class='article-content'></div>");
                $articleCard.append($postImage);
                $articleCard.append($postContent);
                thisPost.previewContent.bodyModel.paragraphs.forEach(function(preview) {
                    if (preview.type === 1) {
                        var $postDesc = $("<p>" + preview.text + "</p>");
                        $postContent.append($postDesc);
                    }
                    else if (preview.type === 13) {
                        var $postSubtitle = $("<p>" + preview.text + "</p>");
                        $postContent.append($postSubtitle);
                    }
                    else if (preview.type === 4) {
                        $postImage.css({"background-image": "url(https://cdn-images-1.medium.com/max/500/" + preview.metadata.id });
                    }
                    else if (preview.type === 3) {
                        var $postTitle = $("<h3>" + preview.text + "</h1>");
                        $postContent.append($postTitle);
                    }
                    else if (preview.type === 2) {
                        var $postPreviewTitle = $("<p>" + preview.text + "</p>");
                        $postContent.append($postPreviewTitle);
                    }
                    else {
                        console.log("Unrecognized preview content type: " + preview.type);
                    }
                });

                $("#medium-articles").append($articleCard);

                buildMediumPosts(data, count + 1);
            }
            else {
                var $moreLink = $("<div class='read-more-card'><a href='https://medium.com/akita-association-of-jets'>Read more articles on Medium.com</a></div>")
                $("#medium-articles").append($moreLink);
            }
        }

        buildMediumPosts(postData);
    });
});

