<?php
require '../inc/db.php';
//  JSON – Ausgabe

$target_dir = "../";
function outputJSON($msg, $status = 'error'): void
{
    header('Content-Type: application/json');
    die(json_encode(array(
        'data' => $msg,
        'status' => $status
    )));
    exit();
}

/** Über alle Dateien laufen
 * und den Pfad für den Dateispeicher setzen
 **/


if (isset($_POST['magazine']) && isset($_POST['issue']) && isset($_POST['year']) && isset($_POST['article'])) {
    $magazine = $_POST['magazine'];
    $issue = $_POST['issue'];
    $year = $_POST['year'];
    $contentId = $_POST['article'];
    $listLayoutQuery = 'SELECT * FROM `pages` WHERE `magazine_id` = "'.$magazine.'" AND `issue_id` = "'.$issue.'" AND `content_id` = "'.$contentId.'" AND `year` = "'.$year.'";';
    $layoutUpdate = $db->query($listLayoutQuery);
    $isFilledResponse = $layoutUpdate -> {'num_rows'};
    if($isFilledResponse) {
        while($singleLayout = $layoutUpdate->fetch_assoc()) {
            $thumb = utf8_encode($singleLayout['background_image']);
            $layout = utf8_encode($singleLayout['layout_file']);
            if(!empty($thumb)) {
                $jpeg = str_replace('-thumb-', '-', $thumb);
                print_r($thumb.' jpeg -> '.$jpeg);
                unlink($target_dir.$jpeg);
                unlink($target_dir.$thumb);
            }
            if(!empty($layout)) {
                print_r($layout);
                unlink($target_dir.$layout);
            }
        }
        $updateLayoutQuery = 'UPDATE `pages` SET `background_image`=NULL, `layout_file`=NULL WHERE `magazine_id` = "'.$magazine.'" AND `issue_id` = "'.$issue.'" AND `content_id` = "'.$contentId.'" AND `year` = "'.$year.'";';
        print_r($updateLayoutQuery);
        $deleteLayout = $db->query($updateLayoutQuery);
        $isFilledResponse = $db->affectedRows;
        print_r($isFilledResponse);
        exit();
    } else {
        $db->close();
    }
}