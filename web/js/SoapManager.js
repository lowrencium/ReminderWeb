/**
 * @param string fonction Fonction soap à appeler
 * @param object donnees Données à passer au webservice
 */
function SoapManager(fonction, donnees) {
    var result = $.soap({
        url: 'http://remindme-webservice.amineamanzou.com/Server.php/',
        method: fonction,
        data: donnees,
        success: function (soapResponse) {
            return soapResponse;
        },
        error: function (soapResponse) {
            return soapResponse;
        }
    });

    var xml = result.responseText;
    xml = $(xml);
    return xml.find("return");
}