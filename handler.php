<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

use Bitrix\Main\Loader;
use Bitrix\Main\Application;
use Bitrix\Main\Error;
use Bitrix\Main\Result;

// Убираем вывод deprecated-ошибок на продакшене
error_reporting(E_ALL & ~E_DEPRECATED);

// Загружаем модуль CRM
if (!Loader::includeModule('crm')) {
    die(json_encode([
        'status' => 'error',
        'message' => 'Модуль CRM не доступен',
        'error_code' => 'CRM_MODULE_NOT_FOUND'
    ]));
}

// Получаем данные из формы
$request = Application::getInstance()->getContext()->getRequest();
$name = trim($request->getPost('name') ?? '');
$phone = trim($request->getPost('phone') ?? '');
$comment = trim($request->getPost('comment') ?? '');

// Валидация данных
$errors = [];
if (empty($name)) {
    $errors[] = 'Не указано имя';
}
if (empty($phone)) {
    $errors[] = 'Не указан телефон';
}

if (!empty($errors)) {
    die(json_encode([
        'status' => 'validation_error',
        'errors' => $errors,
        'error_code' => 'FORM_VALIDATION_FAILED'
    ]));
}

// Создаем контакт
$contact = new CCrmContact(false);
$contactFields = [
    'NAME' => $name,
    'PHONE' => [['VALUE' => $phone, 'VALUE_TYPE' => 'WORK']],
    'SOURCE_DESCRIPTION' => 'Заявка с сайта',
    'TYPE_ID' => 'CLIENT'
];

$contactId = $contact->Add($contactFields, true);
if (!$contactId) {
    $errorMessage = $contact->LAST_ERROR ?: 'Неизвестная ошибка создания контакта';
    die(json_encode([
        'status' => 'error',
        'message' => $errorMessage,
        'error_code' => 'CONTACT_CREATION_FAILED'
    ]));
}

// Создаем сделку
$deal = new CCrmDeal(false);
$dealFields = [
    'TITLE' => 'Заявка от ' . $name . ' (' . date('d.m.Y H:i') . ')',
    'CONTACT_ID' => $contactId,
    'STAGE_ID' => 'NEW',
    'SOURCE_ID' => 'WEB',
    'ASSIGNED_BY_ID' => 1, // ID ответственного
    'OPENED' => 'Y'
];

$dealId = $deal->Add($dealFields, true);
if (!$dealId) {
    $errorMessage = $deal->LAST_ERROR ?: 'Неизвестная ошибка создания сделки';
    die(json_encode([
        'status' => 'error',
        'message' => $errorMessage,
        'error_code' => 'DEAL_CREATION_FAILED'
    ]));
}

// Добавляем комментарий
if (!empty($comment)) {
    $commentResult = \Bitrix\Crm\Timeline\CommentEntry::create([
        'ENTITY_TYPE' => 'deal',
        'ENTITY_ID' => $dealId,
        'COMMENT' => $comment,
        'AUTHOR_ID' => $contactId
    ]);
    
    if (!$commentResult->isSuccess()) {
        // Не прерываем выполнение, просто логируем ошибку комментария
        AddMessage2Log(
            'Ошибка добавления комментария: ' . implode('; ', $commentResult->getErrorMessages()),
            'crm_form'
        );
    }
}

// Успешный ответ
echo json_encode([
    'status' => 'success',
    'contact_id' => $contactId,
    'deal_id' => $dealId,
    'message' => 'Заявка успешно создана'
]);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");