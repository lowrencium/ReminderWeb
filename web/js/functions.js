$(document).ready(function(){
    // Same as the above but automatically stops after two seconds
    Ladda.bind( 'button[type=submit]', { timeout: 2000 } ); 
    
    // Disable default behaviour of submit buttons
    $('button[type=submit]').on("click", function(e){
        e.preventDefault();
    });
    
    $("#login").on("click", function(){
        console.log("login");
    });
    
    $("#do_register").on("click", function(){
        console.log("do_register");
    });
});

