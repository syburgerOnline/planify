<?php
function createNewDatabasePage($type, $articleId, $adId): void
{
    print($type.' articleId -> '.$articleId.' adId -> '.$adId);
}
if($_POST["dialogType"] == "addArticle") {
    $formData = $_POST;
    // var_dump($formData);
    $articleId = $_POST["addArticle"];
    $adId = $_POST["addAd"];
    createNewDatabasePage($_POST["dialogType"], $articleId, $adId);
}