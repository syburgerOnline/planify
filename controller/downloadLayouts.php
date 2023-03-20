<?php
require '../inc/db.php';
//  JSON – Ausgabe

$target_dir = "../uploads/images/";
function outputJSON($msg, $status = 'error'): void
{
    header('Content-Type: application/json');
    die(json_encode(array(
        'data' => $msg,
        'status' => $status
    )));
    exit();
}

if (isset($_POST['magazineId'])) {
    $magazineId = $_POST['magazineId'];
    $issueId = $_POST['issueId'];
    $year = $_POST['year'];
    // var_dump($user);
    $result = '';

    $target_dir = $target_dir.$magazineId.'/'.$year.'/'.$issueId.'/';
    /**
     * TODO !!! add year to database
     * AND `year` = "'.$year.'"
     */
    $downloadLayoutQuery = 'SELECT * FROM `pages` WHERE `magazine_id` = '.$magazineId.' AND `issue_id` = '.$issueId.';';
    $downloadUpdate = $db->query($downloadLayoutQuery);
    $isFilledResponse = $downloadUpdate -> {'num_rows'};
    // $pdfString = '';
    $pdfArray = array();
    if($isFilledResponse) {
        // echo('<br><br><label for="issue">Ausgabe:</label><br>');
        // echo('<select name="issue" id="issue" onchange="javascript:this.form.submit()">');
        while ($singleLayout = $downloadUpdate->fetch_assoc()) {
            if($singleLayout['layout_file']) {
                $pdfFile = utf8_encode($singleLayout['layout_file']);
                // $pdfString .= $pdfFile."\n";
                $pdfArray[] = '../'.$pdfFile;
            }
        }
    } else {
        outputJSON('noLayoutsFound','error');
    }
    /**
     * Pack files to zip
     */
    $zip = new ZipArchive();
    $zipFileName = '../downloads/layouts_'.$issueId.'_'.$magazineId.'_'.$year.'.zip';
    $zip->open($zipFileName, ZipArchive::CREATE);
    foreach ($pdfArray as $key => $pdfFile) {
        if($pdfFile) {
            $zip->addFile($pdfFile, pathinfo($pdfFile)['basename']);
        }
    }
    $zip->close();

    $result .= $target_dir; // .$downloadUpdate;
    if($downloadUpdate) {
        outputJSON("controller/download.php?path=".$zipFileName,'success');
    }
}