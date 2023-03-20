<?php
function createPage($content,$id, $direction): string
{
    // $direction .= $backgroundImage ? 'background {background: url(../img/'.$backgroundImage.') no-repeat;}' : 'no-background';
     return '<div class="page '.$direction.'" id="'.'page_'.$id.'" ondrop="drop(event)" ondragover="allowDrop(event)">'.$content.'</div>';
}

function openTheme($pageContent,$id, $contentType, $direction): string
{
    $functionRemoveArticle = "removeArticle('".$id."')";
    $functionRemoveAd = "removeAd('".$id."')";
    $functionUploadLayout = "openClose('editDialog', 'open', 'uploadImage', 'Layout Seite ".$id." hochladen', '".$id."')";// "uploadArticle('".$id."')";
    $uploadIcon = $GLOBALS['uploadIcon'];
    $articleColor = $contentType['type'] == 'content'? 'white' : 'red';
    $adColor = 'red';
    $contentStatusContainer = ''; // createStatusContainer($contentType['status'], $id);
    $displayArticle = !empty($pageContent)? 'block' : 'none';
    $displayAd = $contentType['type'] == 'ad'? 'block' : 'none';
    $articleTitle = $contentType['title'];
    $adTitle = $contentType['adTitle'];
    $adStatus = $contentType['ad_status'] ?: 'planned';
    $articleStatus = $contentType['article_status']?: 'planned';
    //Articles //
    $mouseOverArticleFunction = "showHideUploadIcon('".$id."', this, 1)";
    $mouseOutArticleFunction = "showHideUploadIcon('".$id."', this, 0)";
    $mouseDownArticleFunction = "articleMouseDown('".$id."', this)";

    $additionalMouseOverArticleFunction = "showHideArticleInfoPanel('".$id."',this, 1)";
    $additionalMouseOutArticleFunction = "showHideArticleInfoPanel('".$id."',this, 0)";
    // print_r('articleStatus -> '.$articleStatus);

    // Ads //
    $mouseOverFunction = "swapZIndex('".$id."', this)";
    $mouseOutFunction = "swapZIndexBack('".$id."', this)";
    $mouseDownFunction = "adMouseDown('".$id."', this)";
    $varsForJavaScript = implode(',', $contentType);

    // $adStatusBar and articleStatusbar
    $adStatusSelectItems = $GLOBALS['adStatusValues'][$_POST['magazines']];
    $selectedStatusArray = array();
    foreach($adStatusSelectItems AS $key => $val) {
        $tmpName = explode(",", $val['value'])[1];
        $name = preg_replace('/[^A-Za-z0-9\-]/', '', $tmpName);
        $selectedStatusArray[$name] = $key;
    }
    $selectedAdStatus = $selectedStatusArray[$adStatus];
    $selectedArticleStatus = $selectedStatusArray[$articleStatus];

    $varsForJavaScript .= ','.count($selectedStatusArray);
    if(checkForUserPermissions($_POST['magazines'], [6]) && $articleStatus == 'layout-final') {
        $articleDisabled = ' onmouseover="'.$mouseOverArticleFunction.'" onmouseout="'.$mouseOutArticleFunction.'" onmousedown="'.$mouseDownArticleFunction.'"';
        $adDisabled = ' onmousedown="'.$mouseDownFunction.'"';

    } else if(checkForUserPermissions($_POST['magazines'], [5, 6]) && $articleStatus != 'layout-final') {
        $articleDisabled = ' onmouseover="'.$mouseOverArticleFunction.'" onmouseout="'.$mouseOutArticleFunction.'" onmousedown="'.$mouseDownArticleFunction.'"';
        $adDisabled = ' onmousedown="'.$mouseDownFunction.'"';

    } else if(checkForUserPermissions($_POST['magazines'], [3]) && $articleStatus != 'layout-final') {
        $articleDisabled = ' onmouseover="'.$mouseOverArticleFunction.'" onmouseout="'.$mouseOutArticleFunction.'" disabled';
        $adDisabled = ' disabled';
    } else {
        $articleDisabled = ' onmouseover="'.$additionalMouseOverArticleFunction.'" onmouseout="'.$additionalMouseOutArticleFunction.'" disabled';
        $adDisabled ='disabled';
    }
    if ($articleStatus != 'layout-final') {
        $removeArticleDisabled = checkForUserPermissions($_POST['magazines'], [5, 6]);
    } else{
        $removeArticleDisabled = checkForUserPermissions($_POST['magazines'], [6]);
    }
    if ($adStatus != 'layout-final') {
        $removeAdDisabled = checkForUserPermissions($_POST['magazines'], [5, 6]);
    } else{
        $removeAdDisabled = checkForUserPermissions($_POST['magazines'], [6]);
    }


    $inputTitleField = '<textarea autocomplete="off" class="theme-content-title" name="theme-content-title_'.$id.'" id="theme-content-title_'.$id.'" rows="3" placeholder="Type in here" value="'.$articleTitle.'" '.$articleDisabled.'>'.$articleTitle.'</textarea>';
    $inputAdTitleField = '<textarea autocomplete="off" class="theme-ad-title" name="theme-ad-title_'.$id.'" id="theme-ad-title_'.$id.'" rows="1" placeholder="Type in here" value="'.$adTitle.'" onmouseover="'.$mouseOverFunction.'" onmouseout="'.$mouseOutFunction.'" '.$adDisabled.'>'.$adTitle.'</textarea>';
    $hiddenField = '<input class="theme-content-type" type="hidden" name="contentType_'.$id.'" value="'.$varsForJavaScript.'">';
    $hiddenAdField = '<input class="ad-content-type" type="hidden" name="adType_'.$id.'" value="'.$varsForJavaScript.'">';
    $mouseoverArticleClose = 'Artikel entfernen';
    $closeContent = $removeArticleDisabled ? '<div class="theme-close" id="theme-close_'.$id.'" onclick="'.$functionRemoveArticle.'" title="'.$mouseoverArticleClose.'">x</div>' : '';
    $mouseoverAdClose = 'Anzeige entfernen';
    $closeAd = $removeAdDisabled ? '<div class="ad-close" id="ad-close_'.$id.'" onclick="'.$functionRemoveAd.'" title="'.$mouseoverAdClose.'">x</div>' : '';
    $adBackground = '<div class="ad-background '.$adColor.'" id="ad-background_'.$id.'" onmouseover="'.$mouseOverFunction.'" onmouseout="'.$mouseOutFunction.'" onmousedown="'.$mouseDownFunction.'"></div>';
    $resizeRight = '<div class="theme-resize_right" id="theme-resizeRight_'.$id.'"></div>';

    $icon = $uploadIcon['icon'];
    $style = $uploadIcon['style'];
    $mouseover = $uploadIcon['mouseover'];
    if($articleStatus != 'layout-final') {
        $articleUploadButton = checkForUserPermissions($_POST['magazines'], [3, 4, 6]) ? '<div class="article-upload" id="articleUpload_'.$id.'" onmouseover="'.$mouseOverArticleFunction.'" onmouseout="'.$mouseOutArticleFunction.'" onclick="'.$functionUploadLayout.'"><i class="'.$icon.'" style="'.getIconStyle($style).'" title="'.$mouseover.'"></i></div>' : '';
    } else {
        $articleUploadButton = checkForUserPermissions($_POST['magazines'], [6]) ? '<div class="article-upload" id="articleUpload_'.$id.'" onmouseover="'.$mouseOverArticleFunction.'" onmouseout="'.$mouseOutArticleFunction.'" onclick="'.$functionUploadLayout.'"><i class="'.$icon.'" style="'.getIconStyle($style).'" title="'.$mouseover.'"></i></div>' : '';
    }

    // print_r('selected -> '.$selectedAdStatus.$selectedArticleStatus.'<br>');

    $onArticleStatusChangedFunction = "setStatusValue(null, null,".$id.")";
    $adStatusBarContent = createSelectBox($id,'adStatus','',$adStatusSelectItems,$selectedAdStatus,'','',[3, 4, 5, 6]);// '<div class="statusbar-triangle"></div>';
    $articleStatusBarContent = createSelectBox($id,'articleStatus','',$adStatusSelectItems,$selectedArticleStatus,$onArticleStatusChangedFunction,'',[3, 4, 5, 6]);// '<div class="statusbar-triangle"></div>';

    $adStatusBarDropDown = '<div class="statusbar-rectangle">'.$adStatusBarContent.'</div>';
    $adStatusBar = ''; // '<div class="statusbar '.$adStatus.'" id="adStatus_'.$id.'">'.$adStatusBarDropDown.'</div>';
    $articleStatusBarDropDown = '<div class="statusbar-rectangle">'.$articleStatusBarContent.'</div>';
    $articleStatusBar = '<div class="statusbar '.$articleStatus.'" id="articleStatus_'.$id.'">'.$articleStatusBarDropDown.'</div>';
    // return value ->
    // $articleStatusBar.
    return '<div class="theme '.$direction.'" id="theme_'.$id.'">'.
    '<div id="theme-content_'.$id.'" class="theme-content '.$articleColor.'" style="display:'.$displayArticle.';">'.$closeContent.$articleUploadButton.$articleStatusBar.$inputTitleField.$contentStatusContainer.$resizeRight.$hiddenField.'</div>'.
    '<div id="ad-content_'.$id.'" class="ad-content" style="display:'.$displayAd.';">'.$closeAd.$inputAdTitleField.$adBackground.$hiddenAdField.$adStatusBar.'</div>';
}
function closeTheme(): string
{
    return '</div>';
}
function createStatusContainer($contentStatus, $id=null): string
{
    // '<a href=" " title="This is some text I want to display." style="background-color:#FFFFFF;color:#000000;text-decoration:none">This link has mouseover text.</a>'
    // $hoverText = ucfirst($contentStatus);
    // <div class="theme-menu '.$contentStatus.'">
    // $iconEdit = '<i class="fa-solid fa-file-pen"></i>';
    // $themeStatus = '<div class="theme-status"><div class="theme-icon">'.$iconEdit.'</div></div>';
    // fa-solid fa-up-right-and-down-left-from-center

    $iconEditArticle = '<i class="fa-solid fa-up-right-and-down-left-from-center"></i>';
    $functionEditTheme = "editTheme('".$id."')";
    $iconMenu = '<div class="theme-menu-icon show-menu"><i class="fa-solid fa-ellipsis"></i></div>';
    $iconEdit = '<div class="theme-menu-icon edit-theme">
                       <div class="theme-menu-icon edit-theme-icon" id="dragEditArticleItem_passive_'.$id.'">
                            '.$iconEditArticle.'
                       </div>
                       </div>';
    /*
                       <div class="theme-menu-icon edit-theme-icon" id="dragEditArticleItem_active_'.$id.'" onmouseover="'.$functionEditTheme.'" draggable="true" ondragstart="drag(event)">
                            '.$iconEditArticle.'
                       </div>
                    <!-- <i class="fa-solid fa-up-right-and-down-left-from-center"></i> -->
                 </div>';
    */
    // $statusMenu = '<div class="theme-menu">'.$iconMenu.$iconEdit.'</div>';
    return '<div class="theme-menu">'.$iconMenu.$iconEdit.'</div>'; // '<a href=" " title="'.$hoverText.'" style="text-decoration: none; color: inherit;">'.$statusMenu.$themeStatus.'</a>';
}
function createSinglePage($pageCount, $pageContent, $contentType, $contentArray): string
{
    $class = '';
    if($contentArray['page_id_start'] == $contentArray['page_id_end']) {
        $class = 'single-page-content-complete';
        // print_r('samePageContent'.$contentArray['page_id'].'<br>');
    } else if($contentArray['page_id_start'] == $contentArray['page_id']) {
        $class = 'single-page-content-start';
        // print_r('pageContent Start'.$contentArray['page_id'].'<br>');
    } else if($contentArray['page_id_end'] == $contentArray['page_id']) {
        $class = 'single-page-content-end';
        // print_r('pageContent End'.$contentArray['page_id'].'<br>');
    } else if($contentArray['page_id_start'] < $contentArray['page_id'] && $contentArray['page_id_end'] > $contentArray['page_id']) {
        $class = 'single-page-content-mid';
        // print_r('pageContent Mid'.$contentArray['page_id'].'<br>');
    }
    $adSizes = array('1/1' => 'full','1/2' => 'half', '1/3' => 'third');
    $countPos = ($pageCount % 2 == 0)? 'left-pos' : 'right-pos';
    $pageClass = ($pageCount % 2 == 0)? 'left-page' : 'right-page';

    /**
     * background mouseover and zoom function
     */
    if($contentType['background_image']) {
        $pageClass .= ' uploaded-background';
        $thumbName = $contentType['background_image'];
        $styleAttributes = ' style="background: url('.$thumbName.') no-repeat; background-size: cover; background-position-x: center;"';
        $backgroundMouseOverTitle = ' title="Layout ansehen."';
        /**
         * attention - writing the thumb name to the pic_name AND the pdf fileName
         * 018_027_ImDoppelPack_02_2022-thumb-6.jpg
         * trying to replace -thumb-6.jpg
         */

        $thumbPos = strpos($thumbName,'-thumb-');
        $jpegName = str_replace('-thumb','',$contentType['background_image']);
        $pdfName = substr($thumbName,0, $thumbPos).'.pdf';
        $backgroundArray = "{articleId:'".$contentType['contentId']."', pageId:'".$contentType['id']."', magazineId:'".$_POST['magazines']."',issueId:'".$_POST['issue']."',image:'".$jpegName."',file:'".$pdfName."'}";
        $backgroundMouseOverFunction = ' onclick="showLayout(event,'.$contentType['id'].','.$backgroundArray.');" ontouchstart="showLayout(event,'.$contentType['id'].','.$backgroundArray.');"';
        $pageAttributes = $styleAttributes.$backgroundMouseOverTitle.$backgroundMouseOverFunction;
    } else {
        $pageClass .= ' no-background';
        $pageAttributes = '';
    }

    $adType = $contentType['type'] == 'ad'? $adSizes[$contentType['ad_type']] : '';
    $adContent = $contentType['type'] == 'ad'? $contentType['title'] : '';
    $contentStatusContainer = createStatusContainer($contentType['status'], $contentType['id']);
    $contentDiv = $contentType['type'] == 'content'? '<div class="single-page-content '.$class.'">'.'</div>' : '<div class="single-page-ad '.$adType.'">'.$adContent.$contentStatusContainer.'</div>';
    return '<div class="single-page '.$pageClass.'" '.$pageAttributes.'>'.$contentDiv.'<div class="single-page-count '.$countPos.'">'.$pageCount.'</div></div>';
}
function createPageStatusBar(): string
{
    $label = '<label for="statusBarProgressContainer"><h2>Status der Ausgabe</h2></label>';
    $statusBar = '<div class="statusbar-progress bar" id="statusBarProgressStatus"></div>';
    return '<div class="statusbar-progress">'.$label.'<div class="statusbar-progress container" id="statusBarProgressContainer" name="statusBarProgressContainer">'.$statusBar.'</div></div>';
}
function createTestField(): string
{
    $label = '<br><label for="testTextContainer">Diagnose Werte</label>';
    $buttonClear = '<button id="testClearButton"> -clear- </button><br>';
    return '<div class="test-container">'.$label.$buttonClear.'<textarea type="text" class="test-container" id="testTextContainer" name="testTextContainer" autocomplete="off" multiline></textarea></div>';
}
