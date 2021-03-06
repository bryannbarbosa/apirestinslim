<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Firebase\JWT\JWT;

$app->post('/api/signin', function (Request $request, Response $response) {
    global $db;
    $data = $request->getParams();

    if (array_key_exists('email', $data) && array_key_exists('password', $data)) {
        $account = $db->select("users", "*", [
        "AND" => [
        "email" => $data['email'],
        "password" => $data['password']
        ]
    ]);

        $id = $db->id();

        if (count($account) > 0) {
            $key = "bobesponja56";
            $token = array(
            "iss" => "http://amazonasseguros.com.br",
            "user_id" => $id
            );
            $jwt = JWT::encode($token, $key);
            return $response->withJson(array(
              'response' => $jwt,
              'success' => true
            ));
        } else {
            return $response->withJson(array(
            'response' => 'Not Authenticated!'
        ));
        }
    } else {
        return $response->withJson(array(
            'response' => 'E-mail and password are required.'
        ));
    }
});
