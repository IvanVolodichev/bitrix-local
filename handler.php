<?php

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

use Bitrix\Main\Loader;

// Загружаем модуль CRM
Loader::includeModule('crm');

// Получаем данные из формы
$name = $_POST['name'] ?? '';
$phone = $_POST['phone'] ?? '';
$comment = $_POST['comment'] ?? '';

try {
    $contact = new CCrmContact;
    $contactFields = [
        'NAME' => $name,
        "FM" => [
            "PHONE" => [
                "1" => [
                    "VALUE"      => $phone,
                    "VALUE_TYPE" => "WORK",
                ],   
            ]
        ]
    ];

    $contactId = $contact->Add($contactFields);
    
    $deal = new CCrmDeal;

    $dealFields = [
        'TITLE' => 'Заявка с сайта ' . date('Y-m-d H:i:s'),
        'CONTACT_ID' => $contactId,
        'COMMENTS' => $comment,
    ];

    $dealId = $deal->Add($dealFields);

    if (!$dealId) {
        throw new Exception("Ошибка добавления сделки: " . $deal->LAST_ERROR);
    }

    header("Location: ./thanks.html");
    exit;

} catch (Exception $e) {
    error_log("Form handler error: " . $e->getMessage());
    http_response_code(500);
    echo "Ошибка при обработке формы";
}