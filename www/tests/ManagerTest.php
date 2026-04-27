<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/FakeDatabase.php';

if (!defined('SUBDOMAIN')) {
    define('SUBDOMAIN', 'TEST');
}

final class ManagerTest extends TestCase
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

    public function testAddErrorStoresMessageAndSetsFlag(): void
    {
        $manager = new class extends Manager {
            public function validate(array $data): bool
            {
                return !empty($data);
            }
        };

        $manager->addError("Erreur critique");

        $this->assertTrue($manager->hasErrors());
        $this->assertSame(["Erreur critique"], $manager->errors());
        $this->assertSame("Erreur critique", $manager->errorsAsString());
    }

    public function testAddErrorsMergesMessagesAndResetClearsState(): void
    {
        $manager = new class extends Manager {};

        $manager->addErrors(["E1", "E2"]);
        $manager->resetErrors();

        $this->assertFalse($manager->hasErrors());
        $this->assertSame([], $manager->errors());
        $this->assertSame("", $manager->errorsAsString());
    }

    private function baseConfig(): array
    {
        return [
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
