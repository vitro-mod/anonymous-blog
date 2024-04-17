<?php

namespace App\Repository;

use App\Core\Database;
use App\Entities\AbstractEntity;
use PDO;
use PDOException;
use Pecee\SimpleRouter\Exceptions\NotFoundHttpException;

abstract class AbstractRepository
{
    public const ENTITY = '';

    protected string $entityClass;
    protected string $tableName;
    protected string $primaryKey;

    protected $entity;

    protected PDO $dbConnection;

    public function __construct()
    {
        $this->dbConnection = Database::getConnection();

        $this->entityClass = $this::ENTITY;
        $this->tableName = $this->entityClass::TABLE_NAME;
        $this->primaryKey = $this->entityClass::PRIMARY_KEY;
    }

    public function getOne(int $id): AbstractEntity
    {
        $sql = <<<SQL
            SELECT * FROM {$this->tableName} WHERE {$this->primaryKey} = ? LIMIT 1
        SQL;

        $sth = $this->dbConnection->prepare($sql);
        $sth->execute([$id]);

        $entity = $sth->fetchObject($this::ENTITY);

        if (!$entity) {
            throw new NotFoundHttpException("Сущность не найдена", 404);
        }

        return $entity;
    }

    public function getAllBy(array $where, int $limit = 0, int $offset = 0): array
    {
        $sql = <<<SQL
            SELECT * FROM {$this->tableName} WHERE {$where[0]} = ? ORDER BY {$this->primaryKey} DESC
        SQL;

        if ($limit) {
            $sql .= " LIMIT {$limit}";
            if ($offset) {
                $sql .= " OFFSET {$offset}";
            }
        }
        $sql .= ';';

        $sth = $this->dbConnection->prepare($sql);
        $sth->execute([$where[1]]);

        return $sth->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAll(int $limit = 0, int $offset = 0): array
    {
        $sql = <<<SQL
            SELECT * FROM {$this->tableName} ORDER BY {$this->primaryKey} DESC
        SQL;

        if ($limit) {
            $sql .= " LIMIT {$limit}";
            if ($offset) {
                $sql .= " OFFSET {$offset}";
            }
        }
        $sql .= ';';

        $sth = $this->dbConnection->prepare($sql);
        $sth->execute();

        return $sth->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTotal(): int
    {
        $sql = <<<SQL
            SELECT COUNT(*) FROM {$this->tableName};
        SQL;

        $sth = $this->dbConnection->prepare($sql);
        $sth->execute();

        return (int) $sth->fetchColumn();
    }

    public function save(AbstractEntity $entity): ?int
    {
        $entityVars = get_object_vars($entity);
        unset($entityVars[$this->primaryKey]);

        $entityKeys = implode(', ', array_keys($entityVars));
        $questions = implode(', ', array_fill(0, count($entityVars), '?'));

        $entityValues = array_values($entityVars);

        $sql = <<<SQL
            INSERT INTO {$this->tableName} ({$entityKeys}) VALUES ({$questions})
        SQL;

        $sth = $this->dbConnection->prepare($sql);
        $sth->execute($entityValues);

        return (int) $this->dbConnection->lastInsertId();
    }
}
