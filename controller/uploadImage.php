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
function outputJSONSolo($msg, $status = 'error'): void
{
    // header('Content-Type: application/json');
    echo(json_encode(array(
        'data' => $msg,
        'status' => $status
    )));
}

/**
 * @throws ImagickException
 */
function convertPdfToJpeg($target, $target_dir, $target_name, $user, $db): void
{
    $magazine = $user['magazine'];
    $issue = $user['issue'];
    $year = $user['year'];
    // print_r('convertPdfToJpeg -> '.$target.' to '.$target_name.' with new extension php'."\n" );
    $imagick = new Imagick();
    // $imagick->setResolution (400, 800);
    $imagick->setBackgroundColor('white');
    $imagick->readImage($target);
    $imageCount = $imagick->getNumberImages();
    // $layOutOk = true;
    if($user['affectedPages'] != $imageCount) {
        $pageId = $user['pageId'];
        $updateLayoutQuery = 'UPDATE `pages` SET `layout_file` = NULL WHERE `magazine_id` = "'.$magazine.'" AND `issue_id` = "'.$issue.'"  AND `page_id` = "'.$pageId.'" AND `year` = "'.$year.'";';
        $layoutUpdate = $db->query($updateLayoutQuery);
        // print_r($layoutUpdate.' try to delete -> '.$target);
        if($layoutUpdate) {
            unlink($target);
        }
        if($user['affectedPages'] < $imageCount) {
            // print_r('Das Layout hat zuviele Seiten.'."\n");
            outputJSON('pageAmountToHigh');
        }else {
            // print_r('Das Layout hat zuwenig Seiten.'."\n");
            outputJSON('pageAmountToSmall');
        }
        exit();
    }
    $layoutConverted = 0;
    for($i = 0; $i < $user['affectedPages']; $i++) {
        $pageId = $user['pageId'] + $i;
        $fileName = $target.'['.$i.']';
        $jpegName = $target_dir.$target_name.'-'.$i.'.jpg';
        $thumbnailName = $target_dir.$target_name.'-thumb-'.$i.'.jpg';
        $baseName = str_replace('../', '', $jpegName);
        $baseThumbnailName = str_replace('../', '', $thumbnailName);
        /**
         * update Database
         */
        $updateBackgroundQuery = 'UPDATE `pages` SET `background_image` = "'.$baseThumbnailName.'" WHERE `magazine_id` = "'.$magazine.'" AND `issue_id` = "'.$issue.'"  AND `page_id` = "'.$pageId.'" AND `year` = "'.$year.'";';
        $layoutUpdate = $db->query($updateBackgroundQuery);

        $imagickSingle = new Imagick();

        $imagickSingle->setBackgroundColor('white');
        $imagickSingle->readImage($fileName);
        $imagickSingle->setImageAlphaChannel(Imagick::ALPHACHANNEL_REMOVE );
        $imagickSingle->setImageResolution (72, 72);
        $imagickSingle->resampleImage  (72,72,Imagick::FILTER_UNDEFINED,1);
        $imagickSingle->setImageFormat('jpg');

        $imagickSingle->writeImages($jpegName, false);
        $imagickSingle->thumbnailImage(290, (int)(290 * $imagick->getImageHeight() / $imagick->getImageWidth()));
        $imagickSingle->writeImages($thumbnailName, false);
        if($layoutUpdate) {
            $layoutConverted ++;
        }
    }
    $db->close();
    outputJSON('allFilesWritten');
    /*if($layoutConverted == $user['affectedPages']) {
        $db->close();
        outputJSON('allFilesWritten');
    }*/
}
/** Über alle Dateien laufen
 * und den Pfad für den Dateispeicher setzen
 **/


if (isset($_FILES['photos']['name'])) {
    $user = array (
        'email' => $_POST["email"],
        'name' => $_POST["name"],
        'year' => $_POST["actualYear"],
        'magazine' => $_POST["magazines"],
        'issue' => $_POST["issue"],
        'pageId' => $_POST["pageId"],
        'affectedPages' => $_POST["affectedPages"],
    );
    // var_dump($user);

    $magazineDirectory = $target_dir.$_POST["magazines"];
    $yearDirectory = $magazineDirectory.'/'.$_POST["year"];
    $issueDirectory = $yearDirectory.'/'.$_POST["issue"];
    if (!is_dir($magazineDirectory)) {
        mkdir($magazineDirectory, 0777, true);
    }
    if (!is_dir($yearDirectory)) {
        mkdir($yearDirectory, 0777, true);
    }
    if (!is_dir($issueDirectory)) {
        mkdir($issueDirectory, 0777, true);
    }
    $target_dir = $issueDirectory.'/';
    // print_r('FilePath -> '.$target_dir."\n");
    $fileContainer = $_FILES['photos'];
    $total_files = count($fileContainer['name']);
    $original_filenames = array();
    $target = '';

    for ($key = 0; $key < $total_files; $key++) {

        // Check if file is selected
        if (isset($fileContainer['name'][$key]) && $fileContainer['size'][$key] > 0) {
            $fileType = $fileContainer['type'][$key];
            // Check filetype

            if ($fileType == 'image/png' || $fileType == 'image/jpg') {
                if (!getimagesize($fileContainer['tmp_name'][$key])) {
                    outputJSON('fileIsNoImage');
                }
            } else if ($fileType != 'application/pdf') {
                outputJSON('fileTypeNoPdf');
            }

            // Check filesize
            if ($fileContainer['size'][$key] > 500000000) {
                outputJSON('fileSizeToHigh');
            }

            // Check ob der Dateiname bereits existiert
            $formattedFileName = str_replace(['%20', ' '],'_',$fileContainer['name'][$key]);
            if (file_exists($target_dir . $formattedFileName)) {
                // outputJSON('fileAlreadyExist');
            }

            $original_filename = $formattedFileName;

            $target = $target_dir . basename($original_filename);
            $target_name = pathinfo($target)['filename'];
            $tmp = $fileContainer['tmp_name'][$key];
            $original_filenames[] = $original_filename;
            move_uploaded_file($tmp, $target);
            $magazine = $user['magazine'];
            $issue = $user['issue'];
            $pageId = $user['pageId'];
            $year = $user['year'];
            $baseName = str_replace('../', '', $target);
            $updateLayoutQuery = 'UPDATE `pages` SET `layout_file` = "'.$baseName.'" WHERE `magazine_id` = "'.$magazine.'" AND `issue_id` = "'.$issue.'" AND `page_id` = "'.$pageId.'" AND `year` = "'.$year.'";';
            $layoutUpdate = $db->query($updateLayoutQuery);
            // var_dump($layoutUpdate);
            // outputJSONSolo('uploadReady');
            if($layoutUpdate) {

                try {
                    convertPdfToJpeg($target, $target_dir, $target_name, $user, $db);
                } catch (ImagickException $e) {
                }
            }

        }
    }

    // outputJSON($total_files . implode(',', $original_filenames). ' Dateien in das Verzeichnis UPLOADS geladen ', 'OK');

}