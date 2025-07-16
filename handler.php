<?php

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

use Bitrix\Main\Loader;
use Bitrix\Main\UI\Extension;

Loader::includeModule("crm");
Loader::includeModule("main");

echo "<pre>";

$entityId = 'CRM_DEAL'; // можно заменить на CRM_CONTACT, CRM_COMPANY и т.д.

$res = CUserTypeEntity::GetList([], ['ENTITY_ID' => $entityId]);

while ($field = $res->Fetch()) {
    echo "Поле: " . $field['FIELD_NAME'] . PHP_EOL;
    echo "Название: " . $field['EDIT_FORM_LABEL'] . PHP_EOL;
    echo "Тип: " . $field['USER_TYPE_ID'] . PHP_EOL;
    echo "---------------------------" . PHP_EOL;
}

echo "</pre>";

// Получаем данные из формы
$name = $_POST['name'] ?? '';
$phone = $_POST['phone'] ?? '';
$comment = $_POST['comment'] ?? '';

try {
    $contact = new CCrmContact;
    $contactFields = [
        'NAME' => $name,
        'PHONE' => [
            ['VALUE' => $phone, 'VALUE_TYPE' => 'WORK']
        ],
    ];

    $contactId = $contact->Add($contactFields);
    if (!$contactId) {
        throw new Exception("Ошибка добавления контакта: " . $contact->LAST_ERROR);
    }

    $deal = new CCrmDeal;
    $dealFields = [
        'TITLE' => 'Заявка с сайта ' . date('Y-m-d H:i:s'),
        'CONTACT_ID' => $contactId,
    ];

    $dealId = $deal->Add($dealFields);
    if (!$dealId) {
        throw new Exception("Ошибка добавления сделки: " . $deal->LAST_ERROR);
    }

    $commentFields = [
        'ENTITY_TYPE' => 'deal',
        'ENTITY_ID' => $dealId,
        'COMMENT' => $comment,
        'AUTHOR_ID' => $contactId,
    ];

    $result = \Bitrix\Crm\Timeline\CommentEntry::create($commentFields);
    if (!$result->isSuccess()) {
        throw new Exception("Ошибка добавления комментария: " . implode('; ', $result->getErrorMessages()));
    }
    exit;
    header("Location: /thanks.html");

} catch (Exception $e) {
    error_log("Form handler error: " . $e->getMessage());
    http_response_code(500);
    echo "Ошибка при обработке формы";
}