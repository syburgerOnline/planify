<?php
function createNewIssueForm(): string
{

    $openAction = "openClose('editDialog', 'open', 'addIssue', 'Ausgabe erstellen')";
    $issuesMax = $GLOBALS['issuesPerYear'];
    $class = $GLOBALS['newIssueIcon']['icon'];
    $title = $GLOBALS['newIssueIcon']['mouseover'];
    return '<button><i class="'.$class.'" onclick="'.$openAction.'" title="'.$title.'"></i></button>';
}
function deleteIssueForm(): string
{

    $openAction = "openClose('editDialog', 'open', 'deleteIssue', 'Ausgabe löschen')";
    $issuesMax = $GLOBALS['issuesPerYear'];
    $class = $GLOBALS['deleteIssueIcon']['icon'];
    $title = $GLOBALS['deleteIssueIcon']['mouseover'];
    return '<button><i class="'.$class.'" onclick="'.$openAction.'" title="'.$title.'"></i></button>';
}
function addUserAccountButton(): string
{

    $openAction = "openClose('editDialog', 'open', 'addUser', 'User anlegen')";
    $class = $GLOBALS['addUserIcon']['icon'];
    $title = $GLOBALS['addUserIcon']['mouseover'];
    return '<button><i class="'.$class.'" onclick="'.$openAction.'" title="'.$title.'"></i></button>';
}
function editUserAccountButton(): string
{

    $openAction = "openClose('editDialog', 'open', 'editUser', 'User bearbeiten')";
    $class = $GLOBALS['editUserIcon']['icon'];
    $title = $GLOBALS['editUserIcon']['mouseover'];
    return '<button><i class="'.$class.'" onclick="'.$openAction.'" title="'.$title.'"></i></button>';
}
function contactSupportTeamButton(): string
{

    $openAction = "openClose('editDialog', 'open', 'contactSupportTeam', 'Support kontaktieren')";
    $class = $GLOBALS['contactSupportTeamIcon']['icon'];
    $title = $GLOBALS['contactSupportTeamIcon']['mouseover'];
    return '<button><i class="'.$class.'" onclick="'.$openAction.'" title="'.$title.'"></i></button>';
}
function createLogoutButton(): string{
    $openAction = "logOut('user')";
    $class = $GLOBALS['logOutIcon']['icon'];
    $title = $GLOBALS['logOutIcon']['mouseover'];
    return '<a href="logout.php"><button><i class="'.$class.'" onclick="'.$openAction.'" title="'.$title.'"></i></button></a>';
}
function createIssueButtonContainer(): string
{
    $userPermissionCreateIssue = checkForUserPermissions($_POST['magazines'], [5, 6]);
    $userPermissionDeleteIssue = checkForUserPermissions($_POST['magazines'], [6]);
    $userPermissionAddUser = checkForUserPermissions($_POST['magazines'], [6]);
    $userPermissionEditUser = checkForUserPermissions($_POST['magazines'], [6]);
    $creatNewIssueButton = $userPermissionCreateIssue ? createNewIssueForm() : '';
    $deleteIssueButton = $userPermissionDeleteIssue ? deleteIssueForm() : '';
    $addUserButton = $userPermissionAddUser ? addUserAccountButton() : '';
    $editUserButton = $userPermissionEditUser ? editUserAccountButton() : '';
    $contactSupportTeamButton = contactSupportTeamButton();
    $createLogoutButton = createLogoutButton();
    return '<div class="issue-buttons">'.$creatNewIssueButton.$deleteIssueButton.$addUserButton.$editUserButton.$contactSupportTeamButton.$createLogoutButton.'</div>';
}
// <i class="fa-solid fa-trash-can"></i>
function getAvailableIssues($db, $magazineId, $issuesPerYear, $averagePageAmount): void
{
    // looking for issues already ar created
    $issueQuery = "SELECT * FROM `issue` WHERE `magazine_id` =".$magazineId;
    $issueDetails = $db->query($issueQuery);
    $isFilledResponse = $issueDetails -> {'num_rows'};
    $issuesExisting = array();
    $yearsExisting = array();
    while( $singleIssue = $issueDetails->fetch_assoc()) {
        $issueId = utf8_encode($singleIssue['issue']);
        $year = utf8_encode($singleIssue['year']);

        if(!in_array($year, $yearsExisting)) {
            $yearsExisting[] = $year;
        }
        $issuesExisting[] = $issueId;
    }

    // Page Amounts

    $averagePageAmountMin = $averagePageAmount - 16;
    $averagePageAmountMax = 156; //$averagePageAmount + 16;
    $GLOBALS['averagePageAmountItems'] = array(
        /*
        0 => array(
            'title' => 'Null',
            'selected' => '',
            'value' => '',
            'function' => 'PageAmount(#)'
        )
        */
    );
    $pages = 0;
    for($i = $averagePageAmountMin; $i <= $averagePageAmountMax; $i+=4) {
        if($i == $averagePageAmount) {
            $GLOBALS['averagePageAmountSelected'] = $pages;
        }
        $pages += 1;
        $averagePageAmountArray = array(
            'title' => $i,
            'selected' => '',
            'value' => $i,
            'function' => 'selectPageAmount(#)'
        );
        $GLOBALS['averagePageAmountItems'][] = $averagePageAmountArray;
    }

    // Years
    $actualYear = date('Y');
    $nextYear = $actualYear + 1;
    $availableYears = array($actualYear,$nextYear);
    $GLOBALS['existingYearItems'] = array(
        /*
        0 => array(
            'title' => 'Keins',
            'selected' => '',
            'value' => '',
            'function' => 'selectYear(#)'
        )
        */
    );
    for($z = 0; $z < count($yearsExisting); $z++) {
        $existingYearArray = array(
            'title' => $yearsExisting[$z],
            'selected' => '',
            'value' => $yearsExisting[$z],
            'function' => 'selectYear(#)'
        );
        $GLOBALS['existingYearItems'][] = $existingYearArray;
    }
    $GLOBALS['yearItems'] = array(
        /*
        0 => array(
            'title' => 'Keins',
            'selected' => '',
            'value' => '',
            'function' => 'selectYear(#)'
        )
        */
    );
    for($i = 0; $i < count($availableYears); $i++) {
        $yearArray = array(
            'title' => $availableYears[$i],
            'selected' => '',
            'value' => $availableYears[$i],
            'function' => 'selectYear(#)'
        );
        $GLOBALS['yearItems'][] = $yearArray;
    }

    // Issues
    $GLOBALS['issueExistingItems'] = array();
    $GLOBALS['issueItems'] = array(
        /*
        0 => array(
            'title' => 'Keine',
            'selected' => '',
            'value' => '',
            'function' => 'selectIssue(#)'
        )
        */
    );
    for($i = 1; $i <= $issuesPerYear; $i++) {
        $issueArray = array(
            'title' => $i,
            'selected' => '',
            'value' => $i,
            'function' => 'selectIssue(#)'
        );
        if (!in_array($i, $issuesExisting)) {
            $GLOBALS['issueItems'][] = $issueArray;
        } else {
            $GLOBALS['issueExistingItems'][] = $issueArray;
        }
    }
}