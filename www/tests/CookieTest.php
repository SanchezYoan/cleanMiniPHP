<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/FakeDatabase.php';

if (!defined('SUBDOMAIN')) {
    define('SUBDOMAIN', 'BO');
}

final class CookieTest extends TestCase
{
    private mixed $originalDatabase;
    private array $originalConfig;
    
    protected function setUp(): void
    {
        $this->originalDatabase = $this->getDatabaseSingleton();
        $this->originalConfig   = Config::$config;
        
        Config::$config = $this->baseConfig();
        $_COOKIE        = [];
        
        $this->resetLoggerSingleton();
    }
    
    protected function tearDown(): void
    {
        $this->setDatabaseSingleton($this->originalDatabase);
        Config::$config = $this->originalConfig;
        $this->resetLoggerSingleton();
        $_COOKIE = [];
    }
    
    public function testIsCookieValidReturnsTrueAndBindsTokenSafely(): void
    {
        $selectCookieTokenQuery = "SELECT id, cookie_token FROM users\n                      WHERE id = :id AND cookie_token = :cookie_token LIMIT 1";
        
        $db = new FakeDatabase([
                                   "SHOW TABLES LIKE 'logs'" => 1,
                                   $selectCookieTokenQuery   => 1,
                               ]);
        
        $this->setDatabaseSingleton($db);
        
        $userId     = 42;
        $token      = 'secure-token';
        $encrypted  = Encryption::encryptId($userId);
        $cookieHash = hash('sha256', $userId . ':' . $token . Config::get('COOKIE_SECRET_KEY'));
        
        $_COOKIE['auth'] = implode(':', [$encrypted, $token, $cookieHash]);
        
        $this->assertTrue(Cookie::isCookieValid());
        $this->assertSame($userId, Cookie::getUserId());
        
        $bindings = $db->getBindingsFor(
            $selectCookieTokenQuery
        );
        
        $this->assertSame([
                              ':id' => $userId,
                                                                                                                                                                                                                                                                                                                                                                                                      ':cookie_token' => $token,
                          ], $bindings);
    }
    
    public function testIsCookieValidFailsAndTriggersCleanupWhenHashTampered(): void
    {
        $db = new FakeDatabase([
                                   "SHOW TABLES LIKE 'logs'" => 1,
                               ]);
        $this->setDatabaseSingleton($db);
        
        $userId    = 7;
        $token     = 'token-to-tamper';
        $encrypted = Encryption::encryptId($userId);
        
        $_COOKIE['auth'] = implode(':', [$encrypted, $token, 'invalid-hash']);
        
        $this->assertFalse(Cookie::isCookieValid());
        $this->assertSame(0, Cookie::getUserId());
        $this->assertEmpty(array_filter(
                               $db->getPreparedQueries(),
                               static fn(string $query) => str_contains($query, 'FROM users')
                           ));
    }
    
    private function baseConfig(): array
    {
        return [
            'HASH_KEY'                    => 'unit-test-hash-key',
            'ENCRYPTION_KEY'              => 'unit-test-encryption-key',
            'HMAC_SALT'                   => 'unit-test-hmac-salt',
            'HASH_PEPPER'                 => 'unit-test-pepper',
            'HASH_COST_FACTOR'            => 4,
            'COOKIE_SECRET_KEY'           => 'unit-test-cookie-secret',
            'COOKIE_PATH'                 => '/',
            'COOKIE_DOMAIN'               => 'localhost',
            'COOKIE_SECURE'               => false,
            'COOKIE_HTTP'                 => true,
            'REMEMBER_ME_COOKIE_LIFETIME' => 3600,
            'FEATURES'                    => [
                'LOGGER' => [
                    'DEBUG'    => ['WRITE_TXT' => false, 'WRITE_DB' => false, 'SEND_MAIL' => false],
                    'NOTICE'   => ['WRITE_TXT' => false, 'WRITE_DB' => false, 'SEND_MAIL' => false],
                    'WARNING'  => ['WRITE_TXT' => false, 'WRITE_DB' => false, 'SEND_MAIL' => false],
                    'ERROR'    => ['WRITE_TXT' => false, 'WRITE_DB' => false, 'SEND_MAIL' => false],
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