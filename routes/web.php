<?php

use App\Http\Controllers\AnalyticsEventController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\LandingPageController;
use App\Http\Controllers\MapSearchController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ReferralController;
use App\Http\Controllers\RazorpayController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\SocialAuthController;
use App\Http\Controllers\UnlockController;
use App\Http\Controllers\WishlistController;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

// Social authentication
Route::get('auth/{provider}/redirect', [SocialAuthController::class, 'redirect'])
    ->where('provider', 'google|facebook')
    ->name('social.redirect');
Route::get('auth/{provider}/callback', [SocialAuthController::class, 'callback'])
    ->where('provider', 'google|facebook')
    ->name('social.callback');

// Public pages and discovery
Route::get('/', [LandingPageController::class, 'index'])->name('home');
Route::get('/set-city', [RoomController::class, 'setCity'])->name('set-city');
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/robots.txt', [SitemapController::class, 'robots'])->name('robots');
Route::post('/analytics/events', [AnalyticsEventController::class, 'store'])
    ->middleware('throttle:60,1')
    ->name('analytics.events.store');
Route::get('/ref/{code}', [ReferralController::class, 'track'])
    ->name('referral.track');

Route::get('/dashboard', function () {
    return match (auth()->user()->role) {
        'admin' => redirect()->route('admin.dashboard'),
        'broker' => redirect()->route('agent.dashboard'),
        'owner' => redirect()->route('owner.dashboard'),
        default => redirect()->route('home'),
    };
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/rooms', [RoomController::class, 'index'])->name('rooms.index');
Route::get('/buy', function () {
    return redirect()->route('rooms.index', ['purpose' => 'sell']);
})->name('rooms.buy');
Route::get('/rent', function () {
    return redirect()->route('rooms.index', ['purpose' => 'rent']);
})->name('rooms.rent');
Route::get('/properties-for-sale', function () {
    return redirect()->route('rooms.index', ['purpose' => 'sell']);
});
Route::get('/properties-for-rent', function () {
    return redirect()->route('rooms.index', ['purpose' => 'rent']);
});
Route::get('/rooms/compare', [RoomController::class, 'compare'])->name('rooms.compare');
Route::get('/rooms/{room}', [RoomController::class, 'show'])->name('rooms.show');
Route::get('/map-search', [MapSearchController::class, 'index'])->name('rooms.map');
Route::get('/agencies', [\App\Http\Controllers\AgencyController::class, 'index'])->name('agencies.index');
Route::get('/agency/{user}', [\App\Http\Controllers\AgencyController::class, 'show'])->name('agency.show');
Route::post('/agency/{user}/reviews', [\App\Http\Controllers\AgencyController::class, 'storeReview'])->name('agency.review.store');

// Wishlist & coupon routes (accessible to all authenticated users: user, admin, owner, broker)
Route::middleware('auth')->group(function () {
    Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
    Route::post('/wishlist/toggle/{roomId}', [WishlistController::class, 'toggle'])->name('wishlist.toggle');
    Route::post('/coupon/apply', [\App\Http\Controllers\CouponController::class, 'apply'])->name('coupon.apply');
});

// Role-specific route modules
require __DIR__.'/admin.php';
require __DIR__.'/owner.php';
require __DIR__.'/broker.php';
require __DIR__.'/user.php';

// Public content
Route::post('/unlock/{room}', [UnlockController::class, 'unlock'])->name('unlock.contact');
Route::get('/blog', [BlogController::class, 'index'])->name('blogs.index');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blogs.show');

// Static admin-managed pages (separate admin panels)
Route::controller(PageController::class)->group(function () {
    Route::get('/faq', 'faq')->name('pages.faq');
    Route::get('/how-it-works', 'howItWorks')->name('pages.how-it-works');
    Route::get('/contact-us', 'contact')->name('pages.contact');
});

// Dynamic CMS pages from admin panel (footer/about/terms/etc.)
Route::get('/page/{slug}', [PageController::class, 'show'])
    ->where('slug', '[A-Za-z0-9\-_]+')
    ->name('pages.show');

// Contact form submission (stays fixed)
Route::post('/contact-us', [ContactController::class, 'store'])
    ->middleware('throttle:public_form')
    ->name('pages.contact.store');

// External callbacks and framework auth routes
Route::post('/webhook/razorpay', [RazorpayController::class, 'webhook'])->name('razorpay.webhook');
require __DIR__.'/auth.php';

Route::get('/youtube-proxy/{videoId}', function ($videoId) {
    $response = Http::get("https://img.youtube.com/vi/{$videoId}/hqdefault.jpg");
    abort_unless($response->successful(), 404, 'YouTube thumbnail not found');
    return response($response->body())->header('Content-Type', $response->header('Content-Type'))
        ->header('Cache-Control', 'public, max-age=31536000');
})->name('youtube.proxy');

// City landing pages
Route::get('/{citySlug}', [LandingPageController::class, 'city'])
    ->where('citySlug', '[A-Za-z0-9\-_]+')->name('cities.show');
