<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

function sendOrderConfirmation($userEmail, $userName, $orderDetails) {
    $mail = new PHPMailer(true);

    try {
        // SMTP settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com'; // Use your SMTP
        $mail->SMTPAuth   = true;
        $mail->Username   = 'MAIL_USER'; // your email
        $mail->Password   = 'MAIL_PASS';    // use App Password
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        // Sender & Recipient
        $mail->setFrom('your_email@gmail.com', 'Classic StakeHouse');
        $mail->addAddress($userEmail, $userName);

        // Email Content
        $mail->isHTML(true);
        $mail->Subject = 'Your OnlineFood Order Confirmation';
        $mail->Body    = "
            Hi <strong>$userName</strong>,<br><br>
            Thank you for your order!<br><br>
            <strong>Order Summary:</strong><br>
            $orderDetails<br><br>
            We’ll let you know when it’s out for delivery.<br><br>
            Regards,<br>
            Classic Stakehouse Team
        ";

        $mail->send();
        return true;

    } catch (Exception $e) {
        error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}
?>
