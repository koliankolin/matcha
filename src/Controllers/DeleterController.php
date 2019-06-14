<?php

namespace Controllers;

use Classes\Deleter;
use Slim\Http\Request;
use Slim\Http\Response;
use Interop\Container\ContainerInterface;

class DeleterController extends Controller
{
    private $deleter;

    public function __construct(ContainerInterface $c)
    {
        parent::__construct($c);
        $this->deleter = new Deleter($c);
    }

    public function deletePhoto(Request $request, Response $response)
    {
        $photoId = $request->getParam("photo_id");
        $this->deleter->deletePhoto($photoId);

        return $response->withRedirect($this->router->pathFor("myProfile"));
    }

    public function deleteAvatar(Request $request, Response $response)
    {
        $avatarId = $request->getParam("avatar_id");
        $this->deleter->deleteAvatar($avatarId);

        return $response->withRedirect($this->router->pathFor("myProfile"));
    }
}
