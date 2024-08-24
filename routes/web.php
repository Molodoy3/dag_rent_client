<?php

use App\Http\Controllers\AccountsController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\NotificationManagerController;
use App\Http\Controllers\PlatformController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StatisticController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\AdminMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

//для удаления сообщения
//Route::post('delete-flash-message', [AccountsController::class, 'deleteFlashMessage'])->name('delete-flash-message');


//главная
Route::get('/', [ProductController::class, 'index'])->name('products.index');
//Клиентская часть
Route::resource('products', ProductController::class)->only(['index', 'show']);
//для обновления
Route::get('/get-update-products', [ProductController::class, 'get'])->name('products.get');
//покупка
Route::post('/products/buy', [ProductController::class, 'buy'])->name('products.buy');
//чаты (хороший способ как все пути формировать для контроллера)
Route::controller(ChatController::class)
    ->middleware('auth')
    ->prefix('chats')
    ->as('chats.')
    ->group(function () {
        //ресурсы тут не робят почему-то
        //Route::resource('chats', ChatController::class)->only(['index', 'show']);
        Route::get('/', 'index')->name('index');
        Route::post('/{chat}/message', 'message')->name('message');
    });
Route::get('chats/{chat}', [ChatController::class, 'show'])->name('chats.show');

//Юзеры
Route::get('login', [UserController::class, 'create'])->name('user.login');
Route::post('login', [UserController::class, 'store'])->name('user.store');
Route::post('logout', [UserController::class, 'logout'])->name('user.logout');
Route::get('/users/{id}', [UserController::class, 'show'])->name('user.show');
Route::post('/user/update-icon', [UserController::class, 'updateIcon'])->name('user.update-icon');

//изменение пароля
Route::get('edit-password', [UserController::class, 'editPassword'])->name('password.edit');
Route::put('update-password', [UserController::class, 'updatePassword'])->name('password.update');

//Все админское
Route::middleware(AdminMiddleware::class)->group(function () {
    //Аккаунты
    Route::resource('accounts', AccountsController::class)->except(['show']);
    //из-за того, что put не хочет передавать файлы, используем дополнительный путь с post запросом для обновления аккаунта
    Route::post('/accounts/{id}', [AccountsController::class, 'update'])->name('account.update');
    //для обновления
    Route::get('/get-update-accounts', [AccountsController::class, 'get'])->name('account.get');

    //Статистика для акков
    Route::resource('statistics', StatisticController::class)->except(['show']);
    //для обновления
    Route::get('/get-update-statistics', [StatisticController::class, 'get']);

    //Добавление пользователя
    Route::get('/user/create', [UserController::class, 'createNew'])->name('user.create');
    Route::post('/user/create', [UserController::class, 'storeNew'])->name('user.storeNew');

    //игры и платформы
    Route::resource('games', GameController::class)->except(['show']);
    Route::resource('platforms', PlatformController::class)->except(['show']);

});

//для подписки и отписки от уведомлений и отправки уведомления
Route::post('/notifications/subscribe', [NotificationManagerController::class, 'subscribe']);
Route::post('/notifications/unsubscribe', [NotificationManagerController::class, 'unsubscribe']);
Route::get('/notifications/send', [NotificationManagerController::class, 'sendNotification']);




