function showNotification(title, message, icon) {
    notify.createNotification(title, {body: message, icon: icon})
}

$(document).ready(function() {
    var rappels = getRappels(1, "token");

    setInterval(function()
    {
        for(var i = 0; i < rappels.length; i++)
        {
            var now = new Date();

            var start = new Date(rappels[i].start.date.substr(0, 4), rappels[i].start.date.substr(4, 2) - 1, rappels[i].start.date.substr(6, 2), rappels[i].start.time.substr(0, 2), rappels[i].start.time.substr(3, 2), 0);
            var end = new Date(rappels[i].end.date.substr(0, 4), rappels[i].end.date.substr(4, 2) - 1, rappels[i].end.date.substr(6, 2), rappels[i].end.time.substr(0, 2), rappels[i].end.time.substr(3, 2), 0);

            if(start.getTime() <= now.getTime() && start.getTime() > now.getTime() - 10000)
            {console.log("ok");
                var start = start.getDate() +"/"+(start.getMonth() + 1) +"/"+start.getFullYear()+" "+getFullPartDate(start.getHours()) + ":" + getFullPartDate(start.getMinutes());
                var end = end.getDate() +"/"+ (end.getMonth() + 1) +"/"+end.getFullYear()+" "+getFullPartDate(end.getHours()) + ":" + getFullPartDate(end.getMinutes());

                showNotification("Rappel en cours : "+rappels[i].title, rappels[i].title+"\nDebut : "+start+"\nFin : "+end, "images/warning.ico");
            }

            rappels.splice(i, 1);
            i--;
        }
    }, 10000);
});

function showCurrentRappel()
{
    var rappels = getRappels(1, "token");

    for(var i = 0; i < rappels.length; i++)
    {
        var now = new Date();

        var start = new Date(rappels[i].start.date.substr(0, 4), rappels[i].start.date.substr(4, 2) - 1, rappels[i].start.date.substr(6, 2), rappels[i].start.time.substr(0, 2), rappels[i].start.time.substr(3, 2), 0);
        var end = new Date(rappels[i].end.date.substr(0, 4), rappels[i].end.date.substr(4, 2) - 1, rappels[i].end.date.substr(6, 2), rappels[i].end.time.substr(0, 2), rappels[i].end.time.substr(3, 2), 0);

        if(start.getTime() <= now.getTime() && end.getTime() > now.getTime())
        {
            var start = start.getDate() +"/"+(start.getMonth() + 1) +"/"+start.getFullYear()+" "+getFullPartDate(start.getHours()) + ":" + getFullPartDate(start.getMinutes());
            var end = end.getDate() +"/"+ (end.getMonth() + 1) +"/"+end.getFullYear()+" "+getFullPartDate(end.getHours()) + ":" + getFullPartDate(end.getMinutes());

            showNotification("Rappel en cours : "+rappels[i].title, rappels[i].title+"\nDebut : "+start+"\nFin : "+end, "images/warning.ico");
        }

        rappels.splice(i, 1);
        i--;
    }
}