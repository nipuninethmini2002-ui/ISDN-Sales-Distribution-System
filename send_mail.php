<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/vendor/autoload.php';

function sendEmail($to, $subject, $body){

    $mail = new PHPMailer(true);

    try {
        // SMTP settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'isdnsystem@gmail.com'; // 🔴 change this
        $mail->Password   = 'webnqrkahihtfato'; // 🔴 change this
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Email details
        $mail->setFrom('YOUR_GMAIL@gmail.com', 'ISDN System');
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
