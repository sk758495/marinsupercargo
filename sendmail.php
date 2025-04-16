<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

// === Company Info ===
$companyName = 'Marine Super Cargo';
$logoUrl = 'https://cleanship.jashmainfosoft.com/images/logbg2.png'; // ✅ Use full URL for email clients
$yourEmail = 'supercargomarine@gmail.com';
$appPassword = 'dehpvdweimkzcbnm';

// === Get Form Data ===
$name = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';
$subject = $_POST['subject'] ?? 'No Subject';
$message = $_POST['message'] ?? '';

// === Create HTML Email Template Function ===
function buildEmailBody($logoUrl, $companyName, $title, $contentHtml) {
    return "
    <div style='font-family: Arial, sans-serif; background: #f4f4f4; padding: 30px;'>
        <div style='max-width: 600px; margin: auto; background: #fff; padding: 30px; border-radius: 10px;'>
            <div style='text-align: center; margin-bottom: 20px;'>
                <img src='{$logoUrl}' alt='{$companyName} Logo' style='max-width: 150px;' />
            </div>
            <h2 style='color: #2c3e50; text-align:center;'>$title</h2>
            $contentHtml
            <hr style='margin-top: 30px;'>
            <p style='text-align: center; font-size: 12px; color: #aaa;'>&copy; ".date('Y')." $companyName. All rights reserved.</p>
        </div>
    </div>
    ";
}

// === Try Sending Both Emails ===
$mail = new PHPMailer(true);

try {
    // === SMTP Settings ===
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = $yourEmail;
    $mail->Password = $appPassword;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    // === Admin Notification ===
    $mail->setFrom($yourEmail, "$companyName Website");
    $mail->addAddress($yourEmail); // Send to yourself
    $mail->addReplyTo($email, $name);

    $adminContent = "
        <p><strong>Name:</strong> $name</p>
        <p><strong>Email:</strong> $email</p>
        <p><strong>Subject:</strong> $subject</p>
        <p><strong>Message:</strong><br><div style='background: #f9f9f9; padding: 10px; border-radius: 5px;'>$message</div></p>
    ";
    $mail->isHTML(true);
    $mail->Subject = "New Contact Form Submission: $subject";
    $mail->Body = buildEmailBody($logoUrl, $companyName, 'New Contact Form Submission', $adminContent);
    $mail->send();

    // === Auto-reply to User ===
    $mail->clearAddresses();
    $mail->addAddress($email);

    $userContent = "
        <p>Hi <strong>$name</strong>,</p>
        <p>Thank you for reaching out to <strong>$companyName</strong>. We've received your message and will respond as soon as possible.</p>
        <p>Here's a copy of what you sent us:</p>
        <p><strong>Subject:</strong> $subject</p>
        <div style='background: #f9f9f9; padding: 10px; border-radius: 5px;'>$message</div>
        <br>
        <p>Best regards,<br><strong>$companyName Team</strong></p>
    ";

    $mail->Subject = "Thanks for contacting $companyName!";
    $mail->Body = buildEmailBody($logoUrl, $companyName, "We've received your message!", $userContent);
    $mail->send();

    // Return JSON response for JavaScript handling
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Message sent successfully!'
    ]);

} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    // header("Location: index.php?status=error"); exit();
}
