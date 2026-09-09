<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MapSearchController extends Controller
{
    public function index(Request $request)
    {
        $query = Room::query()
            ->with(['owner:id,name', 'propertyType:id,name'])
            ->publicVisible()
            ->whereNotNull('latitude')
            ->whereNotNull('longitude');

        if ($request->filled('city')) {
            $query->where('city', 'like', '%' . $request->city . '%');
        }
        if ($request->filled('purpose') && in_array($request->purpose, ['rent', 'sell'], true)) {
            $query->where('purpose', $request->purpose);
        }
        if ($request->filled('min_rent')) {
            $query->where('rent', '>=', $request->min_rent);
        }
        if ($request->filled('max_rent')) {
            $query->where('rent', '<=', $request->max_rent);
        }
        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }
        if ($request->filled('property_type_id')) {
            $query->whereIn('property_type_id', (array) $request->property_type_id);
        }
        if ($request->filled('room_type')) {
            $query->where('room_type', $request->room_type);
        }

        $bounds = $this->parseBounds($request);
        $centerLat = $request->filled('lat') ? (float) $request->lat : null;
        $centerLng = $request->filled('lng') ? (float) $request->lng : null;
        $radius = $request->filled('radius') ? (float) $request->radius : null;

        if ($bounds) {
            [$west, $south, $east, $north] = $bounds;
            $query->whereBetween('latitude', [$south, $north])
                  ->whereBetween('longitude', [$west, $east]);
        } elseif ($centerLat !== null && $centerLng !== null && $radius !== null) {
            $query->selectRaw('*, (6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance', [$centerLat, $centerLng, $centerLat])
                ->having('distance', '<=', $radius)
                ->orderBy('distance', 'asc');
        } else {
            $query->orderBy('is_featured', 'desc')->orderBy('created_at', 'desc');
        }

        $rooms = $query->limit(500)->get();

        $markers = $rooms->map(function ($room) {
            $photo = $room->photo ?: (method_exists($room, 'photos') ? optional($room->photos()->first())->path : null);
            return [
                'id'           => $room->id,
                'slug'         => $room->slug,
                'title'        => $room->title,
                'purpose'      => $room->purpose ?? 'rent',
                'is_for_sell'  => $room->isForSell(),
                'rent'         => (float) $room->rent,
                'price'        => (float) ($room->price ?? 0),
                'display_price'=> $room->displayPrice(),
                'city'         => $room->city,
                'area'         => $room->address ?? $room->locality ?? null,
                'lat'          => (float) $room->latitude,
                'lng'          => (float) $room->longitude,
                'photo'        => null,
                'thumb'        => $photo ? (Str::startsWith($photo, ['http://', 'https://']) ? $photo : asset('storage/' . $photo)) : null,
                'is_featured'  => (bool) $room->is_featured,
                'property_type'=> $room->propertyType?->name,
                'url'          => route('rooms.show', $room),
            ];
        })->values();

        $cities = \App\Services\CityOperations::selectorCities();

        if ($request->wantsJson() || $request->ajax() || $request->input('format') === 'json') {
            return response()->json([
                'markers' => $markers,
                'count'   => $markers->count(),
            ]);
        }

        return view('rooms.map', compact('markers', 'cities'));
    }

    private function parseBounds(Request $request): ?array
    {
        $raw = $request->input('bounds');
        if (!$raw) {
            $raw = $request->input('bbox');
        }
        if ($raw && str_contains($raw, ',')) {
            $parts = array_map('trim', explode(',', $raw));
            if (count($parts) === 4) {
                $parts = array_map('floatval', $parts);
                [$west, $south, $east, $north] = $parts;
                if ($west < $east) [$west, $east] = [$east, $west];
                if ($south > $north) [$south, $north] = [$north, $south];
                return [$west, $south, $east, $north];
            }
        }
        return null;
    }
}
