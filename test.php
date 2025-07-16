<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

use Bitrix\Main\Loader;

Loader::includeModule('crm');

echo "<pre>";

$res = CUserTypeEntity::GetList([], ['ENTITY_ID' => 'CRM_DEAL']);

while ($field = $res->Fetch()) {
    echo "FIELD_NAME: " . $field['FIELD_NAME'] . "\n";
    echo "LABEL: " . $field['EDIT_FORM_LABEL'] . "\n";
    echo "TYPE: " . $field['USER_TYPE_ID'] . "\n";
    echo "--------------------------\n";
}

echo "</pre>";