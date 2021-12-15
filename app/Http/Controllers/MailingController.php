<?php

namespace App\Http\Controllers;

use App\Http\Requests\MailRequest;
use App\Http\Resources\MailListResource;
use App\Jobs\SendMail;
use Illuminate\Http\Request;
use Laravel\Horizon\Contracts\JobRepository;

class MailingController extends Controller
{
    /**
     * Create delayed jobs to send a batch of up to 1000 emails.
     *
     * @param MailRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function send(MailRequest $request) {
        $data = $request->validated();

        foreach($data['emails'] as $i => $email)
        {
            // Delay the emails so that we don't have emails get cancelled for sending too many a minute
            SendMail::dispatch($email['to'], $email['subject'], $email['body'], $email['attachments'])->delay(now()->addSeconds($i * 2));
        }

        return response()->json();
    }

    /**
     * Get a list of all emails that have been sent
     *
     * @param Request $request
     * @param JobRepository $jobs
     * @return array
     */
    public function list(Request $request, JobRepository $jobs) {
        $jobData = $jobs->getCompleted($request->query('starting_at', -1))->map(function($job) {
            $job->payload = json_decode($job->payload);

            return $job;
        })->values();

        return [
            'data' => MailListResource::collection($jobData),
            'total' => $jobs->countCompleted()
        ];
    }
}
