<?php
require '../inc/db.php';
function uploadFile($file, $db): void {
    $newData = file_get_contents( "php://input" );
    $formData = json_decode($newData);
    $formArray = (array) $formData;
    $files = $_FILES;
    print_r('newData -> '."\n");
    var_dump($newData);

    print_r('formData -> '."\n");
    var_dump($formData);

    print_r('$formArray[uploadFile] -> '."\n");
    var_dump($formArray['uploadFile']);

    print_r('$_FILES -> '."\n");
    var_dump($_FILES);
    // header('Location: ../index.php');
}
uploadFile($_FILES,$db);
