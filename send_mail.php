<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/vendor/autoload.php';

function sendEmail($to, $subject, $body){

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'YOUR_EMAIL'; 
        $mail->Password   = 'YOUR_EMAIL_PASSWORD'; 
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('YOUR_GMAIL@gmail.com', 'YOUR_DATABASE_NAME');
        $mail->addAddress($to);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = nl2br($body);

        $mail->send();
        return true;

    } catch (Exception $e) {
        return false;
    }
}
