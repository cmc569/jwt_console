<?php

namespace App\Util;

class Mail
{
    public static function sendMail($email, $code)
    {
        $msg = 
        "<html>
            <head></head>
            <body>
                <div style='font-size: 25px;'>您的驗證碼為 {$code}</div>
                本信件由系統自動發送，請勿直接回信
            </body>
        </html>";


        $postData = http_build_query(
            array(
                'to' => $email,
                'subject' => '漢堡王忘記密碼通知',
                'fromName' => '郵件發送系統',
                // 'replyAddr' => 'test@gmail.com',
                // 'replyName' => 'test',
                'msg' => $msg
            )
        );

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://ub001.accuhit.net/spider/queue/includes/mail/mailG.php',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $postData,
            CURLOPT_HTTPHEADER => array(
                'Content-type: application/x-www-form-urlencoded'
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }
}
