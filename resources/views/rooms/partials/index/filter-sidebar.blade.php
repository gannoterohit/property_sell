<!-- ===== LEFT SIDEBAR (FILTERS) ===== -->
<div class="rooms-filter-panel flex flex-col h-full bg-white">
    <!-- Header -->
    <div class="rooms-filter-header flex items-center justify-between border-b border-slate-200/80 px-4 py-3.5 shrink-0 bg-white">
        <div class="flex items-center gap-2">
            <div class="w-7 h-7 rounded-lg bg-indigo-600 text-white flex items-center justify-center text-xs shadow-xs">
                <i class="fas fa-sliders"></i>
            </div>
            <h3 class="font-black text-slate-900 text-sm tracking-tight">Filters</h3>
        </div>
        <a href="{{ route('rooms.index', array_filter(['clear' => 1, 'purpose' => request('purpose')])) }}" 
           class="text-[11px] font-bold text-rose-600 hover:text-rose-700 bg-rose-50 hover:bg-rose-100 border border-rose-200/60 px-2.5 py-1 rounded-lg transition-colors flex items-center gap-1">
            <i class="fas fa-rotate-left text-[9px]"></i> Reset
        </a>
    </div>

    <style>
        .rooms-filter-panel .hover-text-primary:hover { color: var(--primary) !important; }
        .rooms-filter-panel .text-primary { color: var(--primary) !important; }
        .rooms-filter-panel .bg-primary-soft { background: rgba(var(--primary-rgb), 0.08) !important; }
        .rooms-filter-panel .text-primary-soft { color: var(--primary) !important; }
        .rooms-filter-panel input[type="checkbox"]:checked { background-color: var(--primary); border-color: var(--primary); }
        .rooms-filter-panel input[type="radio"]:checked { background-color: var(--primary); border-color: var(--primary); }
        .rooms-filter-panel input:focus { border-color: var(--primary) !important; box-shadow: 0 0 0 3px rgba(var(--primary-rgb), 0.15) !important; }
        .rooms-filter-panel select:focus { border-color: var(--primary) !important; }
        .room-theme-primary-button { background: #059669; color: #fff; }
        .room-theme-primary-button:hover { background: #047857; }
    </style>

    <form action="{{ route('rooms.index') }}" method="GET" class="rooms-filter-form flex flex-col flex-1 min-h-0">
        <div class="rooms-filter-scroll flex-1 overflow-y-auto px-4 py-3.5 space-y-4">
                    <!-- Locality Input -->
                    <div class="space-y-1.5 pb-3 border-b border-slate-100">
                        <label class="text-xs font-black text-slate-800 uppercase tracking-wider block flex items-center gap-1.5">
                            <i class="fas fa-location-dot text-rose-500 text-xs"></i> Location
                        </label>
                        <input type="text" name="city" value="{{ request('city') }}" placeholder="Enter locality or area..."
                               class="w-full py-2 px-3 bg-slate-50 hover:bg-white focus:bg-white border border-slate-200 focus:border-indigo-500 text-slate-800 placeholder-slate-400 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-indigo-100 outline-none transition-all shadow-2xs">
                        
                        {{-- City dropdown — fully dynamic from DB popular cities --}}
                        <select name="city_dropdown" onchange="if(this.value){ document.querySelector('input[name=city]').value = this.value; }"
                                class="w-full py-2 px-3 bg-slate-50 hover:bg-white focus:bg-white border border-slate-200 focus:border-indigo-500 text-slate-700 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-indigo-100 outline-none appearance-none transition-all shadow-2xs">
                            <option value="">Select Popular City</option>
                            @foreach($popularCities as $pCity)
                                <option value="{{ $pCity->name }}" {{ $displayCity === $pCity->name ? 'selected' : '' }}>
                                    {{ $pCity->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Purpose Filter -->
                    <div class="space-y-1.5 pb-3 border-b border-slate-100">
                        <label class="text-xs font-black text-slate-800 uppercase tracking-wider block flex items-center gap-1.5">
                            <i class="fas fa-compass text-indigo-500 text-xs"></i> I'm Looking To
                        </label>
                        <div class="grid grid-cols-2 gap-2">
                            <label id="purposeTabRent" class="flex items-center justify-center gap-1.5 cursor-pointer text-xs font-bold py-2 rounded-xl border transition-all shadow-2xs
                                {{ ($currentPurpose ?? '') === 'sell' ? 'border-slate-200 text-slate-600 bg-white hover:bg-slate-50' : 'border-indigo-600 text-indigo-700 bg-indigo-50 font-extrabold ring-1 ring-indigo-500/20' }}">
                                <input type="radio" name="purpose" value="rent"
                                    {{ ($currentPurpose ?? '') !== 'sell' ? 'checked' : '' }}
                                    class="hidden purpose-radio">
                                <i class="fas fa-key text-[10px]"></i> Rent
                            </label>
                            <label id="purposeTabSell" class="flex items-center justify-center gap-1.5 cursor-pointer text-xs font-bold py-2 rounded-xl border transition-all shadow-2xs
                                {{ ($currentPurpose ?? '') === 'sell' ? 'border-purple-600 text-purple-700 bg-purple-50 font-extrabold ring-1 ring-purple-500/20' : 'border-slate-200 text-slate-600 bg-white hover:bg-slate-50' }}">
                                <input type="radio" name="purpose" value="sell"
                                    {{ ($currentPurpose ?? '') === 'sell' ? 'checked' : '' }}
                                    class="hidden purpose-radio">
                                <i class="fas fa-tag text-[10px]"></i> Buy / Sell
                            </label>
                        </div>
                    </div>

                    <!-- Listed By Filter -->
                    <div class="space-y-1.5 pb-3 border-b border-slate-100">
                        <label class="text-xs font-black text-slate-800 uppercase tracking-wider block flex items-center gap-1.5">
                            <i class="fas fa-user-shield text-emerald-600 text-xs"></i> Listed By
                        </label>
                        <div class="space-y-1.5 bg-slate-50/70 p-2.5 rounded-xl border border-slate-200/80">
                            <label class="flex items-center gap-2 text-xs text-slate-700 font-semibold cursor-pointer hover:text-indigo-600 transition-colors">
                                <input type="radio" name="listing_type" value="" {{ !request('listing_type') ? 'checked' : '' }}
                                       class="accent-indigo-600 w-3.5 h-3.5">
                                <span>All Listings</span>
                            </label>
                            <label class="flex items-center gap-2 text-xs text-slate-700 font-semibold cursor-pointer hover:text-emerald-700 transition-colors">
                                <input type="radio" name="listing_type" value="owner" {{ request('listing_type') === 'owner' ? 'checked' : '' }}
                                       class="accent-emerald-600 w-3.5 h-3.5">
                                <span class="flex items-center gap-1 text-emerald-700 font-bold"><i class="fas fa-shield-check text-[11px]"></i> Direct Owner (0%)</span>
                            </label>
                            <label class="flex items-center gap-2 text-xs text-slate-700 font-semibold cursor-pointer hover:text-amber-700 transition-colors">
                                <input type="radio" name="listing_type" value="broker" {{ request('listing_type') === 'broker' ? 'checked' : '' }}
                                       class="accent-amber-500 w-3.5 h-3.5">
                                <span class="flex items-center gap-1 text-amber-700 font-bold"><i class="fas fa-certificate text-[11px]"></i> Verified Agent</span>
                            </label>
                        </div>
                    </div>

                    <!-- Property Type — dynamic: only shows types that exist in DB -->
                    <div class="space-y-1.5 pb-3 border-b border-slate-100">
                        <label class="text-xs font-black text-slate-800 uppercase tracking-wider block flex items-center gap-1.5">
                            <i class="fas fa-building text-indigo-500 text-xs"></i> Property Type
                        </label>
                        <div class="space-y-2">
                            @forelse($propertyTypes as $type)
                                @php
                                    $count = $propertyTypeCounts[$type->id] ?? 0;
                                    $isChecked = in_array($type->id, (array)request('property_type_id'));
                                @endphp
                                <label class="flex items-center justify-between text-xs text-slate-700 font-semibold cursor-pointer hover:text-indigo-600 transition-colors">
                                    <span class="flex items-center gap-2">
                                        <input type="checkbox" name="property_type_id[]" value="{{ $type->id }}" {{ $isChecked ? 'checked' : '' }}
                                               class="rounded border-slate-300 accent-indigo-600 w-3.5 h-3.5">
                                        <span>{{ $type->name }}</span>
                                    </span>
                                    <span class="text-[10px] text-slate-500 font-bold bg-slate-100 px-2 py-0.5 rounded-full">{{ $count }}</span>
                                </label>
                            @empty
                                <p class="rounded-lg bg-amber-50 px-3 py-2 text-[11px] font-semibold text-amber-700">
                                    No active property types configured.
                                </p>
                            @endforelse
                        </div>
                    </div>

                    <!-- Property Category -->
                    <div class="space-y-1.5 pb-3 border-b border-slate-100">
                        <label class="text-xs font-black text-slate-800 uppercase tracking-wider block flex items-center gap-1.5">
                            <i class="fas fa-layer-group text-blue-500 text-xs"></i> Property Category
                        </label>
                        <div class="space-y-2">
                            @forelse(($propertyCategories ?? collect()) as $category)
                                @php
                                    $count = $propertyCategoryCounts[$category->id] ?? 0;
                                    $isChecked = in_array($category->id, (array)request('property_category_id'));
                                @endphp
                                @if($count > 0 || $isChecked)
                                    <label class="flex items-center justify-between text-xs text-slate-700 font-semibold cursor-pointer hover:text-indigo-600 transition-colors">
                                        <span class="flex items-center gap-2">
                                            <input type="checkbox" name="property_category_id[]" value="{{ $category->id }}" {{ $isChecked ? 'checked' : '' }}
                                                   class="rounded border-slate-300 accent-indigo-600 w-3.5 h-3.5">
                                            <span>{{ $category->name }}</span>
                                        </span>
                                        <span class="text-[10px] text-slate-500 font-bold bg-slate-100 px-2 py-0.5 rounded-full">{{ $count }}</span>
                                    </label>
                                @endif
                            @empty
                                <p class="rounded-lg bg-amber-50 px-3 py-2 text-[11px] font-semibold text-amber-700">
                                    No active property categories configured.
                                </p>
                            @endforelse
                        </div>
                    </div>

                    <!-- Budget Range — conditional: Rent or Sell price -->
                    <div class="space-y-1.5 pb-3 border-b border-slate-100" id="filterBudgetSection">
                        <!-- Rent Budget (shown when purpose=rent) -->
                        <div id="filterRentBudget" class="{{ ($currentPurpose ?? '') === 'sell' ? 'hidden' : '' }} space-y-2">
                            <label class="text-xs font-black text-slate-800 uppercase tracking-wider block flex items-center gap-1.5">
                                <i class="fas fa-wallet text-amber-500 text-xs"></i> Budget (per month)
                            </label>
                            <div class="space-y-1.5">
                                @php
                                    $budgetRanges = [
                                        ['label' => 'Under ₹5,000',       'min' => 0,     'max' => 5000],
                                        ['label' => '₹5,000 - ₹10,000',  'min' => 5000,  'max' => 10000],
                                        ['label' => '₹10,000 - ₹15,000', 'min' => 10000, 'max' => 15000],
                                        ['label' => '₹15,000 - ₹20,000', 'min' => 15000, 'max' => 20000],
                                        ['label' => 'Above ₹20,000',       'min' => 20000, 'max' => 999999],
                                    ];
                                @endphp
                                @foreach($budgetRanges as $range)
                                    @php $isSel = request('min_rent') == $range['min'] && request('max_rent') == $range['max']; @endphp
                                    <label class="flex items-center gap-2 text-xs text-slate-700 font-semibold cursor-pointer hover:text-indigo-600 transition-colors">
                                        <input type="radio" name="budget_range" onchange="document.querySelector('input[name=min_rent]').value='{{ $range['min'] }}'; document.querySelector('input[name=max_rent]').value='{{ $range['max'] }}';"
                                               {{ $isSel ? 'checked' : '' }}
                                               class="accent-indigo-600 w-3.5 h-3.5">
                                        <span>{{ $range['label'] }}</span>
                                    </label>
                                @endforeach
                            </div>
                            <div class="grid grid-cols-2 gap-2 pt-1.5">
                                <div class="space-y-1">
                                    <span class="text-[10px] font-bold text-slate-500 uppercase">Min (₹)</span>
                                    <input type="number" name="min_rent" value="{{ request('min_rent') }}" placeholder="Min"
                                           class="w-full py-1.5 px-2.5 bg-slate-50 hover:bg-white focus:bg-white border border-slate-200 text-slate-800 rounded-xl text-xs font-semibold focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 outline-none transition-all">
                                </div>
                                <div class="space-y-1">
                                    <span class="text-[10px] font-bold text-slate-500 uppercase">Max (₹)</span>
                                    <input type="number" name="max_rent" value="{{ request('max_rent') }}" placeholder="Max"
                                           class="w-full py-1.5 px-2.5 bg-slate-50 hover:bg-white focus:bg-white border border-slate-200 text-slate-800 rounded-xl text-xs font-semibold focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 outline-none transition-all">
                                </div>
                            </div>
                        </div>

                        <!-- Sell Price Range (shown when purpose=sell) -->
                        <div id="filterSellPrice" class="{{ ($currentPurpose ?? '') === 'sell' ? '' : 'hidden' }} space-y-2">
                            <label class="text-xs font-black text-slate-800 uppercase tracking-wider block flex items-center gap-1.5">
                                <i class="fas fa-indian-rupee-sign text-emerald-600 text-xs"></i> Price Range
                            </label>
                            <div class="space-y-1.5">
                                @php
                                    $priceRanges = [
                                        ['label' => 'Under ₹25 L',         'min' => 0,         'max' => 2500000],
                                        ['label' => '₹25L - ₹50L',         'min' => 2500000,   'max' => 5000000],
                                        ['label' => '₹50L - ₹1 Cr',        'min' => 5000000,   'max' => 10000000],
                                        ['label' => '₹1 Cr - ₹2 Cr',       'min' => 10000000,  'max' => 20000000],
                                        ['label' => 'Above ₹2 Cr',          'min' => 20000000,  'max' => 999999999],
                                    ];
                                @endphp
                                @foreach($priceRanges as $range)
                                    @php $isPSel = request('min_price') == $range['min'] && request('max_price') == $range['max']; @endphp
                                    <label class="flex items-center gap-2 text-xs text-slate-700 font-semibold cursor-pointer hover:text-indigo-600 transition-colors">
                                        <input type="radio" name="price_range" onchange="document.querySelector('input[name=min_price]').value='{{ $range['min'] }}'; document.querySelector('input[name=max_price]').value='{{ $range['max'] }}';"
                                               {{ $isPSel ? 'checked' : '' }}
                                               class="accent-indigo-600 w-3.5 h-3.5">
                                        <span>{{ $range['label'] }}</span>
                                    </label>
                                @endforeach
                            </div>
                            <div class="grid grid-cols-2 gap-2 pt-1.5">
                                <div class="space-y-1">
                                    <span class="text-[10px] font-bold text-slate-500 uppercase">Min (₹)</span>
                                    <input type="number" name="min_price" value="{{ request('min_price') }}" placeholder="Min"
                                           class="w-full py-1.5 px-2.5 bg-slate-50 hover:bg-white focus:bg-white border border-slate-200 text-slate-800 rounded-xl text-xs font-semibold focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 outline-none transition-all">
                                </div>
                                <div class="space-y-1">
                                    <span class="text-[10px] font-bold text-slate-500 uppercase">Max (₹)</span>
                                    <input type="number" name="max_price" value="{{ request('max_price') }}" placeholder="Max"
                                           class="w-full py-1.5 px-2.5 bg-slate-50 hover:bg-white focus:bg-white border border-slate-200 text-slate-800 rounded-xl text-xs font-semibold focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 outline-none transition-all">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Possession Status (only for sell) -->
                    <div id="filterPossessionSection" class="space-y-1.5 pb-3 border-b border-slate-100 {{ ($currentPurpose ?? '') === 'sell' ? '' : 'hidden' }}">
                        <label class="text-xs font-black text-slate-800 uppercase tracking-wider block flex items-center gap-1.5">
                            <i class="fas fa-key text-emerald-500 text-xs"></i> Possession Status
                        </label>
                        <div class="space-y-1.5">
                            <label class="flex items-center gap-2 text-xs text-slate-700 font-semibold cursor-pointer hover:text-indigo-600 transition-colors">
                                <input type="radio" name="possession_status" value="" {{ !request('possession_status') ? 'checked' : '' }} class="accent-indigo-600 w-3.5 h-3.5"> Any
                            </label>
                            <label class="flex items-center gap-2 text-xs text-slate-700 font-semibold cursor-pointer hover:text-emerald-700 transition-colors">
                                <input type="radio" name="possession_status" value="ready_to_move" {{ request('possession_status') === 'ready_to_move' ? 'checked' : '' }} class="accent-emerald-600 w-3.5 h-3.5">
                                <span class="flex items-center gap-1 text-emerald-700 font-bold"><i class="fas fa-circle-check text-[11px]"></i> Ready to Move</span>
                            </label>
                            <label class="flex items-center gap-2 text-xs text-slate-700 font-semibold cursor-pointer hover:text-amber-700 transition-colors">
                                <input type="radio" name="possession_status" value="under_construction" {{ request('possession_status') === 'under_construction' ? 'checked' : '' }} class="accent-amber-500 w-3.5 h-3.5">
                                <span class="flex items-center gap-1 text-amber-700 font-bold"><i class="fas fa-hard-hat text-[11px]"></i> Under Construction</span>
                            </label>
                        </div>
                    </div>

                    <!-- Area Filter (sq ft) -->
                    <div class="space-y-1.5 pb-3 border-b border-slate-100" id="filterTenantSection" {{ ($currentPurpose ?? '') === 'sell' ? 'style=display:none' : '' }}>
                        <label class="text-xs font-black text-slate-800 uppercase tracking-wider block flex items-center gap-1.5">
                            <i class="fas fa-ruler-combined text-teal-600 text-xs"></i> Area (sq ft)
                        </label>
                        <div class="grid grid-cols-2 gap-2">
                            <div class="space-y-1">
                                <span class="text-[10px] font-bold text-slate-500 uppercase">Min</span>
                                <input type="number" name="min_area_sqft" value="{{ request('min_area_sqft') }}" placeholder="Min sqft"
                                       class="w-full py-1.5 px-2.5 bg-slate-50 hover:bg-white focus:bg-white border border-slate-200 text-slate-800 rounded-xl text-xs font-semibold focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 outline-none transition-all">
                            </div>
                            <div class="space-y-1">
                                <span class="text-[10px] font-bold text-slate-500 uppercase">Max</span>
                                <input type="number" name="max_area_sqft" value="{{ request('max_area_sqft') }}" placeholder="Max sqft"
                                       class="w-full py-1.5 px-2.5 bg-slate-50 hover:bg-white focus:bg-white border border-slate-200 text-slate-800 rounded-xl text-xs font-semibold focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 outline-none transition-all">
                            </div>
                        </div>
                    </div>

                    <!-- Gender / Tenant Preference -->
                    <div class="space-y-1.5 pb-3 border-b border-slate-100">
                        <label class="text-xs font-black text-slate-800 uppercase tracking-wider block flex items-center gap-1.5">
                            <i class="fas fa-users text-pink-500 text-xs"></i> Tenant Preference
                        </label>
                        <div class="space-y-2">
                            @foreach(App\Models\RoomOption::optionsFor('tenant_type') as $option)
                                @php
                                    $tCount = $tenantTypeCounts[$option->id] ?? 0;
                                    $tChecked = in_array($option->id, (array)request('tenant_type'));
                                @endphp
                                @if($tCount > 0 || $tChecked)
                                    <label class="flex items-center justify-between text-xs text-slate-700 font-semibold cursor-pointer hover:text-indigo-600 transition-colors">
                                        <span class="flex items-center gap-2">
                                            <input type="checkbox" name="tenant_type[]" value="{{ $option->id }}" {{ $tChecked ? 'checked' : '' }}
                                                   class="rounded border-slate-300 accent-indigo-600 w-3.5 h-3.5">
                                            <span>{{ $option->label }}</span>
                                        </span>
                                        <span class="text-[10px] text-slate-500 font-bold bg-slate-100 px-2 py-0.5 rounded-full">{{ $tCount }}</span>
                                    </label>
                                @endif
                            @endforeach
                        </div>
                    </div>

                    <!-- Furnishing Type — dynamic from DB -->
                    <div class="space-y-1.5 pb-3 border-b border-slate-100">
                        <label class="text-xs font-black text-slate-800 uppercase tracking-wider block flex items-center gap-1.5">
                            <i class="fas fa-couch text-amber-600 text-xs"></i> Furnishing
                        </label>
                        <div class="space-y-2">
                            @foreach(App\Models\RoomOption::optionsFor('furnishing_type') as $option)
                                @php
                                    $fCount = $furnishingCounts[$option->id] ?? 0;
                                    $fChecked = in_array($option->id, (array)request('furnishing_type'));
                                @endphp
                                @if($fCount > 0 || $fChecked)
                                    <label class="flex items-center justify-between text-xs text-slate-700 font-semibold cursor-pointer hover:text-indigo-600 transition-colors">
                                        <span class="flex items-center gap-2">
                                            <input type="checkbox" name="furnishing_type[]" value="{{ $option->id }}" {{ $fChecked ? 'checked' : '' }}
                                                   class="rounded border-slate-300 accent-indigo-600 w-3.5 h-3.5">
                                            <span>{{ $option->label }}</span>
                                        </span>
                                        <span class="text-[10px] text-slate-500 font-bold bg-slate-100 px-2 py-0.5 rounded-full">{{ $fCount }}</span>
                                    </label>
                                @endif
                            @endforeach
                        </div>
                    </div>

                    <!-- Facilities -->
                    <div class="space-y-1.5 pb-3 border-b border-slate-100">
                        @php
                            $selectedAmenities = (array) request('amenities');
                        @endphp
                        <div class="flex items-center justify-between gap-2">
                            <label class="text-xs font-black text-slate-800 uppercase tracking-wider block flex items-center gap-1.5">
                                <i class="fas fa-sparkles text-indigo-500 text-xs"></i> Facilities
                            </label>
                            @if(count($selectedAmenities))
                                <span class="rounded-full bg-indigo-50 px-2 py-0.5 text-[10px] font-bold text-indigo-700">{{ count($selectedAmenities) }} selected</span>
                            @endif
                        </div>
                        <div class="rooms-amenities-scroll space-y-2">
                            @php
                                $amenityOpts = \App\Models\RoomOption::optionsFor('amenity')
                                    ->pluck('label', 'label')
                                    ->all();
                            @endphp
                            @foreach($amenityOpts as $key => $lbl)
                                <label class="flex items-center gap-2 text-xs text-slate-700 font-semibold cursor-pointer hover:text-indigo-600 transition-colors">
                                    <input type="checkbox" name="amenities[]" value="{{ $key }}" {{ in_array($key, (array)request('amenities')) ? 'checked' : '' }}
                                           class="rounded border-slate-300 accent-indigo-600 w-3.5 h-3.5">
                                    <span>{{ $lbl }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Availability -->
                    <div class="space-y-1.5 pb-2">
                        <label class="text-xs font-black text-slate-800 uppercase tracking-wider block flex items-center gap-1.5">
                            <i class="fas fa-calendar-check text-emerald-600 text-xs"></i> Availability
                        </label>
                        <div class="space-y-2">
                            <label class="flex items-center gap-2 text-xs text-slate-700 font-semibold cursor-pointer hover:text-indigo-600 transition-colors">
                                <input type="checkbox" name="available_now" value="1" {{ request('available_now') == '1' ? 'checked' : '' }}
                                       class="rounded border-slate-300 accent-indigo-600 w-3.5 h-3.5">
                                <span class="font-bold text-emerald-700">Available Now</span>
                            </label>
                            
                            <div class="space-y-1 pt-1">
                                <span class="text-[10px] font-bold text-slate-500 uppercase">Available From</span>
                                <input type="date" name="availability_from" value="{{ request('availability_from') }}"
                                       class="w-full py-1.5 px-2.5 bg-slate-50 hover:bg-white focus:bg-white border border-slate-200 text-slate-800 rounded-xl text-xs font-semibold focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 outline-none transition-all">
                            </div>
                        </div>
                    </div>
        </div>

        <!-- Submit Button (Sticky at bottom of sidebar pane) -->
        <div class="rooms-filter-actions px-4 py-3 border-t border-slate-200/90 bg-white shrink-0 shadow-lg">
            <button type="submit" class="w-full py-2.5 px-4 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-black text-xs rounded-xl shadow-md hover:shadow-lg transition-all flex items-center justify-center gap-2 tracking-wide cursor-pointer">
                <i class="fas fa-check-circle text-xs"></i> Apply Filters
            </button>
        </div>
    </form>

    <script>
    // Purpose Tab Toggle JS
    (function() {
        const rentTab   = document.getElementById('purposeTabRent');
        const sellTab   = document.getElementById('purposeTabSell');
        const radios    = document.querySelectorAll('.purpose-radio');
        const rentBudget     = document.getElementById('filterRentBudget');
        const sellPrice      = document.getElementById('filterSellPrice');
        const possessionSec  = document.getElementById('filterPossessionSection');
        const tenantSec      = document.getElementById('filterTenantSection');

        function applyPurpose(isSell) {
            // Tab styles
            rentTab.className = rentTab.className.replace(/border-\S+ text-\S+ bg-\S+/g, '');
            sellTab.className = sellTab.className.replace(/border-\S+ text-\S+ bg-\S+/g, '');
            if (isSell) {
                rentTab.classList.add('border-slate-200','text-slate-500','bg-white');
                sellTab.classList.add('border-purple-500','text-purple-700','bg-purple-50');
            } else {
                rentTab.classList.add('border-indigo-500','text-indigo-700','bg-indigo-50');
                sellTab.classList.add('border-slate-200','text-slate-500','bg-white');
            }
            // Show/hide budget sections
            rentBudget.classList.toggle('hidden', isSell);
            sellPrice.classList.toggle('hidden', !isSell);
            // Show/hide possession section
            if (possessionSec) possessionSec.classList.toggle('hidden', !isSell);
            // Show/hide tenant type section
            if (tenantSec) tenantSec.style.display = isSell ? 'none' : '';
        }

        radios.forEach(r => r.addEventListener('change', () => applyPurpose(r.value === 'sell')));
        [rentTab, sellTab].forEach(tab => tab.addEventListener('click', () => {
            const r = tab.querySelector('input');
            if (r) { r.checked = true; applyPurpose(r.value === 'sell'); }
        }));
    })();
    </script>
</div>
