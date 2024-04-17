<?php

namespace App\Core;

use JsonSerializable;

class ApiResponse implements JsonSerializable
{
    public ?string $errorMessage;
    public ?array $response;

    public function __construct(?string $errorMessage = '', ?array $response = [])
    {
        $this->errorMessage = $errorMessage;
        $this->response = $response;
    }

    public function jsonSerialize()
    {
        if ($this->errorMessage) {
            $this->response["errorMessage"] = $this->errorMessage;
        }

        return $this->response;
    }
}
