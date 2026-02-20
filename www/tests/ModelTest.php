<?php

use PHPUnit\Framework\TestCase;

final class ModelTest extends TestCase
{
    private mixed $originalDatabase;

    public function setUp(): void
    {
        $this->originalDatabase = $this->getDatabaseSingleton();
    }

    public function tearDown(): void
    {
        $this->setDatabaseSingleton($this->originalDatabase);
    }

    public function testGetAllFilterByWithAllowedColumnReturnsResults(): void
    {
        $fakePdo = new FakePDO([
            [
                "id" => 1,
                "email" => "user@example.com",
                "login" => "user1",
                "level" => "admin",
            ],
            [
                "id" => 2,
                "email" => "user@example.com",
                "login" => "user2",
                "level" => "editor",
            ],
        ]);

        $this->mockDatabaseWith($fakePdo);

        $model = new class extends Model {
            protected string $table = "users";
        };

        $results = $model->getAllFilterBy("email", "=", "user@example.com");

        $this->assertCount(2, $results);
        $this->assertSame("user@example.com", $results[0]->email);
        $this->assertSame("SELECT * FROM users WHERE email = :value", $fakePdo->queries()[0]);
        $this->assertSame([":value" => "user@example.com"], $fakePdo->statement()->bindings());
    }

    public function testGetAllFilterByWithDisallowedColumnThrowsException(): void
    {
        $model = new class extends Model {
            protected string $table = "users";
        };

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Colonne non autorisée");

        $model->getAllFilterBy("password", "=", "secret");
    }

    private function mockDatabaseWith(FakePDO $connection): void
    {
        $databaseReflection = new ReflectionClass(Database::class);
        $database = $databaseReflection->newInstanceWithoutConstructor();

        $connectionProperty = $databaseReflection->getProperty("connection");
        $connectionProperty->setAccessible(true);
        $connectionProperty->setValue($database, $connection);

        $statementProperty = $databaseReflection->getProperty("statement");
        $statementProperty->setAccessible(true);
        $statementProperty->setValue($database, null);

        $databaseProperty = $databaseReflection->getProperty("database");
        $databaseProperty->setAccessible(true);
        $databaseProperty->setValue(null, $database);
    }

    private function getDatabaseSingleton(): mixed
    {
        $databaseReflection = new ReflectionClass(Database::class);
        $databaseProperty = $databaseReflection->getProperty("database");
        $databaseProperty->setAccessible(true);

        return $databaseProperty->getValue();
    }

    private function setDatabaseSingleton(mixed $database): void
    {
        $databaseReflection = new ReflectionClass(Database::class);
        $databaseProperty = $databaseReflection->getProperty("database");
        $databaseProperty->setAccessible(true);
        $databaseProperty->setValue(null, $database);
    }
}

final class FakePDO extends PDO
{
    private array $rows;
    private array $queries = [];
    private ?FakePDOStatement $statement = null;

    public function __construct(array $rows = [])
    {
        $this->rows = $rows;
    }

    public function prepare(string $query, array $options = []): PDOStatement|false
    {
        $this->queries[] = $query;
        $this->statement = new FakePDOStatement($this->rows, $query);

        return $this->statement;
    }

    public function queries(): array
    {
        return $this->queries;
    }

    public function statement(): ?FakePDOStatement
    {
        return $this->statement;
    }
}

final class FakePDOStatement extends PDOStatement
{
    private array $rows;
    private string $query;
    private array $bindings = [];
    private bool $executed = false;

    public function __construct(array $rows = [], string $query = "")
    {
        $this->rows = $rows;
        $this->query = $query;
    }

    public function bindValue($param, $value, $type = null): bool
    {
        $this->bindings[$param] = $value;

        return true;
    }

    public function execute(?array $params = null): bool
    {
        if ($params !== null) {
            foreach ($params as $key => $value) {
                $this->bindings[$key] = $value;
            }
        }

        $this->executed = true;

        return true;
    }

    public function fetchAll(int $mode = PDO::FETCH_DEFAULT, ...$args): array
    {
        if ($mode === PDO::FETCH_OBJ) {
            return array_map(static fn(array $row) => (object) $row, $this->rows);
        }

        return $this->rows;
    }

    public function query(): string
    {
        return $this->query;
    }

    public function bindings(): array
    {
        return $this->bindings;
    }

    public function executed(): bool
    {
        return $this->executed;
    }
}
