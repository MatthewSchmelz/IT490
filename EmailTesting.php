#!/usr/bin/php
<?php
require 'vendor/autoload.php'; //Composer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);  // Create a new PHPMailer instance

//testing variables
$userEmail = 'mgv26@njit.edu';
$title = 'Batman';

try {
    //Server settings
    $mail->isSMTP();                                            
    $mail->Host       = 'smtp.gmail.com';                     
    $mail->SMTPAuth   = true;                                   
    $mail->Username   = 'MoviCritics@gmail.com';               
    $mail->Password   = 'oduf fxlj drhd rbuc';                     
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;       
    $mail->Port       = 587;                           
    //Recipients
    $mail->setFrom('MoviCritics@gmail.com', 'MoviCritics'); //We're sending the movie
    //$mail->addAddress('mws36@njit.edu');
    $mail->addAddress($userEmail);//Address we're sending it to.

    // Content for the email
    $mail->isHTML(true);                                      
    $mail->Subject = 'MoviCritics: A movie on your watchlist released!';
	$mail->Body = "Hello! A movie on your watchlist has been released: $title!";
    $mail->send();
    
    echo 'Email sent successfully.';
} catch (Exception $e) {
    echo "Email could not be sent.";
}
?>

