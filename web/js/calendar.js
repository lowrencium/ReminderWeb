$(document).ready(function() {

    var idUser = 1;
    var tokenUser = "token";

    events = getRappels(idUser, tokenUser);

    var selectedDate;

    var options = {
        onDayShow: function(e) {
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


    $('button#shareEvent').on('click', function(e)
    {
        var dayEvents = getDayEvents(selectedDate);
        var shareContentTableModal = $("#shareEventContent #tableShareEvents tbody");
        var shareEventsContacts = ("#shareEventContent #shareEventsContacts tbody")
        var buttonShareEvent = $("button#do_shareEvent");
        var eventsTable = $("div#shareEventsTable");

        e.preventDefault();

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

    $("#formAddEvent").on("submit", function(e) {
        e.preventDefault();
        var startDate = new Date($(this.startDate).val());
        var endDate = new Date($(this.endDate).val());
        if (startDate > endDate) {
            var errorMsg = "La date de fin doit être supérieure ou égale à la date de début";
            var templateError = '<p class="bg-danger col-xs-12">' + errorMsg + '</p>';
            $("#contentAddEvent").prepend(templateError);
        }
        else {
            var title = $(this.title).val();
            var location = $(this.location).val();

            document.location.reload();
        }

    });

    $("#do_deleteEvent").on("click", function(e) {
        e.preventDefault();
        $("input:checkbox:checked").each(function()
        {
            //remove from the DOM
            $(this).closest("tr").remove();
            //Launch WS to remove an event

        });
        document.location.reload();
    });
});



// return events of the day
function getDayEvents(datePicked) {
    var dayEvents = [];
    //Convert into date
    datePicked = new Date(datePicked);
    for (var i = 0; i < events.length; i++) {
        var startDate = events[i].start.d;
        var endDate = events[i].end.d;
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
    var checkbox = '<input id=' + event.id + ' type="checkbox">';
    var title = event.title;
    var startDate = getFancyDate((event.start.d));
    var endDate = getFancyDate(event.end.d);
    var location = event.location;
    var templateEvent = "<tr><td>" + checkbox + "</td><td>" + title + "</td><td>" + startDate + "</td><td>" + endDate + "</td><td>" + location + "</td></tr>";
    return templateEvent;
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

