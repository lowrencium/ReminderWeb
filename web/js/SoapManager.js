/**
 * @param string fonction Fonction soap à appeler
 * @param object donnees Données à passer au webservice
 */
function SoapManager(fonction, donnees) {
    $.soap({
        url: 'http://remindme-webservice.amineamanzou.com/Server.php/',
        method: fonction,
        data: donnees,
        success: function (soapResponse) {
            console.log(soapResponse);
            return soapResponse;
        },
        error: function (soapResponse) {
            return soapResponse;
        }
    });
}