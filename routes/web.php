<?php

use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\ContentBookingController;
use App\Http\Controllers\CustomerAuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DjController;
use App\Http\Controllers\EmailTrackingController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\GoogleCalendarOAuthController;
use App\Http\Controllers\GuestListCheckInController;
use App\Http\Controllers\GuestListController;
use App\Http\Controllers\GuestListInviteController;
use App\Http\Controllers\GuestListRegisterController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LeadCaptureController;
use App\Http\Controllers\MercadoPagoOAuthController;
use App\Http\Controllers\MercadoPagoWebhookController;
use App\Http\Controllers\PortfolioController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\StripeWebhookController;
use App\Http\Controllers\TicketAttendeeController;
use App\Http\Controllers\TicketCheckInController;
use App\Http\Controllers\TicketCheckoutController;
use App\Http\Controllers\TrascendentalController;
use App\Http\Controllers\TrascendentalLeadController;
use App\Http\Controllers\VideoController;
use Illuminate\Support\Facades\Route;

if (config('trascendental.enabled_as_primary')) {
    Route::get('/', [TrascendentalController::class, 'home'])->name('home');
    Route::get('/servicios', [TrascendentalController::class, 'services'])->name('trascendental.services');
    Route::get('/casos', [TrascendentalController::class, 'cases'])->name('trascendental.cases');
    Route::get('/eventos', [TrascendentalController::class, 'events'])->name('trascendental.events');
    Route::get('/tours-routing', [TrascendentalController::class, 'tours'])->name('trascendental.tours');
    Route::get('/sobre-trascendental', [TrascendentalController::class, 'about'])->name('trascendental.about');
    Route::get('/contacto', [TrascendentalController::class, 'contact'])->name('trascendental.contact');
} else {
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::prefix('trascendental')->name('trascendental.')->group(function (): void {
        Route::get('/', [TrascendentalController::class, 'home'])->name('home');
        Route::get('/servicios', [TrascendentalController::class, 'services'])->name('services');
        Route::get('/casos', [TrascendentalController::class, 'cases'])->name('cases');
        Route::get('/eventos', [TrascendentalController::class, 'events'])->name('events');
        Route::get('/tours-routing', [TrascendentalController::class, 'tours'])->name('tours');
        Route::get('/sobre-trascendental', [TrascendentalController::class, 'about'])->name('about');
        Route::get('/contacto', [TrascendentalController::class, 'contact'])->name('contact');
    });
}

Route::view('/terminos-y-condiciones', 'legal.terms')->name('legal.terms');

// Content Booking Landing Page
Route::get('/sesion-de-contenido', function () {
    return redirect()->to(route('home').'#agenda', 302);
})->name('booking.show');
Route::post('/sesion-de-contenido/checkout', [ContentBookingController::class, 'checkout'])->name('booking.checkout');
Route::get('/sesion-de-contenido/{publicId}/confirm', [ContentBookingController::class, 'confirm'])->name('booking.confirm');
Route::get('/sesion-de-contenido/{publicId}/pending', [ContentBookingController::class, 'pending'])->name('booking.pending');
Route::get('/sesion-de-contenido/{publicId}/failure', [ContentBookingController::class, 'failure'])->name('booking.failure');
Route::post('/sesion-de-contenido/{publicId}/retry', [ContentBookingController::class, 'retryPayment'])->name('booking.retry');
Route::get('/dj-set', [ContentBookingController::class, 'showDjSet'])->name('djset.show');
Route::get('/djset', fn () => redirect()->route('djset.show', status: 301))->name('djset.legacy');
Route::post('/dj-set/checkout', [ContentBookingController::class, 'checkoutDjSet'])->name('djset.checkout');
Route::post('/djset/checkout', [ContentBookingController::class, 'checkoutDjSet'])->name('djset.checkout.legacy');
Route::get('/sesiones-de-dron', [ContentBookingController::class, 'showDroneSession'])->name('drone-sessions.show');
Route::get('/drone-session', fn () => redirect()->route('drone-sessions.show', status: 301))->name('drone-sessions.legacy');
Route::get('/vuelos-con-dron', fn () => redirect()->route('drone-sessions.show', status: 301))->name('drone-sessions.flights-legacy');
Route::post('/sesiones-de-dron/checkout', [ContentBookingController::class, 'checkoutDroneSession'])->name('drone-sessions.checkout');
Route::get('/avances-de-obra', [ContentBookingController::class, 'showConstructionProgress'])->name('construction-progress.show');
Route::post('/avances-de-obra/checkout', [ContentBookingController::class, 'checkoutConstructionProgress'])->name('construction-progress.checkout');
Route::get('/reels-de-comida', [ContentBookingController::class, 'showFoodReels'])->name('food-reels.show');
Route::get('/comida-y-reels', fn () => redirect()->route('food-reels.show', status: 301))->name('food-reels.legacy');

Route::get('/djs', [DjController::class, 'index'])->name('djs.index');
Route::get('/djs/{dj:slug}', [DjController::class, 'show'])->name('djs.show');

if (! config('trascendental.enabled_as_primary')) {
    Route::get('/eventos', fn () => abort(404))->name('events.index');
    Route::get('/eventos/{event:slug}', [EventController::class, 'show'])->name('events.show');
    Route::get('/eventos/{event:slug}/tickets', [TicketCheckoutController::class, 'show'])->name('tickets.checkout.show');
    Route::post('/eventos/{event:slug}/tickets', [TicketCheckoutController::class, 'checkout'])->name('tickets.checkout.store');
}

Route::get('/tickets/manage/{order}', [TicketCheckoutController::class, 'manage'])
    ->middleware('signed')
    ->name('tickets.manage');
Route::get('/tickets/{order}/success', [TicketCheckoutController::class, 'success'])->name('tickets.success');
Route::get('/tickets/{order}/pending', [TicketCheckoutController::class, 'pending'])->name('tickets.pending');
Route::get('/tickets/{order}/failure', [TicketCheckoutController::class, 'failure'])->name('tickets.failure');
Route::post('/tickets/{order}/retry', [TicketCheckoutController::class, 'retryPayment'])->name('tickets.retry');
Route::post('/tickets/{order}/attendees', [TicketAttendeeController::class, 'store'])->name('tickets.attendees.store');

Route::get('/tickets/check-in/{token}', [TicketCheckInController::class, 'show'])
    ->middleware('signed')
    ->name('tickets.checkin.show');
Route::post('/tickets/check-in/{token}', [TicketCheckInController::class, 'confirm'])
    ->middleware('signed')
    ->name('tickets.checkin.confirm');
Route::get('/tickets/check-in/{token}/qr', [TicketCheckInController::class, 'qr'])
    ->name('tickets.checkin.qr');
Route::get('/tickets/check-in/{token}/pdf', [TicketCheckInController::class, 'pdf'])->name('tickets.checkin.pdf');

Route::get('/videos', [VideoController::class, 'index'])->name('videos.index');
Route::get('/videos/{video:slug}', [VideoController::class, 'show'])->name('videos.show');

Route::get('/portafolio', [PortfolioController::class, 'index'])->name('portfolio.index');

Route::get('/blog', [PostController::class, 'index'])->name('posts.index');
Route::get('/blog/{post:slug}', [PostController::class, 'show'])->name('posts.show');

Route::post('/guest-list', [GuestListController::class, 'store'])->name('guestlist.store');

Route::get('/invite/{token}', [GuestListInviteController::class, 'show'])->name('guestlist.invite.show');
Route::post('/invite/{token}', [GuestListInviteController::class, 'confirm'])->name('guestlist.invite.confirm');

// Guest List Registration (público desde links)
Route::get('/register/{token}', [GuestListRegisterController::class, 'show'])->name('guestlist.register');
Route::post('/register/{token}', [GuestListRegisterController::class, 'store'])->name('guestlist.register.store');
Route::get('/register/{token}/success', [GuestListRegisterController::class, 'success'])->name('guestlist.register.success');
Route::get('/register/{token}/thank-you', [GuestListRegisterController::class, 'thankyou'])->name('guestlist.register.thankyou');

Route::get('/check-in/{token}', [GuestListCheckInController::class, 'show'])
    ->middleware('signed')
    ->name('guestlist.checkin.show');
Route::post('/check-in/{token}', [GuestListCheckInController::class, 'confirm'])
    ->middleware('signed')
    ->name('guestlist.checkin.confirm');
Route::get('/check-in/{token}/qr', [GuestListCheckInController::class, 'qr'])
    ->name('guestlist.checkin.qr');
Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
Route::get('/mi-portal', [CustomerController::class, 'portal'])
    ->middleware('customer.auth')
    ->name('customers.portal');
Route::get('/mi-portal/login', [CustomerAuthController::class, 'showLogin'])->name('customers.login');
Route::post('/mi-portal/login', [CustomerAuthController::class, 'login'])->name('customers.login.store');
Route::post('/mi-portal/logout', [CustomerAuthController::class, 'logout'])
    ->middleware('customer.auth')
    ->name('customers.logout');

Route::get('/mi-portal/password/forgot', [\App\Http\Controllers\CustomerPasswordResetController::class, 'create'])
    ->name('customers.password.request');
Route::post('/mi-portal/password/forgot', [\App\Http\Controllers\CustomerPasswordResetController::class, 'store'])
    ->name('customers.password.email');
Route::get('/mi-portal/password/reset/{token}', [\App\Http\Controllers\CustomerPasswordResetController::class, 'edit'])
    ->name('customers.password.reset');
Route::post('/mi-portal/password/reset', [\App\Http\Controllers\CustomerPasswordResetController::class, 'update'])
    ->name('customers.password.update');

// Lead Capture (Popup)
Route::post('/api/leads', [LeadCaptureController::class, 'capture'])->name('leads.capture');
Route::post('/api/trascendental/leads', [TrascendentalLeadController::class, 'store'])->name('trascendental.leads.store');

// Analytics
Route::post('/analytics/collect', [AnalyticsController::class, 'collect'])->name('analytics.collect');

// Email Tracking
Route::get('/track/email/{token}/open', [EmailTrackingController::class, 'trackOpen'])->name('email.track.open');
Route::get('/track/email/{token}/click', [EmailTrackingController::class, 'trackClick'])->name('email.track.click');

// Webhooks
Route::post('/webhooks/mailtrap/events', [EmailTrackingController::class, 'mailtrapWebhook'])->name('webhooks.mailtrap.events');
Route::post('/webhooks/mercadopago', [MercadoPagoWebhookController::class, 'handle'])->name('webhooks.mercadopago');
Route::post('/webhooks/stripe', [StripeWebhookController::class, 'handle'])->name('webhooks.stripe');
Route::post('/webhooks/twilio/sms/status', function () { /* TODO */
})->name('webhooks.twilio.sms');
Route::post('/webhooks/twilio/whatsapp/status', function () { /* TODO */
})->name('webhooks.twilio.whatsapp');
Route::post('/webhooks/twilio/voice/status', function () { /* TODO */
})->name('webhooks.twilio.voice');

// Unsubscribe
Route::get('/unsubscribe', [LeadCaptureController::class, 'unsubscribe'])->name('customer.unsubscribe');
Route::post('/unsubscribe', [LeadCaptureController::class, 'unsubscribe']);

Route::get('/locale/{locale}', function (string $locale) {
    if (! in_array($locale, ['es', 'en'], true)) {
        abort(404);
    }

    session(['locale' => $locale]);

    $previousPath = parse_url(url()->previous(), PHP_URL_PATH) ?: '/';

    if (config('trascendental.enabled_as_primary') || str_starts_with($previousPath, '/trascendental')) {
        session(['trascendental_locale' => $locale]);
    }

    return redirect()
        ->back()
        ->cookie('locale', $locale, 60 * 24 * 365);
})->name('locale.switch');

Route::post('/theme', function () {
    $theme = request()->input('theme', 'dark');

    if (! in_array($theme, ['dark', 'light'])) {
        return response()->json(['success' => false, 'message' => 'Invalid theme'], 400);
    }

    if (auth()->check()) {
        auth()->user()->update(['theme' => $theme]);
    }

    return response()->json(['success' => true, 'theme' => $theme]);
})->name('theme.update');

Route::middleware('auth')->prefix('admin/integrations/mercadopago')->group(function (): void {
    Route::get('/connect', [MercadoPagoOAuthController::class, 'redirect'])->name('mercadopago.oauth.redirect');
    Route::get('/callback', [MercadoPagoOAuthController::class, 'callback'])->name('mercadopago.oauth.callback');
});

Route::middleware('auth')->prefix('admin/integrations/google-calendar')->group(function (): void {
    Route::get('/connect', [GoogleCalendarOAuthController::class, 'redirect'])->name('google-calendar.oauth.redirect');
    Route::get('/callback', [GoogleCalendarOAuthController::class, 'callback'])->name('google-calendar.oauth.callback');
    Route::post('/disconnect', [GoogleCalendarOAuthController::class, 'disconnect'])->name('google-calendar.oauth.disconnect');
});
