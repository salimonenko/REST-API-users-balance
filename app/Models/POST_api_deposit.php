<?php
// Обновляет строку данных в БД

if(!defined('PATH_ABSOLUTE')){die('Forbidden.');} // Запрет непосредственного доступа к этому модулю
$path_ABSOLUTE = PATH_ABSOLUTE;
require_once $path_ABSOLUTE . '/routes/check_access.php'; // Запрет непосредственного доступа к этому модулю по НТТР


$mayBe_params = array("user_id", "amount", "comment"); // Белый список разрешенных имен полей БД, разрешенный для данного метода

// Проводим проверку и валидацию данных из запроса перед тем, как делать SQL-запрос
$checkig_Arr = checking_DATA_CRUD_methods($mayBe_params, $db);
$mayBe_params_val = $checkig_Arr[0];
$params_types_Arr = $checkig_Arr[1];

if($mayBe_params_val === null){ // Значит, при валидации выявлено несоответствие данных
    die_echo_JSON($params_types_Arr, 1, 1);
    return;
}

if($mayBe_params_val['amount'] < 0){
    http_response_code(406);
    die_echo_JSON('Сумма начисляемого депозита должна быть неотрицательным числом.', 1, 1);
}


// Если все хорошо, создаем запись
$public_request = $num_CRUD->POST_api_deposit($mayBe_params_val);

    // Анализируем результат выполнения публичного запроса и сообщаем пользователю
if($public_request !== true){

    if($public_request === 'NOT_exists'){ // Если пользователь НЕ существует
        http_response_code(404);
        die_echo_JSON("Пользователь user_id=". myGlobals::$array_REQUEST['user_id'] ." не найден.", 1, 1);
        return; // По идее, излишнее, на всякий случай
    }elseif ($public_request === 0){ // Если не изменена ни одна строчка БД, т.е. операция не выполнена
        http_response_code(409);
        die_echo_JSON("Конфликт при начислении депозита пользователю user_id=". myGlobals::$array_REQUEST['user_id'] .": запрошенная сумма не начислена.", 1, 1);
    }else{
        http_response_code(200);
        die_echo_JSON("Начисление депозита пользователю user_id=". myGlobals::$array_REQUEST['user_id'] ." успешно выполнено.", 1, 1);
        return;
    }

}else{
    http_response_code(406);
    throw new ErrorException('Ошибка при обработке запроса клиента по начислению депозита: '. $public_request . ' 1');
}
