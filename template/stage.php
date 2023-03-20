<?php
function createStage(): void
{
    echo '<div class="stage" id="stage">',
    $GLOBALS["content"];
    if($_POST["magazines"] && $_POST["issue"]> 0 ) {
        // echo createBottomPageMenu();
    }
    echo '</div>';
}