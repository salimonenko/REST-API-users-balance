<?php
// Обновляет ДВЕ строки данных в БД: в одной баланс увеличивается, в другой на столько же уменьшается

if(!defined('PATH_ABSOLUTE')){die('Forbidden.');} // Запрет непосредственного доступа к этому модулю
$path_ABSOLUTE = PATH_ABSOLUTE;
require_once $path_ABSOLUTE . '/routes/check_access.php'; // Запрет непосредственного доступа к этому модулю по НТТР


$mayBe_params_REQUE = array("from_user_id", "to_user_id", "amount", "comment");
$mayBe_params_BD = array("user_id", "amount", "comment");// Белый список разрешенных имен полей БД, разрешенный для данного метода

// Полей from_user_id, to_user_id НЕТ в БД, но есть user_id.
// 1. Поэтому временно добавляем первое значение ID в массив данных запроса (для валидации значения)
myGlobals::$array_REQUEST['user_id'] = myGlobals::$array_REQUEST['from_user_id']; // Теперь user_id - это from_user_id (это только для проверки)
// Проводим проверку и валидацию данных из запроса перед тем, как делать SQL-запрос
$checkig_Arr1 = checking_DATA_CRUD_methods($mayBe_params_BD, $db);
$mayBe_params_val = $checkig_Arr1[0];
$params_types_Arr = $checkig_Arr1[1];

if($mayBe_params_val === null){ // Если данные не прошли валидацию
    die_echo_JSON($params_types_Arr, 1, 1); // Выводим сообщения о проблемах валидации и прекращаем работу
}

// 2. Теперь временно добавляем второе  значение ID в массив данных запроса (для валидации значения) и также проводим проверку вместе с другими данными
myGlobals::$array_REQUEST['user_id'] = myGlobals::$array_REQUEST['to_user_id']; // Теперь user_id - это to_user_id (это только для проверки)
// Проводим вторую проверку
$checkig_Arr2 = checking_DATA_CRUD_methods($mayBe_params_BD, $db);
$mayBe_params_val = $checkig_Arr2[0];
$params_types_Arr = $checkig_Arr2[1];

if($mayBe_params_val === null){ // Если данные не прошли валидацию
    die_echo_JSON($params_types_Arr, 1, 1); // Выводим сообщения о проблемах валидации и прекращаем работу
}

unset(myGlobals::$array_REQUEST['user_id']); // Более не нужно


if($mayBe_params_val === null){ // Значит, при валидации выявлено несоответствие данных
    http_response_code(422);
    die_echo_JSON($params_types_Arr, 1, 1);
    return;
}

if($mayBe_params_val['amount'] <= 0){
    http_response_code(406);
    die_echo_JSON('Сумма перевода должна быть положительным числом.', 1, 1);
}


// Исходя из массива (полученного из JSON) данных в запросе Формируем массив (проверенных) параметров для отправки на запрос к БД
$mayBe_params_val = array_filter(myGlobals::$array_REQUEST, function ($el) use ($mayBe_params_REQUE){
    $k = array_search($el, myGlobals::$array_REQUEST);
   return in_array($k, $mayBe_params_REQUE);
});

// На всякий случай, проверяем, что полученный массив имеет правильные ключи
if(array_diff(array_keys($mayBe_params_val), $mayBe_params_REQUE) !== array()){
    http_response_code(500);
    throw new ErrorException('Странная ошибка сервера при обработке запроса клиента по перечислению средств.'. ' 1');
}


// Если все хорошо, обновляем ДВЕ записи в БД
$public_request = $num_CRUD->POST_api_transfer($mayBe_params_val);

// Анализируем результат выполнения публичного запроса и сообщаем пользователю
if($public_request !== true){

    if($public_request === 'NOT_exists'){ // Если пользователь(и) НЕ существует
        http_response_code(404);
        die_echo_JSON("Как минимум, один из пользователей: from_user_id=". myGlobals::$array_REQUEST['from_user_id'] ." и/или to_user_id=". myGlobals::$array_REQUEST['to_user_id']. " не наден(ы). Поэтому перевод невозможен.", 1, 1);

        return; // По идее, излишнее, на всякий случай
    }elseif (is_array($public_request)){ // Если не изменена ни одна строчка БД
        $mes = '';
        if($public_request[0] !== 1){
            $mes .= 'Ошибка: запись для пользователя '. $mayBe_params_val['from_user_id'] . ' не обновилась. ';
        }
        if($public_request[1] !== 1){
            $mes .= 'Ошибка: запись для пользователя '. $mayBe_params_val['to_user_id'] . ' не обновилась. ';
        }

        if($mes){
            http_response_code(409);
            die_echo_JSON($mes, 1, 1);
        }else{
            http_response_code(200);
            die_echo_JSON("Перечисление перевода от пользователя from_user_id=". $mayBe_params_val['from_user_id'] ." пользователю to_user_id = ". $mayBe_params_val['to_user_id']. " успешно выполнено.", 1, 1);
        }

    }

}else{
    http_response_code(406);
    throw new ErrorException('Ошибка при обработке запроса клиента по перечислению перевода: '. $public_request . ' 1');
}
