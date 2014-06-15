$(function() {

    var idUser = 1;
    var sessionId = "token";

    var modalAddContact = "#addContactModal";
    var contacts = getContacts(idUser, sessionId);

    if (typeof contacts != 'undefined') {
        var source = $("#contact-template").html();
        for (var i = 0; i < contacts.length; i++) {
            var context = contacts[i];
            var html = useTemplates(source, context);
            $("#contact-list").append(html);
        }
    }

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

    $("ul#contact-list").on("click", ".delete", function(e) {
        var button = $(this);
        $.confirm({
            text: "Voulez-vous vraiment supprimer ce contact?",
            title: "Confirmation requise",
            confirm: function() {
                var contact = $(button).closest("li");
                var email = contact.find('[data-role="email"]').html();

                if (removeContact(idUser, sessionId, email)) {
                    contact.remove();
                }
                else {
                    console.log("erreur SOAP removeContact");
                }
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

        var button = $(this).find('button[type=submit]');
        var l = buttonSubmitLoadStart(button);

        var name = $('#name').val();
        var email = $('#mail').val();
        var phone = $("#phone").val();
        var location = $("#location").val();

        var contact = {
            name: name,
            email: email,
            phone: phone,
            location: location
        };

        if (addContact(idUser, sessionId, name, email, phone, location)) {
            var source = $("#contact-template").html();
            var context = contact;

            $("#addContactModal").modal("toggle");
            $("#contact-list").fadeIn().prepend(useTemplates(source, context));
            $(modalAddContact).modal('toggle');
        }
        else {
            var error = "Imposible d'ajouter l'utilisateur";
            buttonBehaviourSubmitError(button, error);
        }
        buttonSubmitLoadStop(l);
    });

    $("a#addContact").on("click", function(e) {
        var button = $("#formAddContact").find("button[type=submit]");
        buttonBehaviourSubmitDefault(button);
    });
});


