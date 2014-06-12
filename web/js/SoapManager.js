$(document).ready(function() {
    /**
     *
     * @param string fonction Fonction soap à appeler
     * @param object donnees Données à passer au webservice
     */
    function SoapManager(fonction, donnees) {
        $.soap({
            url: 'http://remindme-webservice.amineamanzou.com/Server.php/',
            method: fonction,
            data: donnees,
            success: function (soapResponse) {
                return $.parseXML(soapResponse.content);
            },
            error: function (soapResponse) {
                if(typeof soapResponse !== 'undefined') {
                    return $.parseXML(soapResponse.content);
                } else {
                    return false;
                }
            }
        });
    }
});