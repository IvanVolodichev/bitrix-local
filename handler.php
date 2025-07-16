<?php
    require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

    use Bitrix\Main\Loader;

    $formData = [
        'name' => $_POST['name'] ?? '',
        'phone' => $_POST['phone'] ?? '',
        'comment' => $_POST['comment'] ?? '',
    ];

    if (!Loader::includeModule('crm')) {
        die("Модуль CRM не подключен");
    }

    // 1. Создание контакта
    $contact = new \CCrmContact(false);
    $contactId = $contact->Add([
        'NAME' => $formData['name'],
        'PHONE' => [
            ['VALUE' => $formData['phone']
        ],
    ]);

    if (!$contactId) {
        die("Ошибка при создании контакта");
    }

    // 2. Создание сделки
    $deal = new \CCrmDeal(false);
    $dealId = $deal->Add([
        'TITLE' => 'Заявка с сайта ' . date('d.m.Y H:i'),
        'CONTACT_ID' => $contactId,
    ]);

    if (!$dealId) {
        die("Ошибка при создании сделки");
    }

    // 3. Добавление комментария (примитивно, можно через timeline или livefeed)
    $noteId = \CCrmActivity::Add([
        'TYPE_ID' => \CCrmActivityType::Note,
        'SUBJECT' => 'Комментарий с формы',
        'DESCRIPTION' => $formData['comment'],
        'DESCRIPTION_TYPE' => \CCrmContentType::PlainText,
        'OWNER_TYPE_ID' => \CCrmOwnerType::Deal,
        'OWNER_ID' => $dealId,
        'AUTHOR_ID' => $contactId, // ID пользователя, от чьего имени создаётся
        'RESPONSIBLE_ID' => 1,
    ]);

    if (!$noteId) {
        die("Комментарий не добавлен");
    }

    // Успешно
    header("Location: thanks.html");
    exit;