<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\AuthAdmin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;


Auth::routes();

Route::get('/', [HomeController::class, 'index'])->name('home.index');

Route::get('/shop', [ShopController::class, 'index'])->name('shop.index');
Route::get('/shop/{product_slug}', [ShopController::class, 'productDetails'])->name('shop.product.details');

Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add', [CartController::class, 'addToCart'])->name('cart.add');

Route::put('/cart/increase-quantity/{rowId}', [CartController::class, 'increaseCartQuantity'])->name('cart.qty.increase');
Route::put('/cart/decrease-quantity/{rowId}', [CartController::class, 'decreaseCartQuantity'])->name('cart.qty.decrease');

Route::delete('/cart/remove/{rowId}', [CartController::class, 'removeItem'])->name('cart.remove.item');
Route::delete('/cart/clear', [CartController::class, 'emptyCart'])->name('cart.empty');


Route::middleware(['auth'])->group(function(){
    Route::get('/account-dashboard', [UserController::class, 'index'])->name('user.index');

});




///ADMIN ROUTE GROUP
Route::middleware(['auth', AuthAdmin::class])->group(function(){
    Route::get('/admin', [AdminController::class, 'index'])->name('admin.index');

    
    //BRANDS
    Route::get('/admin/brands', [AdminController::class, 'brands'])->name('admin.brands');
    Route::get('/admin/brand/add', [AdminController::class, 'addBrand'])->name('admin.brand.add');
    Route::post('/admin/brand/store', [AdminController::class, 'brandStore'])->name('admin.brand.store');

    Route::get('/admin/brand/edit/{id}', [AdminController::class, 'brandEdit'])->name('admin.brand.edit');
    Route::put('/admin/brand/update/', [AdminController::class, 'brandUpdate'])->name('admin.brand.update');
    Route::delete('/admin/brand/delete/{id}', [AdminController::class, 'brandDelete'])->name('admin.brand.delete');




    //CATEGORIES
    Route::get('/admin/categories', [AdminController::class, 'categories'])->name('admin.categories');
    Route::get('/admin/category/add', [AdminController::class, 'addCategory'])->name('admin.category.add');
    Route::post('/admin/category/store', [AdminController::class, 'categoryStore'])->name('admin.category.store');
    Route::get('/admin/category/edit/{id}', [AdminController::class, 'categoryEdit'])->name('admin.category.edit');
    Route::put('/admin/category/update', [AdminController::class, 'categoryUpdate'])->name('admin.category.update');
    Route::delete('/admin/category/delete/{id}', [AdminController::class, 'categoryDelete'])->name('admin.category.delete');



    //PRODUCTS
    Route::get('/admin/products', [AdminController::class, 'products'])->name('admin.products');
    Route::get('/admin/product/add', [AdminController::class, 'addProduct'])->name('admin.product.add');
    Route::post('/admin/product/store', [AdminController::class, 'storeProduct'])->name('admin.product.store');
    Route::get('/admin/product/edit/{id}', [AdminController::class, 'productEdit'])->name('admin.product.edit');
    Route::put('/admin/product/update', [AdminController::class, 'productUpdate'])->name('admin.product.update');
    Route::delete('/admin/product/delete/{id}', [AdminController::class, 'productDelete'])->name('admin.product.delete');


    







});