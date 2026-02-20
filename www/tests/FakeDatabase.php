<?php

if (!class_exists(FakeDatabase::class)) {
    final class FakeDatabase extends Database
    {
        private array $rowCountsByQuery;
        private array $preparedQueries = [];
        private array $bindings = [];
        private ?string $currentQuery = null;

        public function __construct(array $rowCountsByQuery = [])
        {
            $this->rowCountsByQuery = $rowCountsByQuery;
        }

        public function prepare(string $query): Database
        {
            $this->currentQuery      = $query;
            $this->preparedQueries[] = $query;

            return $this;
        }

        public function bindValue(string $param, $value, $type = null): Database
        {
            $this->bindings[$this->currentQuery][$param] = $value;
            return $this;
        }

        public function execute($arr = null): bool
        {
            if ($arr !== null && $this->currentQuery !== null) {
                foreach ($arr as $key => $value) {
                    $this->bindings[$this->currentQuery][$key] = $value;
                }
            }
            return true;
        }

        public function countRows(): int
        {
            if ($this->currentQuery === null) {
                return 0;
            }

            return $this->rowCountsByQuery[$this->currentQuery] ?? 0;
        }

        public function getPreparedQueries(): array
        {
            return $this->preparedQueries;
        }

        public function getBindingsFor(string $query): array
        {
            return $this->bindings[$query] ?? [];
        }
    }
}
