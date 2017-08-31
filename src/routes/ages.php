<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->post('/api/ages', function (Request $request, Response $response) {
    global $db;
    $data = $request->getParams();
    $age_name = $data['initial'] . $data['final'];

    $db->insert("ages", [
    "age_name" => $age_name,
    "age_initial" => $data['initial'],
    "age_final" => $data['final']
    ]);
    $id = $db->id();
    if ($id > 0) {
        return $response->withJson(array(
            'response' => 'Age inserted successfully!',
            'success' => true
        ));
    } else {
        return $response->withJson(array(
            'response' => 'Error in inserting age'
        ));
    }
});

$app->delete('/api/ages/{id}', function (Request $request, Response $response, $args) {
    global $db;

    $delete_relation = $result = $db->delete("ages_tbody_tr", [
    "AND" => [
        "id_age" => $args['id']
      ]
    ]);

    $result = $db->delete("ages", [
    "AND" => [
        "id" => $args['id']
      ]
    ]);

    $id_age = $result->rowCount();
    $id_tr = $delete_relation->rowCount();
    if ($id_age > 0) {
        return $response->withJson(array(
            'response' => 'Age deleted with successfully!',
            'success' => true
        ));
    } else {
        return $response->withJson(array(
            'response' => 'Error in deleting age'
        ));
    }
});

$app->put('/api/ages/{id}', function (Request $request, Response $response, $args) {
    global $db;
    $data = $request->getParams();
    $age_name = $data['initial_age'] . $data['final_age'];

    $result = $db->update("ages", [
    "age_name" => $age_name,
    "age_initial" => $data['initial_age'],
    "age_final" => $data['final_age']
  ], [
      "id" => $args['id']
  ]);

    $count = $result->rowCount();
    if ($count > 0) {
        return $response->withJson(array(
      'response' => 'Age is updated!',
      'success' => true
    ));
    } else {
        return $response->withJson(array(
      'response' => 'Error in updating age'
    ));
    }
});

$app->post('/api/ages/relations', function (Request $request, Response $response) {
    global $db;
    $data = $request->getParams();

    $db->insert("ages_tbody_tr", [
    "id_age" => $data['id_age'],
    "id_tbody_tr" => $data['id_tr'],
    ]);
    $id = $db->id();
    if ($id > 0) {
        return $response->withJson(array(
            'response' => 'Relation inserted successfully!',
            'success' => true
        ));
    } else {
        return $response->withJson(array(
            'response' => 'Error in inserting relation'
        ));
    }
});

$app->delete('/api/ages/relations/{id}', function (Request $request, Response $response, $args) {
    global $db;
    $data = $request->getParams();

    $result = $db->delete("ages_tbody_tr", [
    "AND" => [
        "id_age" => $args['id'],
        "id_tbody_tr" => $data['id_tr']
      ]
    ]);

    $id = $result->rowCount();

    if ($id > 0) {
        return $response->withJson(array(
        'response' => 'Relation deleted successfully',
        'success' => true
      ));
    } else {
        return $response->withJson(array(
        'response' => 'Error in deleting relation'
      ));
    }
});

$app->get('/api/ages', function (Request $request, Response $response) {
    global $db;
    $data = $request->getParams();

    $data = $db->select('ages', '*');
    return $response->withJson(array(
      'response' => $data
    ));
});

$app->get('/api/ages/relation', function (Request $request, Response $response) {
    global $db;
    $data = $request->getParams();

    $data = $db->select('ages_tbody_tr', '*');
    return $response->withJson($data);
});
