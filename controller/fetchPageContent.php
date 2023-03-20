<?php
function fetchPageContent($db, $contentId, $pageId, $magazineId, $issueId)
{
    $contentQuery = "SELECT * FROM `content` WHERE `id` =".$contentId." AND `magazine_id` =".$magazineId." AND `issue_id` =".$issueId;
    $contentDetails = $db->query($contentQuery);
    $isFilledResponse = $contentDetails -> {'num_rows'};
    $contentArray = array();
    if($isFilledResponse) {
        while ($singleContent = $contentDetails->fetch_assoc()) {
            $pageIdStart = $singleContent['page_id_start'];
            $pageIdEnd = $singleContent['page_id_end'];
            $articleId = $singleContent['id'];
            $contentType = $singleContent['type'];
            $adType = $singleContent['ad_type'];
            $title = utf8_encode($singleContent['title']);
            $description = utf8_encode($singleContent['description']);
            $adStatus = utf8_encode($singleContent['ad_status']);
            $articleStatus = utf8_encode($singleContent['article_status']);
            $content = array(
                'page_id_start' => $pageIdStart,
                'page_id_end'   => $pageIdEnd,
                'page_id'       => $pageId,
                'type'          => $contentType,
                'ad_type'       => $adType,
                'title'         => $title,
                'description'   => $description,
                'ad_status'     => $adStatus,
                'article_id'    => $articleId,
                'article_status'=> $articleStatus,
            );
            $contentArray[] = $content;
        }
        return $contentArray;
    }
}
function getPages($db, $magazineId, $issueId, $year): void
{
    $pagesQuery = "SELECT * FROM `pages` WHERE `magazine_id` =".$magazineId." AND `issue_id` =".$issueId." AND `year`=".$year;
    $pagesDetails = $db->query($pagesQuery);
    $isFilledResponse = $pagesDetails -> {'num_rows'};
    $pagesArray = array();
    // $articleArray = array();
    $itemNumber = 1;
    $singlePageItem = 1;
    $pageMax = 0;
    $articleId = 0;
    $direction = '';
    $themeDirection = '';
    $content = '';
    $articleOpened = false;
    $articleOpenedAt = 0;
    if($isFilledResponse) {
        while( $singlePage = $pagesDetails->fetch_assoc()){
            $pageId = $singlePage['page_id'];
            $title = utf8_encode($singlePage['title']);
            $description = utf8_encode($singlePage['description']);
            $contentId = $singlePage['content_id'];
            $hasAd = $singlePage['has_ad'];
            $adType = $singlePage['ad_type'];
            $adTitle = $singlePage['ad_title'];
            $adId = $singlePage['ad_id'];
            // print_r('title-> '.$adTitle.'<br>');
            $adStatus = $singlePage['ad_status'];
            $articleStatus = $singlePage['article_status'];
            $backgroundImage = $singlePage['background_image'];
            $contentArray = fetchPageContent($db, $contentId, $pageId, $magazineId, $issueId);
            $page = array(
                'page_id'       => $pageId,
                'title'         => $title,
                'description'   => $description,
                'content'       => $contentArray,
                'ad_status'     => $adStatus,
                'has_ad'        => $hasAd,
                'ad_type'       => $adType,
                'ad_title'      => $adTitle,
                'ad_id'         => $adId,
                'content_id'    => $contentId,
                'article_status' => $articleStatus,
                'background_image' => $backgroundImage
            );
            $pagesArray[] = $page;
            $articleArray[] = $contentArray;
        }
        $pageMax = count($pagesArray);
    }
    foreach($pagesArray AS $key => $val) {
        $pageContent = $val['page_id'].'<br>'.$val['title'].'<br>'.$val['description'];
        // $contentType = $val['content'][0]['type'];
        $adStatus = $val['ad_status'];
        $articleStatus = $val['article_status'];
        $contentAdType = $val['ad_type'];
        $contentTitle = $val['title'];
        $adTitle = $val['ad_title'];
        $adId = $val['ad_id'];
        $contentDescription = $val['description'];
        $contentArray = $val['content'][0];
        $pageId = $val['page_id'];
        $backgroundImage = $val['background_image'];
        $contentType = $val['content'][0]['type'] == 'ad'? array(
            'type' => 'ad',
            'ad_type' => $contentAdType,
            'hasAd' => $val['has_ad'],
            'title' => $contentTitle,
            'description' => $contentDescription,
            'ad_status' => $adStatus,
            'contentId' => $val['content_id'],
            'id' => $pageId,
            'adTitle' => $adTitle,
            'adId' => $adId,
            'article_status' => $articleStatus,
            'background_image' => $backgroundImage
        ) : array(
            'type' => 'content',
            'ad_type' => $contentAdType,
            'hasAd' => $val['has_ad'],
            'title' => $contentTitle,
            'description' => $contentDescription,
            'ad_status' => $adStatus,
            'contentId' => $val['content_id'],
            'id' => $pageId,
            'adTitle' => $adTitle,
            'adId' => $adId,
            'article_status' => $articleStatus,
            'background_image' => $backgroundImage
        );
        // $articleId != $val['content_id'] && !$articleOpened
        if (!$articleOpened){
            if($itemNumber % 2 != 0 && $itemNumber > 0) {
                // Seitenzahl ungerade
                $themeDirection = 'right';
            } else if ($itemNumber > 0){
                $themeDirection = '';
            }
            // openTheme($contentTitle,$val['content_id'], $contentType, $themeDirection);
            $GLOBALS["content"] .= openTheme($contentTitle,$itemNumber, $contentType, $themeDirection);
            $articleId = $val['content_id'];
            $articleOpened = true;
            $articleOpenedAt = $itemNumber;
        }
        $pageCount = $itemNumber;
        $content .= createSinglePage($pageCount,'', $contentType, $contentArray);
        $nextContentId = $pagesArray[$singlePageItem]['content_id'];
        if ($itemNumber == 1 && $singlePageItem < $pageMax) {
            // create Cover
            $GLOBALS["content"] .= createPage($content, $itemNumber, '');
            if($articleOpened) { // && $val['content_id'] != $nextContentId) {
                $GLOBALS["content"] .= closeTheme();
                $articleOpened = false;
            }
            $content = '';
            $itemNumber++;
        } else if ($itemNumber > 1 && $singlePageItem < $pageMax) {
            // create standard page
            // setting margins
            $direction = ($itemNumber % 2 == 0) && $articleOpened && $itemNumber > $articleOpenedAt? 'left' : '';
            $GLOBALS["content"] .= createPage($content, $itemNumber, $direction);
            if($articleOpened) { //  && $val['content_id'] != $nextContentId
                $GLOBALS["content"] .= closeTheme();
                $articleOpened = false;
            }
            $content = '';
            $itemNumber++;
        } else if ($singlePageItem >= $pageMax) {
            // create BackCover
            $GLOBALS["content"] .= createPage($content, $itemNumber, '');
            if ($articleOpened){
                $GLOBALS["content"] .= closeTheme();// 'artikelende';
                $articleOpened = false;
            }
            $content = '';
            $itemNumber++;
        }
        $singlePageItem ++;
    }


}
