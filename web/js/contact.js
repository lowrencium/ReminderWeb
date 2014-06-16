$(function() {
    loadContacts();

    var demandes = getContactRequest();

    if(typeof demandes != 'undefined') {
        var source = $("#demande-template").html();
        for(var i = 0; i < demandes.length; i++) {
            var context = demandes[i];
            var html = useTemplates(source, context);
            $("#demande-list").append(html);
        }
    }

    var mesDemandes = getMyContactRequest();

    if(typeof mesDemandes != 'undefined') {
        var source = $("#mesDemandes-template").html();
        for(var i = 0; i < mesDemandes.length; i++) {
            var context = mesDemandes[i];
            var html = useTemplates(source, context);
            $("#demande-list").append(html);
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
                var id = contact.attr('id');
                var type = contact.attr('data-type');

                if (removeContact(id, type)) {
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

    $("ul#demande-list .accept").on("click", function(e) {
        var button = $(this);

        var demande = $(button).closest("li");
        var id = demande.attr('id');

        if(validateContact(id, true)) {
            demande.remove();
            $("#contact-list").html("");
            loadContacts();
        }
        else {
            console.log("erreur SOAP removeContact");
        }
    });

    $("ul#demande-list .decline").on("click", function(e) {
        var button = $(this);

        var demande = $(button).closest("li");
        var id = demande.attr('id');

        if(validateContact(id, false)) {
            demande.remove();
        }
        else {
            console.log("erreur SOAP removeContact");
        }
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

        if (addContact(name, email, phone, location)) {
            $("#contact-list").empty();
            loadContacts();
            //$(modalAddContact).modal('toggle');
            var message = "Contact créé avec succès";
            buttonBehaviourSubmitSuccess(button, message);
        }
        else {
            var error = "Imposible d'ajouter le contact";
            buttonBehaviourSubmitError(button, error);
        }
        buttonSubmitLoadStop(l);
    });

    $("a#addContact").on("click", function(e) {
        var button = $("#formAddContact").find("button[type=submit]");
        buttonBehaviourSubmitDefault(button);
    });
});

function loadContacts()
{
    var contacts = getContacts();

    if (typeof contacts != 'undefined') {
        var source = $("#contact-template").html();
        for (var i = 0; i < contacts.length; i++) {
            var context = contacts[i];
            var html = useTemplates(source, context);

            $("#contact-list").append(html);
            $("#contact-list li[id="+contacts[i].id+"][data-type="+contacts[i].type+"] .gravatar").empty().html($.gravatar(contacts[i].email, {size: 120, classes: "img-rounded img-responsive"}));
        }
    }
}
