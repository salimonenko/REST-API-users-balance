http://localhost:8000/REST-API-users-balance/public/
 Developing the application for managing user balances (Works in PHP 5.3, PHP 8.0):
— deposit funds,
— withdraw funds,
— transfer money between users,
— get the current balance.
All data is stored in a MySQL database.
Interaction with the application occurs via an HTTP API (JSON requests and responses).
Main Functionality
0. Adding (registering) a new user with an initial balance of zero.
1. Crediting the user
POST /api/deposit {
"user_id": 1,
"amount": 500.00,
"comment": "Deposit via card"
}
2. Withdrawing funds
POST /api/withdraw {
"user_id": 1,
"amount": 200.00,
"comment": "Purchasing a subscription"
}
The balance cannot go into negative territory. 
3. Transfer between users
POST /api/transfer {
"from_user_id": 1,
"to_user_id": 2,
"amount": 150.00,
"comment": "Transfer to friend"
}
4. Get user balance
GET /api/balance/{user_id}
{
"user_id": 1,
"balance": 350.00
}
5. Output information about proposed but not implemented improvements - by specifying specific lines in the corresponding files (using the + + + symbols, without spaces).
6. Delete a database table.
7. Coverage with automated tests. By sending queries from pre-prepared test datasets and comparing them with benchmark results from similar testing. Before starting testing, delete the table from the database and then refresh the page (this will create a new empty database table).

Additionally:
• All monetary transactions with the database are performed in transactions (to ensure "atomicity").
• The balance cannot be negative.
• If the user does not have a balance record, one is created upon the first deposit.
• Almost all responses and errors are in JSON format, with valid HTTP codes. Except for fatal errors.
200 — successful response
400 / 422 — validation errors
404 — user not found
409 — conflict (e.g., insufficient funds)


http://localhost:8000/REST-API-users-balance/public/
Разработка приложения для работы с балансом пользователей (Работает в PHP 5.3, РНР 8.0):
—	зачислять средства,
—	списывать средства,
—	переводить деньги между пользователями,
—	получать текущий баланс.
Все данные хранятся в базе данных MySQL.
Взаимодействие с приложением происходит через HTTP API (JSON-запросы и ответы).
Основной функционал
0. Добавление (регистрация) нового пользователя с нулевым начальным балансом.
1.	Начисление средств пользователю
POST /api/deposit {
"user_id": 1,
"amount": 500.00,
"comment": "Пополнение через карту"
}
2.	Списание средств
POST /api/withdraw {
"user_id": 1,
"amount": 200.00,
"comment": "Покупка подписки"
}
Баланс не может уходить в минус.
3.	Перевод между пользователями
POST /api/transfer {
"from_user_id": 1,
"to_user_id": 2,
"amount": 150.00,
"comment": "Перевод другу"
}
4.	Получение баланса пользователя
GET /api/balance/{user_id}
{
"user_id": 1,
"balance": 350.00
}
5. Вывод информации о предложенных, но не реализованных доработках - в виде указания конкретных строчек в соответствующих файлах (символами + + + , без пробелов).
6. Удаление таблицы базы данных.
7. Покрытие автоматизированными тестами. Путем направления запросов из заранее заготовленных тестовых наборов данных и сравнения с эталонными результатами подобного тестирования. Перед началом тестирования следует удалить таблицу из базы данных, а затем обновить страницу (при этом будет создана новая пустая таблица БД).

Дополнительно:
•	Все денежные операции с БД выполняются в транзакциях (в целях реализации "атомарности").
•	Баланс не может быть отрицательным.
•	Если у пользователя нет записи о балансе — она создаётся при первом пополнении.
•	Почти все ответы и ошибки - в формате JSON, с корректными HTTP-кодами. За исключением фатальных ошибок.
	 200 — успешный ответ 
	 400 / 422 — ошибки валидации 
	 404 — пользователь не найден 
	 409 — конфликт (например, недостаточно средств)

