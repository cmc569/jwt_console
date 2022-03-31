<?php

namespace App\Http\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MailService {

    public static function send(String $to, String $subject, String $content, String $attach=null)
    {
        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = 'smtp.office365.com';
            $mail->Port = 587;
            $mail->SMTPSecure = 'tls';
            $mail->SMTPAuth = true;
            $mail->Username = 'project@accunix.com.tw';
            $mail->Password = 'Qop73620';
            $mail->From = "project@accunix.com.tw";
            $mail->FromName = "漢堡王CMS信件發送系統";
            $mail->AddAddress($to);
            $mail->IsHTML (true);
            $mail->CharSet = 'UTF-8';
            $mail->Subject = $subject;
            $mail->Body = nl2br($content);
            $mail->AltBody = "text/html";

            if ($attach) {
                $mail->addAttachment($attach); 
            }

            if ($mail->Send()) {
                \Log::info("Message has been sent. ({$to}, {$content})");
                return true;
            } else {
                \Log::error("Message sent failed. ({$to}, {$content})");
                return false;
            }
            
        }catch (Exception $exception){
            \Log::error("Message sent failed. ({$to}, {$content}, ".$exception->getMessage().")");
            return false;
        }
    }
}
