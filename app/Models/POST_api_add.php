<?php
// Добавляет строку данных в БД

if(!defined('PATH_ABSOLUTE')){die('Forbidden.');} // Запрет непосредственного доступа к этому модулю
$path_ABSOLUTE = PATH_ABSOLUTE;
require_once $path_ABSOLUTE . '/routes/check_access.php'; // Запрет непосредственного доступа к этому модулю по НТТР

$mayBe_params = array("user_id", "amount", "comment"); // Белый список разрешенных имен полей БД, разрешенный для данного метода

// Проводим проверку и валидацию данных из запроса перед тем, как делать SQL-запрос
$checkig_Arr = checking_DATA_CRUD_methods($mayBe_params, $db);
$mayBe_params_val = $checkig_Arr[0];
$params_types_Arr = $checkig_Arr[1];

if($mayBe_params_val === null){ // Значит, при валидации выявлено несоответствие данных
    http_response_code(422);
    die_echo_JSON($params_types_Arr, 1, 1);
    return;
}

if($mayBe_params_val['amount'] < 0){
    http_response_code(406);
    die_echo_JSON('Сумма зачисления средств должна быть неотрицательным числом.', 1, 1);
}

// Если все хорошо, создаем запись
$public_request = $num_CRUD->POST_api_add($mayBe_params_val);

    // Анализируем результат выполнения публичного запроса и сообщаем пользователю
if($public_request !== true){

    if($public_request === 'exists'){ // Если пользователь уже существует
        http_response_code(400);
        die_echo_JSON("Пользователь user_id=". myGlobals::$array_REQUEST['user_id'] ." уже существует.", 1, 1);
        return;
    }

    http_response_code(201);
    die_echo_JSON("Пользователь user_id=". myGlobals::$array_REQUEST['user_id'] ." успешно добавлен.", 1, 1);
    return;

    }else{
        http_response_code(406);
        throw new ErrorException('Ошибка при обработке запроса клиента по добавлению пользователя: '. $public_request . ' 1');
    }


