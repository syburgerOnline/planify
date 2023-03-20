<?php
function createPrintJobContainer($cookie): string
{
    $buttons = createButton('button', 'Abbrechen', "cancel('printDialog','printAd')", 'printDialog_cancel');
    $buttons .= createButton('submit', 'Drucken', "printDialog('printTable','printAd')", 'printDialog_save');
    $left = explode(',', $cookie['printDialog'])[0];
    $top = explode(',', $cookie['printDialog'])[1];
    $style = !empty($cookie) ? ' style="left:'.$left.'px ; top:'.$top.'px ;"' : '';
    $dialogHeader = '<input type="text" class="dialog-header-headline" id="printDialogHeader" placeholder="Enter Something" disabled>';
return '<div class="dialog print" id="printDialog" draggable="true" ondragstart="drag(event)" ontouchstart="drag(event)" '.$style.'>'.
            '<div class="dialog-header padding" id="printContent">'.
                '<span>'.$dialogHeader.'</span>'.
            '</div>'.
            '<div class="dialog-actions padding">'.
            '<span>'.
                $buttons.
            '</span>'.
            '</div>'.
        '</div>';
}