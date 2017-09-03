<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

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
    $ids_table = [];

    for ($i = 0; $i < count($id_table); $i++) {
      array_push($ids_table, $id_table[$i]['id_agreement']);
    }

    $informations = $db->select('agreements', '*', [
        'id' => $ids_table
      ]);


    for ($i = 0; $i < count($informations); $i++) {
        $result_tbody_tr = $db->query("select agreements.id as id_agreement, trs_tbody.id as id, ages_tbody_tr.id_age as id_age
      from ((agreements
      inner join trs_tbody on agreements.id = trs_tbody.id_agreement)
      inner join ages_tbody_tr on trs_tbody.id = ages_tbody_tr.id_tbody_tr and ages_tbody_tr.id_age in(".implode(',', $data['id_ages']).") and agreements.id = {$ids_table[$i]})")->fetchAll();
        $informations[$i]['tbody_tr'] = $result_tbody_tr;
    }

    // return $response->withJson(array(
    //     'response' => $informations
    //   ));

    for ($i = 0; $i < count($informations); $i++) {
        for ($j = 0; $j < count($informations[$i]['tbody_tr']); $j++) {
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
        'success' => true,
        'email' => false
      ));
});

$app->post('/api/tables/search/email', function (Request $request, Response $response) {
    global $db;
    $data = $request->getParams();
    $mail = new PHPMailer(true);
    $mail->CharSet = 'UTF-8';
    $profession_name = $db->select("professions", "profession_name", [
          "id" => $data['id_profession']
        ]);
    $ages = $db->select("ages", "*", [
          "id" => $data['id_ages']
        ]);
    try {
        $mail->SMTPDebug = 0;
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'bryannbarbosa@gmail.com';
        $mail->Password = 'ylmbayageejfmpsm';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('bryannbarbosa@gmail.com', 'Bryann Barbosa');
        $mail->addAddress($data['informations']['email'], $data['informations']['name']);
        $mail->addBCC('bryannbarbosa@gmail.com');

        $mail->isHTML(true);
        $mail->Subject = 'Contato de Formulário (Amazonas Seguros)';
        $mail->Body = "Nome: <b>{$data['informations']['name']}</b><br />
        E-mail: <b>{$data['informations']['email']}</b><br />
        Telefone: <b>".'('.substr($data['informations']['telephone'], 0, 2).') '.substr($data['informations']['telephone'], 4, 4).'-'.substr($data['informations']['telephone'], 6)."</b><br />
        Empresa: <b>{$data['informations']['company']}</b><br />
        Cidade: <b>{$data['informations']['city']}</b><br />
        Consulta por: <b>".strtoupper($data['informations']['person_type'])."</b><br />";

        if ($data['informations']['person_type'] == 'cpf') {
            $mail->Body .= "Profissão a ser consultada: <b>".ucfirst($profession_name[0])."</b><br />
          Idades consultadas: <br />";
            for ($i = 0; $i < count($ages); $i++) {
              if($data['informations']['age'][$i] == 1) {
                $mail->Body .= " <b>{$ages[$i]['age_initial']}</b> até <b>{$ages[$i]['age_final']} anos</b> <b>({$data['informations']['age'][$i]}</b> pessoa)<br />";
              } else {
                $mail->Body .= " <b>{$ages[$i]['age_initial']}</b> até <b>{$ages[$i]['age_final']} anos</b> <b>({$data['informations']['age'][$i]}</b> pessoas)<br />";
              }
            }
        }

        $mail->send();
        return $response->withJson(array(
        'response' => 'email sent successfully!',
        'success' => true,
      ));
    } catch (Exception $e) {
        return $response->withJson(array(
        'response' => 'error in send email',
        'success' => false
      ));
    }
});
