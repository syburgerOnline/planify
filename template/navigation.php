<?php
function createNavigation($db, $magazineDetails): void {
    echo '<div id="navigation" class="nav">';
    // $magazineSelectArray = "{";
    $count = 0;
    if(!empty($_SESSION["deepLink"])) {
        if(!empty($_SESSION["deepLink"]["magazines"])) {
            if($_SESSION["deepLink"]["magazines"] != $_POST["magazines"] && $_POST["magazines"] != 0) {
                $_SESSION["deepLink"]["magazines"] = $_POST["magazines"];
                $_SESSION["deepLink"]["issue"] = 0;
                $_SESSION["deepLink"]["year"] = 0;

            } else{
                $_POST["magazines"] = $_SESSION["deepLink"]["magazines"];
            }

        }
        if(!empty($_SESSION["deepLink"]["issue"])) {
            $_POST["issue"] = $_SESSION["deepLink"]["issue"];
        }
        if(!empty($_SESSION["deepLink"]["year"])) {
            $_POST["year"] = $_SESSION["deepLink"]["year"];
        }

    }
    while( $singleMagazine = $magazineDetails->fetch_assoc()){
        $title = utf8_encode($singleMagazine['title']);
        $folderName = utf8_encode($singleMagazine['folder_name']);
        $id = utf8_encode($singleMagazine['id']);
        $magazineNumber = utf8_encode($singleMagazine['magazine_number']);
        $ebiNumber = utf8_encode($singleMagazine['ebi_number']);
        $position = utf8_encode($singleMagazine['position']);
        $issuesPerYear = utf8_encode($singleMagazine['issues_per_year']);
        $averagePageAmount = utf8_encode($singleMagazine['average_page_amount']);
        $selectedMagazine = $_POST["magazines"] == $id? 'selected' : '';
        // $magazineSelectArray .= $count == 0 ? "magazineId:'".$id."'" : ",magazineId:'".$id."'";
        // $magazineSelectArray .= ",issuesPerYear:'".$issuesPerYear."'";
        $magazine = array(
            'title'             => $title,
            'folderName'        => $folderName,
            'id'                => $id,
            'ebiNumber'         => $ebiNumber,
            'position'          => $position,
            'selected'          => $selectedMagazine,
            'function'          => 'changeMagazine('.$id.')',
            'issuesPerYear'     => $issuesPerYear,
            'averagePageAmount' => $averagePageAmount
        );
        $GLOBALS['magazineArray'][] = $magazine;
        $count ++;
    }
    $magazineSelected = $_POST["magazines"] > 0 ? $_POST["magazines"]: '';
    // $magazineSelectArray .= ",magazineSelected:'".$magazineSelected."'";
    // $magazineSelectArray .= "}";
    $onFormChangedFunction = "changeMagazine(".$magazineSelected.")";// "changeMagazine('null',".$magazineSelectArray.")";// ''; // 'javascript:this.form.submit()';
    echo
    '<form class="nav-form" action="index.php" method="post" id="navigationForm">',
    createSelectBox(null,'magazines', 'Magazin', $GLOBALS['magazineArray'], $magazineSelected, $onFormChangedFunction,'issue',[0, 1, 2, 3, 4, 5, 6]);
    if ($_POST["magazines"] > 0) {
        $_SESSION["deepLink"] = [];
        if (!empty($GLOBALS['login']) && $GLOBALS['login']['user'] == 'admin') {
            $availableIssues = $GLOBALS['magazineArray'][$_POST["magazines"]]['issuesPerYear'];
            $averagePageAmount = $GLOBALS['magazineArray'][$_POST["magazines"]]['averagePageAmount'];
            getAvailableIssues($db, $_POST["magazines"], $availableIssues, $averagePageAmount);
        }
        // print_r('issue - >'.$_POST["issue"]);
        getIssues($db, $_POST["magazines"]);
    }
    echo '</form>';
    if($_POST["magazines"] > 0 ) {
        // echo createNewIssueForm();
        // echo deleteIssueForm();
        echo createIssueButtonContainer();
        if($_POST["issue"] > 0) {
            if(!$_SESSION["deepLink"]) {
                $_SESSION["deepLink"] = [];
                $_SESSION["deepLink"]["magazines"] = $_POST["magazines"];
                $_SESSION["deepLink"]["issue"] = $_POST["issue"];
                $_SESSION["deepLink"]["year"] = $_POST["year"];
            }

            echo createTestField();
        }
    }
    echo '</div>';
}