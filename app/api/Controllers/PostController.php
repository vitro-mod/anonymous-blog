<?php

namespace App\Controllers;

use App\Core\ApiResponse;
use App\Exceptions\ValidationException;
use App\Repository\CommentRepository;
use App\Repository\PostRepository;
use InvalidArgumentException;

class PostController extends AbstractController
{
    protected const LIMIT_LENGTH = 5000;

    protected const COUNT_POSTS_ON_PAGE = 15;
    protected const COUNT_COMMENTS_WITH_A_POST = 3;

    public function __construct()
    {
        parent::__construct();

        $this->repository = new PostRepository();
    }

    public function getPageWithComments(int $page)
    {
        $totalRecords = $this->repository->getTotal();
        $totalPages = ceil($totalRecords / self::COUNT_POSTS_ON_PAGE);

        $page = $page <= 0 ? 1 : $page;

        if ($page > $totalPages) throw new InvalidArgumentException('Несуществующая страница', 404);

        $offset = ($page - 1) * self::COUNT_POSTS_ON_PAGE;
        $posts = $this->repository->getAll(self::COUNT_POSTS_ON_PAGE, $offset);

        $commentRepository = new CommentRepository();

        $result = [];
        foreach ($posts as $post) {
            $result[] = [
                'post' => $post,
                'comments' => $commentRepository->getAllBy(['post_id', $post['id']], self::COUNT_COMMENTS_WITH_A_POST),
            ];
        }

        $apiResponse = new ApiResponse(null, $result);

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
        }
    }
}
