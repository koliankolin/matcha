<?php

namespace Controllers;

use Slim\Http\Request;
use Slim\Http\Response;
use Classes\Chat;
use Interop\Container\ContainerInterface;

class ChatController extends Controller
{
    private $chat;

    public function __construct(ContainerInterface $c)
    {
        parent::__construct($c);
        $this->chat = new Chat($c);
    }

    public function sendMessage(Request $request, Response $response)
    {
        $message = $request->getParam("message");
        $userIdFrom = $_SESSION["logged"]["user_id"];
        $userIdTo = $request->getParam("user_id_to");

        try {
            $this->chat->saveMessage($message, $userIdFrom, $userIdTo);
        } catch (\Exception $e) {
            $response = $response->write($e->getMessage());
        }
        return $response->withRedirect("/chat?user_id_to={$userIdTo}");
    }
}
