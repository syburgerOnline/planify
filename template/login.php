<?php
function checkForUserPermissions($permissionKey, $userGroup = array()) : bool
{
    $permission = false;
    $permissionsArray = $_SESSION['user']['permissions'];
    // print_r('checkForPermission -> '.$permissionKey.'at value -> '.$minValue);
    if($permissionKey > 0) {
        $checkValue = 'userPermission_'.$permissionKey;
        foreach($permissionsArray AS $key => $val) {
            if($checkValue == $val[0] && in_array($val[1],$userGroup)) {
                // print_r('$checkValue-> '.$checkValue.' $val[0] -> '.$val[0].' $val[1] -> '.$val[1].' is in? -> '.in_array($val[1],$minValue).'<br>');
                $permission = true;
            }
        }
    } else {
        $permission = true;
    }

    return $permission;
}

function checkUser($db) :array
{
    $loggedIn = [];
    $user = [];
    $passwordCorrect = false;
    $notificationGroupArray = $GLOBALS['loginArray'];
    $userQuery = 'SELECT * FROM `fe_user` WHERE `email` = "'.$_POST["email"].'";';
    // print_r($userQuery);
    $userDetails = $db->query($userQuery);
    $isFilledResponse = $userDetails -> {'num_rows'};
    $userRights = 0;
    if($isFilledResponse) {

        while ($userSingleDetails = $userDetails->fetch_assoc()) {
            $user['name'] = utf8_encode($userSingleDetails['name']);
            $user['email'] = utf8_encode($userSingleDetails['email']);
            $user['password'] = $userSingleDetails['password'];
            $user['lastLogin'] = date($userSingleDetails['last_login']);
            $permissions = str_replace(['{', '}'],[''], $userSingleDetails['permissions']);
            $permissionsArray = explode(',', $permissions);
            $user['permissions'] = [];
            foreach($permissionsArray AS $key => $val) {
                $cnt = explode(':', $val);
                $user['permissions'][] = [$cnt[0], number_format($cnt[1])];
                $userRights += number_format($cnt[1]);
                $permission = number_format($cnt[1]);
                $notificationGroupString = $notificationGroupArray[$permission]['notificationGroup'];
                $user['permissionGroup'] = $notificationGroupString;
            }

            // print_r($user['email'].' all Permissions-sum -> '.$userRights);
        }
        // var_dump($user['permissions']);
        $GLOBALS['user'] = $user;

        $md5Password = md5($_POST["password"]);
        $passwordCorrect = $user['password']==$md5Password;
        $loggedIn['user'] = $user;
        $date = date("Y-m-d H:i:s");
        // var_dump($user);
        if(!empty($user['email'])) {
            $updateUserQuery = 'UPDATE `fe_user` SET `last_login` = CURRENT_TIMESTAMP WHERE `email` = "'.$user['email'].'";';
            $userLastLoginUpdate = $db->query($updateUserQuery);
            // print_r('UpdateQuery -> '.$userLastLoginUpdate.$updateUserQuery);
        }

    }else{
        // print_r('error');
    }

    $loggedIn['loggedIn'] = false;
    $loggedIn['errorEmail'] = false;
    $loggedIn['errorPassword'] = false;
    $emailEmpty = true;
    $passwordEmpty = true;
    if($user['email']) {
        $emailEmpty = false;
    } else if(!empty($_POST["email"])){
        $loggedIn['errorEmail'] = true;
    }
    if($user['password'] && $passwordCorrect) {
        $passwordEmpty = false;
    } else if(!empty($_POST["password"])){
        $loggedIn['errorPassword'] = true;
    }
    if(!$passwordEmpty && !$emailEmpty) {
        if ($userRights == 0) {
            die('user known but no permissions for anything');
        } else {
            $loggedIn['errorEmail'] = false;
            $loggedIn['errorPassword'] = false;
            $loggedIn['loggedIn'] = true;
            $user['password'] = '';
            $_SESSION["login"] = 1;
            $_SESSION['user'] = $user;
            $_POST["email"] = '';
            $_POST["password"] = '';
        }
    }

    return $loggedIn;
}
function createLoginDialog($db, $loggedIn): void
{
    $loggedIn = checkUser($db);
    $dialogHeader = 'Login';
    $emailTextFieldLabel = 'E-Mail:';
    $passwordTextFieldLabel = 'Passwort:';
    $errorEmailText = '<br>*Bitte geben Sie eine korrekte E-Mail Adresse ein.';
    $errorPasswordText = '<br>*Bitte geben Sie das korrekte Passwort ein.';
    $errorEmail = $loggedIn['errorEmail'] ? '<span class="error" id="errorEmail">'.$errorEmailText.'</span>' : '';
    $errorPassword = $loggedIn['errorPassword'] ? '<span class="error" id="errorPassword">'.$errorPasswordText.'</span>' : '';

    $dialogTop = '<div class="login-dialog-top" id="loginDialogTop">'.$dialogHeader.'</div>';
    $userMailValue = !empty($loggedIn['user']) ? 'value="'.$loggedIn['user']['email'].'"':'';
    $emailTextField = '<label for="email">'.$emailTextFieldLabel.'</label><br><input type="email" name="email" '.$userMailValue.'>'.$errorEmail;
    $passwordTextField = '<label for="password">'.$passwordTextFieldLabel.'</label><br><input type="password" name="password">'.$errorPassword;

    $loginForm = '<form action="index.php" method="post" id="loginDialogForm" class="login-dialog-form">'.
                    '<div class="dialog-body padding">'.
                        '<div>'.
                            $emailTextField.'<br>'.$passwordTextField.
                        '</div>'.
                    '</div>'.
                    '<div class="dialog-actions padding">'.
                        '<span>'.
                            createButton('button', 'Abbrechen', "cancel('loginDialog','login')", 'loginDialog_cancel').
                            createButton('submit', 'Absenden', "save('loginDialog','login')", 'loginDialog_save').
                        '</span>'.
                    '</div>'.
                '</form>';

    $dialogContent = '<div class="login-dialog-content" id="loginDialogContent">'.$loginForm.'</div>';
    // $dialogBottom = '<div class="login-dialog-bottom" id="loginDialogBottom">Bottom</div>';

    $dialog = '<div class="login-dialog" id="loginDialog">'.$dialogTop.$dialogContent.'</div>';
    echo !$loggedIn['loggedIn'] ? '<div class="login-dialog-container" id="loginDialogContainer">'.$dialog.'</div>' : '';
    if($_SESSION['login']) {
        getMagazines($db);
    }
}