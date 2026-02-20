<?php

use PHPUnit\Framework\TestCase;

final class EncryptionTest extends TestCase
{
    private array $originalConfig;

    protected function setUp(): void
    {
        $this->originalConfig = Config::$config;

        Config::$config = [
            'ENCRYPTION_KEY' => 'encryption-key-for-tests',
            'HMAC_SALT' => 'hmac-salt-for-tests',
            'HASH_KEY' => 'hash-key-for-tests',
            'HASH_PEPPER' => 'pepper-for-tests',
            'HASH_COST_FACTOR' => 4,
        ];
    }

    protected function tearDown(): void
    {
        Config::$config = $this->originalConfig;
    }

    public function testEncryptAndDecryptRoundTrip(): void
    {
        $plain      = 'donnée-sensible';
        $ciphertext = Encryption::encrypt($plain);

        $this->assertNotSame($plain, $ciphertext);
        $this->assertSame($plain, Encryption::decrypt($ciphertext));
    }

    public function testDecryptFailsOnTamperedHmac(): void
    {
        $ciphertext = Encryption::encrypt('message-de-test');

        $tampered = substr_replace(
            $ciphertext,
            $ciphertext[0] === 'a' ? 'b' : 'a',
            0,
            1
        );

        $this->assertFalse(Encryption::decrypt($tampered));
    }
}
