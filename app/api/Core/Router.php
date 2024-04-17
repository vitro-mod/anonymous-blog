<?php

namespace App\Core;

use App\Middlewares\CommentLimitMiddleware;
use App\Middlewares\RateLimitMiddleware;
use Pecee\SimpleRouter\SimpleRouter;
use Pecee\Http\Request;
use Pecee\Http\Response;
use Exception;
use PDOException;

class Router extends SimpleRouter
{
    public static function start(): void
    {
        parent::setDefaultNamespace('App\Controllers');

        parent::group(
            [
                'prefix' => 'api/',
            ],
            fn () => self::setupRoutes()
        );

        parent::start();
    }

    private static function setupRoutes(): void
    {
        parent::post('/posts/', 'PostController@create');
        parent::get('/posts/{postId}', 'PostController@get');
        parent::get('/posts/page/{page}', 'PostController@getPageWithComments');

        parent::post('/posts/{postId}/comments', 'CommentController@createComment');
        parent::get('/posts/{postId}/comments', 'CommentController@getCommentsFor');

        parent::error(function (Request $request, Exception $exception) {
            if ($exception instanceof PDOException) {
                $apiResponse = new ApiResponse('Ошибка при выполнении запроса');
                parent::response()->httpCode(404)->json((array) $apiResponse);
            } else {
                $apiResponse = new ApiResponse($exception->getMessage());
                parent::response()->httpCode($exception->getCode())->json((array) $apiResponse);
            }
        });
    }
}
