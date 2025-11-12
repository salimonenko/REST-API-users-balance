<?php

require_once __DIR__ . '/../routes/check_access.php' ;

require_once PATH_ABSOLUTE . '/config/parameters.php';

$servername = SERVERNAME; // "localhost"
$database = DATABASE; // "REST_CRUD_test"
$username = USERNAME; // "root" или "" - в зависимости от версии РНР
$password = PASSWORD; // ""

$database_table = TABLE_NAME; // "REST_CRUD_test_table";

// ********  Актуально при первом запуске, когда еще нет базы данных  **********************
// Создание соединения
$conn = @(new mysqli($servername, $username, $password));
// Проверка соединения
    if ($conn->connect_error) {
        http_response_code(500);
        throw new ErrorException("Ошибка подключения: " . $conn->connect_error. ' 1');
    }

// Создание базы данных, если ее еще нет
$sql = "CREATE DATABASE IF NOT EXISTS $database";
    if ($conn->query($sql) !== TRUE) {
        http_response_code(500);
        throw new ErrorException("Ошибка создания базы данных: " . $conn->connect_error . ' 1');
    }
$conn->close();

// Создание нового соединения - для созданной базы данных
$conn_t = new mysqli($servername, $username, $password, $database);
// Создание таблицы в базе данных
$sql = "CREATE TABLE IF NOT EXISTS $database_table (user_id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY, amount FLOAT(12) NOT NULL, balance FLOAT(12) NOT NULL CHECK (balance >=0), comment CHAR (50))";

$mes = '';
    if(!mysqli_query($conn_t, $sql)){
        $mes = "ERROR: Не удалось выполнить $sql. " . mysqli_error($conn_t);
    }
// Закрыть подключение
$conn_t->close();

    if($mes !== ''){
        http_response_code(500);
        throw new ErrorException($mes. '. 1'); // 1 - работу прекращаем
    }


