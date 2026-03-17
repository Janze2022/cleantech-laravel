<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| CONTROLLERS
|--------------------------------------------------------------------------
*/

// =====================
// CUSTOMER
// =====================
use App\Http\Controllers\CustomerRegisterController;
use App\Http\Controllers\CustomerOtpController;
use App\Http\Controllers\CustomerLoginController;
use App\Http\Controllers\CustomerDashboardController;
use App\Http\Controllers\CustomerLogoutController;
use App\Http\Controllers\CustomerBookingController;
use App\Http\Controllers\CustomerForgotPasswordController;
use App\Http\Controllers\CustomerProfileController;
use App\Http\Controllers\Customer\ServicesController;
use App\Http\Controllers\CustomerReviewController;
use App\Http\Controllers\CustomerNotificationController;
use App\Http\Controllers\PublicPageController;

// =====================
// PROVIDER
// =====================
use App\Http\Controllers\ProviderRegisterController;
use App\Http\Controllers\ProviderPreRegisterController;
use App\Http\Controllers\ProviderOtpController;
use App\Http\Controllers\ProviderLoginController;
use App\Http\Controllers\ProviderDashboardController;
use App\Http\Controllers\ProviderLogoutController;
use App\Http\Controllers\ProviderProfileController;
use App\Http\Controllers\ProviderForgotPasswordController;
use App\Http\Controllers\ProviderAvailabilityController;
use App\Http\Controllers\ProviderBookingController;
use App\Http\Controllers\ProviderEarningsController;
use App\Http\Controllers\ProviderRatingsController;
use App\Http\Controllers\ProviderNotificationController;

// =====================
// ADMIN
// =====================
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminProviderController;
use App\Http\Controllers\Admin\AdminCustomerController;
use App\Http\Controllers\Admin\AdminBookingController;
use App\Http\Controllers\Admin\AdminReportController;
use App\Http\Controllers\Admin\ProfileController;

/*
|--------------------------------------------------------------------------
| HOME
|--------------------------------------------------------------------------
*/
Route::get('/', fn () => view('pages.home'))->name('home');

/*
|--------------------------------------------------------------------------
| PUBLIC STATIC PAGES
|--------------------------------------------------------------------------
*/
Route::get('/services', fn () => view('pages.services'))->name('services');
Route::get('/how-it-works', fn () => view('pages.how-it-works'))->name('how.it.works');
Route::get('/pricing', fn () => view('pages.pricing'))->name('pricing');
Route::get('/blog', fn () => view('pages.blog'))->name('blog');
Route::get('/contact', fn () => view('pages.contact'))->name('contact');
Route::get('/faq', fn () => view('pages.faq'))->name('faq');
Route::get('/about', fn () => view('pages.about'))->name('about');

/*
|--------------------------------------------------------------------------
| PUBLIC PROVIDER FILE ROUTES
|--------------------------------------------------------------------------
| Keep these OUTSIDE auth middleware groups so images/documents can open
|--------------------------------------------------------------------------
*/
Route::get('/provider-image/{filename}', [ProviderProfileController::class, 'publicImage'])
    ->where('filename', '.*')
    ->name('provider.image.public');

Route::get('/provider-document/{filename}', [ProviderProfileController::class, 'publicDocument'])
    ->where('filename', '.*')
    ->name('provider.document.public');

Route::get('/customer-image/{filename}', [CustomerProfileController::class, 'publicImage'])
    ->where('filename', '.*')
    ->name('customer.image.public');

/*
|--------------------------------------------------------------------------
| ADMIN — GUEST
|--------------------------------------------------------------------------
*/
Route::prefix('admin')
    ->name('admin.')
    ->middleware('admin.guest')
    ->group(function () {

        Route::get('/login', [AdminAuthController::class, 'showLogin'])
            ->name('login');

        Route::post('/login', [AdminAuthController::class, 'login'])
            ->name('login.submit');
    });

/*
|/*
|--------------------------------------------------------------------------
| ADMIN — AUTHENTICATED
|--------------------------------------------------------------------------
*/
Route::prefix('admin')
    ->name('admin.')
    ->middleware('admin.session')
    ->group(function () {

        Route::get('/dashboard', [AdminDashboardController::class, 'index'])
            ->name('dashboard');

        Route::post('/logout', [AdminAuthController::class, 'logout'])
            ->name('logout');

        // =====================
        // CUSTOMERS
        // =====================
        Route::get('/customers', [AdminCustomerController::class, 'index'])
            ->name('customers');

        Route::get('/customers/{id}', [AdminCustomerController::class, 'show'])
            ->name('customers.show');

        Route::put('/customers/{id}', [AdminCustomerController::class, 'update'])
            ->name('customers.update');

        Route::delete('/customers/{id}', [AdminCustomerController::class, 'destroy'])
            ->name('customers.delete');

        // =====================
        // PROVIDERS
        // =====================
        Route::get('/providers', [AdminProviderController::class, 'index'])
            ->name('providers');

        Route::get('/providers/{id}/document', [AdminProviderController::class, 'document'])
            ->name('providers.document');

        Route::post('/providers/{id}/approve', [AdminProviderController::class, 'approve'])
            ->name('providers.approve');

        Route::post('/providers/{id}/reject', [AdminProviderController::class, 'reject'])
            ->name('providers.reject');

        Route::post('/providers/{id}/suspend', [AdminProviderController::class, 'suspend'])
            ->name('providers.suspend');

        Route::post('/providers/{id}/unapprove', [AdminProviderController::class, 'unapprove'])
            ->name('providers.unapprove');

        Route::delete('/providers/{id}', [AdminProviderController::class, 'destroy'])
            ->name('providers.delete');

        // =====================
        // BOOKINGS
        // =====================
        Route::get('/bookings', [AdminBookingController::class, 'index'])
            ->name('bookings');

        Route::post('/bookings', [AdminBookingController::class, 'store'])
            ->name('bookings.store');

        Route::put('/bookings/{id}', [AdminBookingController::class, 'update'])
            ->name('bookings.update');

        Route::delete('/bookings/{id}', [AdminBookingController::class, 'destroy'])
            ->name('bookings.destroy');

        Route::post('/bookings/{id}/status', [AdminBookingController::class, 'updateStatus'])
            ->name('bookings.status');

        // =====================
        // PROFILE
        // =====================
        Route::get('/profile', [ProfileController::class, 'index'])
            ->name('profile');

        Route::put('/profile/update', [ProfileController::class, 'update'])
            ->name('profile.update');

        Route::post('/profile/password', [ProfileController::class, 'updatePassword'])
            ->name('profile.password');

        // =====================
        // REPORTS
        // =====================
        Route::get('/reports', [AdminReportController::class, 'index'])
            ->name('reports');

        Route::get('/reports/export', [AdminReportController::class, 'export'])
            ->name('reports.export');
    });
/*
|--------------------------------------------------------------------------
| CUSTOMER — GUEST
|--------------------------------------------------------------------------
*/
Route::prefix('customer')
    ->name('customer.')
    ->middleware('customer.guest')
    ->group(function () {

        Route::get('/register', [CustomerRegisterController::class, 'show'])
            ->name('register');

        Route::post('/register', [CustomerRegisterController::class, 'store'])
            ->name('register.submit');

        Route::get('/verify', [CustomerOtpController::class, 'show'])
            ->name('verify');

        Route::post('/verify', [CustomerOtpController::class, 'verify'])
            ->name('verify.submit');

        Route::post('/verify/resend', [CustomerOtpController::class, 'resend'])
            ->name('otp.resend');

        Route::get('/verified', fn () => view('customer.verified'))
            ->name('verified');

        Route::get('/login', [CustomerLoginController::class, 'show'])
            ->name('login');

        Route::post('/login', [CustomerLoginController::class, 'login'])
            ->name('login.submit');

        Route::get('/forgot-password', [CustomerForgotPasswordController::class, 'show'])
            ->name('forgot');

        Route::post('/forgot-password', [CustomerForgotPasswordController::class, 'sendOtp'])
            ->name('forgot.submit');

        Route::get('/forgot-password/verify', [CustomerForgotPasswordController::class, 'showVerifyOtp'])
            ->name('forgot.verify');

        Route::post('/forgot-password/verify', [CustomerForgotPasswordController::class, 'verifyOtp'])
            ->name('forgot.verify.submit');

        Route::get('/forgot-password/reset', [CustomerForgotPasswordController::class, 'showResetForm'])
            ->name('forgot.reset');

        Route::post('/forgot-password/reset', [CustomerForgotPasswordController::class, 'resetPassword'])
            ->name('forgot.reset.submit');

        Route::get('/forgot-password/success', [CustomerForgotPasswordController::class, 'success'])
            ->name('forgot.success');
    });

/*
|--------------------------------------------------------------------------
| CUSTOMER — AUTHENTICATED
|--------------------------------------------------------------------------
*/
Route::prefix('customer')
    ->name('customer.')
    ->middleware('customer.session')
    ->group(function () {

        Route::get('/dashboard', [CustomerDashboardController::class, 'dashboard'])
            ->name('dashboard');

        Route::get('/bookings/history', [CustomerDashboardController::class, 'bookingsHistory'])
            ->name('bookings.history');

        Route::get('/services', [ServicesController::class, 'index'])
            ->name('services');

        Route::get('/providers/{provider}', [ServicesController::class, 'provider'])
            ->name('providers.show');

        // BOOKING
        Route::get('/book/{provider}', [CustomerBookingController::class, 'create'])
            ->name('book.service');

        Route::post('/book', [CustomerBookingController::class, 'store'])
            ->name('book.submit');

        Route::get('/book/confirmed/{reference}', [CustomerBookingController::class, 'confirmed'])
            ->name('book.confirmed');

        // BOOKINGS LIST
        Route::get('/bookings', [CustomerBookingController::class, 'index'])
            ->name('bookings');

        // BOOKING DETAILS
        Route::get('/bookings/{reference}', [CustomerBookingController::class, 'show'])
            ->name('bookings.show');

        // REVIEWS
        Route::get('/reviews', [CustomerReviewController::class, 'index'])
            ->name('reviews');

        Route::post('/reviews', [CustomerReviewController::class, 'store'])
            ->name('reviews.store');

        // NOTIFICATIONS
        Route::get('/notifications/open/{id}', [CustomerNotificationController::class, 'open'])
            ->name('notifications.open');

        Route::post('/notifications/read-all', [CustomerNotificationController::class, 'readAll'])
            ->name('notifications.readAll');

        Route::post('/notifications/clear', [CustomerNotificationController::class, 'clear'])
            ->name('notifications.clear');

        // PROFILE
        Route::get('/profile', [CustomerProfileController::class, 'show'])
            ->name('profile');

        Route::match(['post', 'put'], '/profile', [CustomerProfileController::class, 'update'])
            ->name('profile.update');

        Route::put('/profile/image', [CustomerProfileController::class, 'updateImage'])
            ->name('profile.image');

        Route::post('/profile/password', [CustomerProfileController::class, 'changePassword'])
            ->name('profile.password');

        // LOGOUT
        Route::post('/logout', [CustomerLogoutController::class, 'logout'])
            ->name('logout');
    });

/*
|--------------------------------------------------------------------------
| PROVIDER — GUEST
|--------------------------------------------------------------------------
*/
Route::prefix('provider')
    ->name('provider.')
    ->middleware('provider.guest')
    ->group(function () {

        // TERMS
        Route::get('/pre-register/terms', [ProviderPreRegisterController::class, 'terms'])
            ->name('pre_register.terms');

        Route::post('/pre-register/terms', [ProviderPreRegisterController::class, 'acceptTerms'])
            ->name('pre_register.terms.submit');

        // PRE-REGISTER
        Route::get('/pre-register', [ProviderPreRegisterController::class, 'show'])
            ->name('pre_register');

        Route::post('/pre-register', [ProviderPreRegisterController::class, 'store'])
            ->name('pre_register.submit');

        // REGISTER
        Route::get('/register', [ProviderRegisterController::class, 'show'])
            ->name('register');

        Route::post('/register', [ProviderRegisterController::class, 'store'])
            ->name('register.submit');

        // VERIFY
        Route::get('/verify', [ProviderOtpController::class, 'show'])
            ->name('verify');

        Route::post('/verify', [ProviderOtpController::class, 'verify'])
            ->name('verify.submit');

        Route::post('/verify/resend', [ProviderOtpController::class, 'resend'])
            ->name('otp.resend');

        // LOGIN
        Route::get('/login', [ProviderLoginController::class, 'show'])
            ->name('login');

        Route::post('/login', [ProviderLoginController::class, 'login'])
            ->name('login.submit');

        // FORGOT PASSWORD
        Route::get('/forgot-password', [ProviderForgotPasswordController::class, 'show'])
            ->name('forgot');

        Route::post('/forgot-password', [ProviderForgotPasswordController::class, 'sendOtp'])
            ->name('forgot.submit');

        // VERIFY OTP
        Route::get('/forgot-password/verify', [ProviderForgotPasswordController::class, 'verifyForm'])
            ->name('reset.verify');

        Route::post('/forgot-password/verify', [ProviderForgotPasswordController::class, 'verifyOtp'])
            ->name('reset.verify.submit');

        // RESET PASSWORD
        Route::get('/forgot-password/reset', [ProviderForgotPasswordController::class, 'resetForm'])
            ->name('reset.password');

        Route::post('/forgot-password/reset', [ProviderForgotPasswordController::class, 'reset'])
            ->name('reset.password.submit');

        // SUCCESS
        Route::get('/forgot-password/success', [ProviderForgotPasswordController::class, 'success'])
            ->name('reset.success');
    });

/*
|--------------------------------------------------------------------------
| PROVIDER — AUTHENTICATED
|--------------------------------------------------------------------------
*/
Route::prefix('provider')
    ->name('provider.')
    ->middleware('provider.session')
    ->group(function () {

        Route::get('/pending', [ProviderDashboardController::class, 'pending'])
            ->name('pending');

        Route::get('/dashboard', [ProviderDashboardController::class, 'index'])
            ->name('dashboard');

        // AVAILABILITY
        Route::get('/availability', [ProviderAvailabilityController::class, 'index'])
            ->name('availability');

        Route::post('/availability', [ProviderAvailabilityController::class, 'store'])
            ->name('availability.store');

        Route::post('/availability/{id}/toggle', [ProviderAvailabilityController::class, 'toggle'])
            ->name('availability.toggle');

        Route::delete('/availability/{id}', [ProviderAvailabilityController::class, 'destroy'])
            ->name('availability.destroy');

        // BOOKINGS
        Route::get('/bookings', [ProviderBookingController::class, 'index'])
            ->name('bookings');

        Route::post('/bookings/{reference}/status', [ProviderBookingController::class, 'updateStatus'])
            ->name('bookings.status');

        Route::get('/bookings/history', [ProviderBookingController::class, 'history'])
            ->name('bookings.history');

        Route::get('/bookings/{reference_code}', [ProviderBookingController::class, 'show'])
            ->name('bookings.show');

        // NOTIFICATIONS
        Route::post('/notifications/read-all', [ProviderNotificationController::class, 'readAll'])
            ->name('notifications.readAll');

        Route::post('/notifications/clear', [ProviderNotificationController::class, 'clear'])
            ->name('notifications.clear');

        Route::get('/notifications/{id}/open', [ProviderNotificationController::class, 'open'])
            ->name('notifications.open');

        // ANALYTICS / EARNINGS / RATINGS
        Route::get('/analytics', [ProviderBookingController::class, 'analytics'])
            ->name('analytics');

        Route::get('/earnings', [ProviderEarningsController::class, 'index'])
            ->name('earnings');

        Route::get('/ratings', [ProviderRatingsController::class, 'index'])
            ->name('ratings');

        // PROFILE
        Route::get('/profile', [ProviderProfileController::class, 'show'])
            ->name('profile');

        Route::match(['post', 'put'], '/profile', [ProviderProfileController::class, 'update'])
            ->name('profile.update');

        Route::put('/profile/image', [ProviderProfileController::class, 'updateImage'])
            ->name('profile.image.update');

        Route::post('/profile/password', [ProviderProfileController::class, 'changePassword'])
            ->name('password.update');

        Route::get('/profile-image/{filename}', [ProviderProfileController::class, 'image'])
            ->where('filename', '.*')
            ->name('profile.image');

        // LOGOUT
        Route::post('/logout', [ProviderLogoutController::class, 'logout'])
            ->name('logout');
    });
