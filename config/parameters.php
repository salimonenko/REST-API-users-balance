<?php

if(!defined('ACCESS') && ACCESS !== 'permit'){die('Forbidden.');} // Запрет непосредственного доступа к этому модулю

define('MAX_TODO_SIZE', 1000);

mb_internal_encoding("utf-8");
define('INTERNAL_ENC', mb_internal_encoding()); // Определяем кодировку UTF-8 везде
mb_regex_encoding("utf-8");


define('THIS_DIR', 'REST-API-users-balance'); // Каталог, содержащий ВСЕ файлы и подкаталоги этого проекта

/********    АВТОМАТИЗИРОВАННОЕ ТЕСТИРОВАНИЕ    **************/
define('PATH_TESTING_REZULTS', '/app/Models/Testing/Data/testing_REZULTS.txt'); // Относит. путь к файлу с результатами автом. тестирования
define('PATH_TESTING_REZULTS_CORR', '/app/Models/Testing/Data/testing_REZULTS_correct.txt'); // Относит. путь к файлу с эталонными результатами автом. тестирования
define('PATH_TESTING_PARAMETERS', '/app/Models/Testing/Data/data_for_testing.js'); // Относит. путь к файлу с параметрами для тестирования
define('PATH_TESTING_JSON', '/app/Models/Testing/Data/testing_REZULTS.JSON'); // Относит. путь к файлу с результатами сравнения текущего тестирования и эталонного


/********    БАЗА ДАННЫХ    **************/
define('SERVERNAME', "localhost");
define('USERNAME', "root");

if(PHP_MAJOR_VERSION >= 7){
    define('PASSWORD', "root");
}else{
    define('PASSWORD', "");
}

define('DATABASE', "REST_CRUD_test"); // Имя БД
define('TABLE_NAME', "REST_CRUD_test_table"); // Имя таблицы БД


/* Временно задаем типы и размеры полей БД. Потом можно бы справить, использовав метод fetch_field_direct() +++
 Впрочем, fetch_field_direct(1)->max_length - Максимальная ширина поля результирующего набора. Но, начиная с PHP 8.1, это значение всегда равно 0. Поэтому для РНР 8 это бесполезно (а в РНР 5.3 не работает). Т.е. лучше бы как-то доработать...
 То же касается метода fetch_field. Поэтому, с учетом глупых перемен в РНР, надежнее будет задавать эти данные жестко, НЕ определять их из запросов к БД.
 */
/****    ТИПЫ И МАКСИМАЛЬНЫЕ РАЗМЕРЫ ПОЛЕЙ БАЗЫ ДАННЫХ MySQL:   ****/
function field_MAX_TYPE_SIZE($field){

    $fields_TYPES = array('user_id' =>  array('int', 6, 'not_NEGATIVE'), /* 3-й параметр - проверочная функция */
                          'amount'  =>  array('FLOAT', 12, 'not_NEGATIVE'),
                          'balance' =>  array('FLOAT', 12),
                          'comment' =>  array('VARCHAR', 50)
                         );

    if($field === ''){ // Если требуется вернуть типы и макс. размеры для ВСЕХ полей, наверное, имеющихся в БД
        return $fields_TYPES;
    }

    if(!array_key_exists($field, $fields_TYPES)){
        http_response_code(500);
        throw new ErrorException('Неверный индекс массива при валидации данных запроса: '. $field);
    }


return array($field => $fields_TYPES[$field]);
}

function not_NEGATIVE($number){
    return (is_numeric($number) && $number >= 0);
}
