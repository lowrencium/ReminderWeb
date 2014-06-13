$(document).ready(function() {
    Ladda.bind('button[type=submit]', {timeout: 2000});

    $("#login").on("click", function() {
        console.log("login");
    });


    $("modal#registerModal").on("click", "button#do_register", function(e) {
        e.preventDefault();
        //$(this).delay(1000).modal('toggle');
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
    $('#add-contact').on('submit', function(e) {

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
        $(this).css('margin-top', $(this).parent().height() - $(this).height() - 18);
    });
});

function getFullPartDate(number) {
    return ((number + 1) < 10 ? "0" + (number + 1) : (number + 1));
}

function getRappels(id, sessionId)
{
    var result = SoapManager("RecupererRappel", {"id": id, "token": sessionId});
    var resultat = result.find("Resultat");
    var erreur = result.find("Erreur");
    if (resultat.text() == "true" && erreur.text() == "")
    {
        var array = new Array();
        result.find("Rappels").find("item").each(function(index) {
            var debut = new Date($(this).find('Debut').text() * 1000);
            var fin = new Date($(this).find('Fin').text() * 1000);

            array.push(
                    {
                        "id": $(this).find('Id').text(),
                        "title": $(this).find('Titre').text(),
                        "location": $(this).find('Lieu').text(),
                        "start": {
                            date: debut.getFullYear() + "" + getFullPartDate(debut.getMonth() + 1) + "" + getFullPartDate(debut.getDate() + 1),
                            time: getFullPartDate(debut.getHours()) + ":" + getFullPartDate(debut.getMinutes()),
                        },
                        "end": {
                            date: fin.getFullYear() + "" + getFullPartDate(fin.getMonth() + 1) + "" + getFullPartDate(fin.getDate() + 1),
                            time: getFullPartDate(fin.getHours()) + ":" + getFullPartDate(fin.getMinutes()),
                        },
                    }
            );
        });
    }
    else
    {
        //Erreur
    }
}

