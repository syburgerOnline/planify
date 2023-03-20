<?php
function createMd5($data): void
{
    $newData = file_get_contents( "php://input" );
    $formData = json_decode($newData);
    $result = md5($formData);
    print(json_encode($result));

}

createMd5($_POST);