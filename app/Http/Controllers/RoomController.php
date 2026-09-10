<?php

namespace App\Http\Controllers;
use App\Models\Room;
use App\Models\Payment;
use App\Models\Enquiry;
use App\Models\RoomOption;
use App\Models\PropertyCategory;
use App\Models\PropertyType;
use App\Models\Setting;
use App\Models\SubscriptionUsage;
use App\Services\CityOperations;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;

class RoomController extends Controller {
    public function index(Request $request)
    {
        $request->validate([
            'q'                  => ['nullable', 'string', 'max:120'],
            'purpose'            => ['nullable', 'in:rent,sell'],
            'min_rent'           => ['nullable', 'numeric', 'min:0'],
            'max_rent'           => ['nullable', 'numeric', 'min:0'],
            'min_price'          => ['nullable', 'numeric', 'min:0'],
            'max_price'          => ['nullable', 'numeric', 'min:0'],
            'min_area_sqft'      => ['nullable', 'numeric', 'min:0'],
            'max_area_sqft'      => ['nullable', 'numeric', 'min:0'],
            'listing_type'       => ['nullable', 'in:owner,broker'],
            'possession_status'  => ['nullable', 'in:ready_to_move,under_construction'],
        ]);

        $query = Room::query();
    
        // Check if owner wants to see their OWN rooms (management view)
        $isOwnerManagementView = Auth::check() && Auth::user()->role === 'owner' && $request->get('view') === 'mine';

        if ($isOwnerManagementView) {
            $query->where('user_id', Auth::id());
        } 
        // Public/Explore view (default)
        else {
            $query->publicVisible();
        }
    
    // Search or Auto-Detect City
    if ($request->has('clear')) {
        session()->forget(['user_city', 'user_lat', 'user_lng', 'location_verified']);
        session(['no_auto' => true]); // Prevent auto-detection from re-triggering immediately
        return redirect()->route('rooms.index');
    }

    $userCity = session('user_city');
    $locationVerified = session('location_verified', false);

    // Prioritize Request > Session
    $lat = $request->lat ?: ($request->filled('city') ? null : session('user_lat'));
    $lng = $request->lng ?: ($request->filled('city') ? null : session('user_lng'));

    if ($request->filled('city')) {
        session(['user_city' => $request->city]);
        session()->forget('no_auto');

        if ($request->has('lat') && $request->has('lng')) {
            session(['user_lat' => $request->lat, 'user_lng' => $request->lng, 'location_verified' => true]);
        } else {
            // If they searched a new city manually, clear previous coordinates
            $lat = $lng = null;
            session()->forget(['user_lat', 'user_lng', 'location_verified']);
        }
    } else {
        // Server-side IP-based city auto-detection fallback
        // Runs only if no session city, no request city, and user hasn't opted out
        if (!session('no_auto') && !$request->filled('city')) {
            try {
                $ip = $request->ip();
                // Skip for localhost/private IPs
                if (!in_array($ip, ['127.0.0.1', '::1']) && !str_starts_with($ip, '192.168.') && !str_starts_with($ip, '10.')) {
                    $geoResponse = \Illuminate\Support\Facades\Http::timeout(3)->get("http://ip-api.com/json/{$ip}?fields=status,city,lat,lon");
                    if ($geoResponse->successful()) {
                        $geo = $geoResponse->json();
                        if (($geo['status'] ?? '') === 'success' && !empty($geo['city'])) {
                            $detectedCity = $geo['city'];
                            session(['user_city' => $detectedCity, 'user_lat' => $geo['lat'], 'user_lng' => $geo['lon'], 'location_verified' => true]);
                            $userCity = $detectedCity;
                            $lat = $geo['lat'];
                            $lng = $geo['lon'];
                            $locationVerified = true;
                        }
                    }
                }
            } catch (\Exception $e) {
                // Fail silently — don't break page if geo API is down
            }
        }
    }

    $cityContext = CityOperations::resolve($request->input('city'), session('user_city'));
    if (!$isOwnerManagementView) {
        CityOperations::applyRoomCity($query, $cityContext);
    }

    if ($lat && $lng && $locationVerified && !$cityContext['isFallback']) {
        // SORT BY DISTANCE
        $query->selectRaw("*, (6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance", [$lat, $lng, $lat])
              ->orderBy('distance', 'asc');
    }
    // No more hidden fallback to session('user_city') if not verified or requested

    // Purpose filter (rent vs sell)
    if ($request->filled('purpose') && in_array($request->purpose, ['rent', 'sell'], true)) {
        $query->where('purpose', $request->purpose);
    }

    // Rent range filter (for rent listings)
    if ($request->filled('min_rent')) {
        $query->where('rent', '>=', $request->min_rent);
    }

    if ($request->filled('max_rent')) {
        $query->where('rent', '<=', $request->max_rent);
    }

    // Price range filter (for sell listings)
    if ($request->filled('min_price')) {
        $query->where('price', '>=', $request->min_price);
    }

    if ($request->filled('max_price')) {
        $query->where('price', '<=', $request->max_price);
    }

    // Possession status filter (sell only)
    if ($request->filled('possession_status') && in_array($request->possession_status, ['ready_to_move', 'under_construction'], true)) {
        $query->where('possession_status', $request->possession_status);
    }

    if ($request->filled('q')) {
        $search = trim($request->input('q'));
        $query->where(function ($searchQuery) use ($search) {
            $searchQuery->where('title', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")
                ->orWhere('city', 'like', "%{$search}%")
                ->orWhere('address', 'like', "%{$search}%")
                ->orWhere('landmarks', 'like', "%{$search}%");
        });
    }

    // Advanced Filters
    if ($request->filled('furnishing_type')) {
        $this->applyOptionFilter($query, 'furnishing_type', $request->furnishing_type);
    }

    if ($request->filled('tenant_type')) {
        $this->applyOptionFilter($query, 'tenant_type', $request->tenant_type);
    }

    if ($request->filled('property_type_id')) {
        $query->whereIn('property_type_id', (array) $request->property_type_id);
    }

    if ($request->filled('property_category_id')) {
        $query->whereIn('property_category_id', (array) $request->property_category_id);
    }

    if ($request->filled('min_area_sqft')) {
        $query->where('area_sqft', '>=', $request->min_area_sqft);
    }

    if ($request->filled('max_area_sqft')) {
        $query->where('area_sqft', '<=', $request->max_area_sqft);
    }

    if ($request->filled('listing_type')) {
        $query->where('listing_type', $request->listing_type);
    }

    if ($request->filled('area')) {
        $area = trim($request->area);
        if ($area !== '') {
            $query->where(function ($q) use ($area) {
                $q->where('address', 'like', '%' . $area . '%');
            });
        }
    }

    if ($request->filled('room_type')) {
        $this->applyOptionFilter($query, 'room_type', $request->room_type);
    }

    if ($request->filled('amenities')) {
        $amenities = $request->amenities;
        if (is_array($amenities)) {
            foreach ($amenities as $amenity) {
                $query->whereJsonContains('amenities', $amenity);
            }
        } else {
            $query->whereJsonContains('amenities', $amenities);
        }
    }

    if ($request->filled('available_now') && $request->available_now == '1') {
        $query->where('availability_from', '<=', now()->toDateString());
    } elseif ($request->filled('availability_from')) {
        $query->where('availability_from', '<=', $request->availability_from);
    }
    
    // Sorting logic
    $sortBy = $request->get('sort_by', 'newest');
    $query->orderBy('is_featured', 'desc');

    if ($lat && $lng && $locationVerified && !$cityContext['isFallback']) {
        $query->orderBy('distance', 'asc');
    }

    if ($sortBy === 'rent_asc') {
        $query->orderBy('rent', 'asc');
    } elseif ($sortBy === 'rent_desc') {
        $query->orderBy('rent', 'desc');
    } elseif ($sortBy === 'price_asc') {
        $query->orderByRaw('COALESCE(NULLIF(price, 0), rent) ASC');
    } elseif ($sortBy === 'price_desc') {
        $query->orderByRaw('COALESCE(NULLIF(price, 0), rent) DESC');
    } else {
        $query->orderBy('created_at', 'desc');
    }
    
    $userWishlistIds = Auth::check() ? Auth::user()->wishlists()->pluck('room_id')->toArray() : [];

    $rooms = $query->with(['user:id,name,avatar', 'propertyType', 'propertyCategory', 'roomTypeOption', 'furnishingOption', 'tenantOption'])
                   ->paginate(20)
                   ->withQueryString();

    // Handle AJAX request for mobile infinite scroll
    if ($request->ajax()) {
        $view = '';
        foreach ($rooms as $room) {
            $view .= view('partials.mobile-room-card', compact('room'))->render();
        }
        return response()->json([
            'html' => $view,
            'hasMore' => $rooms->hasMorePages(),
        ]);
    }

    $popularCities = CityOperations::selectorCities();

    // Log the search or visit if city is detected (with rate limiting)
    if (($request->filled('city') || $request->filled('min_rent') || $request->filled('max_rent') || isset($userCity)) && !$request->ajax()) {
        try {
            $ipAddress = $request->ip();
            $recentLog = \App\Models\SearchLog::where('ip_address', $ipAddress)
                ->where('created_at', '>=', now()->subMinutes(5))
                ->exists();

            if (!$recentLog) {
                \App\Models\SearchLog::create([
                    'city' => $request->city ?? $userCity ?? 'Unknown',
                    'search_term' => $request->city ?? 'Auto-Detected',
                    'filters' => [
                        'min_rent' => $request->min_rent,
                        'max_rent' => $request->max_rent,
                        'property_type_id' => $request->property_type_id,
                        'property_category_id' => $request->property_category_id,
                        'min_area_sqft' => $request->min_area_sqft,
                        'max_area_sqft' => $request->max_area_sqft,
                        'is_auto_detected' => ! $request->filled('city')
                    ],
                    'user_id' => Auth::id(),
                    'ip_address' => $ipAddress,
                ]);
            }
        } catch(\Exception $e) {
            // Fail silently
        }
    }
    
    $filterCacheKey = 'rooms.filter-stats:' . md5((string) ($cityContext['activeCityName'] ?? ''));
    $filterStats = Cache::remember($filterCacheKey, now()->addMinutes(5), function () use ($cityContext) {
        $cityFilter = fn ($q) => $q->when($cityContext['activeCityName'], fn ($query) => $query->where('city', 'like', '%' . $cityContext['activeCityName'] . '%'));

        return [
            'property_type_counts' => $cityFilter(Room::publicVisible())
        ->select('property_type_id', DB::raw('count(*) as total'))
        ->whereNotNull('property_type_id')
        ->groupBy('property_type_id')
        ->pluck('total', 'property_type_id')
        ->map(fn ($total) => (int) $total)
        ->toArray(),
            'property_category_counts' => $cityFilter(Room::publicVisible())
                ->select('property_category_id', DB::raw('count(*) as total'))
                ->whereNotNull('property_category_id')
                ->groupBy('property_category_id')
                ->pluck('total', 'property_category_id')
                ->map(fn ($total) => (int) $total)
                ->toArray(),
            'rent_bounds' => $cityFilter(Room::publicVisible()->forRent())
                ->selectRaw('MIN(rent) as min_rent, MAX(rent) as max_rent')
                ->first(),
            'price_bounds' => $cityFilter(Room::publicVisible()->forSell())
                ->selectRaw('MIN(price) as min_price, MAX(price) as max_price')
                ->first(),
            'tenant_type_counts' => $cityFilter(Room::publicVisible())
                ->select('tenant_option_id', DB::raw('count(*) as total'))
                ->whereNotNull('tenant_option_id')
                ->groupBy('tenant_option_id')
                ->pluck('total', 'tenant_option_id')
                ->map(fn ($total) => (int) $total)
                ->toArray(),
            'furnishing_counts' => $cityFilter(Room::publicVisible())
                ->select('furnishing_option_id', DB::raw('count(*) as total'))
                ->whereNotNull('furnishing_option_id')
                ->groupBy('furnishing_option_id')
                ->pluck('total', 'furnishing_option_id')
                ->map(fn ($total) => (int) $total)
                ->toArray(),
        ];
    });

    $propertyTypeCounts = $filterStats['property_type_counts'];

    $propertyTypes = PropertyType::cachedActive();

    $propertyCategoryCounts = $filterStats['property_category_counts'];

    $propertyCategories = PropertyCategory::with('propertyType:id,name')
        ->publicSelectable()
        ->orderBy('property_type_id')
        ->orderBy('name')
        ->get(['id', 'property_type_id', 'name']);

    // Dynamic rent bounds from actual DB data
    $rentBounds  = $filterStats['rent_bounds'];
    $priceBounds = $filterStats['price_bounds'];

    // Tenant type counts (girls/boys/family/any)
    $tenantTypeCounts = $filterStats['tenant_type_counts'];

    // Furnishing counts from DB
    $furnishingCounts = $filterStats['furnishing_counts'];

    // Current purpose for view
    $currentPurpose = $request->get('purpose', '');

    return view('rooms.index', compact(
        'rooms', 'popularCities', 'propertyTypes', 'propertyTypeCounts',
        'propertyCategories', 'propertyCategoryCounts', 'rentBounds', 'priceBounds',
        'tenantTypeCounts', 'furnishingCounts', 'cityContext', 'userWishlistIds',
        'currentPurpose'
    ));
    }
    

    public function create(Request $request) {
        $propertyTypes = \App\Models\PropertyType::with(['categories' => function($q) {
            $q->where('status', true)->orderBy('name');
        }])->where('status', true)->orderBy('name')->get();

        $amenities = \App\Models\RoomOption::optionsFor('amenity')->pluck('label')->all();
        if (empty($amenities)) {
            $amenities = ['High-speed WiFi', 'Car & Bike Parking', 'Air Conditioner', 'Power Backup', 'Lift / Elevator', '24x7 Security Guard', 'CCTV Surveillance', '24hr Water Supply', 'Fitness Gym', 'Swimming Pool', 'Club House', 'Park / Green Area', 'Fire Safety', 'Piped Gas (PNG)'];
        }

        $furnishingOptions = \App\Models\RoomOption::optionsFor('furnishing_type');
        $tenantOptions = \App\Models\RoomOption::optionsFor('tenant_type');

        $storeRoute = route('owner.rooms.store');
        $draftsIndex = route('owner.rooms.drafts');

        return view('owner.rooms.create-multistep', compact('propertyTypes', 'amenities', 'furnishingOptions', 'tenantOptions', 'storeRoute', 'draftsIndex'));
    }

    /**
     * Validates input and prepares normalized payload for room create & update.
     * Consolidates extended specifications into the single 'features' JSON column.
     */
    protected function validateAndPrepareRoomPayload(Request $req, ?Room $existingRoom = null): array
    {
        $isUpdate = $existingRoom !== null;

        $rules = [
            'title'                 => 'required|string|max:255',
            'description'           => 'nullable|string',
            'property_type_id'      => ['required', 'integer', Rule::exists('property_types', 'id')->where('status', true)],
            'property_category_id'  => ['nullable', 'integer', Rule::exists('property_categories', 'id')->where('status', true)],
            'purpose'               => 'required|in:rent,sell',
            'rent'                  => 'required_if:purpose,rent|nullable|numeric|min:0',
            'price'                 => 'required_if:purpose,sell|nullable|numeric|min:0',
            'deposit'               => 'nullable|numeric|min:0',
            'area_sqft'             => 'nullable|numeric|min:0',
            'possession_status'     => 'nullable|in:ready_to_move,under_construction',
            'city'                  => 'required|string',
            'state'                 => 'nullable|string',
            'country'               => 'nullable|string',
            'address'               => 'nullable|string',
            'latitude'              => 'nullable|numeric',
            'longitude'             => 'nullable|numeric',
            'furnishing_type'       => ['nullable'],
            'tenant_type'           => ['nullable'],
            'amenities'             => 'nullable|array',
            'amenities.*'           => ['string'],
            'landmarks'             => 'nullable|array',
            'landmarks.*'           => 'string',
            'photos.*'              => 'image|mimes:jpg,jpeg,png,webp|max:5120',
            'photos'                => $isUpdate ? 'nullable|array|max:10' : 'required|array|min:1|max:10',
            'video'                 => 'nullable|mimes:mp4,avi,mov,wmv|max:20480',
            'video_url'             => 'nullable|url|max:255',
            'listing_type'          => 'nullable|in:owner,broker',
            'broker_fee'            => 'nullable|numeric|min:0',
        ];

        $data = $req->validate($rules);

        // Extended market & specification attributes stored directly into 'features' JSON column
        $booleanFeatureKeys = [
            'price_negotiable', 'is_bank_loan_approved', 'is_main_road_facing',
            'is_corner_property', 'gated_community', 'pet_friendly',
        ];

        $allFeatureKeys = [
            'super_builtup_area', 'carpet_area', 'plot_area', 'plot_area_unit',
            'facing', 'bathrooms', 'balconies', 'floor_no', 'total_floors',
            'parking_type', 'water_supply', 'possession_date', 'property_age',
            'ownership_type', 'rera_id', 'commercial_type', 'frontage_width_ft',
            'washroom_type', 'power_backup', 'suitable_for', 'maintenance_charges',
            'maintenance_type', 'lockin_period_months', 'notice_period_days', 'food_preference',
            ...$booleanFeatureKeys,
        ];

        $features = is_array($existingRoom?->features) ? $existingRoom->features : [];
        foreach ($allFeatureKeys as $key) {
            if ($req->has($key)) {
                $features[$key] = in_array($key, $booleanFeatureKeys, true)
                    ? $req->boolean($key)
                    : $req->input($key);
            }
        }

        $data['features'] = $features;
        $data['area_sqft'] = $data['area_sqft'] ?? ($features['carpet_area'] ?? ($features['super_builtup_area'] ?? ($features['plot_area'] ?? $existingRoom?->area_sqft)));

        // Synchronize rent/price for backward-compatible queries when purpose is sell
        if (($data['purpose'] ?? ($existingRoom?->purpose ?? 'rent')) === 'sell') {
            $data['rent'] = !empty($data['rent']) ? $data['rent'] : ($data['price'] ?? $existingRoom?->rent ?? 0);
        }

        // Clean coordinates
        if (isset($data['latitude']) && $data['latitude'] === '') {
            $data['latitude'] = null;
        }
        if (isset($data['longitude']) && $data['longitude'] === '') {
            $data['longitude'] = null;
        }

        return $data;
    }

    public function store(Request $req) {
        $data = $this->validateAndPrepareRoomPayload($req);

        $newPhotoPaths = [];
        $newVideoPath = null;
        DB::beginTransaction();
        try {
            $data['user_id'] = Auth::id();

            // Set broker_id and listed_by based on role
            $isBroker = Auth::user()->role === 'broker';
            if ($isBroker) {
                $data['broker_id'] = Auth::id();
                $data['listed_by'] = 'broker';
                $expiryDays = (int) \App\Models\BrokerSetting::get('broker_listing_expiry_days', 30);
                $data['expires_at'] = now()->addDays($expiryDays > 0 ? $expiryDays : 30);
            } else {
                $data['listed_by'] = 'owner';
                $data['listing_type'] = 'owner';
            }

            $data['status'] = 'pending';
            $data['listing_fee_paid'] = false;
            
            // Handle multiple photos with Compression
            if ($req->hasFile('photos')) {
                $photos = [];
                foreach ($req->file('photos') as $photo) {
                    $path = \App\Services\ImageOptimizer::optimize($photo, 'room_photo');
                    $photos[] = $path;
                    $newPhotoPaths[] = $path;
                }
                $data['photos'] = $photos;
                $data['photo'] = $photos[0]; // First photo as main photo
            }

            // Handle video upload
            if ($req->hasFile('video')) {
                $newVideoPath = $req->file('video')->store('rooms/videos', 'public');
                $data['video'] = $newVideoPath;
            }

            $data = $this->mapRoomOptionData($data);

            $room = Room::create($data);
            \Illuminate\Support\Facades\Cache::forget('public_cities_list');
            \Illuminate\Support\Facades\Cache::forget('popular_cities_web');

            // Clean up the source draft (if any) — published so don't keep it
            if ($req->filled('draft_id')) {
                try {
                    $draft = \App\Models\RoomDraft::where('id', $req->input('draft_id'))
                        ->where('user_id', Auth::id())
                        ->first();
                    if ($draft) {
                        $draft->update(['is_published' => true]);
                    }
                } catch (\Throwable $e) {
                    report($e);
                }
            }

            try {
                \App\Models\AdminNotification::send(
                    'room_posted',
                    'New Room Listed',
                    '"' . \Illuminate\Support\Str::limit($room->title, 35) . '" in ' . ($room->city ?: 'Unknown') . ' by ' . (Auth::user()?->name ?? 'Owner'),
                    route('admin.rooms.show', $room->id),
                    'fa-building'
                );
            } catch (\Throwable $e) {
                report($e);
            }

            // Notify broker of property submission & check remaining listing credits
            if (Auth::user()?->role === 'broker') {
                try {
                    \App\Services\NotificationService::notifyPropertySubmitted(Auth::user(), $room);
                    $creditRecord = \App\Models\BrokerListingCredit::where('broker_id', Auth::id())
                        ->where('credits_remaining', '>', 0)
                        ->where(function ($q) {
                            $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
                        })
                        ->first();
                    $remaining = $creditRecord ? $creditRecord->credits_remaining : 0;
                    if ($remaining <= 1) {
                        \App\Services\NotificationService::notifyLowListingCredits(Auth::user(), $remaining);
                    }
                } catch (\Throwable $ex) {
                    report($ex);
                }
            }

            // Free-launch mode: skip subscriptions, wallet and Razorpay while
            // keeping the configured listing amount saved for future use.
            $listingFeeEnabled = filter_var(Setting::get('listing_fee_enabled', '0'), FILTER_VALIDATE_BOOLEAN);

            // Broker-specific listing fee and free quota logic
            $brokerListingChargesEnabled = \App\Models\BrokerSetting::isEnabled('broker_listing_charges_enabled', false);

            if ($isBroker) {
                if (!$brokerListingChargesEnabled) {
                    $listingFeeEnabled = false;
                } else {
                    $freeQuota = (int) \App\Models\BrokerSetting::get('broker_free_listing_limit', 0);
                    if ($freeQuota > 0) {
                        $existingBrokerRooms = Room::where('broker_id', Auth::id())->where('id', '!=', $room->id)->count();
                        if ($existingBrokerRooms < $freeQuota) {
                            $listingFeeEnabled = false; // Eligible under free listing quota
                        }
                    }
                }
            }

            if (!$listingFeeEnabled) {
                $payment = Payment::create([
                    'user_id' => Auth::id(),
                    'type' => $isBroker ? 'broker_listing' : 'listing',
                    'amount' => 0,
                    'gateway' => 'free',
                    'reference_id' => $room->id,
                    'status' => 'completed',
                ]);
                $room->update([
                    'listing_payment_id' => $payment->id,
                    'listing_fee_paid' => true,
                    'status' => 'active',
                ]);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'room_id' => $room->id,
                    'free_listing' => true,
                    'message' => 'Room submitted successfully. It will be visible after admin approval.',
                ]);
            }

            // Check broker credits for room listing
            $useCredits = false;

            if ($isBroker) {
                $credits = \App\Models\BrokerListingCredit::where('broker_id', Auth::id())
                    ->where('credits_remaining', '>', 0)
                    ->where(function ($q) {
                        $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
                    })
                    ->where('type', 'listing')
                    ->lockForUpdate()
                    ->first();

                if ($credits) {
                    $credits->decrement('credits_remaining');
                    $room->update([
                        'listing_fee_paid' => true,
                        'status' => 'active',
                        'listing_payment_id' => null,
                    ]);
                    $useCredits = true;
                }
            }

            if ($useCredits) {
                DB::commit();

                return response()->json([
                    'success' => true,
                    'room_id' => $room->id,
                    'credits_used' => true,
                    'message' => 'Room listed successfully!',
                ]);
            }

            // Owner subscription check or broker payment required
            $activeSubscription = \App\Models\Subscription::where('user_id', Auth::id())
                ->where('status', 'active')
                ->whereHas('plan', fn ($query) => $query->where('type', 'owner')->where('is_active', true))
                ->lockForUpdate()
                ->with('plan')
                ->first();
            
            $useSubscription = false;
            if ($activeSubscription && $activeSubscription->plan && $activeSubscription->plan->type === 'owner') {
                // Count rooms listed using subscription (listing_fee_paid = true and listing_payment_id is null)
                $usedListings = $activeSubscription->usages()->where('usage_type', 'listing')->count();
                
                $totalListings = $activeSubscription->plan->listing_limit ?? 0;
                
                // Check for Unlimited Plan (-1)
                $remainingListings = 0;
                if ($totalListings === -1) {
                    $remainingListings = 9999;
                } else {
                    $remainingListings = max(0, $totalListings - $usedListings);
                }
                
                if ($remainingListings > 0) {
                    // Use subscription - mark as paid without payment
                    $room->update([
                        'listing_fee_paid' => true,
                        'status' => 'active',
                        'listing_payment_id' => null // null means used subscription
                    ]);
                    SubscriptionUsage::firstOrCreate(
                        ['subscription_id' => $activeSubscription->id, 'usage_type' => 'listing', 'room_id' => $room->id],
                        ['user_id' => Auth::id(), 'used_at' => now()]
                    );
                    $useSubscription = true;
                }
            }

            if (!$useSubscription) {
                // Create payment record for listing fee
                $isBroker = Auth::user()->role === 'broker';
                if ($isBroker) {
                    $listingFee = \App\Models\BrokerSetting::get('broker_per_listing_charge', 199);
                } else {
                    $listingFee = Setting::get('listing_fee', 199);
                }

                // Check if user has enough balance in wallet
                $user = Auth::user();
                // Check if payment method is wallet
                if ($req->payment_method === 'wallet') {
                    if ($user->wallet_balance >= $listingFee) {
                        // Deduct from wallet balance
                        $user->decrement('wallet_balance', $listingFee);
                        
                        // Create payment record for wallet usage
                        $payment = Payment::create([
                            'user_id' => $user->id,
                            'type' => $isBroker ? 'broker_listing' : 'listing',
                            'amount' => $listingFee,
                            'gateway' => 'wallet',
                            'reference_id' => $room->id,
                            'status' => 'completed'
                        ]);
                        
                        // Store payment_id in room for tracking
                        $room->update([
                            'listing_payment_id' => $payment->id,
                            'listing_fee_paid' => true,
                            'status' => 'active'
                        ]);

                        DB::commit();

                        return response()->json([
                            'success' => true,
                            'room_id' => $room->id,
                            'wallet_used' => true,
                            'new_balance' => $user->wallet_balance,
                            'message' => 'Room listed successfully using wallet balance!'
                        ]);
                    } else {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => 'Insufficient wallet balance'
                        ], 400);
                    }
                }

                if ($listingFee <= 0) {
                    $payment = Payment::create([
                        'user_id' => Auth::id(),
                        'type' => 'listing',
                        'amount' => 0,
                        'gateway' => 'free',
                        'reference_id' => $room->id,
                        'status' => 'completed'
                    ]);
                    
                    // Store payment_id in room for tracking
                    $room->update([
                        'listing_payment_id' => $payment->id,
                        'listing_fee_paid' => true,
                        'status' => 'active'
                    ]);

                    DB::commit();

                    return response()->json([
                        'success' => true,
                        'room_id' => $room->id,
                        'free_listing' => true,
                        'message' => 'Room listed successfully!'
                    ]);
                }

                $payment = Payment::create([
                    'user_id' => Auth::id(),
                    'type' => $isBroker ? 'broker_listing' : 'listing',
                    'amount' => $listingFee,
                    'gateway' => 'razorpay',
                    'reference_id' => $room->id,
                    'status' => 'pending'
                ]);
                
                // Store payment_id in room for tracking
                $room->update(['listing_payment_id' => $payment->id]);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'room_id' => $room->id,
                    'payment_id' => $payment->id,
                    'amount' => $listingFee,
                    'message' => 'Room created. Please pay listing fee to activate.'
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'room_id' => $room->id,
                'subscription_used' => true,
                'message' => 'Room listed successfully using subscription!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            foreach ($newPhotoPaths as $newPhotoPath) {
                Storage::disk('public')->delete($newPhotoPath);
            }
            if ($newVideoPath) {
                Storage::disk('public')->delete($newVideoPath);
            }
            return response()->json([
                'success' => false,
                'message' => $e instanceof \RuntimeException
                    ? $e->getMessage()
                    : 'The room could not be created. Please try again.'
            ], 500);
        }
    }

    public function show(Request $request, Room $room) {
        // Redirect to slug if accessed by ID
        // We check the URL segment to see if it's numeric (ID) instead of the slug
        if (is_numeric($request->segment(2)) && $room->slug) {
            return redirect()->route('rooms.show', $room, 301);
        }

        $isOwner = false;
        $isAdmin = false;
        if (Auth::check()) {
            $isOwner = Auth::id() === $room->user_id && in_array(Auth::user()->role, ['owner', 'broker']);
            $isAdmin = Auth::user()->role === 'admin';
        }

        if (!$isOwner && !$isAdmin) {
            if (! Room::publicVisible()->whereKey($room->getKey())->exists()) {
                abort(404);
            }
        }

        $isUnlocked = false;
        $subscriptionRemaining = 0;
        
        if (Auth::check()) {
            // Check if user is the owner of this room
            if (
                Auth::check() &&
                (
                    (Auth::id() === $room->user_id && in_array(Auth::user()->role, ['owner', 'broker']))
                    || Auth::user()->role === 'admin'
                )
            ) {
                $isOwner = true;
                $isUnlocked = true; // Owner/broker can see their own room contact
            } else {
                // Check subscription first - count based, not date based
                $activeSubscription = \App\Models\Subscription::where('user_id', Auth::id())
                    ->where('status', 'active')
                    ->whereHas('plan', fn ($query) => $query->where('type', 'user')->where('is_active', true))
                    ->with('plan')
                    ->first();
                
                if ($activeSubscription && $activeSubscription->plan && $activeSubscription->plan->type === 'user') {
                    // Check subscription usage - count only subscription unlocks (payment_id is null)
                    $usedContacts = $activeSubscription->usages()->where('usage_type', 'contact')->count();
                    
                    $totalContacts = $activeSubscription->plan->contacts_limit ?? 0;
                    
                    if ($totalContacts === -1) {
                        $subscriptionRemaining = 9999;
                    } else {
                        $subscriptionRemaining = max(0, $totalContacts - $usedContacts);
                    }
                    
                    // Check if this specific room was unlocked via subscription
                    $roomUnlockedViaSubscription = \App\Models\Enquiry::where('user_id', Auth::id())
                        ->where('room_id', $room->id)
                        ->where('unlocked', true)
                        ->whereNull('payment_id') // Subscription unlocks have null payment_id
                        ->exists();
                    
                    if ($roomUnlockedViaSubscription) {
                        $isUnlocked = true; // Already unlocked via subscription
                    } elseif ($subscriptionRemaining > 0) {
                        // Has remaining subscription contacts but this room not unlocked yet
                        $isUnlocked = false; // Will unlock via subscription when clicked
                    }
                }
                
                // If not unlocked via subscription, check single unlock (paid unlock)
                if (!$isUnlocked) {
                    // Check if unlock fee is 0
                    $unlockFee = Setting::get('unlock_fee', 49);
                    if ($unlockFee <= 0) {
                        $isUnlocked = true;
                    } else {
                        $enquiry = Enquiry::where('user_id', Auth::id())
                            ->where('room_id', $room->id)
                            ->where('unlocked', true)
                            ->whereNotNull('payment_id') // Single paid unlock
                            ->first();
                        $isUnlocked = $enquiry ? true : false;
                    }
                }
            }
        }
        
        // Auto-unlock for brokers as per transparent model
        if ($room->listing_type === 'broker') {
            $isUnlocked = true;

            // Safely log lead for broker dashboard if authenticated tenant views room
            if (Auth::check() && Auth::id() !== $room->user_id && Auth::id() !== $room->broker_id) {
                \App\Models\Enquiry::firstOrCreate(
                    [
                        'user_id' => Auth::id(),
                        'room_id' => $room->id,
                    ],
                    [
                        'unlocked' => true,
                        'unlocked_at' => now(),
                        'payment_id' => null,
                    ]
                );
            }
        }
        
        $room->load(['owner', 'propertyType', 'propertyCategory', 'roomTypeOption', 'furnishingOption', 'tenantOption']);

        // 1. Primary match: same city + same category/type
        $relatedRooms = Room::publicVisible()
            ->whereKeyNot($room->getKey())
            ->where('city', $room->city)
            ->when($room->property_category_id, fn ($query) => $query->where('property_category_id', $room->property_category_id))
            ->when(!$room->property_category_id && $room->property_type_id, fn ($query) => $query->where('property_type_id', $room->property_type_id))
            ->with(['owner', 'propertyType', 'propertyCategory'])
            ->orderByDesc('is_featured')
            ->latest()
            ->take(4)
            ->get();

        // 2. Fallback: If fewer than 4, backfill with other rooms in the same city
        if ($relatedRooms->count() < 4) {
            $excludeIds = $relatedRooms->pluck('id')->push($room->id);
            $cityBackfill = Room::publicVisible()
                ->whereNotIn('id', $excludeIds)
                ->where('city', $room->city)
                ->with(['owner', 'propertyType', 'propertyCategory'])
                ->orderByDesc('is_featured')
                ->latest()
                ->take(4 - $relatedRooms->count())
                ->get();
            $relatedRooms = $relatedRooms->concat($cityBackfill);
        }

        // 3. Global fallback: If still fewer than 4 (e.g. small city), backfill with top active featured/latest rooms
        if ($relatedRooms->count() < 4) {
            $excludeIds = $relatedRooms->pluck('id')->push($room->id);
            $globalBackfill = Room::publicVisible()
                ->whereNotIn('id', $excludeIds)
                ->with(['owner', 'propertyType', 'propertyCategory'])
                ->orderByDesc('is_featured')
                ->latest()
                ->take(4 - $relatedRooms->count())
                ->get();
            $relatedRooms = $relatedRooms->concat($globalBackfill);
        }

        return view('rooms.show', compact('room', 'isUnlocked', 'isOwner', 'subscriptionRemaining', 'relatedRooms'));
    }

    public function edit(Room $room) {
        if ($room->user_id !== Auth::id() || !in_array(Auth::user()->role, ['owner', 'broker'])) {
            abort(403, 'Unauthorized');
        }

        $propertyTypes = \App\Models\PropertyType::with(['categories' => function($q) {
            $q->where('status', true)->orderBy('name');
        }])->where('status', true)->orderBy('name')->get();

        $amenities = \App\Models\RoomOption::optionsFor('amenity')->pluck('label')->all();
        if (empty($amenities)) {
            $amenities = ['High-speed WiFi', 'Car & Bike Parking', 'Air Conditioner', 'Power Backup', 'Lift / Elevator', '24x7 Security Guard', 'CCTV Surveillance', '24hr Water Supply', 'Fitness Gym', 'Swimming Pool', 'Club House', 'Park / Green Area', 'Fire Safety', 'Piped Gas (PNG)'];
        }

        $furnishingOptions = \App\Models\RoomOption::optionsFor('furnishing_type');
        $tenantOptions = \App\Models\RoomOption::optionsFor('tenant_type');

        return view('owner.rooms.edit', compact('room', 'propertyTypes', 'amenities', 'furnishingOptions', 'tenantOptions'));
    }

    public function update(Request $req, Room $room) {
        if ($room->user_id !== Auth::id() || !in_array(Auth::user()->role, ['owner', 'broker'])) {
            abort(403, 'Unauthorized');
        }

        $data = $this->validateAndPrepareRoomPayload($req, $room);

        $newPhotoPaths = [];
        $oldPhotoPaths = [];
        DB::beginTransaction();
        try {
            // Handle multiple photos with Compression
            if ($req->hasFile('photos')) {
                $photos = [];
                foreach ($req->file('photos') as $photo) {
                    $path = \App\Services\ImageOptimizer::optimize($photo, 'room_photo');
                    $photos[] = $path;
                    $newPhotoPaths[] = $path;
                }
                $oldPhotoPaths = collect($room->photos ?: [])
                    ->filter(fn ($path) => is_string($path) && !preg_match('/^https?:\/\//i', $path))
                    ->values()->all();
                $data['photos'] = $photos;
                $data['photo'] = $photos[0]; // First photo as main photo
            }

            // Handle video upload
            if ($req->hasFile('video')) {
                // Delete old video
                if ($room->video) {
                    Storage::disk('public')->delete($room->video);
                }
                $data['video'] = $req->file('video')->store('rooms/videos', 'public');
            }

            $data = $this->mapRoomOptionData($data);

            // Any owner edit requires moderation again before public display.
            $data['listing_status'] = 'pending';

            $room->update($data);
            \Illuminate\Support\Facades\Cache::forget('public_cities_list');
            \Illuminate\Support\Facades\Cache::forget('popular_cities_web');

            DB::commit();

            foreach ($oldPhotoPaths as $oldPhotoPath) {
                Storage::disk('public')->delete($oldPhotoPath);
            }

            return response()->json([
                'success' => true,
                'message' => 'Room updated successfully'
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            foreach ($newPhotoPaths as $newPhotoPath) {
                Storage::disk('public')->delete($newPhotoPath);
            }
            report($e);
            return response()->json([
                'success' => false,
                'message' => $e instanceof \RuntimeException
                    ? $e->getMessage()
                    : 'The room could not be updated. Please try again.'
            ], 500);
        }
    }

    public function destroy(Room $room) {
        if ($room->user_id !== Auth::id() || !in_array(Auth::user()->role, ['owner', 'broker'])) {
            abort(403, 'Unauthorized');
        }

        DB::beginTransaction();
        try {
            // Delete photos
            if ($room->photos) {
                foreach ($room->photos as $photo) {
                    Storage::disk('public')->delete($photo);
                }
            }
            
            // Delete video
            if ($room->video) {
                Storage::disk('public')->delete($room->video);
            }

            $room->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Room deleted successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            report($e);
            return response()->json([
                'success' => false,
                'message' => 'Unable to delete the room. Please try again.'
            ], 500);
        }
    }

    public function makeFeatured(Request $request, Room $room) {
        if ($room->user_id !== Auth::id() || !in_array(Auth::user()->role, ['owner', 'broker'])) {
            return back()->with('error', 'Unauthorized');
        }

        if ($room->is_featured) {
            return back()->with('info', 'Room is already featured');
        }

        $isBroker = Auth::user()->role === 'broker';

        // Check broker featured toggle
        if ($isBroker && !\App\Models\BrokerSetting::isEnabled('broker_featured_enabled', true)) {
            return back()->with('error', 'Featured listing is currently disabled for brokers.');
        }

        DB::beginTransaction();
        try {
            if ($isBroker) {
                $featuredFee = (float) \App\Models\BrokerSetting::get('broker_featured_charge', 99);
            } else {
                $featuredFee = (float) Setting::get('featured_fee', 99);
            }
            $user = Auth::user();

            if ($featuredFee <= 0) {
                $room->update(['is_featured' => true]);
                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Room featured successfully!'
                ]);
            }

            // Wallet Payment
            if ($request->payment_method === 'wallet' && $featuredFee > 0) {
                if ($user->wallet_balance >= $featuredFee) {
                    $user->decrement('wallet_balance', $featuredFee);
                    
                    $payment = Payment::create([
                        'user_id' => $user->id,
                        'type' => $isBroker ? 'broker_featured' : 'featured',
                        'amount' => $featuredFee,
                        'gateway' => 'wallet',
                        'reference_id' => $room->id,
                        'status' => 'completed'
                    ]);

                    $room->update(['is_featured' => true]);

                    DB::commit();

                    return response()->json([
                        'success' => true,
                        'wallet_used' => true,
                        'new_balance' => $user->wallet_balance,
                        'message' => 'Room featured successfully using wallet balance!'
                    ]);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Insufficient wallet balance'
                    ], 400);
                }
            }
            
            if ($featuredFee <= 0) {
                $payment = Payment::create([
                    'user_id' => Auth::id(),
                    'type' => $isBroker ? 'broker_featured' : 'featured',
                    'amount' => 0,
                    'gateway' => 'free',
                    'reference_id' => $room->id,
                    'status' => 'completed'
                ]);

                $room->update(['is_featured' => true]);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'free_feature' => true,
                    'message' => 'Room featured successfully!'
                ]);
            }

            $payment = Payment::create([
                'user_id' => Auth::id(),
                'type' => $isBroker ? 'broker_featured' : 'featured',
                'amount' => $featuredFee,
                'gateway' => 'razorpay',
                'reference_id' => $room->id,
                'status' => 'pending'
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'payment_id' => $payment->id,
                'amount' => $featuredFee,
                'message' => 'Please complete payment to feature your room'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            report($e);
            return back()->withInput()->with('error', 'Unable to feature the room. Please try again.');
        }
    }

    public function markBooked(Room $room) {
        if ($room->user_id !== Auth::id() || !in_array(Auth::user()->role, ['owner', 'broker'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        // A booked room is removed from public inventory. Its previous listing
        // entitlement is released; publishing it again must pass the current
        // subscription/payment checks in markAvailable().
        $room->update([
            'status' => 'booked',
            'listing_fee_paid' => false,
            'listing_payment_id' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Room marked as rented'
        ]);
    }

    public function markAvailable(Request $request, Room $room) {
        if ($room->user_id !== Auth::id() || !in_array(Auth::user()->role, ['owner', 'broker'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        // If room is booked, charge listing fee to make it available again
        if ($room->status === 'booked') {
            DB::beginTransaction();
            try {
                $isBroker = Auth::user()->role === 'broker';

                // For brokers: Listing Count / Credit based logic
                if ($isBroker) {
                    // Check if broker has listing credits available
                    $brokerCredit = \App\Models\BrokerListingCredit::where('broker_id', Auth::id())
                        ->where('credits_remaining', '>', 0)
                        ->where('type', 'listing')
                        ->lockForUpdate()
                        ->first();

                    if ($brokerCredit) {
                        $brokerCredit->decrement('credits_remaining');

                        $room->update([
                            'status' => 'active',
                            'listing_fee_paid' => true,
                            'listing_payment_id' => null,
                            'expires_at' => null, // No days limit, listing count based
                        ]);

                        DB::commit();

                        return response()->json([
                            'success' => true,
                            'credits_used' => true,
                            'credits_remaining' => $brokerCredit->credits_remaining,
                            'message' => "Room marked as available! 1 listing credit used. Remaining credits: {$brokerCredit->credits_remaining}",
                        ]);
                    }

                    // If broker wants to pay from wallet
                    $brokerPerListingCharge = (float) \App\Models\BrokerSetting::get('broker_per_listing_charge', 199);
                    $user = Auth::user();
                    $brokerWallet = $user->brokerWallet;
                    $walletBalance = (float) ($brokerWallet?->balance ?? $user->wallet_balance ?? 0);

                    if ($request->payment_method === 'wallet' && $walletBalance >= $brokerPerListingCharge) {
                        if ($brokerWallet && $brokerWallet->balance >= $brokerPerListingCharge) {
                            $brokerWallet->decrement('balance', $brokerPerListingCharge);
                        } else {
                            $user->decrement('wallet_balance', $brokerPerListingCharge);
                        }

                        $payment = Payment::create([
                            'user_id' => $user->id,
                            'type' => 'broker_listing',
                            'amount' => $brokerPerListingCharge,
                            'gateway' => 'wallet',
                            'reference_id' => $room->id,
                            'status' => 'completed',
                        ]);

                        $room->update([
                            'status' => 'active',
                            'listing_fee_paid' => true,
                            'listing_payment_id' => $payment->id,
                            'expires_at' => null,
                        ]);

                        DB::commit();

                        return response()->json([
                            'success' => true,
                            'wallet_used' => true,
                            'message' => 'Room made available successfully using wallet balance!',
                        ]);
                    }

                    // Broker has 0 credits remaining
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'insufficient_credits' => true,
                        'plans_url' => route('agent.plans'),
                        'message' => 'You have 0 listing credits remaining. Please purchase a plan or add credits to make this room available again.',
                    ], 402);
                }

                $listingFeeEnabled = filter_var(Setting::get('listing_fee_enabled', '0'), FILTER_VALIDATE_BOOLEAN);
                if (!$isBroker && !$listingFeeEnabled) {
                    $payment = Payment::create([
                        'user_id' => Auth::id(),
                        'type' => 'listing',
                        'amount' => 0,
                        'gateway' => 'free',
                        'reference_id' => $room->id,
                        'status' => 'completed',
                    ]);
                    $room->update([
                        'status' => 'active',
                        'listing_fee_paid' => true,
                        'listing_payment_id' => $payment->id,
                    ]);
                    DB::commit();

                    return response()->json([
                        'success' => true,
                        'free_listing' => true,
                        'message' => 'Room marked as available successfully.',
                    ]);
                }

                // Check owner subscription for room listing - count based, not date based
                $activeSubscription = \App\Models\Subscription::where('user_id', Auth::id())
                    ->where('status', 'active')
                    ->whereHas('plan', fn ($query) => $query->where('type', 'owner')->where('is_active', true))
                    ->with('plan')
                    ->first();
                
                $useSubscription = false;
                if (!$isBroker && $activeSubscription && $activeSubscription->plan && $activeSubscription->plan->type === 'owner') {
                    // Count rooms listed using subscription
                    $usedListings = $activeSubscription->usages()->where('usage_type', 'listing')->count();
                    
                    $totalListings = $activeSubscription->plan->listing_limit ?? 0;
                    
                    // Check for Unlimited Plan (-1)
                    $remainingListings = 0;
                    if ($totalListings === -1) {
                        $remainingListings = 9999;
                    } else {
                        $remainingListings = max(0, $totalListings - $usedListings);
                    }
                    
                    if ($remainingListings > 0) {
                        // Use subscription - mark as paid without payment
                        $room->update([
                            'listing_fee_paid' => true,
                            'status' => 'active',
                            'listing_payment_id' => null // null means used subscription
                        ]);
                        $useSubscription = true;
                    }
                }

                if (!$useSubscription) {
                    if ($isBroker) {
                        $listingFee = \App\Models\BrokerSetting::get('broker_per_listing_charge', 199);
                    } else {
                        $listingFee = Setting::get('listing_fee', 199);
                    }
                    
                    // Check if payment method is wallet
                    if ($request->payment_method === 'wallet') {
                        // Check if user has enough balance in wallet
                        $user = Auth::user();
                        if ($user->wallet_balance >= $listingFee) {
                            // Deduct from wallet
                            $user->decrement('wallet_balance', $listingFee);
                            
                            // Create payment record for wallet usage
                            $payment = Payment::create([
                                'user_id' => $user->id,
                                'type' => $isBroker ? 'broker_listing' : 'listing',
                                'amount' => $listingFee,
                                'gateway' => 'wallet',
                                'reference_id' => $room->id,
                                'status' => 'completed'
                            ]);
                            
                            // Store payment_id in room for tracking
                            $room->update([
                                'listing_payment_id' => $payment->id,
                                'listing_fee_paid' => true,
                                'status' => 'active'
                            ]);

                            DB::commit();

                            return response()->json([
                                'success' => true,
                                'wallet_used' => true,
                                'new_balance' => $user->wallet_balance,
                                'message' => 'Room made available successfully using wallet balance!'
                            ]);
                        } else {
                            DB::rollBack();
                            return response()->json([
                                'success' => false,
                                'message' => 'Insufficient wallet balance'
                            ], 400);
                        }
                    }

                    if ($listingFee <= 0) {
                        $payment = Payment::create([
                            'user_id' => Auth::id(),
                            'type' => $isBroker ? 'broker_listing' : 'listing',
                            'amount' => 0,
                            'gateway' => 'free',
                            'reference_id' => $room->id,
                            'status' => 'completed'
                        ]);
                        
                        // Store payment_id in room for tracking
                        $room->update([
                            'listing_payment_id' => $payment->id,
                            'listing_fee_paid' => true,
                            'status' => 'active'
                        ]);
    
                        DB::commit();
    
                        return response()->json([
                            'success' => true,
                            'free_listing' => true,
                            'message' => 'Room made available successfully!'
                        ]);
                    }

                    // Create payment record
                    $payment = Payment::create([
                        'user_id' => Auth::id(),
                        'type' => $isBroker ? 'broker_listing' : 'listing',
                        'amount' => $listingFee,
                        'gateway' => 'razorpay',
                        'reference_id' => $room->id,
                        'status' => 'pending'
                    ]);
                    
                    // Store payment_id in room for tracking
                    $room->update(['listing_payment_id' => $payment->id]);

                    DB::commit();

                    return response()->json([
                        'success' => true,
                        'payment_id' => $payment->id,
                        'amount' => $listingFee,
                        'message' => 'Please complete payment to make room available again'
                    ]);
                }

                DB::commit();

                return response()->json([
                    'success' => true,
                    'subscription_used' => true,
                    'message' => 'Room made available using subscription!'
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                report($e);
                return response()->json([
                    'success' => false,
                    'message' => 'Unable to make the room available. Please try again.'
                ], 500);
            }
        } else {
            // If not booked, just update status
            $room->update(['status' => 'active']);
            return response()->json([
                'success' => true,
                'message' => 'Room marked as available'
            ]);
        }
    }
    
    public function compare(Request $request)
    {
        $rawIds = $request->get('ids');
        $ids = [];
        if (is_array($rawIds)) {
            $ids = array_filter(array_map('intval', $rawIds));
        } elseif (is_string($rawIds) && trim($rawIds) !== '') {
            $ids = array_filter(array_map('intval', explode(',', $rawIds)));
        }

        $ids = array_slice(array_unique($ids), 0, 3);

        $rooms = collect();
        if (!empty($ids)) {
            $rooms = Room::whereIn('id', $ids)
                ->with(['propertyType', 'propertyCategory', 'roomTypeOption', 'furnishingOption', 'tenantOption'])
                ->get()
                ->sortBy(function ($room) use ($ids) {
                    return array_search($room->id, $ids);
                })
                ->values();
        }

        // Active amenities for comparison matrix
        $allAmenities = RoomOption::activeLabelsFor('amenity')->values()->all();
        if (empty($allAmenities)) {
            $allAmenities = ['WiFi', 'AC', 'Attached Bathroom', 'Geyser', 'RO Water', 'Parking', 'Refrigerator', 'Power Backup', 'Security/CCTV', 'Lift', 'Balcony', 'Washing Machine'];
        }

        return view('rooms.compare', compact('rooms', 'allAmenities', 'ids'));
    }

    public function setCity(Request $request) {
        $city = $request->get('city');
        if ($city) {
            session(['user_city' => $city]);
            session()->forget('no_auto');
            if ($request->has('lat') && $request->has('lng')) {
                session(['user_lat' => $request->lat, 'user_lng' => $request->lng]);
            }
            if ($request->has('verified')) {
                session(['location_verified' => true]);
            }
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false], 400);
    }
}
