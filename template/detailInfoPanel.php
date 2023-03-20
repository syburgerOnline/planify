<?php
function createInfoPanel(): string
{
    $infoPanelHead = '<label class="info-panel-Head" for="infoPanelCopy" id="infoPanelHead">Infos</label>';
    $infoPanelCopy = '<textarea class="info-panel-copy" id="infoPanelCopy" name="infoPanelCopy">Example</textarea>';
    $infoPanelTextArea = '<div class="info-panel-textarea" id="infoPanelTextArea">'.$infoPanelHead.$infoPanelCopy.'</div>';

    $infoPanelBubble = '<div class="info-panel-bubble" id="infoPanelBubble">'.$infoPanelTextArea.'</div>';
    $infoPanelHook = '<div class="info-panel-hook hook-left" id="infoPanelHook"></div>';
    // $infoPanelBackground = '<div class="info-panel-background" id="infoPanelBackground"></div>';


    $infoPanelContent = $infoPanelBubble.$infoPanelHook;
    return '<div class="info-panel" id="infoPanel">'.$infoPanelContent.'</div>';
}