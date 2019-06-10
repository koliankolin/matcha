<?php


namespace Middleware;

use Slim\Http\Request;
use Slim\Http\Response;

class RedirectIfUnAuth extends Middleware
{
    public function __invoke(Request $request, Response $response, $next)
    {
        if (!isset($_SESSION["logged"])) {
            $response = $response->withRedirect($this->router->pathFor("login"));
        }

        return $next($request, $response);
    }
}