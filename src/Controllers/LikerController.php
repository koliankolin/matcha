<?php

namespace Controllers;

use Classes\Liker;
use Interop\Container\ContainerInterface;
use Slim\Http\Request;
use Slim\Http\Response;
use Exception;

class LikerController extends Controller
{
    private $liker;

    public function __construct(ContainerInterface $c)
    {
        parent::__construct($c);
        $this->liker = new Liker($c);
    }

    public function addLike(Request $request, Response $response)
    {
        $toUserId = $request->getParam("like");
        try {
            $this->liker->addLike($_SESSION["logged"]["user_id"], $toUserId, date("Y-m-d H:i:s"));
        } catch (Exception $exception) {
            $response = $response->write($exception->getMessage());
        }
        return $response->withRedirect("/id" . $toUserId);
    }
}
