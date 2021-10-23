<?php
/*
This class is to send mail
*/
// !!!important for PHPMailer
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\SMTP;
    use PHPMailer\PHPMailer\Exception;
// !!!important for PHPMailer

    
// Load Composer's autoloader
require '../vendor/autoload.php';

    // Instantiation and passing `true` enables exceptions
function sendMail($to,$subject,$message) {
    $mail = new PHPMailer(true);

    try {
        //Server settings
        $mail->isSMTP();                                            // Send using SMTP
        $mail->Host       = '';
        // Set the SMTP server to send through
        $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
        $mail->Username   = '';
        // SMTP username
        $mail->Password   = '';// SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        
        $mail->Port       = 465;                                    // TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above

        //Recipients
        $mail->setFrom('');
        $mail->addAddress($to);     // Add a recipient
        $mail->addReplyTo('', 'Name');
        
        // Content
        $mail->isHTML(true);                                  // Set email format to HTML
        $mail->Subject = $subject;
        $mail->Body    = $message;

        $mail->send();
        return 'success';
    } catch (Exception $e) {
        return 'error';
    }
}
                    