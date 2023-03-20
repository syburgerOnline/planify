<?php
function getIconStyle ($style): string
{
    $newStyle = '';
    foreach($style AS $key => $val) {
        $newStyle .= $key.$val.';';
    }
    return $newStyle;
}
function createAddAdIcon($class): string
{
    return '<div class="add-ad-icon">'.
            '<div class="add-ad-icon-background">'.
                '<div class="add-ad-icon-page"></div>'.
                '<div class="add-ad-icon-page"></div>'.
            '</div>'.
            '<div class="add-ad-icon-ad '.$class.'"></div>'.
        '</div>';
}
function createSubmenuTools($type, $action, $typeOfAction, $draggable, $scope): string
{
    $submenu = '<div class="tool-submenu-list">';

    foreach($scope AS $key => $val) {
        // var_dump($scope);
        $title = $val['title'];
        $class =  $val['class'];
        $value =  $val['value'];
        $selected = $val['selected'] ? 'selected' : '';
        $function = str_replace('#',$key,$val['function']);
        if ($typeOfAction == 'mouseover') {
            $mouseFunction = 'onmouseover="'.$function.'"';
        } else if ($typeOfAction == 'click') {
            $mouseFunction = 'onclick="'.$function.'"';
        } else {
            $mouseFunction = '';
        }
        // $title.
        $layerContent = createAddAdIcon($class);
        $dragOptions = $draggable === 'draggable' ? 'draggable="true" ondragstart="drag(event)"' : '';
        $dragItem = '<div class="tool-submenu-item btn drag-item" '.$mouseFunction.' id="dragItem_submenu_'.$type.'_'.$key.'" '.$dragOptions.' title="'.$title.'">'.
                        '<div class="submenu_active active">'.$layerContent.'active'.'</div>'.
                        '<div class="submenu_passive passive">'.$layerContent.'passiv'.'</div>'.
                        '<input type="hidden" id="advalue_'.$key.'" value="'.$value.'">'.
                        '<input type="hidden" id="adclass_'.$key.'" value="'.$class.'">'.
                    '</div>';

        $dragItemBase = '<div class="tool-submenu-item btn drag-item" title="'.$title.'">'.
                        $layerContent.
                        '</div>';

        $submenu .= '<div class="tool-submenu-list-item" '.$selected.'>'.$dragItemBase.$dragItem.'</div>';
    }
    $submenu .= '</div>';
    return $submenu;
}
function createTool($type, $action, $typeOfAction, $draggable, $submenu): string
{
    $newArticleIcon = $GLOBALS['newArticleIcon']; // = '<i class="gg-file-document"></i>';
    $newAdIcon = $GLOBALS['newAdIcon']; // = '<i class="gg-file-document"></i>';
    $saveIcon = $GLOBALS['saveIcon']; // = '<i class="fa-solid fa-floppy-disk save-icon"></i>';
    $notifyIcon = $GLOBALS['notifyIcon']; //  = '<i class="gg-mail"></i>';
    $printAdIcon = $GLOBALS['printAdIcon'];
    $downloadIcon = $GLOBALS['downloadIcon'];
    $icon = '';
    $style = '';
    $mouseover = '';
    $dragIcon = '';
    $submenuItem = '';
    $dragOptions = $draggable === 'draggable' ? 'draggable="true" ondragstart="drag(event)"' : '';
    // $pseudoIcon = '';
    if($type == 'addArticle') {
        $mouseover = $newArticleIcon['mouseover'];
        $icon = $newArticleIcon['icon'];
        $dragIcon = $newArticleIcon['icon'];
        $style = $newArticleIcon['style'];
    } else if($type == 'addAd') {
        $mouseover = $newAdIcon['mouseover'];
        $icon = $newAdIcon['icon'];
        $dragIcon = $newAdIcon['icon'];
        $style = $newAdIcon['style'];
    } else if($type == 'notify') {
        $mouseover = $notifyIcon['mouseover'];
        $icon = $notifyIcon['icon'];
        $dragIcon = $notifyIcon['icon'];
        $style = $notifyIcon['style'];
    }else if($type == 'saveArticle') {
        $mouseover = $saveIcon['mouseover'];
        $icon = $saveIcon['icon'];
        $dragIcon = $saveIcon['icon'];
        $style = $saveIcon['style'];
    }else if($type == 'printAd') {
        $mouseover = $printAdIcon['mouseover'];
        $icon = $printAdIcon['icon'];
        $dragIcon = $printAdIcon['icon'];
        $style = $printAdIcon['style'];
    }else if($type == 'download') {
        $mouseover = $downloadIcon['mouseover'];
        $icon = $downloadIcon['icon'];
        $dragIcon = $downloadIcon['icon'];
        $style = $downloadIcon['style'];
    }
    $mouseAction = !$submenu ? $action : "showHideSubmenu('".$type."','show')";
    switch($typeOfAction) {
        case 'mouseover': {
            $buttonAction = 'onmouseover="'.$mouseAction.'"';
            break;
        }
        case 'click': {
            $buttonAction = 'onclick="'.$mouseAction.'"';
            break;
        }
        default: {
            $buttonAction = '';
        }
    }
    if($submenu) {
        $submenuItem = '<div class="toolbox-submenu-item '.$type.' tool-submenu" id="subMenu_'.$type.'">'.
            createSubmenuTools($type, $action, 'mouseover', 'draggable', $GLOBALS['adTypeItems'][$_POST['magazines']]).
            '</div>';
    }
    $btnIcon = '<div class="toolbox-item btn"><i class="'.$icon.' tool" style="'.getIconStyle($style).'"></i></div>';
    $btnDragIcon = '<div class="toolbox-item btn"><i class="'.$dragIcon.' tool" style="'.getIconStyle($style).'"></i></div>';
    $dragElement = '<div class="toolbox-item btn drag-item" id="dragItem_'.$type.'" '.$dragOptions.' '.$buttonAction.' title="'.$mouseover.'">'.
                        '<div class="toolbox-item btn drag-item passive" id="dragItem_'.$type.'_passive">'.$btnIcon.'</div>'.
                        '<div class="toolbox-item btn drag-item active" id="dragItem_'.$type.'_active">'.$btnDragIcon.'</div>'.
                    '</div>';

    return '<div class="toolbox-item '.$type.' tool">'.
        // $btnIcon.
        $dragElement.
        '</div>'.$submenuItem;
}