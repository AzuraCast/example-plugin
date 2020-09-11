<?php

declare(strict_types=1);

namespace Plugin\ExamplePlugin\Controller;

use App\Http\Response;
use App\Http\ServerRequest;

class HelloWorld
{
    public function __invoke(ServerRequest $request, Response $response): Response
    {
        return $request->getView()->renderToResponse($response, 'example::hello_world');
    }
}
