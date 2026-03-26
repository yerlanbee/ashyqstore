<?php

declare(strict_types=1);

use App\Orchid\Screens\Category\CategoryEditScreen;
use App\Orchid\Screens\Category\CategoryListScreen;
use App\Orchid\Screens\Fridge\FridgeEditScreen;
use App\Orchid\Screens\Fridge\FridgeListScreen;
use App\Orchid\Screens\PlatformScreen;
use App\Orchid\Screens\Products\ProductEditScreen;
use App\Orchid\Screens\Products\ProductListScreen;
use App\Orchid\Screens\User\UserEditScreen;
use App\Orchid\Screens\User\UserListScreen;
use App\Orchid\Screens\User\UserProfileScreen;
use Illuminate\Support\Facades\Route;
use Tabuna\Breadcrumbs\Trail;

/*
|--------------------------------------------------------------------------
| Dashboard Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the need "dashboard" middleware group. Now create something great!
|
*/

// Main
Route::screen('/main', PlatformScreen::class)
    ->name('platform.main');

// Platform > Profile
Route::screen('profile', UserProfileScreen::class)
    ->name('platform.profile')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push(__('Profile'), route('platform.profile')));

// Platform > System > Users > User
Route::screen('users/{user}/edit', UserEditScreen::class)
    ->name('platform.systems.users.edit')
    ->breadcrumbs(fn (Trail $trail, $user) => $trail
        ->parent('platform.systems.users')
        ->push($user->name, route('platform.systems.users.edit', $user)));

// Platform > System > Users > Create
Route::screen('users/create', UserEditScreen::class)
    ->name('platform.systems.users.create')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.systems.users')
        ->push(__('Create'), route('platform.systems.users.create')));

// Platform > System > Users
Route::screen('users', UserListScreen::class)
    ->name('platform.systems.users')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push(__('Users'), route('platform.systems.users')));

// Platform > System > Fridges
Route::screen('fridges', FridgeListScreen::class)
    ->name('platform.systems.fridges')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push(__('Fridges'), route('platform.systems.fridges')));

// Platform > System > Fridges > Create
Route::screen('fridges/create', FridgeEditScreen::class)
    ->name('platform.systems.fridges.create')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.systems.fridges')
        ->push(__('Fridge-Create'), route('platform.systems.fridges.create')));


// Platform > System > Products
Route::screen('products', ProductListScreen::class)
    ->name('platform.systems.products')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push(__('Products'), route('platform.systems.products')));

// Platform > System > Products > Create
Route::screen('products/create', ProductEditScreen::class)
    ->name('platform.systems.products.create')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.systems.products')
        ->push(__('Product-Create'), route('platform.systems.products.create')));

// Platform > System > Products > Edit
Route::screen('products/{product}/edit', ProductEditScreen::class)
    ->name('platform.systems.products.edit')
    ->breadcrumbs(fn (Trail $trail, $product) => $trail
        ->parent('platform.systems.products')
        ->push($product->id, route('platform.systems.products.edit', $product)));

// Platform > System > Categories
Route::screen('categories', CategoryListScreen::class)
    ->name('platform.systems.categories')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push(__('Categories'), route('platform.systems.categories')));

// Platform > System > Categories > Create
Route::screen('categories/create', CategoryEditScreen::class)
    ->name('platform.systems.categories.create')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.systems.categories')
        ->push(__('Category-Create'), route('platform.systems.categories.create')));

// Platform > System > Categories > Edit
Route::screen('categories/{category}/edit', CategoryEditScreen::class)
    ->name('platform.systems.categories.edit')
    ->breadcrumbs(fn (Trail $trail, $category) => $trail
        ->parent('platform.systems.categories')
        ->push($category->id, route('platform.systems.categories.edit', $category)));
