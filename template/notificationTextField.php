<?php
function setLabelForNotificationField($field, $fieldName): string {
    return '<label for="'.$field.'">'.$fieldName.': </label>';
}
function createNotificationTextField($notificationArray): string
{
    $notificationGroupArray = $GLOBALS['loginArray'];
    $notificationTo = '';
    $permissionsAlreadyExist = 0;
    $emailArray = [];
    $nameArray = [];
    $mailToFields = '';
    foreach($notificationArray AS $key => $val){
        foreach($val AS $keyNew => $valNew) {
            $permission = $valNew[0];
            if($permissionsAlreadyExist != $permission) {
                $permissionsAlreadyExist = $permission;
                $emailArray[$valNew[0]] = [$valNew[1]['email']];
                $nameArray[$valNew[0]] = [$valNew[1]['name']];
            } else {
                $emailArray[$valNew[0]][] = $valNew[1]['email'];
                $nameArray[$valNew[0]][] = $valNew[1]['name'];
            }
            $notificationTo .= $valNew[0]."\n";
            $email = $valNew[1]['email'];
            $notificationTo .= 'email: '.$email."\n";
            foreach($valNew[1] AS $keyMail => $valMail) {
                // $notificationTo .= $keyMail.' ->'.$valMail. "\n";
            }
        }
        $label = setLabelForNotificationField('notificationTo_'.$key, $notificationGroupArray[$key]['title']);
        $mailToFields .= $label.'<textarea type="text" name="notificationTo_'.$key.'" id="notificationTo_'.$key.'">'.implode(',',$emailArray[$key]).'</textarea><br>'.
            '<textarea type="text" name="notificationNameTo_'.$key.'" id="notificationNameTo_'.$key.'">'.implode(',',$nameArray[$key]).'</textarea>'.
            '<br>';
    }
    // Magazine
    $magazine = setLabelForNotificationField('notificationMagazine','Magazin');
    $notificationFormFieldMagazine = $magazine.'<input type="text" name="notificationMagazine" id="notificationMagazine" value="'.$_POST["magazines"].'"><br><br>';
    // Issue
    $issue = setLabelForNotificationField('notificationIssue','Ausgabe');
    $notificationFormFieldIssue = $issue.'<input type="text" name="notificationIssue" id="notificationIssue" value="'.$_POST["issue"].'"><br><br>';
    // Articles
    $articlePages = setLabelForNotificationField('notificationArticlePages','Seiten');
    $articleTitles = setLabelForNotificationField('notificationArticleTitles','Titel');
    $articleStatus = setLabelForNotificationField('notificationArticleStatus','Status');
    $notificationFormFieldArticlePages = $articlePages.'<input type="text" name="notificationArticlePages" id="notificationArticlePages"><br>';
    $notificationFormFieldArticleTitles = $articleTitles.'<input type="text" name="notificationArticleTitles" id="notificationArticleTitles"><br>';
    $notificationFormFieldArticleStatus = $articleStatus.'<input type="text" name="notificationArticleStatus" id="notificationArticleStatus"><br>';
    // Ads
    $adPages = setLabelForNotificationField('notificationAdPages','Seiten');
    $adTitles = setLabelForNotificationField('notificationAdTitles','Titel');
    $adStatus = setLabelForNotificationField('notificationAdStatus','Status');
    $notificationFormFieldAdPages = $adPages.'<input type="text" name="notificationAdPages" id="notificationAdPages"><br>';
    $notificationFormFieldAdTitles = $adTitles.'<input type="text" name="notificationAdTitles" id="notificationAdTitles"><br>';
    $notificationFormFieldAdStatus = $adStatus.'<input type="text" name="notificationAdStatus" id="notificationAdStatus"><br>';

    // Article
    $article = setLabelForNotificationField('notificationArticle','Artikel');
    $notificationFormFieldArticle = $article.'<div class="notification content" id="notificationArticle">'.$notificationFormFieldArticlePages.$notificationFormFieldArticleTitles.$notificationFormFieldArticleStatus.'</div><br><br>';
    // Ads
    $ad = setLabelForNotificationField('notificationAd','Anzeigen');
    $notificationFormFieldAd = $ad.'<div class="notification content" id="notificationAd">'.$notificationFormFieldAdPages.$notificationFormFieldAdTitles.$notificationFormFieldAdStatus.'</div>';

    return '<div class="notification" id="notificationContainer"><form action="index.php" method="POST" id="notification">'.$mailToFields.$notificationFormFieldMagazine.$notificationFormFieldIssue.$notificationFormFieldArticle.$notificationFormFieldAd.'</form></div>';
}
