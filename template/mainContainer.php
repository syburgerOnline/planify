<?php

function createMainContainer($db): void
{
    $magazineQuery = "SELECT * FROM `magazine` ORDER BY `magazine`.`position` ASC";
    $magazineDetails = $db->query($magazineQuery);
    echo '<div class="container" id="container" ondrop="drop(event)" ondragover="allowDrop(event)">';
    createHeaderArea();
    echo '<div class="main" id="mainContainer">';
    if($_SESSION['user']) {
        echo '<div class="user-field" id="user">
                <h1>Hallo '.$_SESSION['user']['name'].'</h1>
                <input type="hidden" name="userFieldEmail" id="mainUserEmail" value="'.$_SESSION['user']['email'].'">
                <input type="hidden" name="userFieldName" id="mainUserName" value="'.$_SESSION['user']['name'].'">
                </div>';
    }
    createNavigation($db, $magazineDetails);
    createStage();
        echo '<div class="footer" id="footer">';
        if($_POST["magazines"] > 0 && $_POST["issue"] > 0) {
            echo createPageStatusBar();
        }
        echo '</div>';
    echo '</div>';
    if($_POST["magazines"] && $_POST["issue"]) {
        // createGlobalArticleArray($db, $_POST["magazines"], $_POST["issue"]);
        // echo createDialogElements($_POST["magazines"], $_POST["issue"], $_COOKIE);
        if($_POST["issue"] > 0) {
            echo createToolBox($_COOKIE);
            echo createInfoPanel();
            echo createNotificationTextField(getNotificationGroup($db, $_POST["magazines"], $_POST["issue"]));
            echo createDialogElements($_POST["magazines"], $_POST["issue"], $_COOKIE); // CHECK!!!
            echo createPrintJobContainer($_COOKIE);
        }
        echo '<script type="text/javascript">initGlobalDragElements();</script>';
    } else if ($_POST["magazines"] > 0) {
        $GLOBALS['notificationGroup'] = getNotificationGroup($db, $_POST["magazines"], $_POST["issue"]);
        echo createDialogElements($_POST["magazines"],null, $_COOKIE);
        echo createNotificationTextField(getNotificationGroup($db, $_POST["magazines"], $_POST["issue"]));
    }
    echo '<div class="layer" id="overLayer">';
    echo '<iframe id="downloadFrame" style="display:none"></iframe>';
    createImageViewer();
    echo '</div></div>';
}