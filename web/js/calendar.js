$(document).ready(function() {

    events = [{
            id: 12,
            title: "Faire le projet Commun",
            location: "A la maison",
            start: {
                date: "20140601", time: "17:00"
            },
            end: {
                date: "20140816", time: "17:00"
            }
        },
        {
            id: 13,
            title: "Partiels",
            location: "A la maison",
            start: {
                date: "20140610", time: "17.00"
            },
            end: {
                date: "20140623", time: "17.00"
            }
        },
        {
            id: 14,
            title: "Partiels",
            location: "A la maison",
            start: {
                date: "20140610", time: "17.00"
            },
            end: {
                date: "20140623", time: "17.00"
            }
        }
    ];

    contacts = [{
            id: 1,
            nom: "didier jacob",
            mail: "dj@gmail.com"
        }, {
            id: 2,
            nom: "Jean Noel",
            mail: "jn@gmail.com"
        }, {
            id: 3,
            nom: "Fred Lamouche",
            mail: "fl@tele2.fr"
        }
    ];

    var selectedDate;

    //CALENDAR & EVENT
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


    $('button#shareEvent').on('click', function(e)
    {
        var dayEvents = getDayEvents(selectedDate);
        var shareContentModal = $("#shareEventContent #tableShareEvents tbody");
        var buttonShareEvent = $("button#do_shareEvent");
        var eventsTable = $("div#shareEventsTable");
        var contactsShare = $("div#shareEventsContacts");
        e.preventDefault();
        contactsShare.hide();
        eventsTable.show();
        
        shareContentModal.empty(); //cleaning before append

        for (var i = 0; i < dayEvents.length; i++) {
            var templateEvent = getRowEvent(dayEvents[i]);
            shareContentModal.append(templateEvent);
        }

        
        buttonShareEvent.html('Passer à la sélection des contacts');

        $(buttonShareEvent).on("click", function(e) {
            e.preventDefault();
            
            
            eventsTable.hide();
            
            contactsShare.fadeIn();
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
        var rawStartDate = new Date($(this.startDate).val());
        var rawEndDate = new Date($(this.endDate).val());
        if (startDate > endDate) {
            var errorMsg = "La date de fin doit être supérieure ou égale à la date de début";
            var templateError = '<p class="bg-danger col-xs-12">' + errorMsg + '</p>';
            $("#contentAddEvent").prepend(templateError);
        }
        else {
//            var startDate = getFancyDate(rawStartDate);
//            var endDate = getFancyDate(rawEndDate);
//            var startHours = getRawHours(rawStartDate);
//            var endHours = getRawHours(rawEndDate);
            var title = $(this.title).val();
            var location = $(this.location).val();

            location.reload();
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
        location.reload();
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

function getListContactsForSharing(){
    
}