<?php

function createSubject($user):string {
    return $user['issue'] > 0 ? 'PLANIFY Support: Magazin: '.$user['magazine'].', Ausgabe: '.$user['issue'] : 'PLANIFY Support: Magazin: '.$user['magazine'];;
}
function createBody($user):string {
    $userLink = '<a href="mailto:'.$user['email'].'"><strong>'.$user['name'].'</strong></a>';
    $mailHeader = '<strong>'.$userLink.' meldet ein Problem aus dem Planify Tool</strong><br><br>';
    $mailBody = utf8_decode($user['message']);
    $mailFooter = '<br><br>Bitte antworten Sie nicht auf diese Email-Adresse.<br>Die Message kam von '.$userLink;

    return '<!DOCTYPE html>
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
                            <p>'.$mailBody.'</p>
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
}

if (isset($_POST['emailTextField'])) {
    require_once("../../../inc/mailer/PHPMailerAutoload.php");
    /**
     * @throws phpmailerException
     */
    $attachmentFile = null;
    $attachmentName = null;
    $target_dir = "../uploads/images/";
    $user = [
        'email' => $_POST['emailTextField'],
        'name' => $_POST['nameTextField'],
        'message' => $_POST['userMessage'],
        'magazine' => $_POST['magazines'],
        'issue' => $_POST['issue']
    ];
    if (isset($_FILES['contactSupportTeamDialogFormUploadFile']['name'])) {
        /**
         * upload files for sending as attachment
         */
        $fileContainer = $_FILES['contactSupportTeamDialogFormUploadFile'];
        $fileType = $fileContainer['type'];
        $fileName = $fileContainer['name'];
        $formattedFileName = str_replace(['%20', ' '],'_',$fileName);
        $original_filename = $formattedFileName;
        $target = $target_dir . basename($original_filename);
        $target_name = pathinfo($target)['filename'];
        $tmp = $fileContainer['tmp_name'];
        $original_filenames[] = $original_filename;
        move_uploaded_file($tmp, $target);
        $attachmentFile = $target;
        $attachmentName = $original_filename;
        // $attachmentFile = chunk_split(base64_encode(file_get_contents($attachmentName)));
    }

    $subject = createSubject($user);
    $body = createBody($user);
    /**
     * mailer settings
     */

    $mail_settings = array(
        'email' => 'planify@motorradmenschen.de',
        'name' => 'Planify',
        'password'=>'fHtkg7n7aS9,',
        'port' => '587',
        'host'	=> 'smtprelaypool.ispgateway.de',
        'replyTo'=> 'planify@motorradmenschen.de',
        'replyToName' => 'Planify',
        'subject'=> $subject,
        'message' => $body,
        'mailTo' => 'syburger-support@6088405145985.hostingkunde.de',
        'mailToName' => 'Support by Planify',
        'bccTo' => 'info@schuessler-multimedia.de',
        'bccTo2' => 'doernen@syburger.de'
    );

    /**
     * send mail
     */

    $email = new PHPMailer(true);
    $email->isSMTP();
    $email->isHtml(true);
    $email->SMTPAuth  = true;
    $email->Username  = $mail_settings['email'];
    $email->Password  = $mail_settings['password'];
    $email->Host      = $mail_settings['host'];
    $email->Port      = $mail_settings['port'];
    $email->Subject   = $mail_settings['subject'];
    $email->Body      = $mail_settings['message'];
    try {
        $email->SetFrom($mail_settings['email'], $mail_settings['name']);
    } catch (phpmailerException $e) {
        print_r('setFrom failed -> '.$e);
    }
    $email->AddAddress($mail_settings['mailTo'], $mail_settings['mailToName']);
    $email->AddBCC($mail_settings['bccTo'], 'Daniel Schuessler');
    $email->AddBCC($mail_settings['bccTo2'], 'Jennifer Doernen');
    $email->AddReplyTo($mail_settings['replyTo'], $mail_settings['replyToName']);
    if($attachmentFile) {
        try {
            $email->AddAttachment($attachmentFile, $attachmentName);
        } catch (phpmailerException $e) {
            print_r('addAttachment failed -> '.$e);
        }
    }
    try {
        $email->send();
        print_r('mailSentSuccessfully --> '.$mail_settings['email']."\n");
        $email->ClearAllRecipients();
        unlink($attachmentFile);
    } catch (phpmailerException $e) {
        print_r($e->errorMessage());
    } catch (Exception $e) {
        print_r($e->getMessage());
    }
}
    // var_dump($user);
/*
function contactSupportTeam($data, $db): void
{
    $newData = file_get_contents( "php://input" );
    var_dump($newData);
    $formData = json_decode($newData);
    $mail = (array) $formData;
    $mailToArray = [$mail['emailTextField']];
    $nameToArray = [$mail['nameTextField']];
    $bodyArray = [$mail['userMessage']];
    $attachment = $mail['contactSupportTeamDialogFormUploadFile'];
    $attachmentArray = [];
    if($attachment) {
        foreach ($attachment as $value) {
            $attachmentArray[] = $value->post_id;
        }
    }
    $subjectArray = [$mail['magazines'], $mail['issue']];
    var_dump($attachmentArray);
    sendEmail($mailToArray,$nameToArray, $subjectArray, $bodyArray, $attachmentArray);
}

function sendEmail($mailToArray, $nameToArray, $subjectArray, $bodyArray, $attachmentArray):void {
    require_once("../../../inc/mailer/PHPMailerAutoload.php");
    print_r('sendEmail '."\n");
    $subjectContent = $subjectArray[1] > 0 ? 'PLANIFY Support: Magazin: '.$subjectArray[0].', Ausgabe: '.$subjectArray[1] : 'PLANIFY Support: Magazin: '.$subjectArray[0];
    print_r($subjectContent."\n");
    $userLink = '<a href="mailto:'.$mailToArray[0].'">'.$nameToArray[0].'</a>';
    $mailHeader = '<strong>'.$userLink.' meldet ein Problem aus dem Planify Tool</strong><br><br>';
    $mailBody = utf8_decode($bodyArray[0]);
    $mailFooter = '<br><br>Bitte antworten Sie nicht auf diese Email-Adresse.<br>Die Message kam von <a href="mailto:'.$mailToArray[0].'"><strong>'.$nameToArray[0].'</strong></a>';
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
                            <p>'.$mailBody.'</p>
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
        'subject'=> $subjectContent,
        'message' => $htmlContent,
        // 'message' => utf8_decode($bodyArray[$key]),
        'mailTo' => 'syburger-support@6088405145985.hostingkunde.de',
        'mailToName' => 'Support by Planify',
        'bccTo' => 'info@schuessler-multimedia.de',
        'bccTo2' => 'doernen@syburger.de'
    );


    $email = new PHPMailer(true);
    $email->isSMTP();
    $email->isHtml(true);
    $email->SMTPAuth  = true;
    $email->Username  = $mail_settings['email'];
    $email->Password  = $mail_settings['password'];
    $email->Host      = $mail_settings['host'];
    $email->Port      = $mail_settings['port'];
    $email->Subject   = $mail_settings['subject'];
    $email->Body      = $mail_settings['message'];
    $email->SetFrom($mail_settings['email'], $mail_settings['name']);
    $email->AddAddress($mail_settings['mailTo'], $mail_settings['mailToName']);
    $email->AddBCC($mail_settings['bccTo'], 'Daniel Schuessler');
    $email->AddBCC($mail_settings['bccTo2'], 'Jennifer Doernen');
    $email->AddReplyTo($mail_settings['replyTo'], $mail_settings['replyToName']);
    if(!empty($attachmentArray)) {
        $email->AddAttachment( $attachmentArray , 'Screenshot' );
    }
    try {
        $email->send();
        print_r('mailSentSuccessfully --> '.$mail_settings['email']."\n");
        $email->ClearAllRecipients();
    } catch (phpmailerException $e) {
        print_r($e->errorMessage());
    } catch (Exception $e) {
        print_r($e->getMessage());
    }

}

contactSupportTeam($_POST, $db);
*/
