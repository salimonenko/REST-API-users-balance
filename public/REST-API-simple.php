<?php

define('ACCESS', 'permit'); // Константа доступа к модулям
// Для более серьезной защиты нужно, к примеру, реализовать полный запрет непосредственного доступа ко ВСЕМ модулям кроме этого и routes/web.php +++
// Например, с ипользованием сессий/токенов.

header('Content-Type: text/html; charset=utf-8');


require_once '../config/determine_absolute_PATH.php';
// Рекуррентно определяем путь до начального каталога, определяемого константой THIS_DIR (не более 10 итераций)
define('PATH_ABSOLUTE', PATH(__DIR__));

$url_RELATIVE = dirname($_SERVER['SCRIPT_NAME']). '/..';


include_once PATH_ABSOLUTE. '/database/create_database.php';

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <meta name="description" content="Проект работает как на РНР 5.3, так и на PHP 8.0.
•	Хранение данных — в MySQL.
•	Все денежные операции должны выполняться в транзакциях.
•	Баланс не может быть отрицательным.
•	Если у пользователя нет записи о балансе — она создаётся при первом пополнении.
•	Все ответы и ошибки должны быть в формате JSON, с корректными HTTP-кодами.
о 200 — успешный ответ о 400 / 422 — ошибки валидации о 404 — пользователь не найден о 409 — конфликт (например, недостаточно средств)
•	Транзакция имеет следующие статусы: deposit, withdraw, transfer_in, transfer_out"/>

    <title>REST API для управления списком задач (users-balance) на PHP</title>


<style>
    * {font-size: 14px}
    p {margin: 2px; line-height: 100%}

    .save_number, .show_number{display: inline-block}

    .parent_info {position: relative}
    .info{border: solid 1px; min-height: 100px; display: inline-block; min-width: 100%; width: 100%; margin: 10px; }
    .for_scripts {position: absolute; display: block; border: none; z-index: -1}

    .show_number{margin-top: 10px}
    .ol{display: inline-block; max-width: 40%; margin-right: 1%; }
    .ol > ol {border: solid 1px}
    button {cursor: pointer}

    #buttons{display: inline-block; vertical-align: top; max-width: 55%; margin-left: 20px;}
    #buttons > div {margin: 15px}
    #buttons > div > button {width: 250px; height: auto}
    #buttons > div.POST, #buttons > div.GET {display: inline-block; vertical-align: top}
    #buttons > div.POST, #buttons > div.GET, #buttons > div.PUT, #buttons > div.DELETE {max-width: 250px}
    #buttons > div.POST > input, #buttons > div.GET > input, #buttons > div.PUT > input, #buttons > div.DELETE > input {max-width: 100%; width: 100%; box-sizing: border-box}

    pre {display: inline-block; white-space: pre-wrap;}
    #testing_INFO, #drop_table, #testing_INFO1, #testing_CHECK {display: inline-block; vertical-align: top; width: 100%}
    #testing_INFO > button {padding: 5px; background-color: #C6D534;}

    #testing_INFO1, #testing_CHECK {width: 50%}
    #testing_INFO1 > button, #testing_CHECK > button {background-color: #00C600; height: 60px; width: 100%}
    #testing_CHECK > button {background-color: #277CFC}
    #drop_table {margin-bottom: 10px;}
    #drop_table > button {padding: 10px; background-color: red}

    .testing {max-width: 250px; display: inline-block}
    .testing_results td > div {max-width: 300px; hyphens: auto; word-wrap: break-word; display: inline-block;}

    .show-ALL_REZULTS {float: right; margin: 0 0 10px 10px; display: inline-block; max-width: 120px; font-size: 12px; line-height: 100%; vertical-align: middle;}

    .testing_results .font-size_12px {font-size: 11px;}
    .testing_results .font-size_12px > div {width: 10px; height: 10px; display: inline-block; vertical-align: middle; margin-right: 3px; border: solid 1px; }
    .color_yellow {background-color: #FFFF9E}
    .color_grey {background-color: grey}
    .color_red {background-color: #FF7171}

</style>

</head>

<body>


<div class="ol">

<p><pre><b>Реализовано приложение на РНР, которое позволяет управлять
            балансом пользователей:</b>
    — зачислять средства,
    — списывать средства,
    — переводить деньги между пользователями,
    — получать текущий баланс.
    Все данные хранятся в базе данных MySQL. Взаимодействие
    с приложением происходит через HTTP API (JSON-запросы и ответы).</pre>
</p>

    <ol>
        <li>Начисление средств пользователю POST /api/deposit {
            "user_id": 1,
            "amount": 500.00,
            "comment": "Пополнение через карту"
            }</li>
        <li>Списание средств POST /api/withdraw
            {
            "user_id": 1,
            "amount": 200.00,
            "comment": "Покупка подписки"
            }.</li>
        <li>Перевод между пользователями POST /api/transfer
            {
            "from_user_id": 1,
            "to_user_id": 2,
            "amount": 150.00,
            "comment": "Перевод другу"
            }</li>
        <li>Получение баланса пользователя GET /api/balance/{user_id}
            {
            "user_id": 1,
            "balance": 350.00
            }</li>
        <li>Посмотреть структуру проекта и узнать о требующихся доработках: CHECK_PROBLEMS</li>
    </ol>
<p>С учетом валидации данных.</p>

    <br/>
    <div class="parent_info">
        <div id="xhr_message_for_scripts" class="info for_scripts"></div>
        <div id="xhr_message" class="info"></div>
    </div>
</div>



<div id="buttons">

<div class="POST">
    <button title="Добавить нового пользователя POST /api/add" data-route="/api/add" value="POST">Добавить нового пользователя <br/>POST /api/add<br/>(поля: "user_id", "amount", "balance")</button>
    <input type="text" name="user_id" placeholder="user_id..." />
    <input type="hidden" name="amount" value="0"/>
    <input type="hidden" name="comment" value="0"/>
</div>

<div class="POST">
    <button title="Начисление средств пользователю POST /api/deposit" data-route="/api/deposit" value="POST">Начисление средств пользователю <br/>POST /api/deposit<br/>(поля: "user_id", "amount", "comment")</button>
    <input type="text" name="user_id" placeholder="user_id..." />
    <input type="text" name="amount" placeholder="amount..."/>
    <input type="text" name="comment" placeholder="comment..." />
</div>

<div class="POST">
    <button title="Списание средств POST /api/withdraw" data-route="/api/withdraw" value="POST">Списание средств <br/>POST /api/withdraw<br/>(поля: user_id", "amount", "comment")</button>
    <input type="text" name="user_id" placeholder="user_id..." />
    <input type="text" name="amount" placeholder="amount..."/>
    <input type="text" name="comment" placeholder="comment..." />
</div>

<div class="POST">
    <button title="Перевод между пользователями POST /api/transfer" data-route="/api/transfer" value="POST">Перевод между пользователями <br/>POST /api/transfer<br/>(поля: "from_user_id", "to_user_id", "amount", "comment")</button>
    <input type="text" name="from_user_id" placeholder="from_user_id..." />
    <input type="text" name="to_user_id" placeholder="to_user_id..."/>
    <input type="text" name="amount" placeholder="amount..."/>
    <input type="text" name="comment" placeholder="comment..." />
</div>

<div class="GET">
    <button title="Получение баланса пользователя GET /api/balance/" data-route="/api/balance/{user_id}" value="GET">Получение баланса пользователя <br/>GET /api/balance/{user_id}<br/>(Поля: "user_id", "balance")</button>
    <input type="text" name="user_id" placeholder="user_id..." />
<!--    <input type="text" name="balance" placeholder="balance..." />-->
</div>

<div class="testing">
<div  id="drop_table">
    <button title="Удалить таблицу из БД. При этом все данные и сама таблица будут уничтожены">Удалить таблицу из базы данных</button>
</div>

<div id="testing_INFO" >
    <button title="Будут показаны файлы и каталоги этого проекта с указанием страниц у файлов, которые помечены как требующие доработки">Узнать о требующихся доработках <br/>в этом проекте</button>
</div>

<div style="padding-top: 10px">
    <div id="testing_INFO1"><button title="Запустить автоматические тесты для этого проекта">Запуск тестирования</button></div><div id="testing_CHECK"><button title="Проверить результаты предыдущего тестирования">Проверить результаты тестирования</button></div>
</div>

</div>

</div>





<script type="text/javascript" charset="utf-8">

(function () { // Задаем обработчики кликов для кнопок
    var REST_methods = ['POST', 'GET']; // Строго задаем только возможные AJAX-запросы (белый список)

    var buttons_parent = document.getElementById('buttons');
    buttons_parent.addEventListener("click", function (e) { // Задаем обработчик клика для кнопок
        var button = e.target;

        if(button.nodeName.toLowerCase() === 'button'){

            if(button.parentNode.hasAttribute('class')){ // Есть ли атрибут "класс"
                var className = button.parentNode.getAttribute('class').split(' '); // Разбиваем этот атрибут на несколько классов

                for(var j=0; j < className.length; j++){ // Для каждого класса
                    var className_j = className[j].replace(/ /g, '');
                    if(REST_methods.indexOf(className_j) > -1){
                        if(button.value !== className_j){ // Еще одна проверка, на случай
                            alert('Неверное значение "value" в html-коде для кнопки: \n' + button.title);
                            return;
                        }else{
                            var inputs = button.parentNode.getElementsByTagName('input');
                            manager(button, className_j, 'xhr_message', inputs);
                        }
                    }
                }
            }
        }
    });


function manager(button, className, id_to_RESPONSE, inputs) {
    var method = encodeURIComponent(className);
    var route = button.getAttribute('data-route');

    var data_Obj1 = {};

    for(var i = 0; i < inputs.length; i++){ // Просматриваем отдельную строчку из массива тестовых данных
        var valueq = (inputs[i].value);
        var nameq = encodeURIComponent(inputs[i].name);

        if(nameq === ''){
            alert('Ошибка в html-коде для кнопки: \n' + button.title);
            return;
        }
        if(valueq === ''){
            alert('Не заполнено поле "'+ inputs[i].name +'" для кнопки: \n' + button.title);
            return;
        }

        if(inputs[i].name === 'user_id'){
            if(route.indexOf('{user_id}') > -1){
                route = route.replace('{user_id}', "{"+ valueq +"}");
            }
        }

        data_Obj1[nameq] = valueq;
    }

    sender(method, route, id_to_RESPONSE, data_Obj1, 'JSON', true, false); // Посылаем на сервер метод (POST, GET и т.п.), а также данные сообразно этому методу
}






function sender(method, route, id_to_RESPONSE, data_Obj1, format, flag_ALERT, flag_RESPONSE_ADD, Function_AFTER = false, Function_AFTER_args_Arr = false) { // Функция отправляет сообщение на сервер  и ждет того или иного ответа, выводя потом его в alert
    var xhr = new XMLHttpRequest();

            var this_DIR = '<?php echo THIS_DIR; ?>';
            var this_DIR_reg = new RegExp('^(.*?' + this_DIR + ')(.*)$');

            var this_URL = window.location.href;
            this_URL = this_URL.replace(this_DIR_reg, '$1') + '/';


        if(format === 'JSON'){
            // Готовим тело сообщения для отправки
            data_Obj1["route"] = route;

            var body_FINAL = JSON.stringify(data_Obj1);

            var xhrHeader_Content_Type;
            xhrHeader_Content_Type = "application/json; charset=utf-8";

        }else if (format === 'HTML'){ // HTML
            xhrHeader_Content_Type = 'application/x-www-form-urlencoded';

            body_FINAL = data_Obj1 + '&route='+ route; // Предполагается, что data_Obj1 (при формате 'HTML') теперь представляет собой обычную строку HTML-запроса с соединительными амперсандами


        }else {
            alert('Формат сообщений, отправляемых на сервер, может быть либо JSON, либо HTML. Нужно задать тот или иной формат или доработать программу');
            return;
        }

        var GET_reque = '', POST_reque = '';
            if(method === "GET"){
                GET_reque = '?json_str='+ body_FINAL;
            }else if (method === 'POST'){
                POST_reque = body_FINAL;
            }else { // Можно доработать с учетом других методов (PUT, DELETE и т.д.) +++
                method = 'POST';
                POST_reque = (body_FINAL);
            }

console.log('Итак, вот что отправляем на сервер методом '+ method+ ', в формате "'+ format+'":');
console.log(POST_reque ? POST_reque : GET_reque);

        xhr.open(method, this_URL+'routes/web.php'+ GET_reque, true); // Имена всех методов посылаем заданным методом
//        например, xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

        xhr.setRequestHeader('Content-Type', xhrHeader_Content_Type);
        xhr.onreadystatechange = function xhr_state() {
            if (xhr.readyState != 4) return;
            if (xhr.status <= 300) {
// После подтверждения получения сообщения сервером выдаем оповещение
//                if(flag_ALERT) alert('Операция '+ method + ' выполнена правильно.');
            }else {
                if(flag_ALERT)
                alert('xhr message: '+xhr.statusText); // Сообщение об ошибке на транспортном (ТСР) уровне. Обычно вызвано проблемами  с доступом к сети или неправильной работой РНР на сервере, т.п.
            }
          // Ответ придет в блок с id=id_to_RESPONSE

/*  В случае фатальных ошибок или т.п. Дело в том, что тогда РНР по своей инициативе выдает сообщение в ТЕКСТОВОМ виде, не в JSON. Проблема в том, что функция РНР  register_shutdown_function() также выдаст сообщение, а вот оно будет json_encode(array('... текст...'). Поэтому придется ОБРАТНО декодировать JSON (последовательности вида \u0431) в читаемый текст (актуально для кириллицы, например).
*/

// Если определено, что ожидался ответ сервера в JSON
            if(format === 'JSON'){

//            document.getElementById(id_to_RESPONSE).innerHTML = decodeURIComponent(xhr.responseText.replace(/[\r\n]+/g, ' \ '));
//                var f = decodeURIComponent(xhr.responseText).replace(/[\r\n]+/g, ' \ ');
                var f = xhr.responseText; // Убрать?... +++

                try {
// Если будет ошибка с JSON, выполнение этой функции остановится, поэтому ниже запись в блок будет делаться уже НЕ в формате JSON
                    var obj = JSON.parse(xhr.responseText); // Проверяем, корректный ли JSON пришел с сервера. Если да, то разбираем его

                    var str = '';
                    for(var t in obj){
                        if(t !== '0'){
                            str += t + ': ';
                        }
                        str += ( obj[t]+ '<br>');
                    }
                    console.log(str);

                    if(flag_RESPONSE_ADD){
                        document.getElementById(id_to_RESPONSE).innerHTML += str;
                    }else {
                        document.getElementById(id_to_RESPONSE).innerHTML = str;
                    }

                }catch (er){ // Если JSON некорректный (тут надо доработать, иногда при ответе сервера все же появляются юникод-последовательности вида \u0431) +++
                    console.log('Ожидался ответ сервера в виде JSON. Но, ответ оказался некорректным JSON');
// Denwer может в случае ошибки также вставить свой скрипт, а это мешает
                        f = f.replace(/<script[^>]*>([^<]*)<\/script>/g, ' ');
                        f = f.replace(/\\\"/g, ' * '); // Убираем излишние кавычки
                        f = f.replace(/\"/g, '\\"').toString();
// Заменяем последовательности вида \u0431 на читаемые символы (русские или т.п.)
                        f = f.replace(/(\\u[\w]{4})/g, function (match, p1) {
                                return JSON.parse('"' + p1 + '"');
//                return decodeURIComponent(p1.toString()) // Почему-то не работает, хотя должно
                        });

                        if(flag_RESPONSE_ADD){ // Если true, то добавляем очередной ответ сервера в инфо-блок (предыдущие ответы сохраняются)
                            document.getElementById(id_to_RESPONSE).innerHTML += f;
                        }else {
                            document.getElementById(id_to_RESPONSE).innerHTML = f; // Предыдущие ответы НЕ сохраняются
                        }
                }

            }else { // Если не JSON. Например, если формат был задан как HTML
                if(flag_RESPONSE_ADD){
                    document.getElementById(id_to_RESPONSE).innerHTML += xhr.responseText;
                }else {
                    document.getElementById(id_to_RESPONSE).innerHTML = xhr.responseText;
                }
            }

// Если нужно что-то сделать после того, как ответ сервера помещен в соответствующий блок
            if(Function_AFTER && (typeof Function_AFTER) === 'function'){ // Если задана функция-обработчик и она существует
                Function_AFTER(Function_AFTER_args_Arr, xhr);
            }
        };

        xhr.send(POST_reque);
        return false;
 }



// Функция делает запрос на сервер, а он просматривает все файлы проекта и ищет там строчки, содержащие отметки + + + (без пробелов). Это - места, где замечена необходимость доработок
function show_problems() {
    sender('POST', '/CHECK_PROBLEMS', 'xhr_message', '', 'HTML', true, false);
}

function dropTable() { // Удаляет таблицу из БД
    if(confirm('Удалить таблицу из базы данных полностью?')){
        sender('POST', '/DROP_TABLE', 'xhr_message', {}, 'HTML', true, false);
    }
}

// Сравнивает текущие результаты тестирования с эталонными (сравнение идет по кодам ответа сервера)
function testing_CHECK() {
    var DO_testing_CHECK_Function_AFTER_args_Arr = 'xhr_message';
    sender('POST', '/testing_CHECK', 'xhr_message', {}, 'JSON', false, false, DO_testing_CHECK_Function_AFTER, DO_testing_CHECK_Function_AFTER_args_Arr);
}

function DO_testing_CHECK_Function_AFTER(DO_testing_CHECK_Function_AFTER, xhr) {
    var response_id = DO_testing_CHECK_Function_AFTER;

    document.getElementById(response_id).innerHTML = '';

    var obj_JSON = JSON.parse(xhr.responseText);

    var table = '<table class="testing_results" border="1"><tbody>';
        table += '<tr style="line-height: 100%"><td><button class="show-ALL_REZULTS" data-show="1" title="Показать все результаты, в том числе которые не содержат проблем">Показать только результаты с проблемами</button><div style="font-weight: bold; margin-bottom: 10px;">Строка запроса</div><br/><div class="font-size_12px"><div class="color_yellow"></div> - Есть проблемы</div><div class="font-size_12px"><div class="color_red"></div> - Ранее сохраненные (эталонные) тестовые значения не являются корректным JSON</div><div class="font-size_12px"><div class="color_grey"></div> - Невозможно сопоставить, т.к. ранее такой набор тестовых значений еще не проверялся</div></td><td style="font-weight: bold">Фактический код ответа сервера</td><td style="font-weight: bold">Эталонный код ответа сервера</td></tr>';

        for(var i=0; i < obj_JSON.length; i++){
            var xhr_status = obj_JSON[i].xhr_status;
            var xhr_status_correct = obj_JSON[i].xhr_status_correct;

            delete obj_JSON[i].xhr_status;
            delete obj_JSON[i].xhr_status_correct;

            table += '<tr><td><div>'+ JSON.stringify(obj_JSON[i]) +'</div></td><td>'+ xhr_status+ '</td><td>'+ xhr_status_correct+ '</td></tr>';
        }
        table += '</tbody></table>';

    var div = document.createElement('div');
    div.innerHTML = table;
    document.getElementById(response_id).appendChild(div);

    var tr_Arr = div.getElementsByTagName('table')[0].getElementsByTagName('tr');

    for(i=1; i < tr_Arr.length; i++){
        var td_Arr = tr_Arr[i].getElementsByTagName('td');

        if(td_Arr[1].textContent !== td_Arr[2].textContent){
            tr_Arr[i].className = 'color_yellow';
            tr_Arr[i].title = 'Для этого тестового набора проверка показывает несоответствие кодов ответа сервера. Следовательно, здесь есть проблемы';
        }
// Окрашиваем ячейки таблицы, соответствующие тестовые наборы которых не удалось проверить, в другие цвета
        if(parseInt(td_Arr[2].textContent) === -1){
            td_Arr[2].className = 'color_red';
            td_Arr[2].title = 'Строчка в файле с сохраненными эталонными тестовыми показателями не является корректной JSON-строкой, из нее невозможно сделать объект JSON. Поэтому для этого набора тестовых значений проверка НЕ сделана';
        }

        if(td_Arr[2].textContent == 0){
            td_Arr[2].className = 'color_grey';
            td_Arr[2].title = 'Такой строчки тестовых значений вообще нет в файле с эталонными тестовыми значениями. Т.е. для этого набора тестовых значений проверка сделана, но невозможно сделать сравнение результата с эталонным кодом возврата сервера';
        }
    }


    div.getElementsByClassName('show-ALL_REZULTS')[0].onclick = show_ALL_REZULTS;

// Показывает все или только проблемные результаты автоматизированного тестирования
function show_ALL_REZULTS() {
    var title_vizible = 'Показать все результаты, в том числе которые не содержат проблем';
    var title_NOT_vizible = 'Показать результаты только тестов с проблемами';


    var i, td_Arr;

    if(parseInt(this.getAttribute('data-show')) === 1){
        for(i=1; i < tr_Arr.length; i++){
            td_Arr = tr_Arr[i].getElementsByTagName('td');
            if(parseInt(td_Arr[1].textContent) === parseInt(td_Arr[2].textContent)){ // Если разные коды ответа (текущего и эталонного тестирований)
                tr_Arr[i].style.display = 'none';
            }
        }

        this.title = title_vizible;
        this.textContent = 'Показать все результаты тестов';
        this.setAttribute('data-show', 0);
    }else {
        for(i=1; i < tr_Arr.length; i++){
            tr_Arr[i].style.display = '';
        }
        this.title = title_NOT_vizible;
        this.textContent = 'Показать только результаты с проблемами';
        this.setAttribute('data-show', 1);
    }
 }

}



function TESTING_this() { // Вызывается скриптом, пришедшим от сервера после нажатия кнопки запуска тестирования
// Ожидаем, пока скрипт с данными для тестирования загрузится с сервера (если он не загружался ранее)
    var interval = setInterval(function () {
        var i = 0;
            if((typeof functions_to_TEST) !== 'undefined' || i++ > 100){ // Ждем не более 100 итераций (на всякий случай, вдруг скрипт не придет с сервера). В том скрипте приходит переменная-массив для JS (var functions_to_TEST)
                clearInterval(interval);

                if(i >= 100){
                    alert('Похоже, не удалось получить с сервера скрипт с данными для тестирования.');
                }else {
                    DO_TESTING_this();
                }
            }
              }, 400);


function DO_TESTING_this() { // Собственно тестирование
    var i_DO = -1;
    var interval_DO = setInterval(function () { // Через промежуток времени отправляем тестовые запросы на сервер
        try {
            if(i_DO < functions_to_TEST.length - 1){
            console.log('Тестовый набор для i_DO='+ (++i_DO) + ': '+ functions_to_TEST[i_DO]);

            var route_Arr = functions_to_TEST[i_DO][0].split(' '); // Первый элемент массива - всегда "Метод Роут"
            var method = route_Arr[0].trim();
            var route = route_Arr[1].trim();


            var data = '', data_i_Arr = [];  // {"user_id":"1","amount":"44","comment":"амав","route":"/api/deposit"} - примерный шаблон
            var data_Obj1 = {};

                for(var i = 1; i < functions_to_TEST[i_DO].length; i++){ // Просматриваем отдельную строчку из массива тестовых данных
                    var data_i_Arr_begin = functions_to_TEST[i_DO][i].split(':'); // Может быть несколько двоеточий (:)

                    data_i_Arr[0] = data_i_Arr_begin.shift();
                    data_i_Arr[1] = data_i_Arr_begin.join(':'); // Оставшиеся элементы снова соединяем в один (т.к. разделение было по :  )

                    data_Obj1['TEST_route'] = functions_to_TEST[i_DO][0];
                    data_Obj1[data_i_Arr[0]] = data_i_Arr[1];

                    if(data){
                        data += '&' +  data_i_Arr[0] + '=' + (data_i_Arr[1]);
                    }else{
                        data +=  data_i_Arr[0] + '=' + (data_i_Arr[1]);
                    }
                }
                var Function_AFTER_args_Arr = data_Obj1;

console.log('Он же - в объекте: ');
console.log(data_Obj1);

//                    clearInterval(interval_DO); // Раскомментировать, чтобы запускать тест только 1 раз
//Тестовый запрос (имитируем запрос пользователя)
            sender(method, route, 'xhr_message', data_Obj1, 'JSON', false, true, DO_TESTING_this_Function_AFTER, Function_AFTER_args_Arr);
                return;

            }
        }catch (er){
            console.log(er);
            alert('Ошибка при отправке тестового запроса № '+ i_DO);
            clearInterval(interval_DO);
        }
// Если дошли досюда
        alert("Тестирование полностью завершено. \nЧтобы показать полные результаты, нажмите кнопку: Проверить результаты тестирования");
        clearInterval(interval_DO);
    }, 300);

 }

// Функция СОХРАНЯЕТ результат тестового запроса на сервере
function DO_TESTING_this_Function_AFTER(Function_AFTER_args_Arr, xhr) { // Вызывается после обработки ответа сервера на предыдущий запрос
    var xhr_status = xhr.status; // Код ответа на тестовый запрос
    var rezult_to_SAVE = Function_AFTER_args_Arr;

    rezult_to_SAVE['xhr_status'] = xhr.status;
    console.log('   Код ответа сервера на этот тестовый запрос: '+ xhr_status);
// Сохраняем результат на сервере
    sender('POST', '/TESTING{save}', 'xhr_message', rezult_to_SAVE, 'JSON', false, true); // Запрос на сохранение результата тестирования
 }


}


function create_mutation_OBSERVER(scripts_elem_ID, info_elem_ID) {
    var observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
//            console.log(mutation);

            if(mutation.type === 'attributes'){

            }

            if(mutation.type === 'childList'){
                var content_area = document.getElementById(scripts_elem_ID);
                eval(content_area.textContent); // TESTING_this();

                document.getElementById(info_elem_ID).innerHTML = 'Система тестирования запущена...<br/>';

                observer.disconnect(); // Как только сработал, выключаем обозреватель
            }

            if(mutation.type === 'characterData'){

            }
        });

    //    observer.disconnect();
    });

    observer.observe(document.getElementById(scripts_elem_ID), {
        childList: true, // наблюдать за непосредственными детьми
        subtree: true, // и более глубокими потомками
        characterData: true,
        attributes: false,
        attributeOldValue: false, // передавать старое значение в колбэк
        characterDataOldValue: false // передавать старое значение в колбэк
    });
}

// Функция делает ПЕРВОНАЧАЛЬНЫЙ запрос на запуск тестирования
function testing() {
    if(confirm('Запустить процесс автоматического тестирования?')){
// 1. Получаем тестовые наборы данных с сервера
        var script = document.createElement('script');
/*        var location_DIR_Arr = window.location.href.split('/');
        location_DIR_Arr.pop();
        var location_DIR = location_DIR_Arr.join('/');*/

        script.id = 'functions_to_TEST';
        script.src = '<?php echo $url_RELATIVE. PATH_TESTING_PARAMETERS; ?>' +'?'+ Math.random();
        script.type = 'text/javascript';
        script.defer = true;
        document.body.appendChild(script);
// 2. Создаем прослушиватель блока с id="xhr_message_for_scripts" (для JS-ответов сервера)
    create_mutation_OBSERVER('xhr_message_for_scripts', 'xhr_message');
// 3. Запрос на запуск тестирования
    sender('POST', '/TESTING', 'xhr_message_for_scripts', {}, 'HTML', true, false);
    }
}

document.getElementById('testing_INFO').getElementsByTagName('button')[0].onclick = show_problems;
document.getElementById('testing_INFO1').getElementsByTagName('button')[0].onclick = testing;
document.getElementById('drop_table').getElementsByTagName('button')[0].onclick = dropTable;
document.getElementById('testing_CHECK').getElementsByTagName('button')[0].onclick = testing_CHECK;


})();

</script>

</body>
</html>