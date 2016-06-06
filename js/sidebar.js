/* global $ */

$("document").ready(function () {
    var $overlay = $('<div class="menu-overlay"></div>');
    var $sidebar = $('<div class="material-menu"><h4>Navigation</h4></div>');
    $overlay.append($sidebar);

    function buildMenuList(data, $pageList, index) {
        if (index === undefined) {
            index = 0;
        }
        if ($pageList === undefined) {
            $pageList = $("<ul></ul>");
        }
        if (data[index]) {
            if (!data[index].hidden) {
                var listItemURL = "http://charityakita.com/pages/?p=" + data[index].filename
                if (listItemURL === "http://charityakita.com/pages/?p=absolute") {
                    listItemURL = data[index].url;
                }
                var $listItem = $("<a href='" + listItemURL + "'><li>" + data[index].title + "</li></a>");
                $pageList.append($listItem);
            }
            index = index + 1;
            return buildMenuList(data, $pageList, index);
        }
        else {
            return $pageList;
        }
    }


    $.getJSON("http://charityakita.com/pages/valid-pages.json", function (data) {
        var $linkList = buildMenuList(data.pages);
        console.log($linkList);
        $sidebar.append($linkList);
        $("body").append($overlay);
        $(".menu-button").click(function () {
            $overlay.fadeIn(300, function(){
                $sidebar.animate({
                    "left": 0
                }, 300)
            });
        });
        $overlay.click(function () {
            $sidebar.animate({
                "left": -300
            }, 300, function(){
                $overlay.fadeOut(300);
            })
        });
    });
});