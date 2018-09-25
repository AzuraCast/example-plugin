<?php
namespace Plugin\ExamplePlugin\Controller;

use App\Http\Request;
use App\Http\Response;

class HelloWorld
{
    public function __invoke(Request $request, Response $response): Response
    {
        return $request->getView()->renderToResponse($response, 'example::hello_world');
    }
}