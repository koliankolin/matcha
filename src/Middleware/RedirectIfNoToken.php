<?php

namespace Middleware;

use Slim\Http\Request;
use Slim\Http\Response;

class RedirectIfNoToken extends Middleware
{
    public function __invoke(Request $request, Response $response, $next)
    {
        if (!isset($_SESSION["pass_hash"])) {
            $response = $response->withRedirect($this->router->pathFor("home"));
        }

        return $next($request, $response);
    }
}
