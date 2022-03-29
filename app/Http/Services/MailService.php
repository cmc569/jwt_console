<?php

namespace App\Http\Services;

use Illuminate\Support\Facades\Mail;

class MailService {

    public static function send(String $to, String $subject, String $content, String $attach=null, String $from='info@accuhit.net')
    {
        return Mail::raw($content, function ($message) use ($to, $subject, $content, $attach, $from) {
            $message->from($from);
            $message->to($to);
            $message->subject($subject);

            if (!empty($attach)) {
                $message->attach($attach);
            }
        });
    }
}
