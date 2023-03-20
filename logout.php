<?php
session_start();
$_SESSION=[];
session_destroy();
session_write_close();
$_POST=[];
header('Location: index.php',$_POST["magazines"]=0);
exit;

