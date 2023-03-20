<?php
require '../inc/db.php';
function createNewUser($data, $db): void
{
    $newData = file_get_contents( "php://input" );
    $formData = json_decode($newData);
    //print_r('createNewUser -> '.json_decode($newData));
    $result = [];
    $result['error'] = '2';

    foreach($formData AS $key => $queryStr){
        // $result[] = utf8_decode($queryStr);
        $db->query(utf8_decode($queryStr));
    }
    $result['error'] = !empty($db->error) ? $db->error : '0';
    $result['affectedRows'] = !empty($db->affected_rows) ? $db->affected_rows : '0';
    print(json_encode($result));
    $db->close();

}

createNewUser($_POST, $db);

/*
function createNewUser($db, $formData):void {
    $returnValue = [];
    $returnValue['error'] = 0;
    foreach($formData AS $key => $val) {
        $tmpValue = [];
        $tmpValue[$key] = $val;
        $returnValue[] = $tmpValue;
    }
    $name = $formData['name'];
    $email = $formData['email'];
    $password = $formData['password'];
    $permissions = $formData['permissions'];
    $userQuery = 'INSERT INTO `fe_user` (`name`, `email`,`password`,`permissions`) VALUES ('.$name.','.$email.','.$password.','.$permissions.');';
    $returnValue['query'] = $userQuery;
    $db->query($userQuery);
    $returnValue['error'] = $db->affected_rows;

    $jsResult = json_encode($returnValue);
    print_r($jsResult);
}
function createNewUserPermission () {
    $userPermission = [];
    $user = [];
    $magazines = $GLOBALS['magazineArray'];
    for($i = 1; $i<10; $i++ ) {
        $permission = 'userPermission_'.$i;
        $tmpPermission = [];
        $tmpPermission[$permission] = number_format($_POST[$permission]);
        $userPermission[] = $tmpPermission;
    }
    $formData = array(
        'name' => $_POST["userName"],
        'email' => $_POST["email"],
        'password' => $_POST["password"],
        'permissions' => json_encode($userPermission),
    );
    createNewUser($db, $formData);
}
*/