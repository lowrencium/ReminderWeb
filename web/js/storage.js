$(document).ready(function() {
    if(localStorage.length == 0)
    {
        if($("div#auth-info").length > 0)
        {
            localStorage.setItem("token", $("div#auth-info span:first-child").text());
            localStorage.setItem("expire", $("div#auth-info span:nth-child(2)").text());
            localStorage.setItem("id", $("div#auth-info span:nth-child(3)").text());
            localStorage.setItem("host", $("div#auth-info span:nth-child(4)").text());

            showCurrentRappel("http://127.0.0.1/reminderweb/web/images/warning.ico");
        }
    }

    $("a.logout").on("click", function() {
        localStorage.clear();
    });
})