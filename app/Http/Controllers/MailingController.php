<?php

namespace App\Http\Controllers;

use App\Http\Requests\MailRequest;
use App\Jobs\SendMail;
use Illuminate\Http\Request;
use Illuminate\Queue\Queue;

class MailingController extends Controller
{
    public function send(MailRequest $request) {
        $data = $request->validated();

        foreach($data['emails'] as $email)
        {
            new SendMail($email['to'], $email['subject'], $email['body'], $email['attachments']);
        }

        return response()->json();
    }
}
