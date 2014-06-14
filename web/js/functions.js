$(document).ready(function() {
    //Ladda.bind('button[type=submit]', {timeout: 2000});

    $("#login").on("click", function() {
        console.log("login");
    });

    //EVENT NAVTAB
    $('#navCalendarEvent a').click(function(e) {
        e.preventDefault();
        $(this).tab('show');
    });

   
    //for each element that is classed as 'pull-down', set its margin-top to the difference between its own height and the height of its parent
    $('.pull-down').each(function() {
        $(this).css('margin-top', $(this).parent().height() - $(this).height() - 18);
    });
});

