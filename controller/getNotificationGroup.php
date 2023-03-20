<?php
function getNotificationGroup($db, $magazineId, $issueId): array
{
    $userGroupQuery = 'SELECT * FROM `fe_user` ORDER BY `email` ASC;';
    $userGroupDetails = $db->query($userGroupQuery);
    $isFilledResponse = $userGroupDetails -> {'num_rows'};
    $userPermissionGroup = $GLOBALS['userGroupArray'];
    $notificationGroupArray = $GLOBALS['loginArray'];
    $notificationGroup = [];
    $userGroup = [];
    $globalUserGroup = [];

    if($isFilledResponse) {

        while ($userSingleDetails = $userGroupDetails->fetch_assoc()) {
            $userGroup['email'] = utf8_encode($userSingleDetails['email']);
            $userGroup['name'] = utf8_encode($userSingleDetails['name']);
            $userGroup['lastLogin'] = date($userSingleDetails['last_login']);
            $userGroup['password'] = $userSingleDetails['password'];
            $permissions = str_replace(['{', '}'],[''], $userSingleDetails['permissions']);
            $permissionsArray = explode(',', $permissions);
            $userGroup['permissions'] = [];
            foreach($permissionsArray AS $key => $val) {
                $cnt = explode(':', $val);
                $userGroup['permissions'][] = [$cnt[0], number_format($cnt[1])];
                if($magazineId == $userPermissionGroup[$cnt[0]]['id']) {
                    $permission = number_format($cnt[1]);
                    $notificationGroupString = $notificationGroupArray[$permission]['notificationGroup'];
                    $userGroup['notificationGroup'] = $notificationGroupString;
                    // print_r ('<br>'.$userGroup['email'].' has permission for '.$userPermissionGroup[$cnt[0]]['title'].' value-> '.$notificationGroupString.'<br>');
                    if($userGroup['email'] == $_SESSION['user']['email']) {
                        $_SESSION['user']['permissionGroup'] = $notificationGroupString;
                        $_SESSION['user']['permissionNumber'] = $permission;
                    }
                    // $notificationGroup[$notificationGroupString][] = [$notificationGroupString, $userGroup];
                    $notificationGroup[$permission][] = [$permission, $userGroup];
                }
            }
            $globalUserGroup[] = $userGroup;
        }
        $_SESSION['userGroup'] = $globalUserGroup;

    }else{
        print_r('error');
    }
    return $notificationGroup;
}