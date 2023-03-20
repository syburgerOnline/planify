<?php
function createHeader(): void
{
    echo '<head>
    <link rel="stylesheet" href="css/icons.css">
    <link rel="stylesheet" href="css/notification.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/login.css">
    <link rel="stylesheet" href="css/dialog.css">
    <link rel="stylesheet" href="css/toolbox.css">
    <link rel="stylesheet" href="css/ad.css">
    <link rel="stylesheet" href="css/infoPanel.css">
    <link rel="stylesheet" href="css/statusbar.css">
    <link rel="stylesheet" href="css/print.css">
    <!--
    favicon
    -->
    <link rel="apple-touch-icon" sizes="57x57" href="img/favicon/apple-icon-57x57.png">
    <link rel="apple-touch-icon" sizes="60x60" href="img/favicon/apple-icon-60x60.png">
    <link rel="apple-touch-icon" sizes="72x72" href="img/favicon/apple-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="76x76" href="img/favicon/apple-icon-76x76.png">
    <link rel="apple-touch-icon" sizes="114x114" href="img/favicon/apple-icon-114x114.png">
    <link rel="apple-touch-icon" sizes="120x120" href="img/favicon/apple-icon-120x120.png">
    <link rel="apple-touch-icon" sizes="144x144" href="img/favicon/apple-icon-144x144.png">
    <link rel="apple-touch-icon" sizes="152x152" href="img/favicon/apple-icon-152x152.png">
    <link rel="apple-touch-icon" sizes="180x180" href="img/favicon/apple-icon-180x180.png">
    <link rel="icon" type="image/png" sizes="192x192"  href="img/favicon/android-icon-192x192.png">
    <link rel="icon" type="image/png" sizes="32x32" href="img/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="96x96" href="img/favicon/favicon-96x96.png">
    <link rel="icon" type="image/png" sizes="16x16" href="img/favicon/favicon-16x16.png">
    <link rel="manifest" href="img/favicon/manifest.json">
    <!--<meta name="viewport" content="width=device-width, initial-scale=1" /> --> 
    <meta name="msapplication-TileColor" content="#6cad38">
    <meta name="msapplication-TileImage" content="img/favicon/ms-icon-144x144.png">
    <meta name="theme-color" content="#6cad38">
    <!--
    /favicon
    -->
    <!-- 
    fontawesome
    -->
    <link href="fonts/fontawesome/css/fontawesome.css" rel="stylesheet">
    <link href="fonts/fontawesome/css/brands.css" rel="stylesheet">
    <link href="fonts/fontawesome/css/solid.css" rel="stylesheet">
    <!-- 
    /fontawesome
    -->
    <script src="js/magazines.js"></script>
    <script src="js/dragdrop.js"></script>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta http-equiv="cache-control" content="max-age=0" />
    <meta http-equiv="cache-control" content="no-cache" />
    <meta http-equiv="pragma" content="no-cache" />
    </head>';
}
function createHeaderArea(): void {
    echo '<div class="header">
            <div class="header-container" id="headerContainer">
            <img src="img/logo.png" alt="Planify @Syburger" class="logo">
            </div>
            </div>';
}