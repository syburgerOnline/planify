<?php
function createSelectBox($id, $name, $label, $selectItems, $selected, $onFormChangedFunction, $relatedSelectBox = null, $permissionRelated = array()): string
{   $disabled = '';
    if(!empty($permissionRelated) && !empty($onFormChangedFunction)) {
        $userPermission = checkForUserPermissions($_POST['magazines'], $permissionRelated);
        $disabled = $userPermission ? 'onchange="'.$onFormChangedFunction.'"' : 'disabled';
    } else if(!empty($onFormChangedFunction)) {
        $userPermission = checkForUserPermissions($_POST['magazines'], $permissionRelated);
        $disabled = $userPermission ? 'onchange="'.$onFormChangedFunction.'"' : '';
    }
    $tmpName = $id ? $name.'_'.$id : $name;
    $tmpId = $id ? $name.'Select_'.$id : $name.'Select';
    $labelElement = $label == '' ? '' : '<label for="'.$name.'"><h2>'.$label.'</h2></label>';
    $selectTag = '<select name="'.$tmpName.'" id="'.$tmpId.'" '.$disabled.' autocomplete="off">';
    $selectObj = $labelElement.$selectTag;
    $countStart = 0;
    /**
     * hack for layout final in status dropdown
     */
    // print_r(count($selectItems));
    if($name == 'articleStatus' && $selected == count($selectItems)-1 && !checkForUserPermissions($_POST['magazines'], [6])) {
        $countStart = count($selectItems) - 1;
    }

    for($key=$countStart; $key < count($selectItems); $key ++){
        // print_r($selectItems[$key]);
        $val = $selectItems[$key];
        $tmpId = !empty($val['value']) ?  $val['value'].','.$id : $key;
        $tmpFunction = str_replace('#',$tmpId,$val['function']);
        $tmpValue = !empty($val['value']) ?  $val['value'] : null;
        $tmpPermission = $val['permissionGroup'];
        $tmpArray = $val;
        $selectedItem = $key == $selected;
        $tmpTitle = $val['title'];
        // $yearTextValue = $tmpArray['year'] ? '<input type="text" name="year" value="'.$tmpArray['year'].'">' : '';
        if(!empty($permissionRelated)) {
            $userPermission = checkForUserPermissions($key, $permissionRelated);
            if ($name == 'issue') {
                $issueValue = $tmpArray['id'];//.'_'.$tmpArray['year'];
                $selectedIssue = $issueValue == $selected;
                $selectObj .= createOptionsItem($key, $tmpTitle, $selectedIssue, $tmpFunction, $issueValue, $tmpArray, $permissionRelated, $tmpPermission, ' click-item');
            } else {
                $selectObj .= createOptionsItem($key, $tmpTitle, $selectedItem, $tmpFunction, $tmpValue, $tmpArray, $permissionRelated, $tmpPermission);
            }
            // if($userPermission) {

            // }
        } else {
            $selectObj .= createOptionsItem($key, $tmpTitle, $selectedItem, $tmpFunction, $tmpValue, $tmpArray);
        }
    }
    /*
    foreach($selectItems AS $key => $val) {
        $tmpId = !empty($val['value']) ?  $val['value'] : $key;
        $tmpFunction = str_replace('#',$tmpId,$val['function']);
        $tmpValue = !empty($val['value']) ?  $val['value'] : null;
        $tmpPermission = $val['permissionGroup'];
        $tmpArray = $val;
        $selectedItem = $key == $selected;
        $tmpTitle = $val['title'];
        // $yearTextValue = $tmpArray['year'] ? '<input type="text" name="year" value="'.$tmpArray['year'].'">' : '';
        if(!empty($permissionRelated)) {
            $userPermission = checkForUserPermissions($key, $permissionRelated);
            // if($userPermission) {
            $selectObj .= createOptionsItem($key, $tmpTitle, $selectedItem, $tmpFunction, $tmpValue, $tmpArray, $permissionRelated, $tmpPermission);
            // }
        } else {
            $selectObj .= createOptionsItem($key, $tmpTitle, $selectedItem, $tmpFunction, $tmpValue, $tmpArray);
        }
    }*/
    //$hiddenInput = $relatedSelectBox ? '<input type="text" name="'.$relatedSelectBox.'" id="'.$relatedSelectBox.'" value="0">' : '';
    $selectObj .= '</select>'; // .$hiddenInput;
    return $selectObj;
}
function createOptionsItem($id, $title, $selectedItem, $call, $value=null, $tmpArray, $permissionGroup = array(), $tmpPermission = array(), $style=null): string
{
    $tmpValue = $value ? $value : $id;
    $yearValue = $tmpArray['year'] ? 'year='.$tmpArray['year'] : '';
    $clickTag = 'disabled';
    if(!empty($permissionGroup)) {
        $clickTag = checkForUserPermissions($_POST['magazines'], $permissionGroup) ? 'ontouchstart="'.$call.'" onclick="'.$call.'" onselect="'.$call.'"' : 'disabled';
        if(!empty($tmpPermission)) {
            // print_r(implode(',',$tmpPermission).' -> '.$title.' -> '.implode(',',$permissionGroup)."<br>");
            $clickTag = checkForUserPermissions($_POST['magazines'], $tmpPermission) ? 'ontouchstart="'.$call.'" onclick="'.$call.'" onselect="'.$call.'"' : 'disabled';
        }
    } else {
        // $clickTag = 'ontouchend="'.$call.'" onclick="'.$call.'"';
    }

    return $selectedItem? '<option value="'.$tmpValue.'" '.$yearValue.' class="listItem'.$style.'" '.$clickTag.' selected>'.$title.'</option>' : '<option value="'.$tmpValue.'" '.$yearValue.' class="listItem'.$style.'" '.$clickTag.'>'.$title.'</option>';
}
function createButton($type, $value, $action, $id):string
{
    // $button = '<'.$type.' class="button cancel" id="'.$id.'" onclick="'.$action.'">'.$value.'</'.$type.'>';
    $button = '<input type="submit" value="'.$value.'" id="'.$id.'" onclick="'.$action.'" class="button cancel">';
    if ($type == 'submit') {
        $button = '<input type="submit" value="'.$value.'" id="'.$id.'" onclick="'.$action.'" class="button save">';
    }
    return $button;
}