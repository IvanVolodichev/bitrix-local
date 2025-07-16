<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

global $USER;

if ($USER->IsAuthorized()) {
    echo "Привет, " . $USER->GetFullName();
} else {
    echo "Пользователь не авторизован";
}