<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'send_email.php';

$to = 'social@jameshamby.me'; // Replace with your email address
$subject = 'Test Email';
$body = 'This is a test email to verify that send_email.php is working correctly.';

if (sendEmail($to, $subject, $body)) {
    echo "Test email sent successfully.";
} else {
    echo "Failed to send test email.";
}
?>