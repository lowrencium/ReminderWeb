$(document).ready(function () {




    Ladda.bind('button[type=submit]', {timeout: 2000});

    $("#login").on("click", function() {
        console.log("login");
    });

    $("#do_register").on("click", function(e) {
        e.preventDefault();
        console.log("do_register");
    });

    

    //EVENT NAVTAB
    $('#navCalendarEvent a').click(function(e) {
        e.preventDefault();
        $(this).tab('show');
    });

    //CONTACTS
    $('[data-toggle="tooltip"]').tooltip();

    $('[data-command="toggle-search"]').on('click', function(event) {
        event.preventDefault();
        $(this).toggleClass('hide-search');

        if ($(this).hasClass('hide-search')) {
            $('.c-search').closest('.row').slideUp(100);
        } else {
            $('.c-search').closest('.row').slideDown(100);
        }
    });


    $("#contactList .delete").confirm({
        text: "Voulez-vous vraiment supprimer ce contact?",
        title: "Confirmation requise",
        confirm: function(button) {
            $(button).closest("li").remove();
        },
        confirmButton: "Oui",
        cancelButton: "Non",
        post: true
    });


    $('#contact-list').searchable({
        searchField: '#contact-list-search',
        selector: 'li',
        childSelector: '.col-xs-12',
        show: function(elem) {
            elem.slideDown(100);
        },
        hide: function(elem) {
            elem.slideUp(100);
        }
    });

    // lorsque je soumets le formulaire
    $('#add-contact').on('submit', function (e) {

        // je récupère les valeurs
        var nom = $('#InputName').val();
        var email = $('#InputEmail').val();
        var phone = $("#InputPhone").val();
        var location = $("#InputLocation").val();

        console.log("nom : " + nom);
        console.log("email : " + email);
        console.log("phone : " + phone);
        console.log("addresse : " + location);

        //add contact via webservice
        $("ajaxContent").load("contacts.php"); // rechargement

        e.preventDefault();
    });

    //for each element that is classed as 'pull-down', set its margin-top to the difference between its own height and the height of its parent
    $('.pull-down').each(function() {
        $(this).css('margin-top', $(this).parent().height() - $(this).height() - 23);
    });
});


