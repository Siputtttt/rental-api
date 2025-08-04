<?php

namespace App\Services\Core;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MailService
{
    public function sendMail($user, $content)
    {
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'poetraambren@gmail.com';
            $mail->Password   = 'krvsgvthrebawvzs';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom('poetraambren@gmail.com', ENV('APP_NAME'));
            $mail->addAddress($user->email, $user->first_name);

            $mail->isHTML(true);
            $mail->Subject = $content['subject'];;
            $mail->Body    = $content['body'];
            $mail->AltBody = strip_tags($content['body']);

            $mail->send();
            return true;
        } catch (Exception $e) {
            return response()->json([
                'status' => 0,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
