<?php

use PHPUnit\Framework\TestCase;

final class CsrfTest extends TestCase
{
    protected function setUp(): void
    {
        Session::init();
        Session::destroy();
        Session::init();
        Csrf::clear();
    }

    public function testTokenGenerationReturnsValue(): void
    {
        $token = Csrf::token();

        $this->assertNotEmpty($token);
        $this->assertIsString($token);
    }

    public function testValidateSucceedsAndRotatesToken(): void
    {
        $token = Csrf::token();

        $this->assertTrue(Csrf::validate($token));
        $this->assertNotSame($token, Csrf::token());
    }

    public function testValidateFailsWithInvalidToken(): void
    {
        Csrf::token();

        $this->assertFalse(Csrf::validate('invalid-token'));
    }
}
