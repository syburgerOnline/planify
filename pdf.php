<?php
$file = $_POST['pdfLink'];
$filename = $_POST['pdfLink'];
header('Content-type: application/pdf');
header('Content-Disposition: inline; filename="' . $filename . '"');
header('Content-Transfer-Encoding: binary');
header('Content-Length: ' . filesize($file));
header('Accept-Ranges: bytes');
echo '<html><head><link rel="stylesheet" href="css/style.css"></head><body><div class="layer-close" id="overLayerClose">
                <div class="layer-close-btn" id="overLayerCloseBtn">X</div>
            </div></body></html>';
@readfile($file);
