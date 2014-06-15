$(document).ready(function() {

    var idUser = 1;
    var sessionsId = "token";

    events = getRappels(idUser, sessionsId);
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
        $("#formAddEvent").find(".alert").remove();
    });

    $("#formAddEvent").on("submit", function(e) {
        e.preventDefault();
        var begin = new Date($(this.startDate).val()).getTime();
        var end = new Date($(this.endDate).val()).getTime();
        var source;
        var context;
        if (begin > end) {
            source = $("#danger-template").html();
            context = {
                message: "La date de fin doit être supérieure ou égale à la date de début"
            };
        }
        else {
            var title = $(this.title).val();
            var location = $(this.location).val();

            if (addRappel(idUser, sessionsId, title, location, begin, end)) {
                source = $("#success-template").html();
                context = {
                    message: "Le rappel a été ajouté avec succès"
                };
            }
            else {
                source = $("#danger-template").html();
                context = {
                    message: "Problème lors de l'ajout de l'évènement"
                };
            }
        }
        var template = Handlebars.compile(source);
        var html = template(context);
        $("#contentAddEvent").prepend(html);
    });

    $('button#shareEvent').on('click', function(e)
    {
        e.preventDefault();
        var dayEvents = getDayEvents(selectedDate);
        var shareContentTableModal = $("#shareEventContent #tableShareEvents tbody");
        var buttonShareEvent = $("button#do_shareEvent");
        var eventsTable = $("div#shareEventsTable");

        $("#shareEventsContacts").hide();

        eventsTable.show();
        shareContentTableModal.empty(); //cleaning before append

        for (var i = 0; i < dayEvents.length; i++) {
            var templateEvent = getRowEvent(dayEvents[i]);
            shareContentTableModal.append(templateEvent);
        }

        buttonShareEvent.html('Passer à la sélection des contacts');

        $(buttonShareEvent).on("click", function(e) {
            e.preventDefault();
            if (isAtLeastOneCheckedBoxChecked('tableShareEvents')) {
                $("input:checkbox:not(:checked)").each(function()
                {
                    //remove from the DOM
                    $(this).closest("tr").remove();
                    //Launch WS to remove an event
                });
                buttonShareEvent.html('Partager');
                $("div#shareEventsContacts tbody").empty();
                for (var i = 0; i < contacts.length; i++) {
                    var templateContact = getRowContact(contacts[i]);
                    $("div#shareEventsContacts tbody").append(templateContact);
                }
                $("div#shareEventsContacts").fadeIn();
            }
        });
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
    });

    $("#do_deleteEvent").on("click", function(e) {
        e.preventDefault();
        $("input:checkbox:checked").each(function()
        {
            var event = $(this).closest("tr");
            var rappelId = event.attr("id");
            //remove from the DOM
            if (removeRappel(idUser, sessionsId, rappelId)) {
                event.remove();
            }
            //Launch WS to remove an event
        });
        document.location.reload();
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
    
    var checkbox = '<input id=' + contact.id + ' type="checkbox">';
    var name = contact.name;
    var email = contact.mail;
    var templateContact = "<tr><td>" + checkbox + "</td><td>" + name + "</td><td>" + email + "</td></tr>";
    return templateContact;
}

function isAtLeastOneCheckedBoxChecked(containerId) {
    var atLeastOneIsChecked = $('#' + containerId + ' :checkbox:checked').length > 0;
    return atLeastOneIsChecked;
}

function getFullPartDate(number) {
    return ((number + 1) < 10 ? "0" + (number + 1) : (number + 1));
}

