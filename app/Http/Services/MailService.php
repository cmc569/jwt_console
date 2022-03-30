<?php

namespace App\Http\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MailService {

    public static function send(String $to, String $subject, String $content, String $attach=null, String $from='info@accuhit.net')
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
            $mail->AddAddress($to);
            $mail->IsHTML (true);
            $mail->Subject = $subject;
            $mail->Body = nl2br($content);
            $mail->AltBody = "text/html";

            if ($attach) {
                $mail->addAttachment($attach); 
            }

            // print_r($mail->Send());
            echo "Message has been sent\n";
        }catch (Exception $exception){
            echo $exception->getMessage()."\n";
        }
    }
}
