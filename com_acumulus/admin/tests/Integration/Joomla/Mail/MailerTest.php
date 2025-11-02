<?php

declare(strict_types=1);

namespace Siel\Acumulus\Tests\Integration\Joomla\Mail;

use Siel\Acumulus\Tests\Joomla\TestCase;

/**
 * MailerTest tests whether the mailer class mails messages to the mail server.
 *
 * This test is mainly used to test if the mail feature still works in new versions of the
 * shop.
 */
class MailerTest extends TestCase
{
    public function testMailer(): void
    {
        $this->_testMailer(hasTextPart: false);
    }
}
