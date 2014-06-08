$(document).ready(function() {
    // where ajax content will be loaded
    ajaxContent = $("#ajaxContent");
    
    ajaxContent.load("remindme.html");

    //  Menus 
    //  
    // Selectors
    top_navbar = $("#top_navbar");
    top_menu = $("#top_menu");
    left_menu = $("#left-menu");

    
    //top_menu

    var button_register = "#register";
    var button_login = "#login";

    top_menu.on("click", "a, button", function(e){
        menuItem($(this));
        e.preventDefault();
    });



    top_menu.on("click", button_register, function(e){
        console.log("click on register");
        ajaxContent.load("register.html");
        
        e.preventDefault();
    });


    top_menu.on("click", button_login, function(e){
        console.log("click on login");
        ajaxContent.load("login.html");
        
        e.preventDefault();
    });
    
    //left_menu
    left_menu.on("click", "a", function(e) {
        menuItem($(this));
        e.preventDefault();
    });
    //--------------------------------------------

    // Contacts
    
    
    
    //--------------------------------------------
    
    
});

function menuItem(item) {
    menusUnselect();
    item.parent().addClass("active");
    
    var page = item.attr("id") + ".html";
    console.log(page);
    ajaxContent.load(page);

    //window.history.pushState("object or string", "reminder/public_html/"+page, "/"+page);

}

function menusUnselect(){
    $(top_navbar).children().removeClass("active");
    $(left_menu).children().removeClass("active");
}