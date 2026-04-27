<?php

use PHPUnit\Framework\TestCase;
use PragmaRX\Google2FA\Google2FA;

final class Google2FaManagerTest extends TestCase
{
    private array $originalConfig;

    protected function setUp(): void
    {
        $this->originalConfig = Config::$config;
        Config::$config       = $this->baseConfig();
    }

    protected function tearDown(): void
    {
        Config::$config = $this->originalConfig;
    }

    public function testIs2faAllowedAndEnabled(): void
    {
        $manager = $this->withStubbedGoogle2fa(new Google2FaManager(), new FakeGoogle2FA());

        $userAllowed = (new Google2FaDummyUser())->setLevel(User::ADMIN)->setGoogle2faEnabled(true);
        $userDenied  = (new Google2FaDummyUser())->setLevel(User::USER)->setGoogle2faEnabled(true);

        $this->assertTrue($manager->is2faAllowedForUser($userAllowed));
        $this->assertFalse($manager->is2faAllowedForUser($userDenied));

        $this->assertTrue($manager->is2faEnabledForUser($userAllowed));
        $this->assertFalse($manager->is2faEnabledForUser($userDenied));
    }

    public function testEnable2faForUserGeneratesAndPersistsSecret(): void
    {
        $google2fa = new FakeGoogle2FA('generated-secret');
        $manager   = $this->withStubbedGoogle2fa(new Google2FaManager(), $google2fa);
        $user      = (new Google2FaDummyUser())->setLevel(User::ADMIN);

        $secret = $manager->enable2faForUser($user);

        $this->assertSame('generated-secret', $secret);
        $this->assertTrue($user->isTwoFactorActive());
        $this->assertSame('generated-secret', $user->getGoogle2faSecret());
        $this->assertTrue($user->saved);
    }

    public function testGetOrCreateSecretForUserReturnsEmptyWhenNotAllowed(): void
    {
        $manager = $this->withStubbedGoogle2fa(new Google2FaManager(), new FakeGoogle2FA());
        $user    = (new Google2FaDummyUser())->setLevel(User::USER);

        $this->assertSame('', $manager->getOrCreateSecretForUser($user));
    }

    public function testGetOrCreateSecretReusesExisting(): void
    {
        $manager = $this->withStubbedGoogle2fa(new Google2FaManager(), new FakeGoogle2FA('new-secret'));
        $user    = (new Google2FaDummyUser())
            ->setLevel(User::ADMIN)
            ->setGoogle2faSecret('existing-secret');

        $this->assertSame('existing-secret', $manager->getOrCreateSecretForUser($user));
        $this->assertFalse($user->saved);
    }

    public function testVerifyCodeForUserRespectsConfiguration(): void
    {
        $google2fa = new FakeGoogle2FA('secret', true);
        $manager   = $this->withStubbedGoogle2fa(new Google2FaManager(), $google2fa);

        $userAllowed = (new Google2FaDummyUser())
            ->setLevel(User::ADMIN)
            ->setGoogle2faSecret('secret');
        $userDenied = (new Google2FaDummyUser())
            ->setLevel(User::USER)
            ->setGoogle2faSecret('secret');

        $this->assertTrue($manager->verifyCodeForUser($userAllowed, '123456'));
        $this->assertFalse($manager->verifyCodeForUser($userDenied, '123456'));
        $this->assertFalse($manager->verifyCodeForUser($userAllowed, ''));
    }

    public function testVerifyCodeTrimsAndDelegates(): void
    {
        $google2fa = new FakeGoogle2FA('secret', true);
        $manager   = $this->withStubbedGoogle2fa(new Google2FaManager(), $google2fa);

        $this->assertTrue($manager->verifyCode('secret', " 123456 "));
        $this->assertFalse($manager->verifyCode('secret', ''));
    }

    public function testOtpAuthUrlAndInlineQrCode(): void
    {
        $google2fa = new FakeGoogle2FA('secret', true, 'otpauth://app/user?secret=ABC');
        $manager   = $this->withStubbedGoogle2fa(new Google2FaManager(), $google2fa);

        $url = $manager->getOtpAuthUrlForUser('Compagnie', 'user@example.test', 'secret');
        $this->assertSame('otpauth://app/user?secret=ABC', $url);

        $qr = $manager->generateInlineQrCode($url);
        $this->assertStringStartsWith('data:image/svg+xml;base64,', $qr);
        $this->assertStringContainsString('<svg', base64_decode(substr($qr, strlen('data:image/svg+xml;base64,'))));
    }

    private function withStubbedGoogle2fa(Google2FaManager $manager, FakeGoogle2FA $fake): Google2FaManager
    {
        $property = new ReflectionProperty($manager, 'google2fa');
        $property->setAccessible(true);
        $property->setValue($manager, $fake);

        return $manager;
    }

    private function baseConfig(): array
    {
        return [
            'GOOGLE_AUTHENTIFICATOR' => [
                'IS_ACTIVE' => [
                    'ADMIN' => true,
                    'ADMINSU' => true,
                    'USERSU' => false,
                    'USER' => false,
                ],
            ],
        ];
    }
}

final class FakeGoogle2FA extends Google2FA
{
    public function __construct(
        private string $secretToGenerate = 'secret',
        private bool $verifyResult = false,
        private string $qrUrl = 'otpauth://default'
    ) {
    }

    public function generateSecretKey($length = 32, $prefix = '')
    {
        return $this->secretToGenerate;
    }

    public function getQRCodeUrl($company, $holder, $secret)
    {
        return $this->qrUrl;
    }

    public function verifyKey($secret, $key, $window = null, $timestamp = null, $oldTimestamp = null)
    {
        return $this->verifyResult;
    }
}

class Google2FaDummyUser extends User
{
    public bool $saved = false;

    public function __construct() {}

    public function save(): void
    {
        $this->saved = true;
    }
}
