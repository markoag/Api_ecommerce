<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\Coupon\CouponController;
use App\Http\Controllers\Admin\Discount\DiscountController;
use App\Http\Controllers\Admin\Product\AttributeProductController;
use App\Http\Controllers\Admin\Product\BrandController;
use App\Http\Controllers\Admin\Product\CategorieController;
use App\Http\Controllers\Admin\Product\ProductController;
use App\Http\Controllers\Admin\Product\ProductSpecificationsController;
use App\Http\Controllers\Admin\Product\ProductVariationsController;
use App\Http\Controllers\Admin\Product\ProductVariationsNestedController;
use App\Http\Controllers\Admin\SliderController;
use App\Http\Controllers\Ecommerce\CartController;
use App\Http\Controllers\Ecommerce\HomeController;
use App\Http\Controllers\Ecommerce\SaleController;
use App\Http\Controllers\Ecommerce\UserAddressController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::group([

    // 'middleware' => 'auth:api',
    'prefix' => 'auth'

], function ($router) {
    Route::post('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/login_ecommerce', [AuthController::class, 'login_ecommerce'])->name('login_ecommerce');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::post('/refresh', [AuthController::class, 'refresh'])->name('refresh');
    Route::post('/me', [AuthController::class, 'me'])->name('me');
    Route::post('/verified_auth', [AuthController::class, 'verified_auth'])->name('verified_auth');
    // Proceso de verificación de correo
    Route::post('/verified_email', [AuthController::class, 'verified_email'])->name('verified_email');
    Route::post('/verified_code', [AuthController::class, 'verified_code'])->name('verified_code');
    Route::post('/new_password', [AuthController::class, 'new_password'])->name('new_password');
});

Route::group([
    "middleware" => "auth:api",
    "prefix" => "admin",
], function ($router) {
    // Rutas de las categorías
    Route::get("categories/config", [CategorieController::class, "config"]);
    Route::resource("categories", CategorieController::class);
    Route::post("categories/{id}", [CategorieController::class, "update"]);

    // Rutas de las propiedades y atributos
    Route::post("properties", [AttributeProductController::class, "store_propertie"]);
    Route::put("properties/{id}", [AttributeProductController::class, "update_propertie"]);
    Route::delete("properties/{id}", [AttributeProductController::class, "destroy_propertie"]);
    Route::resource("attributes", AttributeProductController::class);

    // Rutas de los sliders
    Route::resource("sliders", SliderController::class);
    Route::post("sliders/{id}", [SliderController::class, "update"]);

    // Rutas de los productos
    Route::get("products/config", [ProductController::class, "config"]);
    Route::post("products/images", [ProductController::class, "images"]);
    Route::delete("products/images/{id}", [ProductController::class, "delete_image"]);
    Route::post("products/index", [ProductController::class, "index"]);
    Route::resource("products", ProductController::class);
    Route::post("products/{id}", [ProductController::class, "update"]);

    // Marcas
    Route::resource("brands", BrandController::class);

    // Rutas de las especificaciones y variaciones
    Route::get("variations/config", [ProductVariationsController::class, "config"]);
    Route::resource("variations", ProductVariationsController::class);
    Route::resource("nested_variations", ProductVariationsNestedController::class);
    Route::resource("specifications", ProductSpecificationsController::class);

    // Rutas de los cupones
    Route::get("coupons/config", [CouponController::class, "config"]);
    Route::resource("coupons", CouponController::class);

    // Rutas de los descuentos
    Route::post("discounts/index", [DiscountController::class, "index"]);
    Route::resource("discounts", DiscountController::class);
});

Route::group([
    "prefix" => "ecommerce",
], function ($router) {
    Route::get("home", [HomeController::class, "home"]);
    Route::get("menus", [HomeController::class, "menus"]);

    Route::get("product/{slug}", [HomeController::class, "show_product"]);

    Route::group([
        "middleware" => "auth:api",
    ], function ($router) {
        // Rutas de los carritos
        Route::post("carts/apply_coupon", [CartController::class, "apply_coupon"]);
        Route::resource("carts", CartController::class);
        
        // Rutas de las direcciones de los usuarios
        Route::get("user_address/config", [UserAddressController::class, "config"]);
        Route::resource("user_address", UserAddressController::class);

        // Rutas de las ventas
        Route::get("sale/{id}", [SaleController::class, "show"]);
        Route::post("checkout", [SaleController::class, "store"]);
        
        // Rutas de perfil de usuario
        Route::get("profile_client/me", [AuthController::class, "me"]);
        Route::get("profile_client/orders", [SaleController::class, "orders"]);
        Route::put("profile_client", [AuthController::class, "update"]);
    });
});
