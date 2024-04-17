<?php

namespace App\Repository;

use App\Entities\CommentEntity;

class CommentRepository extends AbstractRepository
{
    public const ENTITY = CommentEntity::class;

    public function countLastBy(string $author, int $minutes): ?int
    {
        $sql = <<<SQL
            SELECT COUNT(*) FROM {$this->tableName} WHERE author = ? AND updated_at >= CURRENT_TIMESTAMP - INTERVAL ? MINUTE;
        SQL;

        $sth = $this->dbConnection->prepare($sql);
        $sth->execute([$author, $minutes]);

        return (int) $sth->fetchColumn();
    }
}
