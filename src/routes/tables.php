<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

// Get All Tables

$app->get('/api/tables', function (Request $request, Response $response) {
    global $db;
    $data = $db->select('agreements', '*');

    for ($i = 0; $i < count($data); $i++) {
        $trs_tbody = $data[$i]['id'];
        $consult_tbody = $db->query("select trs_tbody.id as id,trs_tbody.id_agreement, ages_tbody_tr.id_age
        from trs_tbody
        left join ages_tbody_tr
        on trs_tbody.id = ages_tbody_tr.id_tbody_tr and trs_tbody.id_agreement = $trs_tbody
        where trs_tbody.id_agreement = $trs_tbody")->fetchAll();

        $consult_thead = $db->select("trs_thead", "*", [
        "id_agreement" => $data[$i]['id']
        ]);
        $data[$i]['thead_tr'] = $consult_thead;
        $data[$i]['tbody_tr'] = $consult_tbody;
    }

    for ($i = 0; $i < count($data); $i++) {
        for ($j = 0; $j < count($data[$i]['thead_tr']); $j++) {
            $consult_th = $db->select("ths_thead", "*", [
            "id_tr" => $data[$i]['thead_tr'][$j]['id']
            ]);
            $data[$i]['thead_tr'][$j]['ths'] = $consult_th;
        }
    }

    for ($i = 0; $i < count($data); $i++) {
        for ($j = 0; $j < count($data[$i]['tbody_tr']); $j++) {
            $consult_td = $db->select("tds_tbody", "*", [
            "id_tr" => $data[$i]['tbody_tr'][$j]['id']
            ]);
            $data[$i]['tbody_tr'][$j]['tds'] = $consult_td;
        }
    }

    return $response->withJson(array(
      'response' => $data
    ));
});

// Create a table

$app->post('/api/tables', function (Request $request, Response $response) {
    global $db;
    $data = $request->getParams();

    $db->insert('agreements', [
    'agreement_name' => $data['agreement_name'],
    'agreement_image_url' => $data['agreement_image_url'],
    'open_agreement' => $data['open_agreement']
    ]);

    $id = $db->id();

    if ($id > 0) {
        for ($i = 0; $i < count($data['thead_tr']); $i++) {
            $db->insert('trs_thead', [
          'id_agreement' => $id
          ]);

            $id_tr = $db->id();

            for ($j = 0; $j < count($data['thead_tr'][$i]['ths']); $j++) {
                $db->insert('ths_thead', [
            'id_tr' => $id_tr,
            'th_value' => $data['thead_tr'][$i]['ths'][$j]['th_value']
            ]);
            }
        }

        for ($i = 0; $i < count($data['tbody_tr']); $i++) {
            $db->insert('trs_tbody', [
          'id_agreement' => $id
          ]);

            $id_tr = $db->id();

            for ($j = 0; $j < count($data['tbody_tr'][$i]['tds']); $j++) {
                $db->insert('tds_tbody', [
            'id_tr' => $id_tr,
            'td_value' => $data['tbody_tr'][$i]['tds'][$j]['td_value']
            ]);
            }
        }

        return $response->withJson(array(
          'response' => 'table created sucessfully!',
          'success' => true
        ));
    } else {
        return $response->withJson(array(
        'response' => 'error in creating table, try again',
        'success' => false
      ));
    }
});

// Delete Table

$app->delete('/api/tables/{id}', function (Request $request, Response $response, $args) {
    global $db;
    $data = $request->getParams();

    $professions = $db->select("professions", "*", [
      "id_agreement" => $args['id']
    ]);

    $trs_thead = $db->select("trs_thead", "*", [
        "id_agreement" => $args['id']
        ]);

    $trs_tbody = $db->select("trs_tbody", "*", [
        "id_agreement" => $args['id']
        ]);


    for ($i = 0; $i < count($trs_thead); $i++) {
        $db->delete("ths_thead", [
            "id_tr" => $trs_thead[$i]['id']
            ]);
    }

    for ($i = 0; $i < count($trs_tbody); $i++) {
        $db->delete("tds_tbody", [
            "id_tr" => $trs_tbody[$i]['id']
            ]);
    }

    for ($i = 0; $i < count($trs_tbody); $i++) {
        $db->delete("ages_tbody_tr", [
            "id_tbody_tr" => $trs_tbody[$i]['id']
            ]);
    }

    $db->delete("trs_thead", [
            "id_agreement" => $args['id']
            ]);

    $db->delete("trs_tbody", [
            "id_agreement" => $args['id']
            ]);

    $db->delete("professions", [
                "id_agreement" => $args['id']
    ]);

    $table = $db->delete("agreements", [
            "id" => $args['id']
            ]);

    if ($table->rowCount() > 0) {
        return $response->withJson(array(
              'response' => 'Table deleted successfully!',
              'success' => true
          ));
    }
});

// Update Th in table

$app->put('/api/tables/th/{id}', function (Request $request, Response $response, $args) {
    global $db;
    $data = $request->getParams();

    $result = $db->update("ths_thead", [
    "th_value" => $data['value'],
  ], [
      "id" => $args['id']
  ]);
    $count = $result->rowCount();
    if ($count > 0) {
        return $response->withJson(array(
      'response' => 'Th is updated!',
      'success' => true
    ));
    } else {
        return $response->withJson(array(
      'response' => 'Error in updating th'
    ));
    }
});

$app->put('/api/tables/td/{id}', function (Request $request, Response $response, $args) {
    global $db;
    $data = $request->getParams();

    $result = $db->update("tds_tbody", [
    "td_value" => $data['value'],
  ], [
      "id" => $args['id']
  ]);

    $count = $result->rowCount();
    if ($count > 0) {
        return $response->withJson(array(
      'response' => 'Th is updated!',
      'success' => true
    ));
    } else {
        return $response->withJson(array(
      'response' => 'Error in updating th'
    ));
    }
});

// Delete Th from tables

$app->delete('/api/tables/th/{id}', function (Request $request, Response $response, $args) {
    global $db;
    $data = $request->getParams();

    $result = $db->delete("ths_thead", [
      "AND" => [
          "id" => $args['id']
      ]
    ]);

    $count = $result->rowCount();

    if ($count > 0) {
        return $response->withJson(array(
      'response' => 'Th is deleted!',
      'success' => true
    ));
    } else {
        return $response->withJson(array(
      'response' => 'Error in deleting th'
    ));
    }
});

$app->delete('/api/tables/td/{id}', function (Request $request, Response $response, $args) {
    global $db;
    $data = $request->getParams();

    $result = $db->delete("tds_tbody", [
      "AND" => [
          "id" => $args['id']
      ]
    ]);

    $count = $result->rowCount();

    if ($count > 0) {
        return $response->withJson(array(
      'response' => 'Td is deleted!',
      'success' => true
    ));
    } else {
        return $response->withJson(array(
      'response' => 'Error in deleting td'
    ));
    }
});

$app->post('/api/tables/th', function (Request $request, Response $response) {
    global $db;
    $data = $request->getParams();

    $db->insert('ths_thead', [
    'id_tr' => $data['id_tr'],
    'th_value' => $data['value']
    ]);

    $count = $db->id();

    if ($count > 0) {
        return $response->withJson(array(
        'response' => 'th created with success',
        'success' => true
      ));
    } else {
        return $response->withJson(array(
        'response' => 'error in creating th'
      ));
    }
});

$app->post('/api/tables/td', function (Request $request, Response $response) {
    global $db;
    $data = $request->getParams();

    $db->insert('tds_tbody', [
    'id_tr' => $data['id_tr'],
    'td_value' => $data['value']
    ]);

    $count = $db->id();

    if ($count > 0) {
        return $response->withJson(array(
        'response' => 'td created with success',
        'success' => true
      ));
    } else {
        return $response->withJson(array(
        'response' => 'error in creating td'
      ));
    }
});

$app->post('/api/tables/search', function (Request $request, Response $response) {
    global $db;
    $data = $request->getParams();

    $id_table = $db->select("professions_agreements", "*", [
      "id_profession" => $data['id_profession']
    ]);

    for($i = 0; $i < count($id_table); $i++) {
      $informations = $db->select('agreements', '*', [
        'id' => $id_table[$i]['id_agreement']
      ]);
    }

    for($i = 0; $i < count($informations); $i++) {
      $result_tbody_tr = $db->query("select agreements.id as id_agreement, trs_tbody.id as id, ages_tbody_tr.id_age as id_age
      from ((agreements
      inner join trs_tbody on agreements.id = trs_tbody.id_agreement)
      inner join ages_tbody_tr on trs_tbody.id = ages_tbody_tr.id_tbody_tr and ages_tbody_tr.id_age in(".implode(',',$data['id_ages'])."))")->fetchAll();
      $informations[$i]['tbody_tr'] = $result_tbody_tr;
    }

    for($i = 0; $i < count($informations); $i++) {
      for($j = 0; $j < count($informations[$i]['tbody_tr']); $j++) {
        $consult_td = $db->select("tds_tbody", "*", [
        "id_tr" => $informations[$i]['tbody_tr'][$j]['id']
        ]);
        $informations[$i]['tbody_tr'][$j]['tds'] = $consult_td;
      }
    }

    for ($i = 0; $i < count($informations); $i++) {

        $consult_thead = $db->select("trs_thead", "*", [
        "id_agreement" => $informations[$i]['id']
        ]);
        $informations[$i]['thead_tr'] = $consult_thead;

    }

    for ($i = 0; $i < count($informations); $i++) {
        for ($j = 0; $j < count($informations[$i]['thead_tr']); $j++) {
            $consult_th = $db->select("ths_thead", "*", [
            "id_tr" => $informations[$i]['thead_tr'][$j]['id']
            ]);
            $informations[$i]['thead_tr'][$j]['ths'] = $consult_th;
        }
    }

    return $response->withJson(array(
      'response' => $informations,
      'success' => true
    ));
});
