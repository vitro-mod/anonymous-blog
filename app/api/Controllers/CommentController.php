<?php

namespace App\Controllers;

use App\Core\ApiResponse;
use App\Entities\CommentEntity;
use App\Exceptions\ValidationException;
use App\Repository\CommentRepository;

class CommentController extends AbstractController
{
    protected const LIMIT_LENGTH = 200;
    protected const LIMIT_MINUTES = 1;
    protected const LIMIT_COUNT = 1;

    public function __construct()
    {
        parent::__construct();
        $this->repository = new CommentRepository();
    }

    public function createComment($postId): void
    {
        $this->entity = $this->mapToClass(CommentEntity::class, $this->requestBody);
        $this->entity->post_id = $postId;

        $this->validateCreation();

        $id = $this->repository->save($this->entity);

        $apiResponse = new ApiResponse(null, ["id" => $id]);
        $this->response->httpCode(201)->json($apiResponse);
    }

    public function getCommentsFor($postId): void
    {
        $response = $this->repository->getAllBy(['post_id', $postId]);

        $apiResponse = new ApiResponse(null, $response);
        $this->response->json($apiResponse);
    }

    protected function validateCreation(): void
    {
        if (strlen($this->entity->content) > self::LIMIT_LENGTH) {
            $errorMessage = sprintf('Превышен лимит длины содержания в %d символов.', self::LIMIT_LENGTH);
            throw new ValidationException($errorMessage, 400);
        } elseif (!strlen($this->entity->content) || !strlen($this->entity->author)) {
            $errorMessage = 'Имя автора или содержание не может быть пустым.';
            throw new ValidationException($errorMessage, 400);
        } elseif ($this->repository->countLastBy($this->entity->author, self::LIMIT_MINUTES) >= self::LIMIT_COUNT) {
            $errorMessage = 'Превышен лимит запросов.';
            throw new ValidationException($errorMessage, 429);
        }
    }
}
