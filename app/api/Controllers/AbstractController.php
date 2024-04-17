<?php

namespace App\Controllers;

use App\Core\ApiResponse;
use App\Core\Router;
use App\Entities\PostEntity;
use App\Repository\PostRepository;
use Pecee\Http\Request;
use Pecee\Http\Response;

abstract class AbstractController
{
    protected $repository;
    protected Request $request;
    protected Response $response;

    protected array $requestBody;

    protected $entity;

    public function __construct()
    {
        $this->request = Router::request();
        $this->response = Router::response();

        $inputHandler = $this->request->getInputHandler();
        $this->requestBody = $inputHandler->all();
    }

    public function create(): void
    {
        $className = $this->repository::ENTITY;
        $this->entity = $this->mapToClass($className, $this->requestBody);

        $this->validateCreation();

        $id = $this->repository->save($this->entity);

        $apiResponse = new ApiResponse(null, ["id" => $id]);
        $this->response->httpCode(201)->json($apiResponse);
    }

    public function get($id)
    {
        $this->entity = $this->repository->getOne($id);

        $apiResponse = new ApiResponse(null, (array) $this->entity);
        $this->response->json($apiResponse);
    }

    protected function mapToClass(string $className, array $array): ?object
    {
        if (!class_exists($className)) return null;

        $result = new $className;

        foreach (get_class_vars($className) as $key => $value) {
            $result->$key = $array[$key];
        }

        return $result;
    }

    protected function validateCreation(): void
    {
    }
}
