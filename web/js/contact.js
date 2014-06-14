$(document).ready(function() {



    $('[data-toggle="tooltip"]').tooltip();
    $('[data-toggle="modal"]').tooltip();

    $('[data-command="toggle-search"]').on('click', function(event) {
        event.preventDefault();
        $(this).toggleClass('hide-search');

        if ($(this).hasClass('hide-search')) {
            $('.c-search').closest('.row').slideUp(100);
        } else {
            $('.c-search').closest('.row').slideDown(100);
        }
    });


    $("ul#contact-list").on("click",  ".delete", function(e) {
        var button = $(this);
        $.confirm({
            text: "Voulez-vous vraiment supprimer ce contact?",
            title: "Confirmation requise",
            confirm: function() {
                var contact = $(button).closest("li");
                var mailContact = contact.find('[data-role="email"]');
                contact.remove();
            },
            confirmButton: "Oui",
            cancelButton: "Non",
            post: true
        });
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


    $('form#formAddContact').on('submit', function(e) {
        e.preventDefault();

        // je récupère les valeurs
        var nom = $('#name').val();
        var email = $('#mail').val();
        var phone = $("#phone").val();
        var location = $("#location").val();

        var contact = {
            name: nom,
            email: email,
            phone: phone,
            location: location
        };

        // Use contact template for rendering
        var source = $("#contact-template").html();
        var template = Handlebars.compile(source);
        var context = contact;
        var html = template(context);

        $("#addContactModal").modal("toggle");

        $("#contact-list").fadeIn().prepend(html);

    });
});