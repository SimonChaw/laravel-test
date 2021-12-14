<?php

namespace Tests\Feature;

use App\Jobs\SendMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class MailingTest extends TestCase
{
    use WithFaker;

    /**
     * Ensure valid emails are accepted and queued for sending
     *
     * @return void
     */
    public function test_valid_mail_is_queued()
    {
        Queue::fake();

        $emails = $this->get_test_emails();

        $this->postJson('/api/send', ['emails' => $emails])->assertStatus(200);
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
        $this->postJson('/api/send', ['emails' => $emails])->assertStatus(422);
        Queue::assertNothingPushed();
    }


    protected function get_test_emails($count = 50, $valid = true) {
        $attachments = [
            [
                'filename' => 'portrait.jpg',
                'file_data' => 'data:image/jpeg;base64,'.base64_encode(file_get_contents(str_replace('@', DIRECTORY_SEPARATOR,'tests@MailAttachments@portrait.jpg')))
            ],
            [
                'filename' => 'resume.pdf',
                'file_data' => 'data:file/pdf;base64,'.base64_encode(file_get_contents(str_replace('@', DIRECTORY_SEPARATOR,'tests@MailAttachments@resume.pdf')))
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

            // TODO: Randomly add attachments
            if ($should_attach)
                $email['attachments'] = $attachments;

            $emails[] = $email;
        }

        return $emails;
    }
}
