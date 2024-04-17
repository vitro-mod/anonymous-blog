<?php

namespace App\Entities;

class CommentEntity extends AbstractEntity
{
    public const TABLE_NAME = 'comments';
    public const PRIMARY_KEY = 'id';

    public ?int $id;
    public ?int $post_id;
    public ?string $author;
    public ?string $content;
}
