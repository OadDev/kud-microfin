<?php

use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\EmiCalculatorController;
use App\Http\Controllers\Admin\LoanApprovalController;
use App\Http\Controllers\Admin\NotificationBroadcastController;
use App\Http\Controllers\Admin\NotificationController as AdminNotificationController;
use App\Http\Controllers\Admin\NotificationSettingController;
use App\Http\Controllers\Admin\NotificationTemplateController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\PaymentSettingController;
use App\Http\Controllers\Admin\PaymentVerificationController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\ShopOwnerController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerAuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CustomerPanel\CartController;
use App\Http\Controllers\CustomerPanel\DocumentController as CustomerDocumentController;
use App\Http\Controllers\CustomerPanel\FavouriteController;
use App\Http\Controllers\CustomerPanel\HelplineController;
use App\Http\Controllers\CustomerPanel\HomeController as CustomerHomeController;
use App\Http\Controllers\CustomerPanel\LoanController as CustomerLoanController;
use App\Http\Controllers\CustomerPanel\OrderController as CustomerOrderController;
use App\Http\Controllers\CustomerPanel\PaymentController as CustomerPaymentController;
use App\Http\Controllers\CustomerPanel\ProductController as CustomerProductController;
use App\Http\Controllers\CustomerPanel\ProfileController as CustomerProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\InstallController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\MarketingController;
use App\Http\Controllers\PublicRegistrationController;
use App\Http\Controllers\QuickLoginController;
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
| Named "home" -- Laravel's `guest` middleware sends an already-logged-in
| user here whenever they hit a guest-only route (e.g. a customer whose
| session is still valid reopening the mobile app at /app/login). Without
| this, it falls back to '/' (the public marketing page) since neither
| 'home' nor 'dashboard' otherwise exists as a route name.
|--------------------------------------------------------------------------
*/
Route::get('/home', fn () => redirect()->to(
    auth()->check() ? (new AuthController)->homeFor(auth()->user()) : route('login')
))->name('home');

/*
|--------------------------------------------------------------------------
| Guest / public routes
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');

    // Mobile app entry point (Android/iOS WebView start URL) -- customer
    // OTP login only, see AuthController::showAppLogin().
    Route::get('/app/login', [AuthController::class, 'showAppLogin'])->name('app.login');
    Route::post('/demo-login/{role}', [AuthController::class, 'demoLogin'])->name('demo.login');

    Route::post('/customer/otp/send', [CustomerAuthController::class, 'sendOtp'])->name('customer.otp.send');
    Route::post('/customer/otp/verify', [CustomerAuthController::class, 'verifyOtp'])->name('customer.otp.verify');

    Route::get('/register/shop-owner', [PublicRegistrationController::class, 'create'])->name('shop-owner.register');
    Route::post('/register/shop-owner', [PublicRegistrationController::class, 'store'])->name('shop-owner.register.submit');

    // Quick PIN login (identifies the user via a per-device cookie set
    // during setup, so this stays inside the guest group).
    Route::post('/quick-login/verify', [QuickLoginController::class, 'verifyPin'])->name('quick-login.verify');
});

// "Forget this device" just clears a cookie -- useful both from the login
// screen (guest) and from an already-authenticated user's own profile, so
// it deliberately carries no auth-state middleware.
Route::post('/quick-login/forget', [QuickLoginController::class, 'forget'])->name('quick-login.forget');

Route::middleware('auth')->prefix('quick-login')->name('quick-login.')->group(function () {
    Route::get('/setup', [QuickLoginController::class, 'setupPrompt'])->name('setup');
    Route::post('/setup', [QuickLoginController::class, 'storePin'])->name('setup.store');
    Route::post('/skip', [QuickLoginController::class, 'skip'])->name('skip');
    Route::post('/disable', [QuickLoginController::class, 'disable'])->name('disable');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// Shared by all authenticated roles; each controller enforces its own
// ownership rules (Admin: any record, Shop Owner/Customer: only their own).
Route::middleware('auth')->group(function () {
    Route::get('/customers/{customer}', [CustomerController::class, 'show'])->name('customers.show');

    Route::get('/loans/{loan}/documents/{type}', [DocumentController::class, 'show'])
        ->whereIn('type', ['welcome_letter', 'sanction_letter', 'noc'])
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

    // Foreclose is an action reached from the customer detail page, not the
    // (Admin-only) Active Loans list, so Shop Owner keeps access to it.
    Route::post('/loans/{loan}/foreclose', [LoanController::class, 'foreclose'])->name('loans.foreclose');

    // Documents (Welcome Letter / Sanction Letter generation + signed upload)
    Route::get('/admin/documents', [DocumentController::class, 'index'])->name('admin.documents.index');
    Route::get('/shop-owner/documents', [DocumentController::class, 'index'])->name('shopowner.documents.index');
    Route::post('/loans/{loan}/documents/{type}/signed', [DocumentController::class, 'storeSigned'])
        ->whereIn('type', ['welcome_letter', 'sanction_letter', 'noc'])
        ->name('documents.signed.store');
});

/*
|--------------------------------------------------------------------------
| Admin-only: EMI/payment tracking (Active Loans list)
|--------------------------------------------------------------------------
| Shop Owner must not see loan payment progress at all, so this list --
| unlike the customer list/detail pages, which stay shared with the
| financial columns stripped -- is simply not reachable by Shop Owner.
*/
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/active-loans', [LoanController::class, 'index'])->name('admin.loans.index');
});

/*
|--------------------------------------------------------------------------
| Admin-only area
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::post('/notifications/read-all', [AdminNotificationController::class, 'readAll'])->name('notifications.read-all');
    Route::get('/notifications/{notification}/read', [AdminNotificationController::class, 'read'])->name('notifications.read');

    Route::get('/shop-owners', [ShopOwnerController::class, 'index'])->name('shop-owners.index');
    Route::post('/shop-owners', [ShopOwnerController::class, 'store'])->name('shop-owners.store');
    Route::get('/shop-owners/{shopOwner}', [ShopOwnerController::class, 'show'])->name('shop-owners.show');
    Route::post('/shop-owners/{shopOwner}/approve', [ShopOwnerController::class, 'approve'])->name('shop-owners.approve');
    Route::post('/shop-owners/{shopOwner}/reject', [ShopOwnerController::class, 'reject'])->name('shop-owners.reject');
    Route::post('/shop-owners/{shopOwner}/suspend', [ShopOwnerController::class, 'suspend'])->name('shop-owners.suspend');

    Route::get('/loan-approvals', [LoanApprovalController::class, 'index'])->name('loan-approvals.index');
    Route::get('/loan-approvals/{loan}', [LoanApprovalController::class, 'show'])->name('loan-approvals.show');
    Route::post('/loan-approvals/{loan}/approve', [LoanApprovalController::class, 'approve'])->name('loan-approvals.approve');
    Route::post('/loan-approvals/{loan}/reject', [LoanApprovalController::class, 'reject'])->name('loan-approvals.reject');

    Route::get('/emi-calculator', [EmiCalculatorController::class, 'index'])->name('emi-calculator');

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

    // Notification Manager (OneSignal push + SMTP email settings, editable
    // templates for the automated events, and a manual push composer)
    Route::prefix('notification-manager')->name('notification-manager.')->group(function () {
        Route::get('/settings', [NotificationSettingController::class, 'edit'])->name('settings');
        Route::post('/settings', [NotificationSettingController::class, 'update'])->name('settings.update');
        Route::post('/settings/test-email', [NotificationSettingController::class, 'testEmail'])->name('settings.test-email');
        Route::post('/settings/test-push', [NotificationSettingController::class, 'testPush'])->name('settings.test-push');

        Route::get('/templates', [NotificationTemplateController::class, 'index'])->name('templates');
        Route::get('/templates/{notificationTemplate}/edit', [NotificationTemplateController::class, 'edit'])->name('templates.edit');
        Route::put('/templates/{notificationTemplate}', [NotificationTemplateController::class, 'update'])->name('templates.update');

        Route::get('/send', [NotificationBroadcastController::class, 'create'])->name('send');
        Route::post('/send', [NotificationBroadcastController::class, 'store'])->name('send.store');
    });
});

/*
|--------------------------------------------------------------------------
| Customer panel
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:customer'])->prefix('customer')->name('customer.')->group(function () {
    Route::get('/home', [CustomerHomeController::class, 'index'])->name('home');
    Route::get('/loan', [CustomerLoanController::class, 'index'])->name('loan');
    Route::get('/loan/foreclose', [CustomerPaymentController::class, 'foreclose'])->name('foreclose');
    Route::post('/loan/foreclose', [CustomerPaymentController::class, 'forecloseStore'])->name('foreclose.store');
    Route::get('/pay', [CustomerPaymentController::class, 'show'])->name('pay');
    Route::post('/pay', [CustomerPaymentController::class, 'store'])->name('pay.store');
    Route::get('/documents', [CustomerDocumentController::class, 'index'])->name('documents');
    Route::get('/profile', [CustomerProfileController::class, 'index'])->name('profile');
    Route::post('/profile/photo', [CustomerProfileController::class, 'updatePhoto'])->name('profile.photo');

    // Products (browse, search/filter) + Cart + Favourites + Orders
    Route::get('/products', [CustomerProductController::class, 'index'])->name('products.index');
    Route::get('/products/{product}', [CustomerProductController::class, 'show'])->name('products.show');

    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/checkout', [CartController::class, 'checkout'])->name('cart.checkout');
    Route::post('/cart/items/{cartItem}', [CartController::class, 'updateQuantity'])->name('cart.update');
    Route::delete('/cart/items/{cartItem}', [CartController::class, 'remove'])->name('cart.remove');
    Route::post('/cart/{product}', [CartController::class, 'add'])->name('cart.add');

    Route::post('/favourites/{product}/toggle', [FavouriteController::class, 'toggle'])->name('favourites.toggle');
    Route::get('/favourites', [FavouriteController::class, 'index'])->name('favourites.index');

    Route::get('/orders', [CustomerOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [CustomerOrderController::class, 'show'])->name('orders.show');
    Route::get('/orders/{order}/pay', [CustomerProductController::class, 'pay'])->name('orders.pay');
    Route::post('/orders/{order}/verify', [CustomerProductController::class, 'verify'])->name('orders.verify');

    Route::get('/helpline', [HelplineController::class, 'index'])->name('helpline');
});
