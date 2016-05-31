$(document).ready(function () {
    $(".japanese-button").click(function () {
        $(".nihongo-de-no").show();
        fadeInTheInfo();
    });
    $(".english-button").click(function () {
        $(".eigo-de-no").show();
        fadeInTheInfo();
    });
    $(".close").click(function () {
        $(".overlay-box").fadeOut(500, function () {
            $(".eigo-de-no").hide();
            $(".nihongo-de-no").hide();
        });

    });


    function fadeInTheInfo() {
        $(".overlay-box").show()
        $(".overlay-box").css({
            display: "flex",
            opacity: 0
        });
        $(".overlay-box").animate({
            opacity: 1
        }, 500)
    }
});