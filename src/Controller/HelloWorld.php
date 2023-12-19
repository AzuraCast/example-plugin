<?php

declare(strict_types=1);

namespace Plugin\ExamplePlugin\Controller;

use App\Controller\SingleActionInterface;
use App\Http\Response;
use App\Http\ServerRequest;
use Psr\Http\Message\ResponseInterface;

final class HelloWorld implements SingleActionInterface
{
    public function __invoke(ServerRequest $request, Response $response, array $params): ResponseInterface
    {
        return $request->getView()
            ->renderToResponse($response, 'example::hello_world');
    }
}
