<!DOCTYPE html> 
<html>
<head>
    <title>RemindMe</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, maximum-scale=1.0">

    <link href="css/flatly.min.css" rel="stylesheet">
    <link href="//netdna.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css" rel="stylesheet">
    <link href="css/general.css" rel="stylesheet" type="text/css">
    <link href="css/contacts.css" rel="stylesheet" type="text/css">

    <script src="http://code.jquery.com/jquery-1.10.2.min.js"></script>
    <script src="http://netdna.bootstrapcdn.com/bootstrap/3.0.0/js/bootstrap.min.js"></script>
    <script src="http://ericwenn.se/kalendar/free/"></script>
    <!-- JavaScrip Search Plugin -->
    <script src="//rawgithub.com/stidges/jquery-searchable/master/dist/jquery.searchable-1.0.0.min.js"></script>
    <script type="text/JavaScript" src="js/sha512.js"></script> 
    <script type="text/JavaScript" src="js/forms.js"></script> 
    <script src="js/jquery.confirm.min.js"></script>
    <script src="js/functions.js"></script>
</head>
<body>
    <?php include_once('top_menu.html'); ?>
    <div class="container">
        <div class="row">
            <div class="col-md-2">
                <?php include_once('left-menu.html');?>
            </div>
            <div id="ajaxContent" class="col-md-10 well"></div>
        </div>
    </div>
</body>
</html>