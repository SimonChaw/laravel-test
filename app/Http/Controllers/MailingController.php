<?php

namespace App\Http\Controllers;

use App\Http\Requests\MailRequest;
use App\Jobs\SendMail;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;

class MailingController extends Controller
{
    public function send(MailRequest $request) {
        $data = $request->validated();

        foreach($data['emails'] as $i => $email)
        {
            // Delay the emails so that we don't have emails get cancelled for sending too many a minute
            SendMail::dispatch($email['to'], $email['subject'], $email['body'], $email['attachments'])->delay(now()->addSeconds($i * 2));
        }

        return response()->json();
    }
}
