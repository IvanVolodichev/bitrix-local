<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

use Bitrix\Main\Loader;
use Bitrix\Main\Application;

// Включаем вывод ошибок для отладки (на продакшене убрать)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Загружаем модуль CRM
if (!Loader::includeModule('crm')) {
    die("Ошибка: Не удалось загрузить модуль CRM");
}

// Получаем данные из формы
$request = Application::getInstance()->getContext()->getRequest();
$name = $request->getPost('name') ?? '';
$phone = $request->getPost('phone') ?? '';
$comment = $request->getPost('comment') ?? '';

// Валидация данных
if (empty($name) || empty($phone)) {
    die("Ошибка: Не заполнены обязательные поля (имя и телефон)");
}

try {
    // 1. Создаем контакт
    $contact = new CCrmContact(false);
    $contactFields = [
        'NAME' => $name,
        'PHONE' => [['VALUE' => $phone, 'VALUE_TYPE' => 'WORK']],
        'SOURCE_DESCRIPTION' => 'Заявка с сайта'
    ];

    $contactId = $contact->Add($contactFields);
    if (!$contactId) {
        throw new Exception("Ошибка создания контакта: " . ($contact->LAST_ERROR ?: 'неизвестная ошибка'));
    }

    // 2. Создаем сделку
    $deal = new CCrmDeal(false);
    $dealFields = [
        'TITLE' => 'Заявка от ' . $name . ' (' . date('d.m.Y H:i') . ')',
        'CONTACT_ID' => $contactId,
        'STAGE_ID' => 'NEW',
        'SOURCE_ID' => 'WEB',
        'ASSIGNED_BY_ID' => 1 // ID ответственного
    ];

    $dealId = $deal->Add($dealFields);
    if (!$dealId) {
        throw new Exception("Ошибка создания сделки: " . ($deal->LAST_ERROR ?: 'неизвестная ошибка'));
    }

    // 3. Добавляем комментарий
    $commentFields = [
        'ENTITY_TYPE' => 'deal',
        'ENTITY_ID' => $dealId,
        'COMMENT' => $comment,
        'AUTHOR_ID' => $contactId,
    ];

    $result = \Bitrix\Crm\Timeline\CommentEntry::create($commentFields);
    if (!$result->isSuccess()) {
        throw new Exception("Ошибка комментария: " . implode(', ', $result->getErrorMessages()));
    }

    // Логируем успешное выполнение
    AddMessage2Log("Успешно создана заявка: Сделка ID {$dealId}, Контакт ID {$contactId}", "form_handler");

    // Перенаправляем на страницу благодарности
    LocalRedirect('/thanks.html');

} catch (Exception $e) {
    // Логируем полную ошибку
    AddMessage2Log("Ошибка в форме: " . $e->getMessage(), "form_handler");
    
    // Показываем пользователю понятное сообщение
    die("Произошла ошибка при обработке заявки. Пожалуйста, попробуйте позже или свяжитесь с нами по телефону. <br><small>Код ошибки: " . 
        substr(md5(time()), 0, 6) . "</small>");
}