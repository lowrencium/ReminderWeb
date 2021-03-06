﻿$(document).ready(function() {
    
    var idUser = 1;
    var sessionId = "token";

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
        $(this).css('margin-top', $(this).parent().height() - $(this).height() - 38);
    });
});

function getFullPartDate(number) {
    return (number < 10 ? "0" + number : number);
}

function getRappels()
{
    var result = SoapManager("RecupererRappel", {"id": localStorage.getItem("id"), "token": localStorage.getItem("token")});
    if(result != false)
    {
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
                            "id": $(this).find('Id').text(),
                            "title": $(this).find('Titre').text(),
                            "location": $(this).find('Lieu').text(),
                            "start": {
                                date: debut.getFullYear() + "" + getFullPartDate(debut.getMonth() + 1) + "" + debut.getDate(),
                                time: getFullPartDate(debut.getHours()) + ":" + (debut.getMinutes() + 1)
                            },
                            "end": {
                                date: fin.getFullYear() + "" + getFullPartDate(fin.getMonth() + 1) + "" + fin.getDate(),
                                time: getFullPartDate(fin.getHours()) + ":" + (fin.getMinutes() + 1)
                            }
                        }
                );
            });
            return array;

        }
        else
        {
            //Erreur
            console.log(erreur.html());
        }
    }
}



function addRappel(title, location, begin, end)
{
    var result = SoapManager("CreerRappel", {"id": localStorage.getItem("id"), "token": localStorage.getItem("token"), "titre": title, "lieu": location, "debut": begin, "fin": end});
    var resultat = result.find("Resultat");
    var erreur = result.find("Erreur");
    if (resultat.text() == "true" && erreur.text() == "")
    {
		
        return 1;
    }
    return 0;
}

function removeRappel(rappelId)
{
    var result = SoapManager("SupprimerRappel", {"id": localStorage.getItem("id"), "token": localStorage.getItem("token"), "rappelId": rappelId});
    var resultat = result.find("Resultat");
    var erreur = result.find("Erreur");
    if (resultat.text() == "true" && erreur.text() == "")
    {
		console.log("remove ok");
        return 1;
    }
    else
    {
		console.log("remove fail");
        return 0;
    }
}

function getContacts()
{
    var result = SoapManager("RecupererContacts", {"id": localStorage.getItem("id"), "token": localStorage.getItem("token")});
    if(result != false)
    {
        var resultat = result.find("Resultat");
        var erreur = result.find("Erreur");
        if (resultat.text() == "true" && erreur.text() == "")
        {
            var array = new Array();
            result.find("Contacts").find("item").each(function(index) {
                array.push(
                    {
                        "id": $(this).find('Id').text(),
                        "name": $(this).find('Nom').text(),
                        "email": $(this).find('Email').text(),
                        "phone": $(this).find('Telephone').text(),
                        "location": $(this).find('Adresse').text(),
                        "type": $(this).find('Type').text()
                    }
                );
            });
            return array;
        }
        else
        {
            console.log(erreur.html());
        }
    }
}

function addContact(name, email, phone, location)
{
    var result = SoapManager("AjouterContact", {"id": localStorage.getItem("id"), "token": localStorage.getItem("token"), "nom": name, "email": email, "telephone": phone, "adresse": location});
    var resultat = result.find("Resultat");
    var erreur = result.find("Erreur");
    if (resultat.text() == "true" && erreur.text() == "")
    {
        return 1;
    }
    else
    {
        return 0;
    }
}

function removeContact(contactId, type)
{
    var result = SoapManager("SupprimerContact", {"id": localStorage.getItem("id"), "token": localStorage.getItem("token"), "contactId": contactId, "type": type});
    var resultat = result.find("Resultat");
    var erreur = result.find("Erreur");
    if (resultat.text() == "true" && erreur.text() == "")
    {
        return 1;
    }
    else
    {
        return 0;
    }
}

function validateContact(contactId, bool)
{
    var result = SoapManager("ValiderContact", {"id": localStorage.getItem("id"), "token": localStorage.getItem("token"), "contactId": contactId, "valider": bool});
    var resultat = result.find("Resultat");
    var erreur = result.find("Erreur");
    if (resultat.text() == "true" && erreur.text() == "")
    {
        return 1;
    }
    else
    {
        return 0;
    }
}

function getContactRequest()
{
    var result = SoapManager("RecupererDemandesContact", {"id": localStorage.getItem("id"), "token": localStorage.getItem("token")});
    if(result != false)
    {
        var resultat = result.find("Resultat");
        var erreur = result.find("Erreur");
        if (resultat.text() == "true" && erreur.text() == "")
        {
            var array = new Array();
            result.find("Contacts").find("item").each(function(index) {
                array.push(
                    {
                        "id": $(this).find('Id').text(),
                        "name": $(this).find('Nom').text(),
                        "type": $(this).find('Type').text()
                    }
                );
            });
            return array;
        }
        else
        {
            console.log(erreur.html());
        }
    }
}

function getMyContactRequest()
{
    var result = SoapManager("RecupererMesDemandesContact", {"id": localStorage.getItem("id"), "token": localStorage.getItem("token")});
    if(result != false)
    {
        var resultat = result.find("Resultat");
        var erreur = result.find("Erreur");
        if (resultat.text() == "true" && erreur.text() == "")
        {
            var array = new Array();
            result.find("Contacts").find("item").each(function(index) {
                array.push(
                    {
                        "id": $(this).find('Id').text(),
                        "name": $(this).find('Nom').text(),
                        "type": $(this).find('Type').text()
                    }
                );
            });
            return array;
        }
        else
        {
            console.log(erreur.html());
        }
    }
}

function shareRappel(rappelId, contactId, contactType)
{
    var result = SoapManager("PartagerRappel", {"id": localStorage.getItem("id"), "token": localStorage.getItem("token"), "rappelId": rappelId, "contactId": contactId, "type": contactType});
    var resultat = result.find("Resultat");
    var erreur = result.find("Erreur");
    if (resultat.text() == "true" && erreur.text() == "")
    {
        return 1;
    }
    else
    {
        console.log(erreur.html());
        return 0;
    }
}

function useTemplates(tmpl, context) {
    var template = Handlebars.compile(tmpl);
    var html = template(context);
    return html;
}


/**
 * 
 * @param {type} button
 * @param {type} message
 * @returns {undefined}
 */
function buttonBehaviourSubmitDefault(button, message) {
    if(typeof message == "undefined"){
        message = "Valider";
    }
    $(button).removeClass("btn-danger");
    $(button).removeClass("btn-success");
    $(button).addClass("btn-primary");
    $(button).html(message);
}

/**
 * 
 * @param {button} button
 * @param {String} error
 * @returns {undefined}
 */
function buttonBehaviourSubmitError(button, error) {
    $(button).removeClass("btn-primary");
    $(button).removeClass("btn-success");
    $(button).addClass("btn-danger");
    $(button).html(error);
}

/**
 * 
 * @param {button} button
 * @param {String} success
 * @returns {undefined}
 */
function buttonBehaviourSubmitSuccess(button, success) {
    $(button).removeClass("btn-primary");
    $(button).removeClass("btn-danger");
    $(button).addClass("btn-success");
    $(button).html(success);
}

function buttonSubmitLoadStart(button) {
    var l = Ladda.create(button[0]);
    l.start();
    return l;
}

function buttonSubmitLoadStop(l) {
    l.stop();
}

function NotificationCenter($scope) {
    var permissionLevels = {};
    permissionLevels[notify.PERMISSION_GRANTED] = 0;
    permissionLevels[notify.PERMISSION_DEFAULT] = 1;
    permissionLevels[notify.PERMISSION_DENIED] = 2;

    $scope.isSupported = notify.isSupported;
    $scope.permissionLevel = permissionLevels[notify.permissionLevel()];

    $scope.getClassName = function() {
        if ($scope.permissionLevel === 0) {
            return "allowed"
        } else if ($scope.permissionLevel === 1) {
            return "default"
        } else {
            return "denied"
        }
    }

    $scope.callback = function() {
        console.log("test");
    }

    $scope.requestPermissions = function() {
        notify.requestPermission(function() {
            $scope.$apply($scope.permissionLevel = permissionLevels[notify.permissionLevel()]);
        })
    }
}

function reloadPage(){
    setTimeout(function(){document.location.reload(true)}, 1000);
}