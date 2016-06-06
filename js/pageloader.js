/* global $ */
/* global w3IncludeHTML */

function parseURLParams(url) {
  var queryStart = url.indexOf("?") + 1,
    queryEnd = url.indexOf("#") + 1 || url.length + 1,
    query = url.slice(queryStart, queryEnd - 1),
    pairs = query.replace(/\+/g, " ").split("&"),
    parms = {},
    i, n, v, nv;

  if (query.substring(0, 7) === "editors" || query === url || query === "") {
    return false;
  }

  for (i = 0; i < pairs.length; i++) {
    nv = pairs[i].split("=");
    n = decodeURIComponent(nv[0]);
    v = decodeURIComponent(nv[1]);

    if (!parms.hasOwnProperty(n)) {
      parms[n] = [];
    }

    parms[n].push(nv.length === 2 ? v : null);
  }
  return parms;
}


function getPageData(page, data) {
    var pageExists = false;
    var pageData = {};

    data.forEach(function(item){
        if (item.filename === page.toString()) {
            pageExists = true;
            pageData = item;
        }
    });
    return pageExists ? pageData : false;
}

function buildPageList(data, $pageList, index) {
  if (index === undefined) {
    index = 0;
  }
  if ($pageList === undefined) {
    $pageList = $("<ul></ul>");
  }
  if (data[index]){
    if (!data[index].hidden) {
    var $listItem = $("<li><a href='../pages/?p=" + data[index].filename + "'>" + data[index].title + "</a></li>");
    $pageList.append($listItem);
    }
    index = index + 1;
    return buildPageList(data, $pageList, index);
  } else {
    return $pageList;
  }
}

$("document").ready(function(){
    var urlParams = parseURLParams(window.location.href);

    $.getJSON("valid-pages.json", function(data){

    if (urlParams.p) {
        var pageData = getPageData(urlParams.p, data.pages);
        if (pageData){
        $(".paper-title h3").html(pageData.title);
        $(".content-render-area").attr("w3-include-html", pageData.filename);
        w3IncludeHTML();
        } else {
          $(".paper-title h3").html("Invalid Page Query");
          $(".content-render-area").html("The page you were looking for cannot be found. Where you perhaps looking for one of these?");
          $pageList = buildPageList(data.pages);
          $(".content-render-area").append($pageList);
        } 
    } else {
          $(".paper-title h3").html("Invalid Page Query");
          $(".content-render-area").html("The page you were looking for cannot be found. Where you perhaps looking for one of these?");
          $pageList = buildPageList(data.pages);
          $(".content-render-area").append($pageList);
    }
    });

});