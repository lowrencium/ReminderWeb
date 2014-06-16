$(document).ready(function() {

    var idUser = 1;
    var sessionId = "token";

    events = getRappels(idUser, sessionId);

    var selectedDate;

    var options = {
        onDayClick: function(e) {
            selectedDate = e.data.date;
            $("#shareEvent").removeAttr("disabled");
            $("#deleteEvent").removeAttr("disabled");
        },
        onGridShow: function(e) {
            $("#shareEvent").attr("disabled", "disabled");
            $("#deleteEvent").attr("disabled", "disabled");
        },
        color: "green",
        events: events,
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

    $("#loadCalendar").kalendar(options); //Load the calendar
    $("#startDate, #endDate").datetimepicker();

    $("#addEvent").on("click", function() {
        var button = $("#formAddEvent").find("button[type=submit]");
        buttonBehaviourSubmitDefault(button);
    });

    $("#formAddEvent").on("submit", function(e) {
        e.preventDefault();
        var button = $(this).find("button[type=submit]");
        var begin = new Date($(this.startDate).val()).getTime();
        var end = new Date($(this.endDate).val()).getTime();
        if (begin > end) {
            var error = "La date de fin doit être supérieure à celle de début";
            buttonBehaviourSubmitError(button, error);
        }
        else {
            var title = $(this.title).val();
            var location = $(this.location).val();

            if (addRappel(idUser, sessionId, title, location, begin, end)) {
                var message = "Le rappel a été ajouté avec succès";
                buttonBehaviourSubmitSuccess(button, message);
            }
            else {
                var error = "Problème lors de l'ajout de l'évènement";
                buttonBehaviourSubmitError(button, error);
            }
        }
    });

    //SHARE
    $('button#shareEvent').on('click', function(e)
    {
        e.preventDefault();

        var button = $('#do_shareEvent');
        var button2 = $("#do_shareEventFinal");

        button.parent().show();
        button2.parent().hide();

        var message = 'Sélectionner les contacts';
        buttonBehaviourSubmitDefault(button, message);

        var message = "Partager";
        buttonBehaviourSubmitDefault(button2, message);

        var dayEvents = getDayEvents(selectedDate);
        var shareContentTableModal = $("#shareEventContent tbody");
        var eventsTable = $("div#shareEventsTable");

        $("#shareEventsContacts").hide();

        eventsTable.show();
        shareContentTableModal.empty(); //cleaning before append

        for (var i = 0; i < dayEvents.length; i++) {
            var templateEvent = getRowEvent(dayEvents[i]);
            shareContentTableModal.append(templateEvent);
        }
    });

    $("#do_shareEvent").on("click", function(e){
        e.preventDefault();
        var button = $(this);

        // At least one event is checked
        if (isAtLeastOneCheckedBoxChecked('#tableShareEvents')) {
            $("input:checkbox:not(:checked)").each(function()
            {
                $(this).closest("tr").remove();
            });

            $("div#shareEventsContacts tbody").empty();
            $("div#shareEventsContacts").fadeIn(); // Display contacts

            var contacts = getContacts(idUser, sessionId);

            // if at leasts 1 contact
            if (typeof contacts != 'undefined') {
                for (var i = 0; i < contacts.length; i++) {
                    var templateContact = getRowContact(contacts[i]);
                    $("table#tableShareContacts tbody").append(templateContact);
                }

                $(button).parent().hide();
                $("#do_shareEventFinal").parent().show();

            }
            else {
                $("div#shareEventsContacts table").remove();
                var error = "Aucun contact";
                buttonBehaviourSubmitError(button, error);
            }
        }
        else { // No event checked
            var error = "Sélectionner au moins un rappel";
            buttonBehaviourSubmitError(button, error);
        }

    });

    $("#do_shareEventFinal").on('click', function(e){
        e.preventDefault();
        var button = $(this);
        if (isAtLeastOneCheckedBoxChecked('#shareEventsContacts')) {
            if (isAtLeastOneCheckedBoxChecked('#tableShareEvents')) {
                var status = true;
                $("#tableShareEvents input:checkbox:checked").each(function() { // each event
                    var event = $(this);
                    $("#tableShareContacts input:checkbox:checked").each(function(){ // each contact
                        var contact = $(this);
                        var rappelId = event.parent().parent().attr('id');
                        var contactId = contact.parent().parent().attr('id');
                        var contactType= contact.parent().parent().attr('data-type');
                        if(!shareRappel(idUser, sessionId, rappelId, contactId, contactType)){
                            status = false;
                        }
                    });
                });
                if(status){
                    var message = "Rappels partagés avec succès";
                    buttonBehaviourSubmitSuccess(button, message);
                }
                else{
                    var error = "Echec du partage";
                    buttonBehaviourSubmitError(button, error);
                }
            }else{
                var error = "Aucun évènement sélectionné";
                buttonBehaviourSubmitError(button, error);
            }
        }
        else{ // No contact checked
            var error = "Aucun contact sélectionné";
            buttonBehaviourSubmitError(button, error);
        }
    });



    $('button#deleteEvent').on('click', function()
    {
        var dayEvents = getDayEvents(selectedDate);
        var deleteContentModal = $("#deleteEventContent tbody");

        deleteContentModal.empty(); //cleaning before append

        for (var i = 0; i < dayEvents.length; i++) {
            var templateEvent = getRowEvent(dayEvents[i]);

            deleteContentModal.append(templateEvent);
        }
        var button = $("#formDeleteEvent").find("button[type=submit]");
        buttonBehaviourSubmitDefault(button);
    });

    $("#formDeleteEvent").on("submit", function(e) {
        e.preventDefault();
        var button = $(this).find('button[type=submit]');
        if (isAtLeastOneCheckedBoxChecked("#tableDeleteEvent")) {
            $("input:checkbox:checked").each(function()
            {
                var event = $(this).closest("tr");
                var rappelId = event.attr("id");
                //remove from the DOM
                if (removeRappel(idUser, sessionId, rappelId)) {
                    event.remove();
                    var message = "Recharger la page pour avoir le calendrier à jour";
                    buttonBehaviourSubmitSuccess(button, message);
                }
                else {
                    var error = "Les rappels n'ont pas pu être supprimés";
                    buttonBehaviourSubmitError(button, error);
                }
            });
        }
        else{
            var error = "Sélectionner au moins un rappel";
            buttonBehaviourSubmitError(button, error);
        }
    });
});

// return events of the day
function getDayEvents(datePicked) {
    var dayEvents = [];
    //Convert into date
    datePicked = getRawDate(new Date(datePicked));
    for (var i = 0; i < events.length; i++) {
        var startDate = getRawDate(events[i].start.d);
        var endDate = getRawDate(events[i].end.d);

        if (datePicked >= startDate && datePicked <= endDate) {
            dayEvents.push(events[i]);
        }
    }
    return dayEvents;
}

function getFormattedDate(dateToBeFormatted, format) {
    return $.datepicker.formatDate(format, new Date(dateToBeFormatted));
}

function getFancyDate(date) {
    return getFormattedDate(date, 'dd MM yy');
}

function getRawDate(date) {
    return getFormattedDate(date, 'yymmdd');
}

function getRawHours(date) {
    date = new Date();
    return date.getHours() + date.getMinutes();
}


function getRowEvent(event) {
    var source = $("#event-table-template").html();
    var context = {
        id: event.id,
        title: event.title,
        start: getFancyDate((event.start.d)),
        end: getFancyDate(event.end.d),
        location: event.location
    };
    var template = Handlebars.compile(source);
    var html = template(context);
    return html;
}

function getRowContact(contact) {
    var source = $("#contact-table-template").html();
    var context = {
        id: contact.id,
        name: contact.name,
        email: contact.email,
        type: contact.type
    }
    var template = Handlebars.compile(source);
    var html = template(context);
    return html;
}

function isAtLeastOneCheckedBoxChecked(container) {
    var atLeastOneIsChecked = $(container + ' :checkbox:checked').length > 0;
    return atLeastOneIsChecked;
}