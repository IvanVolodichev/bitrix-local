<?php

    $method = 'crm.deal.userfield.list'; // Для других сущностей замените `deal` (например, `lead`, `contact`)
    $webhookUrl = 'http://192.168.1.73/rest/1/aod0scfba11vm0y9/';

    $response = file_get_contents($webhookUrl . $method . '.json');
    $data = json_decode($response, true);

    var_dump($data['result']);

    exit;