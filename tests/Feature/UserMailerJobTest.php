<?php

namespace Tests\Feature;

use App\Jobs\UserMailerJob;
use App\Mail\CommunicationSendMail;
use Tests\TestCase;

/**
 * A missing address (e.g. the System account has no email) must not stop the
 * message to everyone else: the job drops empty recipients.
 */
class UserMailerJobTest extends TestCase
{
    public function test_empty_recipients_are_dropped(): void
    {
        $job = new UserMailerJob([], ['applicant@example.com', null, '', '  '], new CommunicationSendMail('Subject', '<p>Body</p>', []), [null, 'copy@example.com']);

        $this->assertSame(['applicant@example.com'], $job->to);
        $this->assertSame(['copy@example.com'], $job->bcc);
    }
}
