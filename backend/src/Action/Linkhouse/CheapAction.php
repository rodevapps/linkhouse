<?php

namespace App\Action\Linkhouse;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class CheapAction
{   
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $data = $this->table->where('price', '<', 200)->where('traffic', '>', 1000)->whete('quality' '>' 7)->get();

        $payload = json_encode($data);

        $response->getBody()->write($payload);

        return $response->withHeader('Content-Type', 'application/json');
    }
}
