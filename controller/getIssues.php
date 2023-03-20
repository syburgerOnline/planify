<?php
function getIssues($db, $magazineId): void
{
    $issueQuery = "SELECT * FROM `issue` WHERE `magazine_id` =".$magazineId;
    $issueDetails = $db->query($issueQuery);
    $isFilledResponse = $issueDetails -> {'num_rows'};
    if($isFilledResponse) {
        // echo('<br><br><label for="issue">Ausgabe:</label><br>');
        // echo('<select name="issue" id="issue" onchange="javascript:this.form.submit()">');
        while( $singleIssue = $issueDetails->fetch_assoc()){
            $htmlText = utf8_encode($singleIssue['abstract']);
            $issueId = utf8_encode($singleIssue['issue']);
            $year = utf8_encode($singleIssue['year']);
            $title = $issueId.'/'.$year;
            $selectedIssue = $_POST["issue"] == $issueId ? 'selected' : '';
            $issues = array(
                'title'         => $title,
                'folderName'    => '',
                'id'            => $issueId,
                'ebiNumber'     => '',
                'position'      => '',
                'selected'      => $selectedIssue,
                'function'      => 'changeIssue('.$issueId.','.$year.')',
                'year'          => $year
            );
            $GLOBALS['issueArray'][] = $issues;
            /*
            if($_POST["issue"] == $issueId) {
                echo createOptionsItem($issueId, $title, 'selected', 'changeMagazine('.$issueId.')');
            }else {
                echo createOptionsItem($issueId, $title, '', 'changeMagazine('.$issueId.')');
            }
            */
        }
        $issueSelected = $_POST["issue"] > 0 ? $_POST["issue"] : 0;
        //$onFormChangedFunction = ''; // 'javascript:this.form.submit()';
        $onFormChangedFunction = "changeIssue(".$issueSelected.",'null')";
        echo createSelectBox(null,'issue', 'Ausgabe', $GLOBALS['issueArray'], $issueSelected, $onFormChangedFunction, 'magazines', [1, 2, 3, 4, 5, 6]);
        echo '<br><div class="test-container"><input type="text" name="year" id="actualIssueYear" value="">'.$_POST["year"].'</input></div>';
        if($_POST["issue"] > 0 ) {
            getPages($db, $_POST["magazines"], $_POST["issue"], $_POST["year"]);
        }
    } else {
        $_POST["issue"] = 0;
        print_r('<br>noch keine Ausgaben vorhanden');
    }
}