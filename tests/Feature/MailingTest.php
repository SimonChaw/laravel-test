<?php

namespace Tests\Feature;

use App\Jobs\SendMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Redis\Connections\PredisConnection;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Redis;
use Illuminate\Testing\Fluent\AssertableJson;
use Laravel\Horizon\Contracts\JobRepository;
use Laravel\Horizon\JobPayload;
use Laravel\Horizon\RedisQueue;
use Tests\TestCase;

class MailingTest extends TestCase
{
    use WithFaker, RefreshDatabase;

    /**
     * Ensure valid emails are accepted and queued for sending
     *
     * @return void
     */
    public function test_valid_mail_is_queued()
    {
        Queue::fake();

        $emails = $this->get_test_emails();
        $token = parent::$token;
        $this->postJson("/api/send?api_token=${token}", ['emails' => $emails])->assertStatus(200);
        Queue::assertPushed(SendMail::class, 50);
    }

    /**
     * Ensure invalid emails are not accepted and are not queued for sending
     *
     * @return void
     */
    public function test_invalid_mail_not_queued()
    {
        Queue::fake();
        $emails = $this->get_test_emails(2, false);
        $token = parent::$token;
        $this->postJson("/api/send?api_token=${token}", ['emails' => $emails])->assertStatus(422);
        Queue::assertNothingPushed();
    }

    public function test_get_redis_mail_list()
    {
        $emails_sent = 5;
        // Clean jobs from redis
        Redis::connection()->command('flushdb'); // For this test, please make sure Redis is running
        Queue::setConnectionName('redis');

        // Spoof jobs being dispatched and completed.
        $jobRepository = app()->make(JobRepository::class);
        $emails = collect($this->get_test_emails($emails_sent));
        $jobIds = $emails->map(function($email) {
            $job = new SendMail($email['to'], $email['subject'], $email['body'], $email['attachments']);
            return Queue::push($job);
        })->toArray();
        $queuedJobs = $jobRepository->getJobs($jobIds)->each(function ($job) use ($jobRepository) {
            $jobPayload = new JobPayload($job->payload);
            $jobRepository->completed($jobPayload);
        });
        $token = parent::$token;

        // We should be able to get a full list of our emails now
        $data = $this->getJson("/api/list?api_token=${token}")->assertStatus(200);
        $this->assertEquals($emails_sent, $data->json('total'));
        $list = $data->json('data');
        $this->assertArrayHasKey('to', $list[0]);
        $this->assertArrayHasKey('body', $list[0]);
        $this->assertArrayHasKey('subject', $list[0]);
        $this->assertArrayHasKey('attachments', $list[0]);
    }


    protected function get_test_emails($count = 50, $valid = true) {
        $attachments = [
            [
                'filename' => 'portrait.jpg',
                'file_data' => base64_encode(file_get_contents(str_replace('@', DIRECTORY_SEPARATOR,'tests@MailAttachments@portrait.jpg')))
            ],
            [
                'filename' => 'resume.pdf',
                'file_data' => base64_encode(file_get_contents(str_replace('@', DIRECTORY_SEPARATOR,'tests@MailAttachments@resume.pdf')))
            ]
        ];

        $emails = [];

        for ($i = 0; $i < $count; $i ++) {
            $email = [
                'to' => $valid ? $this->faker->email : 'not_an_email',
                'subject' => $this->faker->sentence,
                'body' => $this->faker->paragraphs(3, true),
                'attachments' => []
            ];

            $should_attach = rand(0,1) == 1;

            if ($should_attach)
                $email['attachments'] = $attachments;

            $emails[] = $email;
        }

        return $emails;
    }
}
