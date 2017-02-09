# wishlist-api

**Устанавливаем composer и laravel по этим инструкциям:** 

http://laravel.su/docs/4.2/installation 
(установка composer, laravel)
https://scotch.io/tutorials/token-based-authentication-for-angularjs-and-laravel-apps
(настройка token-based аутентификации)
https://gistlog.co/JacobBennett/090369fbab0b31130b51

1. Установить composer. <a href="https://getcomposer.org/Composer-Setup.exe">официальный установщик для Windows</a>

2. Создать laravel-проект следующей командой: composer create-project laravel/laravel --prefer-dist

3. В composer.json добавить последнюю строку

`"require": {
       "php": ">=5.5.9",
       "laravel/framework": "5.1.*",
       "tymon/jwt-auth": "0.5.*"
   },`
   
4. Выполнить composer update

5. В config/app.php в массив providers добавить последней строкой

`Tymon\JWTAuth\Providers\JWTAuthServiceProvider::class`

6. В confug/app.php в массив aliases добавить

`'JWTAuth' => Tymon\JWTAuth\Facades\JWTAuth::class,
'JWTFactory' => Tymon\JWTAuth\Facades\JWTFactory::class`

7. Выполнить: php artisan vendor:publish --provider="Tymon\JWTAuth\Providers\JWTAuthServiceProvider"
(создаст config/jwt.php)

8. Выполнить: php artisan jwt:generate
(создаст secret key, который будет использоваться для подписи токенов и поместит его в config/jwt.php)

9. В database/migrations/2014_10_12_create_users_table.php 

строку

`$table->string('email')->unique();`

заменить на

`$table->string('email', 128)->unique();`

(в противном случае в консоли будет ошибка типа SQLSTATE[42000]: Syntax error or access violation: 
1071 Specified key was too long; max key length is 767 bytes (SQL: alter table "users" add unique 
"users_email_unique"("email")))

10. Выполнить php artisan migrate

11. В database/seeds/DatabaseSeeder.php заменить содержимое на:

```<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use App\User;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        Model::unguard();

        DB::table('users')->delete();

        $users = array(
                ['name' => 'Ryan Chenkie', 'email' => 'ryanchenkie@gmail.com', 'password' => Hash::make('secret')],
                ['name' => 'Chris Sevilleja', 'email' => 'chris@scotch.io', 'password' => Hash::make('secret')],
                ['name' => 'Holly Lloyd', 'email' => 'holly@scotch.io', 'password' => Hash::make('secret')],
                ['name' => 'Adnan Kukic', 'email' => 'adnan@scotch.io', 'password' => Hash::make('secret')],
        );
            
        // Loop through each user above and create the record for them in the database
        foreach ($users as $user)
        {
            User::create($user);
        }

        Model::reguard();
    }
}
```
12. Выполнить php artisan db:seed

13. В routes/api.php добавить:

```Route::get('/', function () {
    return view('index');
});

Route::group(['prefix' => 'api'], function()
{
    Route::resource('authenticate', 'AuthenticateController', ['only' => ['index']]);
    Route::post('authenticate', 'AuthenticateController@authenticate');
});
```

14. Создать AuthenticateController командой: php artisan make:controller AuthenticateController

15. В app/Http/Controllers/AuthenticateController.php заменить содержимое на:

```<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;


class AuthenticateController extends Controller
{

    public function __construct()
    {
        // Apply the jwt.auth middleware to all methods in this controller
        // except for the authenticate method. We don't want to prevent
        // the user from retrieving their token if they don't already have it
        $this->middleware('jwt.auth', ['except' => ['authenticate']]);
    }

    public function index()
    {
        $users = User::all();
        return $users;
    }

    public function authenticate(Request $request)
    {
        $credentials = $request->only('email', 'password');

        try {
            // verify the credentials and create a token for the user
            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'invalid_credentials'], 401);
            }
        } catch (JWTException $e) {
            // something went wrong
            return response()->json(['error' => 'could_not_create_token'], 500);
        }

        // if no errors are encountered we can return a JWT
        return response()->json(compact('token'));
    }

    public function test() {
        $response = array('status' => 'ok', 'productList' => 'product list');

        return \Response::json($response, 200, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
```

16. По умолчанию в laravel включена CSRF token verification. Выключаем её, так как мы используем 
JWT:

В app/Http/Kernel.php в массиве $middlewareGroups закомментировать строку:

`\App\Http\Middleware\VerifyCsrfToken::class,`

17. Отправляет post-запрос с параметрами email и password одного из юзеров в базе:

http://www.wishlist-api.ru/api/api/authenticate?email=holly@scotch.io&password=secret

должен вернуться токен:

`{"token":"eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJzdWIiOjMsImlzcyI6Imh0dHA6XC9cL3d3dy53aXNobGlzdC1
hcGkucnVcL2FwaVwvYXBpXC9hdXRoZW50aWNhdGUiLCJpYXQiOjE0ODY2MjY0MzcsImV4cCI6MTQ4NjYzMDAzNywibmJmIjoxND
g2NjI2NDM3LCJqdGkiOiIwNWFhMDMxZmQ3MGM0ZmZkYmM3ZWYwYzlhNTdjODhhZCJ9.NDJA2V0qUWzsovOlJoUOAIcQkuRL84C
-0va4o7bxRYw"}`

Далее будем использовать этот токен во всех запросах как ключ авторизации пользователя.
