<?php

namespace App\Entities;

class PostEntity extends AbstractEntity
{
    public const TABLE_NAME = 'posts';
    public const PRIMARY_KEY = 'id';

    public ?int $id;
    public ?string $author;
    public ?string $content;
}
