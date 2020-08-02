
RuYou.ru test task
==================

Test task: https://docs.google.com/document/d/1D0iV0d-AQuBF-6lhPpk-CLHEfNIMSoloxmtfp9Qxddo/edit (in Russian).

You can see the working application in action here: http://ruyou.ld.am.

Installation
------------

To install the application you'll need the composer (https://getcomposer.org).

1. Retrieve codebase using `git clone https://github.com/Nilard/RuYou-test-task.git` and move to created directory using the `cd RuYou-test-task` shell command.
2. Run `composer install` command.
3. Create an empty database.
4. Update the file `config/db.php` with real database credentials.
5. Run migration using `php yii migrate`.

Working with REST API
---------------------

You can test working with REST API using `curl`.

Please replace values in brackets with actual data and also replace `https` with `http` for testing purposes if needed.

Please note that all API requests should always be sent via HTTPS.

1. Register new user account using email as username and password:
   ```
   curl -d '{"username":"<email>", "password":"<password>"}' -H "Content-Type: application/json" -X POST https://<host>/user/create
   ```
   You'll get the `id` field in the JSON answer, please remember it for futher use.

2. Get the JSON Web Token using Basic Auth:
   ```
   curl -u <email>:<password> -i -H "Accept:application/json" -X GET https://<host>/user/key
   ```
   You'll get the `token` field in the JSON answer, please remember it for futher use.

3. Update the user account with first name, last name and phone number using JSON Web Token in the header:
   ```
   curl -H "Authorization: Bearer <token>" -d '{"username":"<email>", "password":"<password>", "first_name":"<First name>", "last_name":"<Last name>", "phone":"<Phone number>"}' -H "Content-Type: application/json" -X PUT https://<host>/user/update?id=<id>
   ```
