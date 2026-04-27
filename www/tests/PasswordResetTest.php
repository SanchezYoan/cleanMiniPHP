<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../vendor/autoload.php';

class PasswordResetTest extends TestCase
{
    protected function setUp(): void
    {
        Config::$config = [
            "SECURITY" => [
                "RESET_TOKEN" => [
                    "TTL_MINUTES" => 60,
                    "HMAC_SECRET" => "unit-test-secret",
                ],
            ],
        ];
    }

    public function testTokenValidBeforeExpiration(): void
    {
        $user = new User();
        $token = 'valid-token';
        $user->setResetTokenHash(User::hashResetToken($token))
            ->setResetExpiresAt((new DateTime())->add(new DateInterval('PT30M')));

        $this->assertTrue($user->isResetTokenValid($token));
    }

    public function testTokenInvalidWhenExpired(): void
    {
        $user = new User();
        $token = 'expired-token';
        $user->setResetTokenHash(User::hashResetToken($token))
            ->setResetExpiresAt((new DateTime())->sub(new DateInterval('PT1M')));

        $this->assertFalse($user->isResetTokenValid($token));
    }

    public function testTokenInvalidWhenHashMismatch(): void
    {
        $user = new User();
        $user->setResetTokenHash(User::hashResetToken('expected-token'))
            ->setResetExpiresAt((new DateTime())->add(new DateInterval('PT30M')));

        $this->assertFalse($user->isResetTokenValid('different-token'));
    }
}
