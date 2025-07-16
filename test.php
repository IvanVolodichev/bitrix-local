<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

use Bitrix\Main\Loader;
Loader::includeModule('crm');

$ufFieldName = 'UF_CRM_1752661461592'; // ← сюда подставь имя поля, которое нашёл

$res = CUserFieldEnum::GetList([], ['USER_FIELD_NAME' => $ufFieldName]);

echo "<pre>";
while ($item = $res->Fetch()) {
    echo "ID: " . $item['ID'] . "\n";
    echo "VALUE: " . $item['VALUE'] . "\n";
    echo "-------------------\n";
}
echo "</pre>";