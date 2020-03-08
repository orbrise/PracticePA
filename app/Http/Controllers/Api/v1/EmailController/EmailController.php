<?php

namespace App\Http\Controllers\Api\v1\EmailController;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class EmailController extends Controller
{
   public static function send_default_email($recipient, $subject, $message, $file_path = '', $file_name = '')
    {
        $mail_data['personalizations'] = array(
            0 => array(
                'to' => array(
                    0 => array(
                        'email' => $recipient
                    )
                )
            )
        );
        $mail_data['from'] = array(
            'name' => "Practice PA",
            'email' => 'info@practicepa.com'
        );
        $mail_data['subject'] = $subject;
        $mail_data['content'] = array(
            0 => array(
                'type' => 'text/html',
                'value' => $message)
        );

        if ($file_path != '') {
            $file = file_get_contents($file_path);
            $fileencoded = base64_encode($file);

            $mail_data['attachments'] = array(
                0 => array(
                    'content' => $fileencoded,
                    'filename' => $file_name
                )
            );
        }
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.sendgrid.com/v3/mail/send",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($mail_data),
            CURLOPT_HTTPHEADER => array(
                "authorization: Bearer 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",
                "cache-control: no-cache",
                "content-type: application/json",
                "postman-token: 4777b2d8-61b0-ed32-76ef-1c3be81dd4f7"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
            return json_encode(array('status' => 0, 'message' => "cURL Error #:" . $err));
        } else {
            return json_encode(array('status' => 1, 'message' => $response));
        }
    }
}
