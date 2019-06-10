<?php

namespace Controllers;

use Interop\Container\ContainerInterface;
use Slim\Http\Request;
use Slim\Http\Response;
use Classes\User;
use Exception;

class UserController extends Controller
{
    private $user;

    public function __construct(ContainerInterface $c)
    {
        parent::__construct($c);
        $this->user = new User([
            "login" => $this->c->request->getParam("login"),
//                (!isset($_SESSION["logged"]["login"])) ?
//                         :
//                        $_SESSION["logged"]["login"],
            "password" => $this->c->request->getParam("password")
        ]);
    }

    public function login(Request $request, Response $response)
    {
        try {
            if ($this->user->loginUser()) {
                return $response->withRedirect($this->router->pathFor("home"));
            }
            return $response->write("Invalid login or password");
        } catch (Exception $exception) {
            return $response->write($exception->getMessage());
        }
    }

    public function register(Request $request, Response $response)
    {
        $email = $request->getParam("email");
        if (filter_var($email, FILTER_VALIDATE_EMAIL))
            $this->user->setEmail($email);
        else
            return $response->write("Invalid email");
        try {
            if ($this->user->registerUser()) {
                return $response->withRedirect($this->router->pathFor("email-sent"));
            }
            return $response->write("Something went wrong");
        } catch (Exception $exception) {
            return $response->write($exception->getMessage());
        }
    }

    public function verify(Request $request, Response $response)
    {
        $hash = $request->getParam("hash");

        if ($this->c->qb->updateDataById("users", "hash", $hash, [
            "activated" => 1
        ])) {
            return $response->withRedirect($this->router->pathFor("account-activated"));
        }
        return $response->write("Something went wrong");
    }

    public function changeLogin(Request $request, Response $response)
    {
        $newLogin = $request->getParam("newLogin");
        //TODO : add some check login
        if ($this->user->changeLoginUser($newLogin)) {
            return $response->withRedirect($this->router->pathFor("logout"));
        }
        return $response->write("Login already exists");
    }

    public function changeEmail(Request $request, Response $response)
    {
        $newEmail = $request->getParam("newEmail");
        if ($this->user->changeEmailUser($newEmail)) {
            unset($_SESSION["logged"]);
            return $response->withRedirect($this->router->pathFor("email-sent"));
        }
        return $response->write("Email already exists");
    }
}
