<?php

use PHPUnit\Framework\TestCase;

if (!function_exists('apache_request_headers')) {
    function apache_request_headers(): array
    {
        return [];
    }
}

if (!defined('BASE_DIR')) {
    define('BASE_DIR', dirname(__DIR__));
}

if (!defined('APP')) {
    define('APP', BASE_DIR . '/app');
}

if (!defined('VENDOR_DIR')) {
    define('VENDOR_DIR', BASE_DIR . '/vendor');
}

if (!defined('VIEWS')) {
    define('VIEWS', APP . '/views');
}

if (!defined('ASSETS')) {
    define('ASSETS', BASE_DIR . '/public/assets');
}

class TestableBoController extends BoController
{
    public function __construct()
    {
        // On évite d'exécuter le constructeur parent pour isoler le test.
    }

    public function triggerFinalize(User $user): void
    {
        $this->finalizeLogin($user);
    }
}

final class BoControllerLoginTest extends TestCase
{
    protected function setUp(): void
    {
        Session::init();
    }

    protected function tearDown(): void
    {
        Session::setRegenerateHandler(null);
        Session::destroy();
    }

    public function testFinalizeLoginRegeneratesSessionAndStoresAdmin(): void
    {
        $regenerateCalled = false;
        Session::setRegenerateHandler(static function (bool $deleteOld) use (&$regenerateCalled): bool {
            $regenerateCalled = $deleteOld;
            return true;
        });

        $controller = new TestableBoController();
        $user       = (new User())->setId(1)->setLevel(User::ADMIN);

        $controller->triggerFinalize($user);

        $this->assertTrue($regenerateCalled, "session_regenerate_id doit être invoqué avec suppression de l'ancienne session.");
        $this->assertSame($user, Session::get('admin'), "L'utilisateur authentifié doit être stocké en session après régénération.");
    }
}
