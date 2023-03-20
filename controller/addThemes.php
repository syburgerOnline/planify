<?php
require '../inc/db.php';
function createNewDatabaseTheme($data, $db): void
{
    $newData = file_get_contents( "php://input" );
    $formData = json_decode($newData);
    // print_r('createNewDatabaseTheme -> '.json_decode($newData).$db);
    foreach($formData AS $key => $queryStr){
        // print_r($queryStr."\n");
        $db->query(utf8_decode($queryStr));
    }
    print_r($db->affected_rows);
}

createNewDatabaseTheme($_POST, $db);