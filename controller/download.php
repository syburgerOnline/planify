<?php
if(isset($_GET['path'])) {
    $zipFileName = $_GET['path'];

    header('Content-Type: application/zip');
    header('Content-disposition: attachment; filename='.pathinfo($zipFileName)['filename']);
    header('Content-Length: ' . filesize($zipFileName));
    readfile($zipFileName);

    unlink($zipFileName);
    print_r('downloadSucceeded');
} else {
    header("HTTP/1.1 404 Not Found"); exit;
}
