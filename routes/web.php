<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WishlistController;
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

Route::post('/cart/apply-coupon', [CartController::class, 'applyCouponCode'])->name('cart.coupon.apply');
Route::delete('/cart/remove-coupon', [CartController::class, 'removeCouponCode'])->name('cart.coupon.remove');


Route::post('/wishlist/add', [WishlistController::class, 'addToWishlist'])->name('wishlist.add');
Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
Route::delete('/wishlist/item/remove/{rowId}', [WishlistController::class, 'removeItem'])->name('wishlist.item.remove');
Route::delete('/wishlist/clear', [WishlistController::class, 'emptyWishlist'])->name('wishlist.items.clear');
Route::post('/wishlist/move-to-cart/{rowId}', [WishlistController::class, 'moveToCart'])->name('wishlist.move.to.cart');


Route::get('/checkout', [CartController::class, 'checkout'])->name('cart.checkout');
Route::post('/place-an-order', [CartController::class, 'placeAnOrder'])->name('cart.place.an.order');
Route::get('/order-confirmation', [CartController::class, 'orderConfirmation'])->name('cart.order.confirmation');


Route::get('/contact-us', [HomeController::class, 'contact'])->name('home.contact');
Route::post('/contact/store', [HomeController::class, 'contactStore'])->name('home.contact.store');



//SEARCH
Route::get('/search', [HomeController::class, 'search'])->name('home.search');













Route::middleware(['auth'])->group(function(){
    Route::get('/account-dashboard', [UserController::class, 'index'])->name('user.index');
    Route::get('/account-orders', [UserController::class, 'orders'])->name('user.orders');
    Route::get('/account-order/{order_id}/details', [UserController::class, 'orderDetails'])->name('user.order.details');
    Route::put('/account-order/cancel-order', [UserController::class, 'cancelOrder'])->name('user.order.cancel');

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



    //COUPONS
    Route::get('/admin/coupons', [AdminController::class, 'coupons'])->name('admin.coupons');
    Route::get('/admin/coupons/add', [AdminController::class, 'couponAdd'])->name('admin.coupons.add');
    Route::post('/admin/coupons/store', [AdminController::class, 'couponStore'])->name('admin.coupons.store');
    Route::get('/admin/coupons/{id}/edit', [AdminController::class, 'couponEdit'])->name('admin.coupons.edit');
    Route::put('/admin/coupons/update', [AdminController::class, 'couponUpdate'])->name('admin.coupons.update');
    Route::delete('/admin/coupon/{id}/delete', [AdminController::class, 'couponDelete'])->name('admin.coupon.delete');


    //ORDERS
    Route::get('/admin/orders', [AdminController::class, 'orders'])->name('admin.orders');
    Route::get('/admin/order/{order_id}/details', [AdminController::class, 'orderDetails'])->name('admin.order.details');
    Route::put('/admin/order/update-status', [AdminController::class, 'updateOrderStatus'])->name('admin.order.status.update');

    

    ///SLIDES
    Route::get('/admin/slides', [AdminController::class, 'slides'])->name('admin.slides');
    Route::get('/admin/slide/add', [AdminController::class, 'slideAdd'])->name('admin.slide.add');
    Route::post('/admin/slide/store', [AdminController::class, 'slideStore'])->name('admin.slide.store');
    Route::get('/admin/slide/{id}/edit', [AdminController::class, 'slideEdit'])->name('admin.slide.edit');
    Route::put('/admin/slide/update', [AdminController::class, 'slideUpdate'])->name('admin.slide.update');



    //CONTACT
    Route::get('/admin/contact', [AdminController::class, 'contacts'])->name('admin.contacts');
    Route::delete('/admin/contact/{id}/delete', [AdminController::class, 'contactDelete'])->name('admin.contact.delete');



    //SEARCH
    Route::get('/admin/search', [AdminController::class, 'search'])->name('admin.search');







});