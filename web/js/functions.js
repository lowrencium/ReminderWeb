$(document).ready(function() {
    // Same as the above but automatically stops after two seconds
    Ladda.bind('button[type=submit]', {timeout: 2000});

    // Disable default behaviour of submit buttons
    $('button[type=submit]').on("click", function(e) {
        e.preventDefault();
    });

    $("#login").on("click", function() {
        console.log("login");
    });

    $("#do_register").on("click", function() {
        console.log("do_register");
    });


    //CALENDAR & EVENT
    var options = {
        onDayClick: function(e) {
            $("#shareEvent").removeAttr("disabled");
            $("#deleteEvent").removeAttr("disabled");
        },
        onGridShow: function(e) {
            $("#shareEvent").attr("disabled", "disabled");
            $("#deleteEvent").attr("disabled", "disabled");
        },
        color: "green",
        events: [{
                title: "Faire le projet Commun",
                location: "A la maison",
                start: {
                    date: "20140601", time: "17.00"
                },
                end: {
                    date: "20140816", time: "17.00"
                }
            }, {
                title: "Partiels",
                location: "A la maison",
                start: {
                    date: "20140610", time: "17.00"
                },
                end: {
                    date: "20140623", time: "17.00"
                }
            }],
        firstDayOfWeek: "Monday",
        showDays: true,
        dayHuman: [
            ["D", "Dimanche"],
            ["L", "Lundi"],
            ["M", "Mardi"],
            ["M", "Mercredi"],
            ["J", "Jeudi"],
            ["V", "Vendredi"],
            ["S", "Samedi"]
        ],
        monthHuman: [
            ["Janv", "Janvier"],
            ["Fevr", "Février"],
            ["Mars", "Mars"],
            ["Avr", "Avril"],
            ["Mai", "Mai"],
            ["Juin", "Juin"],
            ["Juill", "Juillet"],
            ["Août", "Août"],
            ["Sept", "Septembre"],
            ["Oct", "Octobre"],
            ["Nov", "Novembre"],
            ["Dec", "Décembre"]
        ]
    };
    $("#loadCalendar").kalendar(options);

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


    $(".delete").confirm({
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
        $(this).css('margin-top', $(this).parent().height() - $(this).height() - 23);
    });
});

