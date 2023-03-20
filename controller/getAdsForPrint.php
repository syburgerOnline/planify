<?php
require '../inc/db.php';
require '../conf/adTypes.php';
function getAdsForPrint($data, $db): void
{
    $newData = file_get_contents( "php://input" );
    $formData = json_decode($newData);
    $printJob = (array) $formData;
    $magazines = $printJob['magazines'];
    $magazineTitle = $printJob['title'];
    $issue = $printJob['issue'];
    $year = $printJob['year'];
    $adTypes = $GLOBALS['adTypeItems'][$magazines];

    $printQuery = "SELECT * FROM `pages` WHERE `magazine_id`=".$magazines." AND `issue_id`=".$issue." AND `year`=".$year." AND `has_ad`='true'";
    $printDetails = $db->query($printQuery);
    // print_r('PrintJob -> '.$printQuery."\n");
    $isFilledResponse = $printDetails -> {'num_rows'};
    $ads = [];
    $adTypeName = '';
    $adId = 0;
    $page='';
    // $htmlContent = '<table>';
    $tableContent = '<tr><th>Seite</th><th>Titel</th><th>Größe</th><th>Magazin</th><th>Ausgabe</th><th>Jahr</th></tr>';
    if($isFilledResponse) {
        while ($singlePrintItem = $printDetails->fetch_assoc()) {
            $adTitle = $singlePrintItem['ad_title'] ? utf8_encode($singlePrintItem['ad_title']) : 'Kein Titel vergeben';
            $adPage = utf8_encode($singlePrintItem['page_id']);
            $adType = utf8_encode($singlePrintItem['ad_type']);
            $adIdInData = utf8_encode($singlePrintItem['ad_id']);
            foreach($adTypes AS $key => $value) {
                $adClassType= 'ad'.$value['class'];
                if($adClassType == $adType) {
                    $adTypeName = $value['title'];
                    break;
                }
            }
            $issueId = utf8_encode($singlePrintItem['issue_id']);
            $magazineId = utf8_encode($singlePrintItem['magazine_id']);
            $year = utf8_encode($singlePrintItem['year']);
            if($adId != $adIdInData){
                $adId = $adIdInData;
                $page = $adPage;
                $ads[] = [$adIdInData, $page, $adTitle, $adType, $adTypeName != '' ? $adTypeName : $adType, $magazineTitle, $issueId];
                //$tableContent .= '<tr>'.$adTypeName != '' ? $adTypeName : $adType.'</tr>';
                $tableContent .= '<tr><th>'.$page.'</th><th>'.$adTitle.'</th><th>'.$adTypeName.'</th><th>'.$magazineTitle.'<th>'.$issueId.'</th><th>'.$year.'</th></tr>';
            }
        }
        $htmlContent = '<table class="print-table border" border="1">'.$tableContent.'</table>';
        echo '<div class="print-content" id="printTable">'.$htmlContent.'</div>';
    } else {
        print_r('no Ads found');
    }

}
getAdsForPrint($_POST, $db);
