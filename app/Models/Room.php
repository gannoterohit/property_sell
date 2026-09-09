<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $fillable = [
        'slug',
        'user_id',
        'title',
        'description',
        'type',
        'property_type_id',
        'property_category_id',
        'room_type_option_id',
        'furnishing_option_id',
        'tenant_option_id',
        'amenities',
        'purpose',
        'rent',
        'price',
        'deposit',
        'area_sqft',
        'possession_status',
        'ownership_type',
        'city',
        'state',
        'country',
        'address',
        'latitude',
        'longitude',
        'availability_from',
        'status',
        'listing_status',
        'video_url',
        'video',
        'photo',
        'photos',
        'landmarks',
        'is_featured',
        'listing_fee_paid',
        'listing_payment_id',
        'listing_type',
        'broker_fee',
        'broker_id',
        'expires_at',
        'moderation_status',
        'moderation_note',
        'features',
    ];

    protected $casts = [
        'is_featured'         => 'boolean',
        'listing_fee_paid'    => 'boolean',
        'area_sqft'           => 'decimal:2',
        'price'               => 'integer',
        'photos'              => 'array',
        'amenities'           => 'array',
        'landmarks'           => 'array',
        'features'            => 'array',
        'expires_at'          => 'datetime',
    ];

    // =========================================================
    // SELL / RENT HELPER METHODS
    // =========================================================

    /**
     * Is this a "For Rent" listing?
     */
    public function isForRent(): bool
    {
        return ($this->purpose ?? 'rent') !== 'sell';
    }

    /**
     * Is this a "For Sale" listing?
     */
    public function isForSell(): bool
    {
        return ($this->purpose ?? 'rent') === 'sell';
    }

    /**
     * Smart price display:
     * - Rent: "₹15,000/mo"
     * - Sell: "₹45.5 L" or "₹1.2 Cr"
     */
    public function displayPrice(): string
    {
        if ($this->isForSell()) {
            $price = (int) ($this->price ?? 0);
            if ($price >= 10_000_000) {
                return '₹' . rtrim(rtrim(number_format($price / 10_000_000, 2), '0'), '.') . ' Cr';
            }
            if ($price >= 100_000) {
                return '₹' . rtrim(rtrim(number_format($price / 100_000, 2), '0'), '.') . ' L';
            }
            return '₹' . number_format($price);
        }
        return '₹' . number_format((int) ($this->rent ?? 0)) . '/mo';
    }

    /**
     * Human-readable possession status label.
     */
    public function possessionLabel(): string
    {
        return match ($this->possession_status) {
            'ready_to_move'      => 'Ready to Move',
            'under_construction' => 'Under Construction',
            default              => 'N/A',
        };
    }

    /**
     * Helper to retrieve an attribute from features JSON.
     */
    public function feature(string $key, mixed $default = null): mixed
    {
        return data_get($this->features, $key, $default);
    }

    public function isCommercial(): bool
    {
        $typeName = strtolower($this->propertyType?->slug ?? $this->propertyType?->name ?? '');
        return in_array($typeName, ['shop', 'office', 'showroom', 'warehouse'], true) || !empty($this->feature('commercial_type'));
    }

    public function isPlot(): bool
    {
        $typeName = strtolower($this->propertyType?->slug ?? $this->propertyType?->name ?? '');
        return str_contains($typeName, 'plot') || str_contains($typeName, 'land');
    }

    public function ratePerSqft(): ?float
    {
        $area = (float) ($this->feature('super_builtup_area') ?: ($this->feature('carpet_area') ?: $this->area_sqft));
        if ($area <= 0) return null;

        if ($this->isForSell() && $this->price > 0) {
            return round($this->price / $area);
        }
        if ($this->isForRent() && $this->rent > 0) {
            return round($this->rent / $area, 1);
        }
        return null;
    }

    // =========================================================
    // QUERY SCOPES FOR PURPOSE
    // =========================================================

    /** Scope: only rent listings */
    public function scopeForRent($query)
    {
        return $query->where('purpose', 'rent');
    }

    /** Scope: only sell listings */
    public function scopeForSell($query)
    {
        return $query->where('purpose', 'sell');
    }

    /** Scope: filter by purpose (rent|sell), or no filter if null/empty */
    public function scopePurpose($query, ?string $purpose)
    {
        if ($purpose && in_array($purpose, ['rent', 'sell'], true)) {
            return $query->where('purpose', $purpose);
        }
        return $query;
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function expiresInDays(): ?int
    {
        if (!$this->expires_at) return null;
        return (int) now()->diffInDays($this->expires_at, false);
    }

    /**
     * Anti-bypass: Detect direct phone numbers or contact phrases in title/description.
     */
    public function detectDirectContactInfo(): array
    {
        $text = ($this->title ?? '') . ' ' . ($this->description ?? '');
        $found = [];

        // Match standard 10-digit Indian numbers starting with 6, 7, 8, 9
        if (preg_match_all('/\b(?:(?:\+|0{0,2})91[\s-]*)?[6-9]\d{9}\b/', $text, $matches)) {
            foreach ($matches[0] as $m) {
                $found[] = trim($m);
            }
        }

        // Match spaced numbers like 98765 43210 or 9876-543-210
        if (preg_match_all('/\b[6-9]\d{4}[\s-]\d{5}\b/', $text, $matches)) {
            foreach ($matches[0] as $m) {
                $found[] = trim($m);
            }
        }

        // Match evasion keywords like "call me at 98...", "whatsapp on 98..."
        if (preg_match_all('/(?:call|whatsapp|contact|ph(?:one)?|mob(?:ile)?)\s*(?:me|on|at|no|number)?\s*[:=\-]?\s*(\d{5,12})/i', $text, $matches)) {
            foreach ($matches[0] as $m) {
                $found[] = trim($m);
            }
        }

        $found = array_unique($found);

        return [
            'flagged' => !empty($found),
            'count' => count($found),
            'matches' => array_slice($found, 0, 3),
        ];
    }

    /**
     * Detect suspected duplicate listings in the same city with identical address or title/rent.
     */
    public function getSuspectedDuplicates(int $limit = 3)
    {
        if (!$this->exists) {
            return collect();
        }

        $query = static::where('id', '!=', $this->id);

        if ($this->city) {
            $query->where('city', $this->city);
        }

        $cleanAddress = trim((string)$this->address);
        $cleanTitle = trim((string)$this->title);

        $query->where(function ($q) use ($cleanAddress, $cleanTitle) {
            if (strlen($cleanAddress) >= 6) {
                $q->where('address', $cleanAddress);
            }
            if (strlen($cleanTitle) >= 6) {
                $q->orWhere(function ($sq) use ($cleanTitle) {
                    $sq->where('title', $cleanTitle)
                       ->where('rent', $this->rent);
                });
            }
        });

        return $query->limit($limit)->get(['id', 'slug', 'title', 'rent', 'city', 'address', 'listing_status', 'user_id', 'created_at']);
    }


    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }

    /**
     * Retrieve the model for a bound value.
     *
     * @param  mixed  $value
     * @param  string|null  $field
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where('slug', $value)
            ->orWhere('id', $value)
            ->first();
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($room) {
            if (!$room->slug) {
                $room->slug = static::generateUniqueSlug($room->title);
            }
        });

        static::updating(function ($room) {
            if ($room->isDirty('title') && !$room->isDirty('slug')) {
                $room->slug = static::generateUniqueSlug($room->title);
            }
        });

        static::deleting(function ($room) {
            foreach ($room->publicMediaPaths() as $path) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($path);
            }
        });
    }

    /**
     * Generate a unique slug.
     */
    public static function generateUniqueSlug($title)
    {
        $slug = \Illuminate\Support\Str::slug($title);
        $originalSlug = $slug;
        $count = 1;

        while (static::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $count++;
        }

        return $slug;
    }

    
    public function owner() {
        return $this->belongsTo(User::class,'user_id');
    }

    /**
     * Alias for owner relationship
     */
    public function user() {
        return $this->belongsTo(User::class,'user_id');
    }

    public function propertyType()
    {
        return $this->belongsTo(PropertyType::class, 'property_type_id');
    }

    public function propertyCategory()
    {
        return $this->belongsTo(PropertyCategory::class, 'property_category_id');
    }

    public function roomTypeOption()
    {
        return $this->belongsTo(RoomOption::class, 'room_type_option_id');
    }

    public function furnishingOption()
    {
        return $this->belongsTo(RoomOption::class, 'furnishing_option_id');
    }

    public function tenantOption()
    {
        return $this->belongsTo(RoomOption::class, 'tenant_option_id');
    }


    public function complaints() {
        return $this->hasMany(Complaint::class);
    }
    
    public function rejectionReasons()
    {
        return $this->belongsToMany(RejectionReason::class, 'room_rejection_reason');
    }

    public function getPhotoUrlAttribute()
    {
        $url = null;
        if (!$this->photo) {
            if ($this->photos && count($this->photos) > 0) {
                $url = $this->photos[0];
            } else {
                return asset('assets/images/default-room.svg');
            }
        } else {
            $url = $this->photo;
        }

        $finalUrl = $this->resolvePublicMediaUrl($url);

        // Optimize Unsplash URLs
        if (str_contains($finalUrl, 'images.unsplash.com')) {
            if (!str_contains($finalUrl, '?') && !str_contains($finalUrl, '&')) {
                $finalUrl .= '?auto=format&fit=crop&w=400&q=60&fm=webp';
            } elseif (str_contains($finalUrl, 'w=800')) {
                $finalUrl = str_replace('w=800', 'w=400', $finalUrl);
                if (!str_contains($finalUrl, 'fm=')) $finalUrl .= '&fm=webp';
                if (!str_contains($finalUrl, 'q=')) $finalUrl .= '&q=60';
            }
        }

        return $finalUrl;
    }

    public function getPhotoUrlsAttribute()
    {
        $urls = [];
        if ($this->photos && is_array($this->photos)) {
            foreach ($this->photos as $photo) {
                $urls[] = $this->resolvePublicMediaUrl($photo);
            }
        }
        
        if (empty($urls) && $this->photo) {
            $urls[] = $this->resolvePublicMediaUrl($this->photo);
        }

        return $urls;
    }

    public function publicMediaPaths(): array
    {
        $paths = array_merge([$this->photo], $this->photos ?: [], [$this->video]);

        return collect($paths)
            ->filter(fn ($path) => is_string($path) && $path !== '' && ! preg_match('/^https?:\/\//', $path))
            ->map(function ($path) {
                $path = ltrim($path, '/');
                return str_starts_with($path, 'storage/') ? substr($path, strlen('storage/')) : $path;
            })
            ->unique()
            ->values()
            ->all();
    }

    private function resolvePublicMediaUrl(?string $path): string
    {
        if (!$path) {
            return asset('assets/images/default-room.svg');
        }

        if (preg_match('/^https?:\/\//', $path)) {
            return $path;
        }

        $path = ltrim($path, '/');
        if (str_starts_with($path, 'storage/')) {
            $path = substr($path, strlen('storage/'));
        }

        if (!\Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
            return asset('assets/images/default-room.svg');
        }

        return asset('storage/' . $path);
    }

    public function roomTypeLabel(): string
    {
        if ($this->relationLoaded('roomTypeOption') && $this->roomTypeOption) {
            return $this->roomTypeOption->label;
        }

        return RoomOption::getLabel('room_type', $this->room_type_option_id);
    }

    public function furnishingTypeLabel(): string
    {
        if ($this->relationLoaded('furnishingOption') && $this->furnishingOption) {
            return $this->furnishingOption->label;
        }

        return RoomOption::getLabel('furnishing_type', $this->furnishing_option_id);
    }

    public function tenantTypeLabel(): string
    {
        if ($this->relationLoaded('tenantOption') && $this->tenantOption) {
            return $this->tenantOption->label;
        }

        return RoomOption::getLabel('tenant_type', $this->tenant_option_id);
    }

    public function scopePublicVisible($query)
    {
        return $query->where('status', 'active')
            ->where('listing_status', 'approved')
            ->where('listing_fee_paid', true)
            ->whereHas('propertyType', fn ($type) => $type->active())
            ->whereHas('propertyCategory', fn ($category) => $category->publicSelectable())
            ->where(fn ($room) => $room->whereNull('room_type_option_id')->orWhereHas('roomTypeOption', fn ($option) => $option->active()))
            ->where(fn ($room) => $room->whereNull('furnishing_option_id')->orWhereHas('furnishingOption', fn ($option) => $option->active()))
            ->where(fn ($room) => $room->whereNull('tenant_option_id')->orWhereHas('tenantOption', fn ($option) => $option->active()));
    }

    public function publicAmenities(): array
    {
        $activeLabels = RoomOption::activeLabelsFor('amenity')
            ->map(fn ($label) => mb_strtolower((string) $label))
            ->all();

        return collect($this->amenities ?: [])
            ->filter(fn ($amenity) => in_array(mb_strtolower((string) $amenity), $activeLabels, true))
            ->values()
            ->all();
    }
}
