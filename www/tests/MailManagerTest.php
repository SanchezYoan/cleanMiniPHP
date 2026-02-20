<?php

use PHPUnit\Framework\TestCase;
use PHPMailer\PHPMailer\PHPMailer;

require_once __DIR__ . '/FakeDatabase.php';

if (!defined('PUBLIC_ROOT')) {
    define('PUBLIC_ROOT', 'https://example.test');
}

final class MailManagerTest extends TestCase
{
    private array $originalConfig;
    private mixed $originalDatabase;

    protected function setUp(): void
    {
        $this->originalConfig   = Config::$config;
        $this->originalDatabase = $this->getDatabaseSingleton();

        Config::$config = $this->baseConfig();
        $this->setDatabaseSingleton(new FakeDatabase());
        $this->resetLoggerSingleton();
    }

    protected function tearDown(): void
    {
        Config::$config = $this->originalConfig;
        $this->setDatabaseSingleton($this->originalDatabase);
        $this->resetLoggerSingleton();
    }

    public function testSendMailBuildsBodyAndAddresses(): void
    {
        $mailer  = new FakeMailer();
        $manager = new class($mailer) extends MailManager {
            public function __construct(private FakeMailer $fakeMailer) {}
            protected function createMailer(): PHPMailer
            {
                return $this->fakeMailer;
            }
        };

        $userA = (new DummyUser())->setId(1)->setEmail('alice@example.test');
        $userB = (new DummyUser())->setId(2)->setEmail('bob@example.test');

        $result = $manager->sendMail('Sujet', 'Corps', [$userA, $userB]);

        $this->assertTrue($result);
        $this->assertSame("[LOCAL] - Sujet", $mailer->Subject);
        $this->assertStringContainsString('<h2>Sujet</h2>', $mailer->Body);
        $this->assertSame(['alice@example.test'], $mailer->to);
        $this->assertSame(['bob@example.test'], $mailer->bcc);
    }

    public function testSendMailReturnsFalseAndRegistersErrorOnFailure(): void
    {
        $mailer        = new FakeMailer(false, 'SMTP unreachable');
        $manager = new class($mailer) extends MailManager {
            public function __construct(private FakeMailer $fakeMailer) {}
            protected function createMailer(): PHPMailer
            {
                return $this->fakeMailer;
            }
        };

        $user = (new DummyUser())->setId(3)->setEmail('charlie@example.test');

        $this->assertFalse($manager->sendMail('Sujet', 'Corps', [$user]));
        $this->assertTrue($manager->hasErrors());
        $this->assertStringContainsString('échoué', $manager->errorsAsString());
    }

    public function testSendForgotPasswordUsesMailer(): void
    {
        $manager = new class extends MailManager {
            public array $calls = [];
            public function sendMail($subject, $body, array $destinataires, bool $duplicateSubjet = true): bool
            {
                $this->calls[] = compact('subject', 'body', 'destinataires', 'duplicateSubjet');
                return true;
            }
        };

        $user = (new DummyUser())->setId(5)->setEmail('user@example.test');
        $token = 'reset-token';

        $this->assertTrue($manager->sendForgotPassword($user, $token));
        $this->assertCount(1, $manager->calls);
        $this->assertStringContainsString("/login/forgot/5/$token", $manager->calls[0]['body']);
    }

    private function baseConfig(): array
    {
        return [
            'EMAILS' => [
                'SETTINGS' => [
                    'NO_REPLY' => 'no-reply@example.test',
                ],
            ],
            'EMAIL_VIEWS_PATH' => __DIR__,
            'FEATURES' => [
                'LOGGER' => [
                    'DEBUG' => ['WRITE_TXT' => false, 'WRITE_DB' => false, 'SEND_MAIL' => false],
                    'NOTICE' => ['WRITE_TXT' => false, 'WRITE_DB' => false, 'SEND_MAIL' => false],
                    'WARNING' => ['WRITE_TXT' => false, 'WRITE_DB' => false, 'SEND_MAIL' => false],
                    'ERROR' => ['WRITE_TXT' => false, 'WRITE_DB' => false, 'SEND_MAIL' => false],
                    'SECURITY' => ['WRITE_TXT' => false, 'WRITE_DB' => false, 'SEND_MAIL' => false],
                    'CRITICAL' => ['WRITE_TXT' => false, 'WRITE_DB' => false, 'SEND_MAIL' => false],
                ],
            ],
        ];
    }

    private function resetLoggerSingleton(): void
    {
        $loggerReflection = new ReflectionClass(Logger::class);
        $instanceProperty = $loggerReflection->getProperty('_instance');
        $instanceProperty->setAccessible(true);
        $instanceProperty->setValue(null, null);
    }

    private function getDatabaseSingleton(): mixed
    {
        $databaseReflection = new ReflectionClass(Database::class);
        $databaseProperty   = $databaseReflection->getProperty("database");
        $databaseProperty->setAccessible(true);

        return $databaseProperty->getValue();
    }

    private function setDatabaseSingleton(mixed $database): void
    {
        $databaseReflection = new ReflectionClass(Database::class);
        $databaseProperty   = $databaseReflection->getProperty("database");
        $databaseProperty->setAccessible(true);
        $databaseProperty->setValue(null, $database);
    }

}

final class FakeMailer extends PHPMailer
{
    public $to = [];
    public $bcc = [];

    public function __construct(private bool $shouldSend = true, string $errorInfo = '')
    {
        parent::__construct(true);
        $this->ErrorInfo = $errorInfo;
    }

    public function addAddress($address, $name = ''): bool
    {
        $this->to[] = $address;
        return true;
    }

    public function addBCC($address, $name = ''): bool
    {
        $this->bcc[] = $address;
        return true;
    }

    public function send(): bool
    {
        return $this->shouldSend;
    }
}

class DummyUser extends User
{
    public function __construct() {}
    public function save(): void {}
}
