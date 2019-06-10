<?php

use Slim\Http\Request;
use Slim\Http\Response;
use Controllers\UserController;
use Middleware\RedirectIfAuth;
use Middleware\RedirectIfUnAuth;

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
        $user = $this->qb->filterDataByCol("users", "id", $_SESSION["logged"]["user_id"])[0];
        return $this->view->render($response, "my-profile.twig", compact("user"));
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
})->add(new RedirectIfUnAuth($container["router"]));





