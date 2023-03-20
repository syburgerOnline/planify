<?php

function createSQLQuery($request, $table, $targetFields, $sourceData): string
{
    return ''.$request.' `'.$table.'` ('.implode(',', $targetFields).') VALUES '.$sourceData.';';
}
function createEmptyPages($formData): array
{
    $issuePageAmount = $formData['issuePageAmount'];
    $targetPagesFields = array(
        '`page_id`','`title`','`type`','`description`','`issue_id`', '`magazine_id`','`year`', '`content_id`', '`has_ad`', '`ad_type`', '`ad_status`', '`article_status`'
    );
    $sourceIssueData = array();
    for($i = 1; $i <= $issuePageAmount; $i++) {
        $title='';
        switch($i) {
            case 1:
                $type='Cover';
                break;
            case 2:
                $type='Inhalt';
                break;
            case $issuePageAmount:
                $type='Backcover';
                break;
            default:
                $type='Standardseite';
        }
        $description ='';
        $page = array(
            $i, '"'.$title.'"', '"'.$type.'"', '"'.$description.'"',$formData['issueId'], $formData['magazineId'], $formData['issueYear'], $i, '"false"','"none"', '"open"', '"open"'
        );
        $sourceIssueData[] = '('.implode(',', $page).')';
    }
    return ['targetFields'=>$targetPagesFields, 'sourceFields'=>$sourceIssueData];
}
function createNewDatabaseIssue($db, $formData): void
{
    $affectedRows = '';
    $targetIssueFields = array(
        '`title`','`year`','`issue`','`page_amount`', '`magazine_id`', '`year`'
    );
    $sourceIssueData = array(
        '"Title"', $formData['issueYear'], $formData['issueId'],$formData['issuePageAmount'], $formData['magazineId'], $formData['issueYear']
    );
    $issueQuery = createSQLQuery('INSERT INTO', 'issue', $targetIssueFields, '('.implode(',', $sourceIssueData).')');
    $db->query($issueQuery);
    $affectedRows .= $db->affected_rows;

    $pagesFields = createEmptyPages($formData);
    $pagesQuery = createSQLQuery('INSERT INTO', 'pages', $pagesFields['targetFields'], implode(',', $pagesFields['sourceFields']));
    $db->query($pagesQuery);
    $affectedRows .= $db->affected_rows;
    // print_r('added Issues affected -> '.$affectedRows.' rows');
    unset($_POST["newIssue"]);
    unset($_POST["newYear"]);
    unset($_POST["newPageCount"]);
    unset($_POST["dialogType"]);
}

function deleteDatabaseIssue($db, $formData) : void
{
    print_r('delete Issues -> <br>');
    $magazineId = $formData['magazineId'];
    $issueId = $formData['issueId'];
    $year = $formData['issueYear'];
    $target_dir = '../';
    $listLayoutQuery = 'SELECT * FROM `pages` WHERE `magazine_id` = "'.$magazineId.'" AND `issue_id` = "'.$issueId.'" AND `year` = "'.$year.'";';
    $layoutUpdate = $db->query($listLayoutQuery);
    $isFilledResponse = $layoutUpdate -> {'num_rows'};
    if($isFilledResponse) {
        while($singleLayout = $layoutUpdate->fetch_assoc()) {
            $thumb = utf8_encode($singleLayout['background_image']);
            $layout = utf8_encode($singleLayout['layout_file']);
            if(!empty($thumb)) {
                $jpeg = str_replace('-thumb-', '-', $thumb);
                print_r($thumb.' jpeg -> '.$jpeg);
                unlink($target_dir.$jpeg);
                unlink($target_dir.$thumb);
            }
            if(!empty($layout)) {
                print_r($layout);
                unlink($target_dir.$layout);
            }
        }
    }
    $affectedRows = '';
    $deletePagesQuery = 'DELETE FROM `pages` WHERE `magazine_id`='.$magazineId.' AND `issue_id`='.$issueId.' AND `year`='.$year.';';
    $deleteIssueQuery = 'DELETE FROM `issue` WHERE `magazine_id`='.$magazineId.' AND `issue`='.$issueId.' AND `year`='.$year.';';
    $db->query($deletePagesQuery);
    $affectedRows .= $db->affected_rows;
    $db->query($deleteIssueQuery);
    $affectedRows .= $db->affected_rows;
    // print_r('deleted Issues affected -> '.$affectedRows.' rows');
    unset($_POST["deleteIssue"]);
    unset($_POST["deleteYear"]);
    unset($_POST["dialogType"]);
}
if($_POST["dialogType"] == "addIssue") {
    $_POST["dialogType"] = 0;
    $formData = array(
        'issueId' => $_POST["newIssue"],
        'issueYear' => $_POST["newYear"],
        'magazineId' => $_POST["magazines"],
        'issuePageAmount' => $_POST["newPageCount"],
    );
    $db = $GLOBALS['db'];

    createNewDatabaseIssue($db, $formData);
}
if($_POST["dialogType"] == "deleteIssue") {
    $_POST["dialogType"] = 0;
    $formData = array(
        'issueId' => $_POST["deleteIssue"],
        'issueYear' => $_POST["deleteYear"],
        'magazineId' => $_POST["magazines"],
    );
    $db = $GLOBALS['db'];

    deleteDatabaseIssue($db, $formData);
}