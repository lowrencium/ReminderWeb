$(document).ready(function() {
    //Ladda.bind('button[type=submit]', {timeout: 2000});

    $("#login").on("click", function() {
        console.log("login");
    });

    //EVENT NAVTAB
    $('#navCalendarEvent a').click(function(e) {
        e.preventDefault();
        $(this).tab('show');
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
    if (resultat.text() === "true" && erreur.text() === "")
    {
        var array = new Array();
        result.find("Rappels").find("item").each(function(index) {
            var debut = new Date($(this).find('Debut').text().trim() * 1000);
            var fin = new Date($(this).find('Fin').text().trim() * 1000);

            array.push(
                    {
                        //"id": $(this).find('Id').text(),
                        "title": $(this).find('Titre').text(),
                        "location": $(this).find('Lieu').text(),
                        "start": {
                            date: debut.getFullYear() + "" + getFullPartDate(debut.getMonth() ) + "" + getFullPartDate(debut.getDate()),
                            time: getFullPartDate(debut.getHours()) + ":" + getFullPartDate(debut.getMinutes())
                        },
                        "end": {
                            date: fin.getFullYear() + "" + getFullPartDate(fin.getMonth()) + "" + getFullPartDate(fin.getDate()).toString(),
                            time: (getFullPartDate(fin.getHours()) + ":" + getFullPartDate(fin.getMinutes())).toString()
                        }
                    }
            );
        });
        return array;
        
    }
    else
    {
        //Erreur
        console.log(erreur.text());
    }
}



function addRappel(id, sessionsId, title, location, begin, end)
{
    var result = SoapManager("CreerRappel", {"id": id, "token": sessionsId, "titre": title, "lieu": location, "debut": begin, "fin": end});
    var resultat = result.find("Resultat");
    var erreur = result.find("Erreur");
    if (resultat.text() == "true" && erreur.text() == "")
    {
        //Ok
    }
    else
    {
        //Erreur
    }
}

function removeRappel(id, sessionId, rappelId)
{
    var result = SoapManager("SupprimerRappel", {"id": id, "token": sessionId, "rappelId": rappelId});
    var resultat = result.find("Resultat");
    var erreur = result.find("Erreur");
    if (resultat.text() == "true" && erreur.text() == "")
    {
        //Ok
    }
    else
    {
        //Erreur
    }
}

function getContacts(id, sessionId)
{
    var result = SoapManager("RecupererContacts", {"id": id, "token": sessionId});
    var resultat = result.find("Resultat");
    var erreur = result.find("Erreur");
    if (resultat.text() == "true" && erreur.text() == "")
    {
        var array = new Array();
        result.find("Contacts").find("item").each(function(index) {
            array.push(
                {
                    "name": $(this).find('Nom').text(),
                    "email": $(this).find('Email').text(),
                    "phone": $(this).find('Telephone').text(),
                    "location": $(this).find('Adresse').text()
                }
            );
        });
        return array;
    }
    else
    {
        //Erreur
    }
}

function addContact(id, sessionId, name, email, phone, location)
{
    var result = SoapManager("AjouterContact", {"id": id, "token": sessionId, "nom": name, "email": email, "telephone": phone, "adresse": location});
    var resultat = result.find("Resultat");
    var erreur = result.find("Erreur");
    if (resultat.text() == "true" && erreur.text() == "")
    {
        //Ok
    }
    else
    {
        //Erreur
    }
}