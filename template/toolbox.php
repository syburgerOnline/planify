<?php
function createToolBox($cookie): string
{
    // $openAction = "addArticle('article')";
    $toolbarVisibility = checkForUserPermissions($_POST['magazines'], [2, 3, 4, 5, 6]) ? 'display: block;' : 'display: none;';
    $left = explode(',', $cookie['toolbox'])[0];
    $top = explode(',', $cookie['toolbox'])[1];
    $style = $cookie['toolbox'] ? ' style="left:'.$left.'px ; top:'.$top.'px ;'.$toolbarVisibility.'"' : ' style="left:90%; '.$toolbarVisibility.'"';
    // $printAction = "openClose('editDialog', 'open', 'addUser', 'User Verwaltung')";
    $userPermissionSavePage = checkForUserPermissions($_POST['magazines'], [3, 4, 5, 6]);
    $userPermissionAddArticle = checkForUserPermissions($_POST['magazines'], [5, 6]);
    $userPermissionAddAd = checkForUserPermissions($_POST['magazines'], [5, 6]);
    $userPermissionPrintAd = checkForUserPermissions($_POST['magazines'], [3, 6]);
    $userPermissionDownloadLayouts = checkForUserPermissions($_POST['magazines'], [2, 6]);
    $addArticle = $userPermissionAddArticle ? createTool('addArticle', "addArticle('addArticle')",'mouseover', 'draggable', false) : '';
    $addAd = $userPermissionAddAd ? createTool('addAd', "addAd('addAd')",'click', 'none', true) : '';
    $savePage = $userPermissionSavePage ? createTool('saveArticle', "saveArticle(".$_POST["magazines"].", ".$_POST["issue"].", true)",'click', 'none', false) : '';
    $printAd = $userPermissionPrintAd ? createTool('printAd', "printAd(".$_POST["magazines"].", ".$_POST["issue"].")",'click', 'none', false) : '';
    $downloadLayouts = $userPermissionDownloadLayouts ? createTool('download', "downloadLayouts(".$_POST["magazines"].", ".$_POST["issue"].", ".$_POST["year"].")",'click', 'none', false) : '';
    return '<div class="toolbox" id="toolbox" draggable="true" ondragstart="drag(event)" ontouchstart="drag(event)" '.$style.'>'.
                '<div class="toolbox-header padding">'.
                    '<span>'.$GLOBALS['toolboxHeader'].'</span>'.
                '</div>'.
                '<div class="toolbox-actions padding">'.
                    '<span>'.
                    $addArticle.
                    $addAd.
                    // createTool('editArticle', "editArticle('editArticle')", 'mouseover', 'draggable').
                    // createTool('editText', "editText()", 'mouseover').
                    // createTool('notify', "notify(".$_POST["magazines"].", ".$_POST["issue"].")",'click', 'none', false).
                    $savePage.
                    $printAd.
                    $downloadLayouts.
                    '</span>'.
                '</div>'.
            '</div>';
}