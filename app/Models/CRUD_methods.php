<?php

class CRUD_methods{
// Методы: POST_api_deposit, POST_api_withdraw, POST_api_transfer, GET_api_balance_user_id
    // для соединения с базой данных и имя таблицы
    protected $conn;
    protected $table_name = TABLE_NAME; // "REST_CRUD_test_table";

    // свойства объекта (только 4 поля)
    public $user_id;
    public $amount;
    public $balance;
    public $comment;

    // конструктор с $db как соединение с базой данных
    public function __construct($db){
        $this->conn = $db;
    }

    public function check_user_id($user_id_Arr){ // Проверяет наличие пользователя в БД пока только для одного или двух id - одним запросом

        try{
            if(sizeof($user_id_Arr) === 1){
                // Вроде бы, выполняется быстрее, чем с использованием SELECT COUNT
            $query = "SELECT EXISTS(SELECT `user_id`  FROM `$this->table_name` WHERE `user_id` = ". $user_id_Arr[0] ." LIMIT 1) AS exist";
//                $query = "SELECT COUNT(DISTINCT user_id)=1 FROM $this->table_name WHERE user_id IN(". $user_id_Arr[0]  .")";

            $returnObj = $this->conn->query($query)->fetch();
            $if_exists = $returnObj;

            if($if_exists['exist']){ // Если пользователь с таким user_id уже существует в БД
                return 'exists';
            }else{
                return 'NOT_exists';
            }
        }

        if(sizeof($user_id_Arr) === 2){
            $query = "SELECT COUNT(DISTINCT user_id)=2 FROM $this->table_name WHERE user_id IN(". $user_id_Arr[0] .",". $user_id_Arr[1] .")";

                $stmt = $this->conn->prepare($query);
                if($stmt->execute()){
                    return $stmt->fetch(PDO::FETCH_ASSOC);
                }else{
                    return true;
                }
        }

        } catch (PDOException $e) {
            return true;
        }

        return false;
    }


    // Создаем запись в базе данных (добавляем пользователя)
    public function POST_api_add($mayBe_params_val){
	
		$mayBe_params_val['balance'] = 0;
	
        $mayBe_params_val_keys = array_keys($mayBe_params_val);

        $user_id = $mayBe_params_val['user_id'];

        $arr = array();
        foreach ($mayBe_params_val as $item){
            $arr[] = $this->conn->quote($item);
        }

        if(sizeof($arr) !== sizeof($mayBe_params_val)){ // Если Вдруг потерялись значения массива
            http_response_code(500);
            throw new ErrorException('Error: Array sizes are not equal. 1'); // 1 - работу прекращаем
        }

        $mayBe_params_val = $arr;

// Проверяем, существует ли пользователь с таким user_id
        $x = $this->check_user_id(array($user_id));
        if($x === 'exists'){
            return 'exists';
        }elseif ($x === true){
            return true;
        }

// Если пользователя с таким user_id еще нет, до добавляем его
        $field_s = "$this->table_name(". implode(", ", $mayBe_params_val_keys) . ") ";
        $value_s = " VALUES (". implode(", ", $mayBe_params_val). ")";

        // Делаем запрос для вставки Реквизитов, полученных от клиента (в частности, user_id и нулевые значения других полей БД)
//        $query = "INSERT INTO $this->table_name($field1, $field2, $field3) VALUES (3, 5, 7)";  // Шаблон
        $query = "INSERT INTO ". $field_s. $value_s;

        $returnObj = false;

        try {
            $this->conn->beginTransaction(); // Оборачиваем в транзакцию (чтобы запись в БД выполнялась на атомарных условиях)
            $returnObj = $this->conn->query($query);
            $this->conn->commit();           // Делаем запись транзакции

            return false;
        } catch (PDOException $e) {
            $this->conn->rollBack();         // В случае ошибки делаем откатку транзакций
            throw new ErrorException('Ошибка при обработке запроса клиента на добавление нового пользователя: '. $e);
        }
    }

    // Обновляем запись в базе данных (добавляем средства имеющемуся пользователю на баланс)
    public function POST_api_deposit($mayBe_params_val){

        $user_id = $mayBe_params_val['user_id'];
        unset($mayBe_params_val['user_id']);

        $amount = $mayBe_params_val['amount']; // Запасаем, т.к. ниже он будет удален из массива

// Проверяем, существует ли пользователь с таким user_id (если нет, то прекращаем работу)
        $x = $this->check_user_id(array($user_id));
        if($x === 'NOT_exists'){
            return 'NOT_exists';
        }elseif ($x === true){
            return true;
        }

        // Делаем запрос для ЗАМЕНЫ Реквизитов задачи, на те, что получены от клиента
        $query = "UPDATE $this->table_name SET ";
        foreach ($mayBe_params_val as $key=>$value){
            $query .= $key. " = ". $this->conn->quote($value);
            array_pop($mayBe_params_val);
            if(sizeof($mayBe_params_val)){ // Если был удален НЕ последний элемент массива, то добавляем запятую
                $query .= ", ";
            }
        }
        $query = $query. ", balance = balance + ". $amount; // При каждой операции подсчитываем баланс

        $query = $query.  " WHERE user_id=". $user_id . " AND (balance + $amount) >= 0";
//      $query = "UPDATE Users SET name = :username, age = :userage WHERE id = :userid" // Шаблон

        $returnObj = false;
        try {
            $this->conn->beginTransaction(); // Оборачиваем в транзакцию для атомарности выполнения записи в БД
            $returnObj = $this->conn->query($query);
            $this->conn->commit();

            return $returnObj->rowCount();
        } catch (PDOException $e) {
            $this->conn->rollBack();
            throw new ErrorException('Ошибка при обработке запроса клиента по изменению суммы депозита: '. $e);
        }

        /* (ЭТО ЕСЛИ ДЕЛАТЬ ЧЕРЕЗ ПОДГОТОВКУ ЗАПРОСА И Т.Д. - другой вариант)
        // Подготовляем запрос
        $stmt = $this->conn->prepare($query);
        // Преобразуем опасные символы в безопасные последовательности
        $this->name=htmlspecialchars(strip_tags($this->num));
        // bind values
        $stmt->bindParam(":num", $this->num);
        // execute query
        if($stmt->execute()){
            return true;
        }*/
    }


    // Списание средств (отдельный метод)
    public function POST_api_withdraw($mayBe_params_val){
        // Пока пустая заготовка на будущее
    }






    public function POST_api_transfer($mayBe_params_val){

        $from_user_id = $mayBe_params_val['from_user_id'];
        $to_user_id = $mayBe_params_val['to_user_id'];

        $amount = $mayBe_params_val['amount'];
        $comment = $mayBe_params_val['comment'];

        // Проверяем, существуют ли пользователи с такими user_id
        $x = $this->check_user_id(array($from_user_id, $to_user_id));
        if($x === true){ // Если ошибка
            return true;
        }

        // $x = Array([COUNT(DISTINCT user_id)=2] => 1) - шаблон
        $x_value_Arr = array_values($x);
        $count = $x_value_Arr[0]; // 1

        if($count == 0){ // Хотя бы один пользователь не найден
            return 'NOT_exists';
        }

// Если ошибок не было, можно делать перевод (вычет с баланса одного и добавление к балансу другого)
        $query1 = "UPDATE $this->table_name SET amount = ".(-1*$amount).", comment = '$comment', balance = balance -". $amount ." WHERE user_id=". $from_user_id . " AND (balance - $amount) >= 0" ;

        $query2 = "UPDATE $this->table_name SET amount = ". $amount. ", comment = '$comment', balance = balance +". $amount. " WHERE user_id=". $to_user_id . " AND (balance + $amount) >= 0" ;

        try{
            $this->conn->beginTransaction();
            $returnObj1 = $this->conn->query($query1);

            if($returnObj1->rowCount() === 1){
                $returnObj2 = $this->conn->query($query2);
            }else{
                $returnObj2 = null;
            }


            $this->conn->commit();

            return array($returnObj1->rowCount(), $returnObj2 ? $returnObj2->rowCount(): 0);

        }catch (PDOException $e) {
            $this->conn->rollBack();
            throw new ErrorException('Ошибка при обработке запроса клиента по перечислению перевода: '. $e);
        }
    }


    public function GET_api_balance_user_id($mayBe_params_val){
        $user_id = $mayBe_params_val['user_id'];

        // Проверяем, существует ли пользователь с таким user_id (если нет, то прекращаем работу)
        $x = $this->check_user_id(array($user_id));
        if($x === 'NOT_exists'){
            return 'NOT_exists';
        }elseif ($x === true){
            return true;
        }

        $query = "SELECT * FROM `$this->table_name` WHERE `user_id` = $user_id";

        try{$stmt = $this->conn->prepare($query);
            if($stmt->execute()){
                return $stmt->fetch(PDO::FETCH_ASSOC);
            }else{
                return true;
            }
        }catch (PDOException $e) {
            throw new ErrorException('Ошибка при доступе к записи БД. '. $e);
        }
    }
// Удаляем таблицу из БД
    public function POST_drop_table(){

        $query = "SHOW TABLES LIKE '$this->table_name'";
        try {
            $returnObj = $this->conn->query($query);

            if(sizeof($returnObj->fetchAll()) > 0){ // Если таблица существует

                $query = "DROP TABLE IF EXISTS $this->table_name";
                $returnObj = $this->conn->query($query);

                try{
// Специально вызываем метод на только что удаленной таблице. Если она действительно удалена, то будет ошибка
					return $returnObj->fetchAll();

                }catch (PDOException $er){ // (для РНР 5.3)
                    return 'table_DROPED';
                }
            }else{ // Если она уже была удалена
                return 'NOT_exists';
            }

        } catch (PDOException $e) { // Если в работе БД что-то пошло не так
            throw new ErrorException('Ошибка при обработке запроса клиента на проверку присутствия таблицы в базе данных или ее удаление: '. $e);
        }
    }

}


class show_DATA_types extends CRUD_methods{

    public function show_DATA_types_MySQL(){ // В будущем, возможно, делать запрос к БД для определения фактических типов полей +++
/****    ЭТО КОРРЕКТНО РАБОТАЕТ В рнр 5.3. А в РНР 8 ЧАСТИЧНО(!) ИЗМЕНИЛСЯ(!) формат данных, выводимых методом fetchall(). Так портят язык РНР.  ****/
// Два вида запроса, на выбор
  /*      $query = array("SHOW COLUMNS  FROM $this->table_name FROM REST_CRUD_test",
            $query = "SELECT   COLUMN_NAME, DATA_TYPE FROM  INFORMATION_SCHEMA.COLUMNS WHERE  TABLE_SCHEMA = 'REST_CRUD_test' AND  TABLE_NAME = '$this->table_name'");
*/

        return field_MAX_TYPE_SIZE('');
    }


}

