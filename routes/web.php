<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MarketplaceAccountController;
use App\Http\Controllers\MarketplaceMappingController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);

    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store']);
});

Route::middleware('auth')->group(function (): void {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/marketplace-accounts', [MarketplaceAccountController::class, 'index'])
        ->name('marketplace-accounts.index');
    Route::get('/marketplace-accounts/create', [MarketplaceAccountController::class, 'create'])
        ->name('marketplace-accounts.create');
    Route::post('/marketplace-accounts', [MarketplaceAccountController::class, 'store'])
        ->name('marketplace-accounts.store');
    Route::get('/products', [ProductController::class, 'index'])
        ->name('products.index');
    Route::get('/products/create', [ProductController::class, 'create'])
        ->name('products.create');
    Route::post('/products', [ProductController::class, 'store'])
        ->name('products.store');
    Route::get('/products/{product}/edit', [ProductController::class, 'edit'])
        ->name('products.edit');
    Route::put('/products/{product}', [ProductController::class, 'update'])
        ->name('products.update');
    Route::redirect('/marketplace-mappings', '/marketplace-mappings/categories')
        ->name('marketplace-mappings.index');
    Route::get('/marketplace-mappings/categories', [MarketplaceMappingController::class, 'categories'])
        ->name('marketplace-mappings.categories');
    Route::post('/marketplace-mappings/categories', [MarketplaceMappingController::class, 'storeCategory'])
        ->name('marketplace-mappings.categories.store');
    Route::put('/marketplace-mappings/categories/{mapping}', [MarketplaceMappingController::class, 'updateCategory'])
        ->name('marketplace-mappings.categories.update');
    Route::get('/marketplace-mappings/brands', [MarketplaceMappingController::class, 'brands'])
        ->name('marketplace-mappings.brands');
    Route::post('/marketplace-mappings/brands', [MarketplaceMappingController::class, 'storeBrand'])
        ->name('marketplace-mappings.brands.store');
    Route::put('/marketplace-mappings/brands/{mapping}', [MarketplaceMappingController::class, 'updateBrand'])
        ->name('marketplace-mappings.brands.update');
    Route::get('/marketplace-mappings/attributes', [MarketplaceMappingController::class, 'attributes'])
        ->name('marketplace-mappings.attributes');
    Route::post('/marketplace-mappings/attributes', [MarketplaceMappingController::class, 'storeAttribute'])
        ->name('marketplace-mappings.attributes.store');
    Route::put('/marketplace-mappings/attributes/{mapping}', [MarketplaceMappingController::class, 'updateAttribute'])
        ->name('marketplace-mappings.attributes.update');
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});
