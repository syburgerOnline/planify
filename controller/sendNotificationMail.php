<?php
require '../inc/db.php';
function sendNotificationMail($data, $db): void
{
    $newData = file_get_contents( "php://input" );
    $formData = json_decode($newData);
    // print_r('createNewDatabaseTheme -> '.json_decode($newData).$db);
    // $mailToArray = [];
    $subjectArray = [];
    $bodyArray = [];
    foreach($formData AS $key => $queryStr){
        $mail = (array) $queryStr;
        $mailToArray = explode(',', $mail['mailTo']);
        $nameToArray = explode(',', $mail['nameTo']);
        $subjectArray[] = $mail['subject'];
        $bodyArray[] = $mail['body'];
        // print_r('reload -> '.$queryStr."\n");
        // print_r('sendEmail '.$key."\n");
        if ($queryStr === 'reload'){
            print_r('nothing to send'."\n");
            // header('Location: ../index.php');
        } else {
            print_r('sendEmail '.$key."\n");
            sendEmail($mailToArray,$nameToArray, $subjectArray, $bodyArray, $key);
        }

        // $db->query(utf8_decode($queryStr));
    }
}
function sendEmail($mailToArray, $nameToArray, $subjectArray, $bodyArray, $key):void {
    require_once("../../../inc/mailer/PHPMailerAutoload.php");
    print_r('sendEmail '.$key."\n");
    $year = date("Y");
    $monthTo = date("m");
    $mailHeader = '<strong>Automatische Planify Info:</strong><br><br>';
    $mailFooter = '<br><br>Bitte antworten Sie nicht auf diese Email-Adresse.<br>Bei Fragen oder Problemen wenden Sie sich bitte an unser Support-Team unter <a href="mailto:support@syburger.de?from=planify@motorradmenschen.de&subject=Planify Support&body=Fragen zu Planify:"><strong>Planify-Support</strong></a>';
    // $head = '<h2 style="color: #002fa6;">'.$mailHeader.'</h2>'
    $htmlContent = '<!DOCTYPE html>
                        <html lang="de">
                         <head>
                          <meta charset="UTF-8">
                          <title>'.$mailHeader.'</title>
                         </head>
                        <body>
                        
                        <table style="background-color: #FFFFFF; width: 100%; padding: 10px;">
                        <tr>
                         <td>
                            <h1 style="color: #000000;">'.$mailHeader.'</h1>
                            <p>'.utf8_decode($bodyArray[$key]).'</p>
                         </td>
                        </tr>
                        <tr>
                         <th>
                          <p>'.$mailFooter.'</p>
                         </th>
                        </tr>
                        </table>
                        </body>
                        </html>';
    $mail_settings = array(
        'email' => 'planify@motorradmenschen.de',
        'name' => 'Planify',
        'password'=>'fHtkg7n7aS9,',
        'port' => '587',
        'host'	=> 'smtprelaypool.ispgateway.de',
        'replyTo'=> 'planify@motorradmenschen.de',
        'replyToName' => 'Planify',
        'subject'=> 'PLANIFY: '.utf8_decode($subjectArray[$key]),
        'message' => $htmlContent,
        // 'message' => utf8_decode($bodyArray[$key]),
        'mailTo' => $mailToArray,
        'mailToName' => $nameToArray,
        'bccTo' => 'info@schuessler-multimedia.de'
    );


    $email = new PHPMailer(true);
    $email->isSMTP();
    $email->isHtml(true);
    //$email->AddEmbeddedImage('top.jpg', 'TBP', 'top.jpg');
    $email->SMTPAuth  = true;
    $email->Username  = $mail_settings['email'];
    $email->Password  = $mail_settings['password'];
    $email->Host      = $mail_settings['host'];
    $email->Port      = $mail_settings['port'];
    $email->Subject   = $mail_settings['subject'];
    $email->Body      = $mail_settings['message'];
    $email->SetFrom($mail_settings['email'], $mail_settings['name']);
    // $email->AddAddress($mail_settings['mailTo'][0], utf8_decode($mail_settings['mailTo'][2]['name']));
    $email->AddAddress($mail_settings['mailTo'][0], $mail_settings['nameTo'][0]);
    foreach($mail_settings['mailTo'] AS $keyNew => $val){
        // print_r('ccTo: '.$val."\n");
        $email->AddCC($val, $mail_settings['nameTo'][$keyNew]);
    }
    // print_r('subject: '.$mail_settings['subject']."\n");
    // print_r('message: '.$mail_settings['message']."\n");
    $email->AddBCC($mail_settings['bccTo'], 'Daniel schuessler');
    // $email->AddBCC( $mail_settings['bccTo'] );
    $email->AddReplyTo($mail_settings['replyTo'], $mail_settings['replyToName']);
    // $email->AddAttachment( $targetFileName );
    try {
        $email->send();
        print_r('mailSentSuccessfully --> '.($key+1)."\n");
        // header('Location: index.php');
        $email->ClearAllRecipients();
    } catch (phpmailerException $e) {
        print_r($e->errorMessage());
    } catch (Exception $e) {
        print_r($e->getMessage());
    }
}
sendNotificationMail($_POST, $db);