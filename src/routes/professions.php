<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->get('/api/professions', function (Request $request, Response $response) {
    global $db;
    $data = $request->getParams();

    $profession = $db->query("SELECT * FROM professions")->fetchAll();

    return $response->withJson(array(
      'response' => $profession
    ));
});

$app->get('/api/professions/relations', function (Request $request, Response $response) {
    global $db;
    $data = $request->getParams();

    $relation = $db->query("select professions_agreements.id as id_profession_agreement, professions.id as id_profession, professions.profession_name, agreements.agreement_name, agreements.id as id_agreement
from ((professions_agreements
inner join professions on professions_agreements.id_profession = professions.id)
inner join agreements on professions_agreements.id_agreement = agreements.id)")->fetchAll();

    return $response->withJson(array(
      'response' => $relation
    ));
});

$app->post('/api/professions', function (Request $request, Response $response) {
    global $db;
    $data = $request->getParams();
    $db->insert("professions", [
    "profession_name" => $data['profession_name'],
    ]);
    $id = $db->id();
    if ($id > 0) {
        return $response->withJson(array(
            'response' => 'Profession inserted successfully!',
            'success' => true
        ));
    } else {
        return $response->withJson(array(
            'response' => 'Error in inserting profession'
        ));
    }
});

$app->post('/api/professions/relations', function (Request $request, Response $response) {
    global $db;
    $data = $request->getParams();
    $db->insert("professions_agreements", [
    "id_agreement" => $data['id_agreement'],
    "id_profession" => $data['id_profession']
    ]);
    $id = $db->id();
    if ($id > 0) {
        return $response->withJson(array(
            'response' => 'Relation made successfully!',
            'success' => true
        ));
    } else {
        return $response->withJson(array(
            'response' => 'Error in made relation'
        ));
    }
});

$app->put('/api/professions/{id}', function (Request $request, Response $response, $args) {
    global $db;
    $data = $request->getParams();

    $result = $db->update("professions", [
    "profession_name" => strtolower($data['profession_name']),
  ], [
      "id" => $args['id']
  ]);

    $count = $result->rowCount();
    if ($count > 0) {
        return $response->withJson(array(
      'response' => 'profession is updated!',
      'success' => true
    ));
    } else {
        return $response->withJson(array(
      'response' => 'error in updating profession'
    ));
    }
});

$app->delete('/api/professions/{id}', function (Request $request, Response $response, $args) {
    global $db;
    $data = $request->getParams();

    $result_relation = $db->delete("professions_agreements", [
    "AND" => [
        "id_profession" => $args['id']
    ]
    ]);

    $result = $db->delete("professions", [
    "AND" => [
        "id" => $args['id']
    ]
    ]);

    $count = $result->rowCount();
    if ($count > 0) {
        return $response->withJson(array(
        'response' => 'profession deleted successfully!',
        'success' => true
      ));
    }
});

$app->delete('/api/profession/relations', function (Request $request, Response $response, $args) {
    global $db;
    $data = $request->getParams();

    $result = $db->delete("professions_agreements", [
    "AND" => [
        "id_agreement" => $data['id_agreement'],
        "id_profession" => $data['id_profession']
    ]
    ]);

    $count = $result->rowCount();
    if ($count > 0) {
        return $response->withJson(array(
        'response' => 'relation deleted successfully!',
        'success' => true
      ));
    }
});
