<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Platform;
use App\Models\Game;
use App\Models\Statistic;
use App\Models\Tariff;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function get(Request $request)
    {
        return response()->json(
            $this->getAccounts($request)
        );
    }

    public function index(Request $request)
    {
        return Inertia::render('Client/Index', [
            "accounts" => $this->getAccounts($request),
            "platforms" => Platform::all(),
            "games" => Game::all(),
        ]);
    }

    public function show(Request $request, $id)
    {
        //получаем нужный акк
        $product = Account::query()->with('platform', 'user', 'games', 'tariffs')->findOrFail($id);

        //если акк не свободен, то ошибку делаем
        if ($product->status != null || $product->busy != null)
            abort(404);

        //кол-во продаж пользователя
        $userAccounts = $product->user->accounts;
        $countSales = $userAccounts->pluck('id');
        $product->user->countSales = Statistic::whereIn('account_id', $countSales)->count();

        //картинка пользователя
        $userId = $product->user->id;
        $userDir = Storage::disk('public')->files('/img/public/users/' . $userId);
        $userIcon = count($userDir) ? '/storage/' . $userDir[0] : '/storage/img/public/users/default.webp';
        $product->user->icon = $userIcon;

        return Inertia::render('Client/Show', [
            'product' => $product,
        ]);
    }

    public function buy(Request $request)
    {
        //если пользователь не авторизован
        if (!auth()->check())
            throw ValidationException::withMessages([
                'user' => trans('Вам необходимо авторизоваться'),
            ]);

        if ($request->tariff_id == 0)
            throw ValidationException::withMessages(['tariff_id' => 'Выберите тариф']);

        $tariff = Tariff::query()->findOrFail($request->tariff_id);

        if (auth()->user()->get()[0]->balance < $tariff->price)
            throw ValidationException::withMessages(['balance' => 'на балансе не хватает денег']);

        dd($request->tariff_id);

    }

    public function getAccounts(Request $request): LengthAwarePaginator|string|null
    {
        $accounts = Account::query()
            ->with('platform', 'user', 'games', 'tariffs')
            ->where('busy', null)
            ->where('status', null)
            ->when($request->input("platform_id") || $request->input("search") || $request->input("game_id"),
                function ($query) use ($request) {
                    if ($request->input("platform_id")) {
                        $query->whereHas("platform", function ($query) use ($request) {
                            $query->where('platforms.id', '=', $request->input("platform_id"));
                        });
                    }

                    if ($request->input("game_id")) {
                        $query->whereHas("games", function ($query) use ($request) {
                            $query->where('games.id', '=', $request->input("game_id"));
                        });
                    }

                    if ($request->input("search")) {
                        $query->where(function ($query) use ($request) {
                            $query
                                ->where('title', 'like', '%' . $request->input("search") . '%')
                                ->orWhere('price', 'like', '%' . $request->input("search") . '%')
                                ->orWhereHas('platform', function ($query) use ($request) {
                                    $query->where('platforms.name', 'like', '%' . $request->input("search") . '%');
                                })
                                ->orWhereHas('user', function ($query) use ($request) {
                                    $query->where('users.name', 'like', '%' . $request->input("search") . '%');
                                })
                                ->orWhereHas('games', function ($query) use ($request) {
                                    $query->where('games.name', 'like', '%' . $request->input("search") . '%');
                                });
                        });
                    }
                })
            ->paginate(100)->setPath('/products')
            ->withQueryString()
            ->fragment('products');

        //Агрегация countSales
        $accounts->each(function ($account) {

            // Получаем количество продаж
            $userAccounts = $account->user->accounts;
            $countSales = $userAccounts->pluck('id');
            $account->user->countSales = Statistic::whereIn('account_id', $countSales)->count();

            //минимальный тарифф
            $account->minTariff = $account->tariffs->min('price');
        });

        //нерабочий поиск по кол-ву продаж пользователями
        /*$searchSales = $request->input("search");
        if ($searchSales) {
            $accounts = $accounts->filter(function ($account) use ($searchSales) {
                return $account->user && str_contains((string)$account->user->countSales, $searchSales);
            });
        }*/

        // Обработка иконки пользователя
        $accounts->transform(function ($account) {
            if ($account->user) {
                $userId = $account->user->id;
                // Получаем файлы из публичного хранилища
                $userDir = Storage::disk('public')->files('/img/public/users/' . $userId);
                // Проверяем наличие иконки
                $userIcon = count($userDir) ? '/storage/' . $userDir[0] : '/storage/img/public/users/default.webp';

                // Добавляем новое поле с иконкой
                $account->user->icon = $userIcon;
            }

            return $account;
        });

        return $accounts;
    }
}
