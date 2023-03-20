<?php
session_start();

    require 'inc/db.php';
    require 'conf/adTypes.php';
    require 'conf/statusValues.php';
    require 'conf/login.php';
    require 'controller/addPage.php';
    require 'controller/addIssue.php';
    require 'controller/createNewIssue.php';
    require 'controller/getNotificationGroup.php';
    require 'controller/fetchPageContent.php';
    require 'controller/getIssues.php';
    require 'template/header.php';
    require 'template/navigation.php';
    require 'template/mainContainer.php';
    require 'template/stage.php';
    require 'template/icons.php';
    require 'template/elements.php';
    require 'template/imageViewer.php';
    require 'template/bottomPageMenu.php';
    require 'template/button.php';
    require 'template/login.php';
    require 'template/dialog.php';
    require 'template/tools.php';
    require 'template/toolbox.php';
    require 'template/detailInfoPanel.php';
    require 'template/printJob.php';
    require 'template/notificationTextField.php';

  // require 'inc/clearAndCreateFolder.php';
  // require 'inc/mailer/PHPMailerAutoload.php';
    //Testing
    if(empty($GLOBALS['user'])) {
        $GLOBALS['user'] = [];
    }
    $GLOBALS['notificationGroup'] = [];
    $GLOBALS['login'] = ['user' => 'admin'];
    $GLOBALS['magazineArray'] = array(
        0 => array (
            'title'             => 'Bitte Wählen',
            'folderName'        => '',
            'id'                => 0,
            'ebiNumber'         => '',
            'position'          => '',
            'selected'          => 'selected',
            'function'          => 'changeMagazine(0)',
            'issuesPerYear'     => 0,
            'averagePageAmount' => 0
        )
    );
    $GLOBALS['issueArray'] = array(
        0 => array (
            'title'         => 'Bitte Wählen',
            'folderName'    => '',
            'id'            => 0,
            'ebiNumber'     => '',
            'position'      => '',
            'selected'      => 'selected',
            'function'      => 'changeIssue(0,0)',
            'year'          => 0
        )
    );
    if(!empty($_GET['magazines'])){
        if(!$_SESSION["deepLink"]) {
            $_SESSION["deepLink"] = [];
        }
        $_SESSION["deepLink"]["magazines"] =  $_GET['magazines'];
    }
    if(!empty($_GET['issue'])){
        $_SESSION["deepLink"]["issue"] = $_GET['issue'];
    }
    if(!empty($_GET['year'])){
        $_SESSION["deepLink"]["year"] = $_GET['year'];
    }
    if ($db->connect_error) {
        die('Verbindung zum MySQL Server fehlgeschlagen: '.$db->connect_error);
    } else {
        $GLOBALS['db'] = $db;
        getMagazines($db);
        // login($db);
    }
    // $_COOKIE['elementpositions', {name: $name, left: $x, top: $y}, strtotime( '+30 days' )]
  if(!empty($_COOKIE)) {
      $toolBox = explode(',', $_COOKIE['toolbox']);
      $dialogBox = explode(',', $_COOKIE['editDialog']);
      $printBox = explode(',', $_COOKIE['printDialog']);

      // $GLOBALS['cookie']['toolBox'];
      $GLOBALS['cookie'] = array(
          'toolBox' => array(
                    'x' => $toolBox[0],
                    'y'=> $toolBox[1]
          ),
          'dialogBox' => array(
              'x' => $dialogBox[0],
              'y'=> $dialogBox[1]
          ),
          'printBox' => array(
              'x' => $printBox[0],
              'y'=> $printBox[1]
          ),
      );
  }
  function getMagazines($db): void
  {
        echo '<!DOCTYPE html><html><body>',
        createHeader();
        if(!$_SESSION["login"]) {
            $loggedIn = [['loggedIn', false], ['errorEmail', false], ['errorPassword', false]];
            createLoginDialog($db, $loggedIn);
        } else {
            createMainContainer($db);
            if (empty($GLOBALS['magazineArray'])) {
                die('No Magazines.');
            }
        }
        echo '</body></html>';
  }
