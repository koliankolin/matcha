<?php

use Slim\Http\Request;
use Slim\Http\Response;
use Controllers\UserController;
use Middleware\RedirectIfAuth;
use Middleware\RedirectIfUnAuth;
use Middleware\RedirectIfNoToken;

// Home Route
$app->get("/", function (Request $request, Response $response) {
    if(!isset($_SESSION["logged"]))
        return $this->view->render($response, "home.twig");
    return $this->view->render($response, "home-authorized.twig");
})->setName("home");

// Users Route


$app->group("", function () {
    // Login
    // Get
    $this->get("/login", function (Request $request, Response $response) {
        return $this->view->render($response, "login.twig");
    })->setName("login");
    // Post
    $this->post("/login", UserController::class . ":login");

    // Register
    // get
    $this->get("/register", function (Request $request, Response $response) {
        return $this->view->render($response, "register.twig");
    })->setName("register");
    // post
    $this->post("/register", UserController::class . ":register");

    //Verify
    $this->get("/verify", UserController::class . ":verify");

    //Send email
    $this->get("/email-sent", function (Request $request, Response $response) {
        return $this->view->render($response, "email-sent.twig");
    })->setName("email-sent");

    //Verify email
    $this->get("/account-activated", function (Request $request, Response $response) {
        return $this->view->render($response, "account-activated.twig");
    })->setName("account-activated");
})->add(new RedirectIfAuth($container["router"]));


$app->group("", function () {
    //Logout
    $this->get("/logout", function (Request $request, Response $response) {
      unset($_SESSION["logged"]);
      return $response->withRedirect("/");
    })->setName("logout");

    //My Profile
    $this->get("/id". $_SESSION["logged"]["user_id"], function (Request $request, Response $response) {
        $userInfo = $this->qb->filterDataByCol("users_info", "user_id", $_SESSION["logged"]["user_id"])[0];
        $userInfo = array_merge(["login" => $_SESSION["logged"]["login"]], $userInfo);
        return $this->view->render($response, "my-profile.twig", compact("userInfo"));
    })->setName("myProfile");

    //Change Login
    $this->group("/change-login", function () {
       $this->get("", function (Request $request, Response $response) {
          return $this->view->render($response, "change-login.twig");
       })->setName("change-login");

       $this->post("",  UserController::class . ":changeLogin");
    });

    //Change Email
    $this->group("/change-email", function () {
        $this->get("", function (Request $request, Response $response) {
            return $this->view->render($response, "change-email.twig");
        })->setName("change-email");

       $this->post("", UserController::class . ":changeEmail");
    });

    //Profile
    $this->get("/id{userId}", function (Request $request, Response $response, $args) {
        if (!($user = $this->qb->filterDataByCol("users", "id", $args["userId"])[0])) {
            return $this->view->render($response, "user-not-found.twig")->withStatus(404);
        }
        return $this->view->render($response, "profile.twig", compact("user"));
    })->setName("profile");

    //Add Personal Info
    $this->group("/change-info", function () {
        $this->get("", function (Request $request, Response $response) {
            $userInfo = $this->qb->filterDataByCol("users_info", "user_id", $_SESSION["logged"]["user_id"])[0];
            //TODO : do search name tag with joins
            $tagsIds = $this->qb->filterDataByCol("tags_users", "user_id", $_SESSION["logged"]["user_id"]);
            $tagsStr = "";
            foreach ($tagsIds as $tagId) {
                $tagName = $this->qb->filterDataByCol("tags", "id", $tagId["tag_id"])[0]["name"];
                $tagsStr .= "#" . $tagName . ", ";
            }
            $userInfo["tags"] = rtrim($tagsStr, ", ");
            return $this->view->render($response, "change-info.twig", compact("userInfo"));
        })->setName("change-info");
        $this->post("", UserController::class . ":changeInfo");
    });

    $this->group("/change-password", function () {
       $this->get("", function (Request $request, Response $response) {
           return $this->view->render($response, "change-password.twig");
       })->setName("change-password");

       $this->post("", UserController::class . ":changePassword");
    });

    //Change Password
    $this->group("/store-password", function () {
       $this->get("", function (Request $request, Response $response) {
          return $this->view->render($response, "store-password");
       })->setName("store-password");

       $this->post("", UserController::class . ":storePassword");
    })->add(new RedirectIfNoToken($this->c->router));
})->add(new RedirectIfUnAuth($container["router"]));





