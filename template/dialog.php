<?php
function createDialogInputHiddenFields($type,$name,$value): string
{
    return '<input type="'.$type.'" name="'.$name.'" id="'.$name.'" value="'.$value.'">';
}
function fetchArrayData($scope, $array): void {
    foreach ($array as $key => $value) {
        print_r($scope.$key.' -> '.$value.'<br>');
    }
}
function uploadFileButton($id, $title):string {
    // $openAction = "uploadScreenshot(event,'contactSupportTeamDialog')";
    $clickTag = ''; // 'onclick="'.$openAction.'" ontouchstart="'.$openAction.'" ';
    $class = $GLOBALS['contactSupportTeamUploadFileIcon']['icon'];
    // $title = $GLOBALS['contactSupportTeamUploadFileIcon']['mouseover'];
    $styleArray = $GLOBALS['contactSupportTeamUploadFileIcon']['style'];
    $style = '';
    foreach($styleArray AS $key => $val) {
        $style .= $key.' '.$val.'; ';
    }
    // $inputField = '<input name="uploadFile" id="uploadFile" type="file" value=""><label for="uploadFile">Datei wählen</label><br>';
    $inputButton = '<i class="'.$class.'"  title="'.$title.'" '.$clickTag.' style="'.$style.'"></i>';
    return '<input name="'.$id.'UploadFile" id="'.$id.'UploadFile" type="file"><label for="'.$id.'UploadFile">'.$inputButton.'<span>'.$title.'</span></label><br>';
}
function createUploadImageDialogForm($id, $elements): string {
    $uploadButton = uploadFileButton('uploadImageDialogForm', $GLOBALS['uploadIcon']['mouseover']);
    $buttons = '';
    $hiddenTextFields = '';
    if(!empty($elements['buttons'])) {
        foreach ($elements['buttons'] as $key => $val) {
            $buttons .= createButton($val[0], $val[1], $val[2], $val[3]);
        }
    }
    if(!empty($elements['hiddenTextFields'])) {
        foreach ($elements['hiddenTextFields'] as $key => $val) {
            $hiddenTextFields .= createDialogInputHiddenFields($val[0], $val[1], $val[2]);
        }
    }
    return '<div class="progress" id="progressDivId">
                <div class="progress-bar" id="progressBar"></div>
                <input class="percent" type="text" name="percent" id="percentTxt" readonly disabled>
            </div>
            <form enctype="multipart/form-data" action="/controller/uploadImage.php" method="post" id="'.$id.'DialogForm" class="'.$id.'-dialog-form form">
            <div class="dialog-body padding">
                <div>
                    '.$uploadButton.'
                    <input type="hidden" name="pageId" id="pageId"><br>
                    <input type="hidden" name="affectedPages" id="affectedPages"><br>
                    <input type="hidden" name="uploadImageMagazineId" id="uploadImageMagazineId" value="'.$_POST["magazines"].'"><br>
                    <input type="hidden" name="uploadImageIssueId" id="uploadImageIssueId" value="'.$_POST["issue"].'"><br>
                </div>
                
            </div>
            <div class="dialog-actions padding">
                <span>'.
                    $buttons.
                '</span>
            </div>'.
                $hiddenTextFields.
            '<input type="hidden" name="magazines" value="'.$_POST["magazines"].'">
            <input type="hidden" name="issue" value="'.$_POST["issue"].'">
            <input type="hidden" name="year" value="'.$_POST["year"].'">
            </form>';
}
function createDialogForm($id, $elements): string
{
    $selectBoxes = '';
    $buttons = '';
    $hiddenTextFields = '';
    $field = '';
    if(!empty($elements['selectBoxes'])) {
        foreach ($elements['selectBoxes'] as $key => $val) {
            $selectBoxes .= createSelectBox(null, $val[0], $val[1], $val[2], $val[3], $val[4], '',$val[5]);// .'<br>';
        }
    }
    if(!empty($elements['buttons'])) {
        foreach ($elements['buttons'] as $key => $val) {
            $buttons .= createButton($val[0], $val[1], $val[2], $val[3]);
        }
    }
    if(!empty($elements['hiddenTextFields'])) {
        foreach ($elements['hiddenTextFields'] as $key => $val) {
            $hiddenTextFields .= createDialogInputHiddenFields($val[0], $val[1], $val[2]);
        }
    }
    if(!empty($elements['additionalFunction'][0])) {
        $selectBoxes .= strval($elements['additionalFunction'][0])();
    }

    /*
     *
     */
    return
        '<form action="index.php" method="post" id="'.$id.'DialogForm" class="'.$id.'-dialog-form form">'.
            '<div class="dialog-body padding">'.
                '<div class="'.$id.' dialog-select">'.
                    $selectBoxes.
                '</div>'.
            '</div>'.
            '<div class="dialog-actions padding">'.
                '<span>'.
                    $buttons.
                '</span>'.
            '</div>'.
                $hiddenTextFields.
            '<input type="hidden" name="magazines" value="'.$_POST["magazines"].'">'.
            '<input type="hidden" name="issue" value="'.$_POST["issue"].'">'.
            '<input type="hidden" name="year" value="'.$_POST["year"].'">'.
        '</form>';
}

function createDialogElements($magazineId, $issueId=null, $cookie): string
{
    // cookie issues
    $left = explode(',', $cookie['editDialog'])[0];
    $top = explode(',', $cookie['editDialog'])[1];
    $style = !empty($cookie) ? ' style="left:'.$left.'px ; top:'.$top.'px ;"' : '';
    // add Issue
    $yearSelected = 0;
    $issueSelected = 0;
    /**
     * add issue
     */
    $articleSelected = 0;
    $adSelected = 0;
    $addIssue = array(
        'selectBoxes' => array(
            0 => array('newIssue','Ausgabe', $GLOBALS['issueItems'], $issueSelected,'', [5,6]),
            1 => array('newYear', 'Jahr', $GLOBALS['yearItems'], $yearSelected, '', [5,6]),
            2 => array('newPageCount', 'Seitenzahl', $GLOBALS['averagePageAmountItems'], $GLOBALS['averagePageAmountSelected'], '', [5,6]),
        ),
        'buttons' => array(
            0 => array('button', 'Abbrechen', "cancel('editDialog','addIssue')", 'addIssueDialog_cancel'),
            1 => array('submit', 'Speichern', "save('editDialog','addIssue')", 'addIssueDialog_save')
        ),
        'hiddenTextFields' => array(
            0 => array('hidden','issueNumberTextField', ''),
            1 => array('hidden','issueYearTextField', ''),
            2 => array('hidden','issuePageAmountTextField', ''),
            3 => array('hidden','dialogType', 'addIssue'),
        )
    );
    $addIssueForm = ($magazineId > 0) ? createDialogForm('addIssue', $addIssue) : '';

    /**
     * delete issue
     */

    $deleteIssue = array(
        'selectBoxes' => array(
            0 => array('deleteIssue','Ausgabe', $GLOBALS['issueExistingItems'], $issueSelected,'', [5,6]),
            1 => array('deleteYear', 'Jahr', $GLOBALS['existingYearItems'], $yearSelected, '', [5,6]),
        ),
        'buttons' => array(
            0 => array('button', 'Abbrechen', "cancel('editDialog','deleteIssue')", 'deleteIssueDialog_cancel'),
            1 => array('submit', 'Löschen', "save('editDialog','deleteIssue')", 'deleteIssueDialog_save')
        ),
        'hiddenTextFields' => array(
            0 => array('hidden','issueNumberTextField', ''),
            1 => array('hidden','issueYearTextField', ''),
            2 => array('hidden','issuePageAmountTextField', ''),
            3 => array('hidden','dialogType', 'deleteIssue'),
        )
    );
    $deleteIssueForm = ($magazineId > 0) ? createDialogForm('deleteIssue', $deleteIssue) : '';

    /**
     * add user
     */

    $magazineSelected = 0;
    $loginValueSelected = 0;
    $addUser = array(
        /*
        'selectBoxes' => array(
            0 => array('loginMagazine','Magazin', $GLOBALS['magazineArray'], $magazineSelected,'', [5,6]),
            1 => array('loginValues','Bereich', $GLOBALS['loginArray'], $loginValueSelected,'', [5,6]),
        ),
        */
        'buttons' => array(
            0 => array('button', 'Abbrechen', "cancel('editDialog','addUser')", 'addUserDialog_cancel'),
            1 => array('submit', 'Speichern', "save('editDialog','addUser')", 'addUserDialog_save')
        ),
        'hiddenTextFields' => array(
            0 => array('hidden','emailTextField', $_SESSION['user']['email']),
            1 => array('hidden','nameTextField', $_SESSION['user']['name']),
            2 => array('hidden','passwordTextField', ''),
            3 => array('hidden','dialogType', 'addUser'),
        ),
        'additionalFunction' => array(
            0 => 'addUserAccountTable',
        ),
    );
    function addUserAccountTable(): string {
        $userNameField = '<div class="user-account-dialog input-field"><label for="userNameAddUser">Username</label><input autocomplete="false" type="text" id="userNameAddUser" name="userNameAddUser"></div>';
        $userEmailField = '<div class="user-account-dialog input-field"><label for="emailAddUser">E-Mail</label><input autocomplete="true" type="email" id="emailAddUser" name="emailAddUser"></div>';
        $userPasswordField = '<div class="user-account-dialog input-field"><label for="passwordAddUser">Passwort</label><input autocomplete="true" type="password" id="passwordAddUser" name="passwordAddUser"></div>';
        $returnValue = '<div class="user-account-dialog">'.$userNameField.'<br>'.$userEmailField.'<br>'.$userPasswordField.'<br>';
        foreach ($GLOBALS['magazineArray'] as $key => $val) {
            if($key > 0) {
                $firstReturnValue = '<div class="user-account-dialog permissions"><label for="userPermissionAddUser_'.$key.'">'.$GLOBALS['magazineArray'][$key]['title'].'</label></div>';// .'<br>';
                $secondReturnValue = '<div class="user-account-dialog permissions right">'.createSelectBox($key,'userPermission', '', $GLOBALS['loginArray'], '', '', '', [6]).'</div>';
                $returnValue .= '<div class="user-account-dialog content">'.$firstReturnValue.$secondReturnValue.'</div><br>';
            }
        }
        return $returnValue.'</div>';
    }
    // && $issueId > 0
    $addUserForm = ($magazineId > 0) ? createDialogForm('addUser', $addUser) : '';

    /**
     * editUser
     * */
    $email = $_SESSION['user']['email'];
    $userSelected = 0;
    $tmpUser = [];
    $editUserArray = [];

    foreach($_SESSION['userGroup'] AS $key => $val) {
        if($email === $val['email']) {
            $userSelected = $key;
        }
        $tmpUser['title'] = $val['email'];
        $tmpUser['selected'] = $val['email'] === $email;
        $permissionsArray = $val['permissions'];
        $userPermission = [];
        foreach($permissionsArray AS $keyPermission => $valPermission){
            $tmpUserPermission = [$valPermission[0],$valPermission[1]];
            $userPermission[] = implode(':', $tmpUserPermission);
        }
        $jsPermissionsArray = implode(',',$userPermission);
        $tmpUser['value'] = "this, '".$val['email']."','".$val['name']."',{".$jsPermissionsArray."},'userNameEditUser','emailEditUser',".$key;
        $tmpUser['function'] = 'selectEditUser(#)';
        $editUserArray[] = $tmpUser;
    }
    $editUser = array(

        'selectBoxes' => array(
            0 => array('editUser','Benutzer auswählen', $editUserArray, $userSelected,'', [6]),
        ),

        'buttons' => array(
            0 => array('button', 'Abbrechen', "cancel('editDialog','editUser')", 'editUserDialog_cancel'),
            1 => array('submit', 'Speichern', "save('editDialog','editUser')", 'editUserDialog_save')
        ),
        'hiddenTextFields' => array(
            0 => array('hidden','emailTextFieldEditUser', $_SESSION['user']['email']),
            1 => array('hidden','nameTextFieldEditUser', $_SESSION['user']['name']),
            2 => array('hidden','passwordTextFieldEditUser', ''),
            3 => array('hidden','dialogType', 'editUser'),
        ),
        'additionalFunction' => array(
            0 => 'editUserAccountTable',
        ),
    );
    function editUserAccountTable(): string {

        $userEmailField = '<br><br><div class="user-account-dialog input-field"><label for="emailEditUser">Username</label><input autocomplete="false" type="text" id="emailEditUser" name="emailEditUser" value="'.$_SESSION['user']['email'].'"></div>';
        $userNameField = '<div class="user-account-dialog input-field"><label for="userNameEditUser">Username</label><input autocomplete="false" type="text" id="userNameEditUser" name="userNameEditUser" value="'.$_SESSION['user']['name'].'"></div>';
        $userPasswordField = '<div class="user-account-dialog input-field"><label for="passwordEditUser">Passwort</label><input autocomplete="true" type="password" id="passwordEditUser" name="passwordEditUser"></div>';
        $returnValue = '<div class="user-account-dialog" id="editUserPermissionsSelectArea">'.$userEmailField.'<br>'.$userNameField.'<br>'.$userPasswordField.'<br>';
        /*
        foreach ($_SESSION['userGroup'] as $key => $val) {
            // print_r($key.$val."<br>");
            $returnValue .= '<div class="user-account-dialog content">'.$val['email'].$val['name'].$val['permissions'].'</div>';
        }
        */
        $userPermissions = $_SESSION['user']['permissions'];
        foreach ($GLOBALS['magazineArray'] as $key => $val) {
            if($key > 0) {
                $selectedPermission = $userPermissions[$key-1][1];
                $firstReturnValue = '<div class="user-account-dialog permissions"><label for="userPermissionEditUser_'.$key.'">'.$GLOBALS['magazineArray'][$key]['title'].'</label></div>';// .'<br>';
                $secondReturnValue = '<div class="user-account-dialog permissions right">'.createSelectBox($key,'userPermission', '', $GLOBALS['loginArray'], $selectedPermission, '', '', [6]).'</div>';
                $returnValue .= '<div class="user-account-dialog content">'.$firstReturnValue.$secondReturnValue.'</div><br>';
            }
        }
        return $returnValue.'</div>';
    }
    // && $issueId > 0
    $editUserForm = ($magazineId > 0) ? createDialogForm('editUser', $editUser) : '';

    // upload Form
    $uploadImage = array(
        'buttons' => array(
            0 => array('button', 'Abbrechen', "cancel('editDialog','uploadImage')", 'uploadImageDialog_cancel'),
            1 => array('submit', 'Speichern', "save('editDialog','uploadImage')", 'uploadImageDialog_save')
        ),
        'hiddenTextFields' => array(
            0 => array('hidden','email', $_SESSION['user']['email']),
            1 => array('hidden','name', $_SESSION['user']['name']),
            4 => array('hidden','dialogType', 'uploadImage'),
        ),
    );
    $uploadImageForm = ($magazineId > 0 && $issueId > 0) ? createUploadImageDialogForm('uploadImage', $uploadImage) : '';

    // contactSupportTeamForm

    $contactSupportTeam = array(
        /*
        'selectBoxes' => array(
            0 => array('loginMagazine','Magazin', $GLOBALS['magazineArray'], $magazineSelected,''),
            1 => array('loginValues','Bereich', $GLOBALS['loginArray'], $loginValueSelected,''),
        ),
        */
        'buttons' => array(
            0 => array('button', 'Abbrechen', "cancel('editDialog','contactSupportTeam')", 'contactSupportTeamDialog_cancel'),
            1 => array('submit', 'Senden', "save('editDialog','contactSupportTeam')", 'contactSupportTeamDialog_save')
        ),
        'hiddenTextFields' => array(
            0 => array('hidden','emailTextField', $_SESSION['user']['email']),
            1 => array('hidden','nameTextField', $_SESSION['user']['name']),
            3 => array('hidden','dialogType', 'contactSupportTeam'),
        ),
        'additionalFunction' => array(
            0 => 'contactSupportTeamField',
        ),
    );
    function contactSupportTeamField(): string {
        // uploadImageDialogForm
        // contactSupportTeamDialogForm
        $uploadButton = uploadFileButton('contactSupportTeamDialogForm', $GLOBALS['contactSupportTeamUploadFileIcon']['mouseover']);
        $userMessageField = '<div class="user-message-dialog input-field"><label for="userMessage">Nachricht:</label><br><textarea autocomplete="false" type="text" id="userMessage" name="userMessage" rows="15" cols="50" placeholder="Ihre Nachricht..."></textarea><br>'.$uploadButton.'</div>';
        $returnValue = '<div class="user-message-dialog">'.$userMessageField.'<br>';
        return $returnValue.'</div>';
    }
    $contactSupportTeamForm = createDialogForm('contactSupportTeam', $contactSupportTeam);
    // ====
    // ====
    // add article
    /*
    $addArticle = array(
        'selectBoxes' => array(
            0 => array('addArticle','Artikel', $GLOBALS['articleItems'], $articleSelected,''),
            1 => array('addAd', 'Anzeige', $GLOBALS['adTypeItems'][$magazineId], $adSelected, ''),
        ),
        'buttons' => array(
            0 => array('button', 'Abbrechen', "cancel('editDialog','addArticle')", 'addArticleDialog_cancel'),
            1 => array('submit', 'Speichern', "save('editDialog','addArticle')", 'addArticleDialog_save')
        ),
        'hiddenTextFields' => array(
            0 => array('hidden','addArticleTextField', ''),
            1 => array('hidden','addAdTextField', ''),
            2 => array('hidden','dialogType', 'addArticle'),
        )
    );
    */
    // $addArticleForm = ($magazineId > 0 && $issueId > 0) ? createDialogForm('addArticle', $addArticle) : '';
    // ====

    $dialogHeader = '<input type="text" class="dialog-header-headline" id="dialogHeader" placeholder="Enter Something" disabled>';
    return
        '<div class="dialog" id="editDialog" draggable="true" ondragstart="drag(event)" ontouchstart="drag(event)" '.$style.'>'.
            '<div class="dialog-header padding">'.
                '<span>'.$dialogHeader.'</span>'.
            '</div>'.
                // $addArticleForm.
                $addUserForm.
                $editUserForm.
                $addIssueForm.
                $deleteIssueForm.
                $contactSupportTeamForm.
                $uploadImageForm.
        '</div>';
}
