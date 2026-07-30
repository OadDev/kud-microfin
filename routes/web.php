<?php

use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\PaymentSettingController;
use App\Http\Controllers\Admin\PaymentVerificationController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\ShopOwnerController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerAuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CustomerPanel\DocumentController as CustomerDocumentController;
use App\Http\Controllers\CustomerPanel\HelplineController;
use App\Http\Controllers\CustomerPanel\HomeController as CustomerHomeController;
use App\Http\Controllers\CustomerPanel\LoanController as CustomerLoanController;
use App\Http\Controllers\CustomerPanel\OrderController as CustomerOrderController;
use App\Http\Controllers\CustomerPanel\PaymentController as CustomerPaymentController;
use App\Http\Controllers\CustomerPanel\ProductController as CustomerProductController;
use App\Http\Controllers\CustomerPanel\ProfileController as CustomerProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\EmiController;
use App\Http\Controllers\InstallController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\MarketingController;
use App\Http\Controllers\PublicRegistrationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Setup wizard (first-run only — see EnsureAppIsInstalled middleware)
|--------------------------------------------------------------------------
*/
Route::get('/install', [InstallController::class, 'show'])->name('install.show');
Route::post('/install', [InstallController::class, 'store'])->name('install.store');

/*
|--------------------------------------------------------------------------
| Public marketing site (the actual "/" homepage — not the login screen)
|--------------------------------------------------------------------------
*/
Route::get('/', [MarketingController::class, 'index'])->name('marketing.home');

/*
|--------------------------------------------------------------------------
| Guest / public routes
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
    Route::post('/demo-login/{role}', [AuthController::class, 'demoLogin'])->name('demo.login');

    Route::post('/customer/otp/send', [CustomerAuthController::class, 'sendOtp'])->name('customer.otp.send');
    Route::post('/customer/otp/verify', [CustomerAuthController::class, 'verifyOtp'])->name('customer.otp.verify');

    Route::get('/register/shop-owner', [PublicRegistrationController::class, 'create'])->name('shop-owner.register');
    Route::post('/register/shop-owner', [PublicRegistrationController::class, 'store'])->name('shop-owner.register.submit');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// Shared by all authenticated roles; each controller enforces its own
// ownership rules (Admin: any record, Shop Owner/Customer: only their own).
Route::middleware('auth')->group(function () {
    Route::get('/customers/{customer}', [CustomerController::class, 'show'])->name('customers.show');

    Route::get('/loans/{loan}/documents/{type}', [DocumentController::class, 'show'])
        ->whereIn('type', ['welcome_letter', 'sanction_letter'])
        ->name('documents.show');
});

/*
|--------------------------------------------------------------------------
| Admin + Shop Owner shared area
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin,shop_owner'])->group(function () {

    Route::get('/admin/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/shop-owner/dashboard', [DashboardController::class, 'index'])->name('shopowner.dashboard');

    // Customers (list scoped by role inside the controller; Admin sees all, Shop Owner sees own)
    Route::get('/admin/customers', [CustomerController::class, 'index'])->name('admin.customers.index');
    Route::get('/shop-owner/customers', [CustomerController::class, 'index'])->name('shopowner.customers.index');
    Route::get('/admin/customers/create', [CustomerController::class, 'create'])->name('admin.customers.create');
    Route::get('/shop-owner/customers/create', [CustomerController::class, 'create'])->name('shopowner.customers.create');
    Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');

    // Active loans
    Route::get('/admin/active-loans', [LoanController::class, 'index'])->name('admin.loans.index');
    Route::get('/shop-owner/active-loans', [LoanController::class, 'index'])->name('shopowner.loans.index');

    // Shop Owner's own EMI list (all EMIs across their customers)
    Route::get('/shop-owner/emi-list', [EmiController::class, 'index'])->name('shopowner.emis.index');

    // Documents (Welcome Letter / Sanction Letter generation + signed upload)
    Route::get('/admin/documents', [DocumentController::class, 'index'])->name('admin.documents.index');
    Route::get('/shop-owner/documents', [DocumentController::class, 'index'])->name('shopowner.documents.index');
    Route::post('/loans/{loan}/documents/{type}/signed', [DocumentController::class, 'storeSigned'])
        ->whereIn('type', ['welcome_letter', 'sanction_letter'])
        ->name('documents.signed.store');
});

/*
|--------------------------------------------------------------------------
| Admin-only area
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/shop-owners', [ShopOwnerController::class, 'index'])->name('shop-owners.index');
    Route::post('/shop-owners', [ShopOwnerController::class, 'store'])->name('shop-owners.store');
    Route::get('/shop-owners/{shopOwner}', [ShopOwnerController::class, 'show'])->name('shop-owners.show');
    Route::post('/shop-owners/{shopOwner}/approve', [ShopOwnerController::class, 'approve'])->name('shop-owners.approve');
    Route::post('/shop-owners/{shopOwner}/reject', [ShopOwnerController::class, 'reject'])->name('shop-owners.reject');
    Route::post('/shop-owners/{shopOwner}/suspend', [ShopOwnerController::class, 'suspend'])->name('shop-owners.suspend');

    Route::get('/payment-verification', [PaymentVerificationController::class, 'index'])->name('payment-verification.index');
    Route::get('/payment-verification/{paymentSubmission}', [PaymentVerificationController::class, 'show'])->name('payment-verification.show');
    Route::post('/payment-verification/{paymentSubmission}/approve', [PaymentVerificationController::class, 'approve'])->name('payment-verification.approve');
    Route::post('/payment-verification/{paymentSubmission}/reject', [PaymentVerificationController::class, 'reject'])->name('payment-verification.reject');

    Route::get('/payment-settings', [PaymentSettingController::class, 'edit'])->name('payment-settings.edit');
    Route::post('/payment-settings', [PaymentSettingController::class, 'update'])->name('payment-settings.update');

    // Home banners (customer home page carousel)
    Route::get('/banners', [BannerController::class, 'index'])->name('banners.index');
    Route::post('/banners', [BannerController::class, 'store'])->name('banners.store');
    Route::post('/banners/{banner}', [BannerController::class, 'update'])->name('banners.update');
    Route::delete('/banners/{banner}', [BannerController::class, 'destroy'])->name('banners.destroy');

    // Product catalog
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::post('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

    Route::get('/products', [AdminProductController::class, 'index'])->name('products.index');
    Route::post('/products', [AdminProductController::class, 'store'])->name('products.store');
    Route::post('/products/{product}', [AdminProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{product}', [AdminProductController::class, 'destroy'])->name('products.destroy');

    // Orders (COD / Razorpay purchases placed by customers)
    Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.status');
    Route::post('/orders/{order}/mark-cod-paid', [AdminOrderController::class, 'markCodPaid'])->name('orders.mark-cod-paid');
});

/*
|--------------------------------------------------------------------------
| Customer panel
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:customer'])->prefix('customer')->name('customer.')->group(function () {
    Route::get('/home', [CustomerHomeController::class, 'index'])->name('home');
    Route::get('/loan', [CustomerLoanController::class, 'index'])->name('loan');
    Route::get('/pay', [CustomerPaymentController::class, 'show'])->name('pay');
    Route::post('/pay', [CustomerPaymentController::class, 'store'])->name('pay.store');
    Route::get('/documents', [CustomerDocumentController::class, 'index'])->name('documents');
    Route::get('/profile', [CustomerProfileController::class, 'index'])->name('profile');

    // Products / Buy Now / Orders
    Route::get('/products', [CustomerProductController::class, 'index'])->name('products.index');
    Route::get('/products/{product}', [CustomerProductController::class, 'show'])->name('products.show');
    Route::post('/products/{product}/buy-now', [CustomerProductController::class, 'buyNow'])->name('products.buy-now');

    Route::get('/orders', [CustomerOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [CustomerOrderController::class, 'show'])->name('orders.show');
    Route::get('/orders/{order}/pay', [CustomerProductController::class, 'pay'])->name('orders.pay');
    Route::post('/orders/{order}/verify', [CustomerProductController::class, 'verify'])->name('orders.verify');

    Route::get('/helpline', [HelplineController::class, 'index'])->name('helpline');
});
