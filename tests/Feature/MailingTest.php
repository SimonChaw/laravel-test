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

        $this->postJson('/api/mail', ['emails' => $emails])->assertStatus(200);
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
        $this->postJson('/api/mail', ['emails' => $emails])->assertStatus(422);
        Queue::assertNothingPushed();
    }


    protected function get_test_emails($count = 50, $valid = true) {
        $emails = [];

        // Create 50 emails
        for ($i = 0; $i < $count; $i ++) {
            $emails[] = [
                'to' => $valid ? $this->faker->email : 'not_an_email',
                'subject' => $this->faker->sentence,
                'body' => $this->faker->paragraphs(3, true),
                'attachments' => []
            ];

            // TODO: Randomly add attachments
        }

        return $emails;
    }
}
