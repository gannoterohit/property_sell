<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\HomeFeature;
use App\Models\Room;
use App\Models\Testimonial;
use App\Models\User;
use App\Services\CityOperations;
use Illuminate\Cache\Cache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache as FacadesCache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class LandingPageController extends Controller
{
    public function city(?Request $request = null, ?string $citySlug = null)
    {
        $request = $request ?: request();

        // If accessed under XAMPP subfolder or citySlug is empty, show index
        if (empty($citySlug) || strtolower($citySlug) === 'apnanestsell') {
            return $this->index($request);
        }

        $city = City::where('slug', $citySlug)->first();
        if (!$city) {
            return $this->index($request);
        }

        session(['user_city' => $city->name]);
        $request->merge(['city' => $city->name]);

        return $this->index($request);
    }

    public function index(Request $request)
    {
        $query = Room::query()
            ->publicVisible()
            ->with(['user:id,name,avatar', 'propertyType', 'propertyCategory']);

        $userCity = session('user_city');
        $locationVerified = session('location_verified', false);
        $lat = $request->lat ?: ($request->filled('city') ? null : session('user_lat'));
        $lng = $request->lng ?: ($request->filled('city') ? null : session('user_lng'));

        if ($request->filled('city')) {
            session(['user_city' => $request->city]);
            session()->forget('no_auto');

            if ($request->has('lat') && $request->has('lng')) {
                session(['user_lat' => $request->lat, 'user_lng' => $request->lng]);
                if ($request->has('verified')) {
                    session(['location_verified' => true]);
                }
            } else {
                $lat = $lng = null;
                session()->forget(['user_lat', 'user_lng', 'location_verified']);
            }
        } else {
            // Server-side IP-based city auto-detection fallback
            if (!session('no_auto') && !$request->filled('city')) {
                try {
                    $ip = $request->ip();
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
                    // Fail silently
                }
            }
        }

        $cityContext = CityOperations::resolve($request->input('city'), session('user_city'));
        CityOperations::applyRoomCity($query, $cityContext);

        if ($lat && $lng && $locationVerified && !$request->filled('city') && !$cityContext['isFallback']) {
            $query->selectRaw("*, (6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance", [$lat, $lng, $lat])
                  ->orderBy('distance', 'asc');
        }

        $rooms = $query->orderBy('is_featured', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(5)
            ->withQueryString();

        $otherRooms = FacadesCache::remember('home.other_rooms.' . md5($cityContext['activeCityName'] ?? 'all'), 600, function () use ($cityContext) {
            return Room::publicVisible()
                ->when($cityContext['activeCityName'], fn($q) => $q->where('city', 'like', '%' . $cityContext['activeCityName'] . '%'))
                ->with(['user:id,name,avatar', 'propertyType', 'propertyCategory'])
                ->orderBy('is_featured', 'desc')
                ->orderBy('created_at', 'desc')
                ->take(32)
                ->get();
        });

        $otherRoomGroups = $otherRooms->filter(fn($room) => $room->propertyType?->id)
            ->groupBy(fn($room) => $room->propertyType->id)
            ->map(function ($group, $typeId) {
                $first = $group->first();

                return (object)[
                    'label' => $first->propertyType->name,
                    'params' => ['property_type_id' => $typeId],
                    'rooms' => $group->take(4),
                ];
            });

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

        $otherRoomGroups = $otherRoomGroups->filter(function ($group) {
            return $group->rooms->count() > 0;
        });

        $popularCities = CityOperations::selectorCities();

        // Room categories with dynamic counts from DB
        $propertyTypes = \App\Models\PropertyType::cachedActive();

        $propertyCategories = FacadesCache::remember('home.property_categories.' . md5($cityContext['activeCityName'] ?? 'all'), 600, function () use ($cityContext) {
            return Room::publicVisible()
                ->select('property_category_id', \DB::raw('count(*) as total'))
                ->when($cityContext['activeCityName'], fn ($q) => $q->where('city', 'like', '%' . $cityContext['activeCityName'] . '%'))
                ->whereNotNull('property_category_id')
                ->groupBy('property_category_id')
                ->orderByDesc('total')
                ->get()
                ->map(function ($item) {
                    $category = \App\Models\PropertyCategory::publicSelectable()->find($item->property_category_id);
                    if (! $category) {
                        return null;
                    }

                    $item->label = $category->name;
                    $item->property_type_id = $category->property_type_id;
                    $item->icon = 'fas fa-building';
                    return $item;
                })
                ->filter()
                ->values();
        });

        $latestBlogs = FacadesCache::remember('home.latest_blogs', 600, function () {
            return \App\Models\Blog::published()->orderBy('created_at', 'desc')->take(3)->get();
        });
        $faqs = json_decode((string) \App\Models\Setting::get('faq_content', '[]'), true);
        $faqs = is_array($faqs) ? collect($faqs)->filter(fn ($faq) => !empty($faq['question']) && !empty($faq['answer']))->values() : collect();
        $homeFeatures = FacadesCache::remember('home.home_features', 600, function () {
            return HomeFeature::active()->orderBy('sort_order')->orderBy('id')->take(6)->get();
        });
        $testimonials = FacadesCache::remember('home.testimonials', 600, function () {
            return Testimonial::active()->orderBy('sort_order')->orderByDesc('created_at')->take(6)->get();
        });

        // Hero room — cheapest featured/active room in current city
        $heroRoom = FacadesCache::remember('home.hero_room.' . md5($cityContext['activeCityName'] ?? 'all'), 600, function () use ($cityContext) {
            return Room::publicVisible()
                ->when($cityContext['activeCityName'], fn($q) => $q->where('city', 'like', '%' . $cityContext['activeCityName'] . '%'))
                ->orderByDesc('is_featured')
                ->orderBy('rent', 'asc')
                ->first();
        });

        // DB-based stats for hero section
        // Popular locations with listing counts
        $popularLocations = FacadesCache::remember('home.popular_locations', 3600, function () {
            return Room::publicVisible()
                ->select('city', \DB::raw('count(*) as total'))
                ->groupBy('city')
                ->orderByDesc('total')
                ->limit(12)
                ->get()
                ->map(fn($item) => (object)[
                    'name' => $item->city,
                    'total' => $item->total,
                    'slug' => \Illuminate\Support\Str::slug($item->city),
                ]);
        });

        $hiwItems = FacadesCache::remember('home.hiw_items', 3600, function () {
            return \App\Models\HowItWorksItem::active()
                ->orderBy('group')
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get()
                ->groupBy('group');
        });

        $ownerCtaItems = $hiwItems['owner_cta'] ?? collect();

        $totalRooms  = FacadesCache::remember('home.stats.total_rooms', 3600, fn() => Room::publicVisible()->count());
        $totalOwners = FacadesCache::remember('home.stats.total_owners', 3600, fn() => Room::publicVisible()->distinct('user_id')->count('user_id'));
        $totalUsers  = FacadesCache::remember('home.stats.total_users', 3600, fn() => User::where('role', 'user')->count());
        $totalAreas  = FacadesCache::remember('home.stats.total_areas.' . md5($cityContext['activeCityName'] ?? 'all'), 3600, function () use ($cityContext) {
            return Room::publicVisible()
                ->when($cityContext['activeCityName'], fn($q) => $q->where('city', 'like', '%' . $cityContext['activeCityName'] . '%'))
                ->distinct('city')->count('city');
        });

        $topAgencies = FacadesCache::remember('home.top_agencies.' . md5($cityContext['activeCityName'] ?? 'all'), 600, function () use ($cityContext) {
            User::cleanupExpiredFeaturedAgencies();

            $query = User::where('role', 'broker')
                ->where('is_broker_active', true)
                ->withCount([
                    'rooms as active_rooms_count' => function ($q) {
                        $q->publicVisible();
                    }
                ])
                ->orderByDesc('is_featured_agency')
                ->orderByDesc('active_rooms_count')
                ->take(6);

            if (!empty($cityContext['activeCityName'])) {
                $city = $cityContext['activeCityName'];
                $query->where(function ($q) use ($city) {
                    $q->where('city', 'like', '%' . $city . '%')
                      ->orWhereHas('rooms', function ($rq) use ($city) {
                          $rq->publicVisible()->where('city', 'like', '%' . $city . '%');
                      });
                });
            }

            $agencies = $query->get();

            if ($agencies->isEmpty()) {
                $agencies = User::where('role', 'broker')
                    ->where('is_broker_active', true)
                    ->withCount([
                        'rooms as active_rooms_count' => function ($q) {
                            $q->publicVisible();
                        }
                    ])
                    ->orderByDesc('is_featured_agency')
                    ->orderByDesc('active_rooms_count')
                    ->take(6)
                    ->get();
            }

            $agencies->load(['rooms' => function ($rq) {
                $rq->publicVisible()->select('rooms.id', 'rooms.broker_id', 'rooms.user_id', 'rooms.city');
            }]);

            return $agencies;
        });

        return view('home.index', compact(
            'rooms', 'otherRooms', 'otherRoomGroups', 'popularCities', 'popularLocations', 'propertyTypes', 'propertyCategories', 'latestBlogs',
            'homeFeatures', 'testimonials',
            'faqs',
            'heroRoom', 'totalRooms', 'totalOwners', 'totalUsers', 'totalAreas',
            'cityContext', 'hiwItems', 'ownerCtaItems', 'topAgencies'
        ));
    }
}
