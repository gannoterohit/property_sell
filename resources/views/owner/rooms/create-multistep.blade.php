@extends('layouts.owner')

@section('title', 'Post Property - Multi Step')

@section('owner-content')
<div class="max-w-4xl mx-auto px-4 py-6">
    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Post Your Property</h1>
            <p class="text-sm text-slate-500 mt-1">List your residential, commercial, or plot property in 6 simple steps.</p>
        </div>
        <a href="{{ $draftsIndex ?? route('owner.rooms.drafts') }}" class="hidden sm:inline-flex items-center gap-2 text-sm font-bold text-indigo-600 hover:text-indigo-700">
            <i class="fas fa-folder-open"></i> My Drafts
        </a>
    </div>

    {{-- Resume Draft Banner --}}
    <div id="resumeBanner" class="hidden mb-4 p-4 rounded-xl bg-amber-50 border border-amber-200 flex items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center shrink-0">
                <i class="fas fa-clock-rotate-left"></i>
            </div>
            <div>
                <p class="font-bold text-amber-900 text-sm">You have a saved draft</p>
                <p class="text-xs text-amber-700"><span id="resumeTitle"></span> • Step <span id="resumeStep"></span> • Saved <span id="resumeTime"></span></p>
            </div>
        </div>
        <div class="flex gap-2 shrink-0">
            <button type="button" id="resumeDraftBtn" class="px-3 py-1.5 rounded-lg bg-amber-600 text-white text-xs font-bold hover:bg-amber-700">Resume</button>
            <button type="button" id="discardDraftBtn" class="px-3 py-1.5 rounded-lg bg-white text-amber-900 border border-amber-200 text-xs font-bold hover:bg-amber-50">Discard</button>
        </div>
    </div>

    {{-- Stepper --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-4 mb-4 sticky top-0 z-20">
        <div class="flex items-center justify-between gap-1 overflow-x-auto" id="stepper">
            @for ($i = 1; $i <= 6; $i++)
                <button type="button" class="step-btn flex flex-col items-center gap-1 min-w-[70px] flex-1 py-1 transition-all" data-step="{{ $i }}">
                    <div class="w-9 h-9 rounded-full flex items-center justify-center text-xs font-bold border-2 border-slate-200 bg-white text-slate-400 step-circle transition-all">
                        {{ $i }}
                    </div>
                    <span class="text-[10px] sm:text-xs font-bold text-slate-400 step-label">
                        @switch($i)
                            @case(1) Basic @break
                            @case(2) Location @break
                            @case(3) Specs @break
                            @case(4) Amenities @break
                            @case(5) Pricing @break
                            @case(6) Review @break
                        @endswitch
                    </span>
                </button>
                @if ($i < 6)
                    <div class="step-connector flex-1 h-0.5 bg-slate-200 rounded-full -mt-5 transition-colors" data-connector="{{ $i }}"></div>
                @endif
            @endfor
        </div>
    </div>

    {{-- Auto-save indicator --}}
    <div class="flex items-center justify-between mb-3 text-xs">
        <div class="flex items-center gap-2 text-slate-500">
            <span id="saveSpinner" class="hidden"><i class="fas fa-circle-notch fa-spin text-indigo-500"></i></span>
            <span id="saveStatus">All changes saved</span>
        </div>
        <div class="text-slate-400 font-bold">
            Step <span id="currentStepLabel">1</span> of 6
        </div>
    </div>

    {{-- Form --}}
    <form id="multiStepForm" method="POST" action="{{ $storeRoute ?? route('owner.rooms.store') }}" enctype="multipart/form-data" class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 sm:p-7">
        @csrf
        <input type="hidden" name="draft_id" id="draft_id" value="">

        {{-- ========== STEP 1: BASIC DETAILS ========== --}}
        <div class="step-pane" data-step="1">
            <h2 class="text-lg font-black text-slate-900 mb-1">Basic Property Information</h2>
            <p class="text-xs sm:text-sm text-slate-500 mb-5">Select purpose and primary property category.</p>

            <div class="space-y-5">
                {{-- Purpose Selector Cards --}}
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wide">Listing Purpose *</label>
                    <div class="grid grid-cols-2 gap-3 sm:gap-4">
                        <label class="purpose-card relative flex items-center gap-3 p-4 rounded-2xl border-2 border-indigo-600 bg-indigo-50/40 cursor-pointer transition select-none">
                            <input type="radio" name="purpose" value="rent" checked class="purpose-radio sr-only">
                            <div class="w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center shrink-0 shadow-sm">
                                <i class="fas fa-key text-base"></i>
                            </div>
                            <div class="min-w-0">
                                <span class="block font-black text-slate-900 text-sm">Rent Out</span>
                                <span class="block text-[11px] text-slate-500 font-medium truncate">Earn recurring monthly rent</span>
                            </div>
                            <div class="absolute top-3 right-3 text-indigo-600 purpose-check">
                                <i class="fas fa-circle-check text-lg"></i>
                            </div>
                        </label>

                        <label class="purpose-card relative flex items-center gap-3 p-4 rounded-2xl border-2 border-slate-200 bg-white hover:bg-slate-50 cursor-pointer transition select-none">
                            <input type="radio" name="purpose" value="sell" class="purpose-radio sr-only">
                            <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center shrink-0">
                                <i class="fas fa-tags text-base"></i>
                            </div>
                            <div class="min-w-0">
                                <span class="block font-black text-slate-900 text-sm">Sell Property</span>
                                <span class="block text-[11px] text-slate-500 font-medium truncate">Direct sale & genuine buyers</span>
                            </div>
                            <div class="absolute top-3 right-3 text-slate-300 purpose-check hidden">
                                <i class="fas fa-circle-check text-lg"></i>
                            </div>
                        </label>
                    </div>
                </div>

                {{-- Property Title --}}
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wide">Property Title *</label>
                    <input type="text" name="title" maxlength="120" placeholder="e.g. Luxurious 3BHK Semi-Furnished Apartment in Indiranagar"
                        class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition text-sm font-medium">
                </div>

                {{-- Property Type & Category --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wide">Property Type *</label>
                        <select name="property_type_id" id="propertyTypeSelect" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition text-sm font-semibold">
                            <option value="">-- Select Property Type --</option>
                            @foreach($propertyTypes as $pt)
                                <option value="{{ $pt->id }}" data-slug="{{ $pt->slug }}">{{ $pt->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wide">Sub-Category / Format</label>
                        <select name="property_category_id" id="propertyCategorySelect" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition text-sm font-semibold">
                            <option value="">-- Select Category --</option>
                        </select>
                    </div>
                </div>

                {{-- Room / Space Subtype (Standard / 1BHK / etc) --}}
                <div id="roomTypeContainer">
                    <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wide">Room Configuration / Unit Type *</label>
                    <select name="room_type" id="roomTypeSelect" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition text-sm font-semibold">
                        @if(isset($roomTypeOptions) && $roomTypeOptions->isNotEmpty())
                            @foreach($roomTypeOptions as $opt)
                                <option value="{{ $opt->id }}" {{ $opt->key === '2bhk' ? 'selected' : '' }}>{{ $opt->label }}</option>
                            @endforeach
                        @else
                            <option value="1BHK">1 BHK</option>
                            <option value="2BHK" selected>2 BHK</option>
                            <option value="3BHK">3 BHK</option>
                            <option value="4BHK+">4 BHK+</option>
                            <option value="1RK">1 RK</option>
                            <option value="Studio">Studio Apartment</option>
                            <option value="PG">PG Unit</option>
                        @endif
                    </select>
                </div>

                {{-- Description --}}
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wide">Property Description *</label>
                    <textarea name="description" rows="4" maxlength="2000" placeholder="Provide details like ventilation, road connectivity, water availability, nearby hotspots..."
                        class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition text-sm"></textarea>
                    <p class="text-[10px] text-slate-400 mt-1 text-right"><span id="descCount">0</span>/2000</p>
                </div>
            </div>
        </div>

        {{-- ========== STEP 2: LOCATION ========== --}}
        <div class="step-pane hidden" data-step="2">
            <h2 class="text-lg font-black text-slate-900 mb-1">Property Location</h2>
            <p class="text-xs sm:text-sm text-slate-500 mb-5">Accurate location helps verified buyers and tenants reach you.</p>

            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wide">Complete Address *</label>
                    <input type="text" name="address" id="location_address" placeholder="Flat/Shop no., Building name, Street, Landmark"
                        class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition text-sm">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wide">City *</label>
                        <input type="text" name="city" id="cityInput" placeholder="e.g. Pune, Delhi"
                            class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition text-sm font-semibold">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wide">State</label>
                        <input type="text" name="state" id="stateInput" placeholder="e.g. Maharashtra"
                            class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition text-sm font-semibold">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wide">Pincode *</label>
                        <input type="text" name="pincode" pattern="[0-9]{6}" placeholder="6-digit pincode"
                            class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition text-sm font-semibold">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wide">Popular Landmark / Locality</label>
                    <input type="text" name="landmark" placeholder="e.g. Near Metro Station Gate 2, Behind Phoenix Mall"
                        class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition text-sm">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wide">Latitude</label>
                        <input type="text" name="latitude" id="latitude" placeholder="Latitude coordinates (optional)"
                            class="w-full px-4 py-3 bg-slate-100 border border-slate-200 rounded-xl text-sm font-mono">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wide">Longitude</label>
                        <input type="text" name="longitude" id="longitude" placeholder="Longitude coordinates (optional)"
                            class="w-full px-4 py-3 bg-slate-100 border border-slate-200 rounded-xl text-sm font-mono">
                    </div>
                </div>
            </div>
        </div>

        {{-- ========== STEP 3: SPECIFICATIONS & DIMENSIONS ========== --}}
        <div class="step-pane hidden" data-step="3">
            <h2 class="text-lg font-black text-slate-900 mb-1">Property Specifications</h2>
            <p class="text-xs sm:text-sm text-slate-500 mb-5">Dimensions, structure, and structural features.</p>

            <div class="space-y-5">
                {{-- RESIDENTIAL SPECIFICATIONS --}}
                <div id="residentialSpecsGroup" class="space-y-4">
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wide">Bathrooms</label>
                            <input type="number" name="bathrooms" min="0" max="20" placeholder="e.g. 2"
                                class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition text-sm font-semibold">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wide">Balconies</label>
                            <input type="number" name="balconies" min="0" max="10" placeholder="e.g. 1"
                                class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition text-sm font-semibold">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wide">Floor No</label>
                            <input type="number" name="floor_no" min="-2" max="150" placeholder="e.g. 4"
                                class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition text-sm font-semibold">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wide">Total Floors</label>
                            <input type="number" name="total_floors" min="0" max="150" placeholder="e.g. 12"
                                class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition text-sm font-semibold">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wide">Carpet Area (sq ft) *</label>
                            <input type="number" name="carpet_area" min="0" placeholder="e.g. 850"
                                class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition text-sm font-semibold">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wide">Super Built-up Area</label>
                            <input type="number" name="super_builtup_area" min="0" placeholder="e.g. 1100"
                                class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition text-sm font-semibold">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wide">Built-up Area (sq ft)</label>
                            <input type="number" name="area_sqft" min="0" placeholder="e.g. 950"
                                class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition text-sm font-semibold">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wide">Facing / Vastu</label>
                            <select name="facing" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition text-sm font-semibold">
                                <option value="">Select Facing</option>
                                <option value="East">East</option>
                                <option value="North">North</option>
                                <option value="North-East">North-East</option>
                                <option value="West">West</option>
                                <option value="South">South</option>
                                <option value="North-West">North-West</option>
                                <option value="South-East">South-East</option>
                                <option value="South-West">South-West</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wide">Parking Type</label>
                            <select name="parking_type" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition text-sm font-semibold">
                                <option value="">Select Parking</option>
                                <option value="Covered Car + Bike">Covered Car + Bike</option>
                                <option value="Open Car Parking">Open Car Parking</option>
                                <option value="Bike Only">Bike Only</option>
                                <option value="No Parking">No Parking</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wide">Water Supply</label>
                            <select name="water_supply" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition text-sm font-semibold">
                                <option value="">Select Water Supply</option>
                                <option value="24 Hours Corporation">24 Hours Corporation</option>
                                <option value="Borewell Supply">Borewell Supply</option>
                                <option value="Both Corporation & Borewell">Both Corporation & Borewell</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- COMMERCIAL SPECIFICATIONS --}}
                <div id="commercialSpecsGroup" class="hidden space-y-4 p-4 rounded-2xl bg-amber-50/50 border border-amber-200">
                    <div class="flex items-center gap-2 text-amber-900 font-bold text-sm">
                        <i class="fas fa-briefcase"></i> Commercial & Business Details
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wide">Commercial Format</label>
                            <select name="commercial_type" class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500/20 transition text-sm font-semibold">
                                <option value="Retail Shop">Retail Shop</option>
                                <option value="Showroom">Showroom</option>
                                <option value="Office Space">Corporate Office Space</option>
                                <option value="Warehouse">Warehouse / Godown</option>
                                <option value="Co-working Space">Co-working Space</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wide">Road Frontage (Feet)</label>
                            <input type="number" name="frontage_width_ft" min="0" placeholder="e.g. 25"
                                class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500/20 transition text-sm font-semibold">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wide">Washroom Facility</label>
                            <select name="washroom_type" class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500/20 transition text-sm font-semibold">
                                <option value="Private Washroom">Private Washroom Attached</option>
                                <option value="Shared Washroom">Shared Complex Washroom</option>
                                <option value="No Washroom">No Washroom</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wide">Main Road Facing?</label>
                            <select name="is_main_road_facing" class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500/20 transition text-sm font-semibold">
                                <option value="1">Yes (Prime Visibility)</option>
                                <option value="0">No (Inside Lane/Complex)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wide">Corner Property?</label>
                            <select name="is_corner_property" class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500/20 transition text-sm font-semibold">
                                <option value="0">No</option>
                                <option value="1">Yes (2 Sides Open)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wide">Power Backup</label>
                            <select name="power_backup" class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500/20 transition text-sm font-semibold">
                                <option value="Full DG Backup">100% Full DG Backup</option>
                                <option value="Partial Backup">Partial Backup</option>
                                <option value="Inverter Backup">Inverter Backup</option>
                                <option value="None">None</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wide">Suitable Businesses / Tenants</label>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-xs">
                            @foreach(['Retail Shop', 'Doctor Clinic', 'IT Office', 'Restaurant / Cafe', 'Gym & Fitness', 'Coaching Center', 'Salon / Spa', 'Storage Godown'] as $biz)
                                <label class="flex items-center gap-2 p-2 bg-white rounded-lg border border-slate-200 cursor-pointer hover:bg-amber-100/40">
                                    <input type="checkbox" name="suitable_for[]" value="{{ $biz }}" class="rounded text-indigo-600 focus:ring-indigo-500">
                                    <span class="font-medium text-slate-700">{{ $biz }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- PLOT / LAND SPECIFICATIONS --}}
                <div id="plotSpecsGroup" class="hidden space-y-4 p-4 rounded-2xl bg-emerald-50/50 border border-emerald-200">
                    <div class="flex items-center gap-2 text-emerald-900 font-bold text-sm">
                        <i class="fas fa-layer-group"></i> Plot & Land Dimensions
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wide">Plot Area</label>
                            <input type="number" name="plot_area" min="0" placeholder="e.g. 1500"
                                class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500/20 transition text-sm font-semibold">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wide">Measurement Unit</label>
                            <select name="plot_area_unit" class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500/20 transition text-sm font-semibold">
                                <option value="sqft">Square Feet (Sq.Ft)</option>
                                <option value="sqyd">Square Yards (Sq.Yards)</option>
                                <option value="gaj">Gaj</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wide">Boundary Wall</label>
                            <select name="gated_community" class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500/20 transition text-sm font-semibold">
                                <option value="1">Yes (Boundary Built)</option>
                                <option value="0">No Boundary</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wide">Available From Date *</label>
                    <input type="date" name="available_from" value="{{ date('Y-m-d') }}" min="{{ date('Y-m-d') }}"
                        class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition text-sm font-semibold">
                </div>
            </div>
        </div>

        {{-- ========== STEP 4: AMENITIES & RULES ========== --}}
        <div class="step-pane hidden" data-step="4">
            <h2 class="text-lg font-black text-slate-900 mb-1" id="step4Title">Amenities & Tenant Preferences</h2>
            <p class="text-xs sm:text-sm text-slate-500 mb-5" id="step4Subtitle">Select facilities provided and preferred occupant guidelines.</p>

            <div class="space-y-5">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wide">Amenities & Features</label>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
                        @foreach($amenities as $amenity)
                            <label class="flex items-center gap-2.5 p-2.5 rounded-xl border border-slate-200 hover:border-indigo-300 hover:bg-indigo-50/40 cursor-pointer transition text-xs font-semibold text-slate-700">
                                <input type="checkbox" name="amenities[]" value="{{ $amenity }}" class="w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500 border-slate-300 shrink-0">
                                <span>{{ $amenity }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- Furnishing status: applies to residential flats/villas (both rent & sell) --}}
                <div id="furnishingContainer">
                    <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wide">Furnishing Status *</label>
                    <select name="furnishing_type" id="furnishingSelect" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition text-sm font-semibold">
                        @if(isset($furnishingOptions) && $furnishingOptions->isNotEmpty())
                            @foreach($furnishingOptions as $opt)
                                <option value="{{ $opt->id }}" {{ in_array($opt->key, ['semi-furnished', 'semi_furnished']) ? 'selected' : '' }}>{{ $opt->label }}</option>
                            @endforeach
                        @else
                            <option value="Semi Furnished" selected>Semi Furnished</option>
                            <option value="Fully Furnished">Fully Furnished</option>
                            <option value="Unfurnished">Unfurnished</option>
                        @endif
                    </select>
                </div>

                {{-- Tenant guidelines (Applies ONLY to Rent; hidden when Purpose = Sell) --}}
                <div id="rentalPreferencesGroup" class="space-y-4 p-4 rounded-2xl bg-indigo-50/40 border border-indigo-100">
                    <div class="text-xs font-bold text-indigo-900 uppercase tracking-wide flex items-center gap-1.5">
                        <i class="fas fa-users text-indigo-600"></i> Tenant & Occupancy Guidelines (For Rent Only)
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wide">Preferred Occupants / Tenants</label>
                            <select name="tenant_type" class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition text-sm font-semibold">
                                @if(isset($tenantOptions) && $tenantOptions->isNotEmpty())
                                    @foreach($tenantOptions as $opt)
                                        <option value="{{ $opt->id }}" {{ $opt->key === 'any' ? 'selected' : '' }}>{{ $opt->label }}</option>
                                    @endforeach
                                @else
                                    <option value="Anyone" selected>Anyone (Family, Bachelor, Company)</option>
                                    <option value="Family">Family Only</option>
                                    <option value="Bachelor">Bachelor Only</option>
                                    <option value="Girls">Girls / Females Only</option>
                                    <option value="Boys">Boys Only</option>
                                    <option value="Company / Corporate">Company / Corporate Lease</option>
                                @endif
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wide">Food Preference</label>
                            <select name="food_preference" class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition text-sm font-semibold">
                                <option value="No Preference">No Food Restriction (All Allowed)</option>
                                <option value="Vegetarian Only">Pure Vegetarian Only</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wide">Pet Friendly?</label>
                            <select name="pet_friendly" class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition text-sm font-semibold">
                                <option value="1">Yes (Pets Allowed)</option>
                                <option value="0" selected>No Pets Allowed</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ========== STEP 5: PRICING & MEDIA ========== --}}
        <div class="step-pane hidden" data-step="5">
            <h2 class="text-lg font-black text-slate-900 mb-1">Pricing, Terms & Media</h2>
            <p class="text-xs sm:text-sm text-slate-500 mb-5">Set realistic pricing and upload attractive images.</p>

            <div class="space-y-5">
                {{-- RENT PRICING SECTION --}}
                <div id="rentPricingGroup" class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wide">Monthly Rent (₹) *</label>
                            <input type="number" name="rent" id="rentInput" min="0" placeholder="e.g. 18000"
                                class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition text-sm font-black text-indigo-700">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wide">Security Deposit (₹)</label>
                            <input type="number" name="deposit" min="0" placeholder="e.g. 36000"
                                class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition text-sm font-semibold">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wide">Maintenance (₹/mo)</label>
                            <input type="number" name="maintenance_charges" min="0" placeholder="e.g. 1500"
                                class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition text-sm font-semibold">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wide">Maintenance Status</label>
                            <select name="maintenance_type" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition text-sm font-semibold">
                                <option value="extra">Extra (Not in Rent)</option>
                                <option value="included">Included in Monthly Rent</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wide">Lock-in Period (Months)</label>
                            <input type="number" name="lockin_period_months" min="0" placeholder="e.g. 6"
                                class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition text-sm font-semibold">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wide">Notice Period (Days)</label>
                            <input type="number" name="notice_period_days" min="0" placeholder="e.g. 30"
                                class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition text-sm font-semibold">
                        </div>
                    </div>
                </div>

                {{-- SELL PRICING SECTION --}}
                <div id="sellPricingGroup" class="hidden space-y-4 p-4 rounded-2xl bg-emerald-50/40 border border-emerald-200">
                    <div class="flex items-center gap-2 text-emerald-900 font-bold text-sm">
                        <i class="fas fa-hand-holding-dollar"></i> Sale Pricing & Ownership Credentials
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wide">Expected Sale Price (₹) *</label>
                            <input type="number" name="price" id="priceInput" min="0" placeholder="e.g. 4500000"
                                class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600 transition text-sm font-black text-emerald-700">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wide">Price Negotiable?</label>
                            <select name="price_negotiable" class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 transition text-sm font-semibold">
                                <option value="1">Yes (Slightly Negotiable)</option>
                                <option value="0">Fixed Price (Non-Negotiable)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wide">Possession Status *</label>
                            <select name="possession_status" class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 transition text-sm font-semibold">
                                <option value="ready_to_move">Ready to Move</option>
                                <option value="under_construction">Under Construction</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wide">Possession Date / Target</label>
                            <input type="text" name="possession_date" placeholder="e.g. Immediate / Dec 2026"
                                class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 transition text-sm font-semibold">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wide">Property Age</label>
                            <select name="property_age" class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 transition text-sm font-semibold">
                                <option value="Brand New">Brand New / Under Construction</option>
                                <option value="0-1 Year">0 to 1 Year Old</option>
                                <option value="1-5 Years">1 to 5 Years Old</option>
                                <option value="5-10 Years">5 to 10 Years Old</option>
                                <option value="10+ Years">10+ Years Old</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wide">Ownership Title</label>
                            <select name="ownership_type" class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 transition text-sm font-semibold">
                                <option value="Freehold">Freehold Title</option>
                                <option value="Leasehold">Leasehold</option>
                                <option value="Co-operative Society">Co-operative Society</option>
                                <option value="Power of Attorney">Power of Attorney (POA)</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wide">RERA Registered ID (optional)</label>
                            <input type="text" name="rera_id" placeholder="e.g. P52100012345"
                                class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 transition text-sm font-mono">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wide">Bank Loan Approved?</label>
                            <select name="is_bank_loan_approved" class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 transition text-sm font-semibold">
                                <option value="1">Yes (Loan Available from Leading Banks)</option>
                                <option value="0">No / Pending Approval</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Listing Type & Broker Fee --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wide">Listing Role</label>
                        <select name="listing_type" id="listingTypeSelect" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition text-sm font-semibold">
                            <option value="owner" selected>Direct Owner (Zero Brokerage)</option>
                            <option value="broker">Verified Agent / Broker</option>
                        </select>
                    </div>
                    <div id="brokerFeeField" class="hidden">
                        <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wide">Broker Fee / Commission (₹)</label>
                        <input type="number" name="broker_fee" min="0" placeholder="e.g. 15000"
                            class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition text-sm font-semibold">
                    </div>
                </div>

                {{-- Photos Upload --}}
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wide">Property Photos * (At least 1 photo)</label>
                    <div class="border-2 border-dashed border-slate-200 rounded-2xl p-6 text-center bg-slate-50 hover:bg-slate-100 transition cursor-pointer" id="photoDropZone">
                        <input type="file" name="photos[]" id="photoInput" accept="image/*" multiple class="hidden">
                        <div class="w-12 h-12 mx-auto rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center mb-3">
                            <i class="fas fa-cloud-arrow-up text-xl"></i>
                        </div>
                        <p class="text-sm font-bold text-slate-800">Click to browse or drag photos here</p>
                        <p class="text-xs text-slate-500 mt-1">High quality photos attract 3x more inquiries. First photo becomes the cover.</p>
                    </div>
                    <div id="photoPreview" class="grid grid-cols-3 sm:grid-cols-5 gap-3 mt-3"></div>
                </div>

                {{-- Video URL --}}
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wide">YouTube / Walkthrough Video URL (optional)</label>
                    <input type="url" name="video_url" placeholder="https://www.youtube.com/watch?v=..."
                        class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition text-sm">
                </div>
            </div>
        </div>

        {{-- ========== STEP 6: REVIEW ========== --}}
        <div class="step-pane hidden" data-step="6">
            <h2 class="text-lg font-black text-slate-900 mb-1">Review & Publish Listing</h2>
            <p class="text-xs sm:text-sm text-slate-500 mb-5">Review all entered parameters before publishing.</p>

            <div id="reviewSummary" class="space-y-4">
                <div class="bg-slate-50 rounded-xl p-4 text-sm text-slate-600 text-center">
                    <i class="fas fa-info-circle text-indigo-500 mr-1"></i> Summary will render dynamically.
                </div>
            </div>

            <div class="mt-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200">
                <p class="text-xs text-emerald-800 font-semibold flex items-center gap-2">
                    <i class="fas fa-shield-check text-emerald-600 text-base shrink-0"></i>
                    Your listing will be verified by our team and published to thousands of active seekers instantly.
                </p>
            </div>
        </div>

        {{-- ========== NAVIGATION ========== --}}
        <div class="flex items-center justify-between mt-8 pt-5 border-t border-slate-200">
            <button type="button" id="prevBtn" class="hidden px-5 py-2.5 rounded-xl border border-slate-200 text-slate-700 font-bold text-sm hover:bg-slate-50 transition inline-flex items-center gap-2">
                <i class="fas fa-arrow-left text-xs"></i> Back
            </button>

            <button type="button" id="saveDraftBtn" class="px-4 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-700 font-bold text-sm hover:bg-slate-50 transition inline-flex items-center gap-2">
                <i class="fas fa-save text-xs"></i> Save Draft
            </button>

            <div class="flex gap-2">
                <button type="button" id="nextBtn" class="px-6 py-2.5 rounded-xl bg-indigo-600 text-white font-bold text-sm hover:bg-indigo-700 transition inline-flex items-center gap-2 shadow-md">
                    Next <i class="fas fa-arrow-right text-xs"></i>
                </button>
                <button type="submit" id="publishBtn" class="hidden px-6 py-2.5 rounded-xl bg-emerald-600 text-white font-bold text-sm hover:bg-emerald-700 transition inline-flex items-center gap-2 shadow-md">
                    <i class="fas fa-rocket text-xs"></i> Publish Property
                </button>
            </div>
        </div>
    </form>
</div>

<style>
.step-pane { animation: fadeIn 0.25s ease; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: none; } }
.step-circle.active { background: #6366f1; color: #fff; border-color: #6366f1; }
.step-circle.completed { background: #10b981; color: #fff; border-color: #10b981; }
.step-label.active { color: #4f46e5; }
.step-label.completed { color: #059669; }
.step-connector.completed { background: #10b981; }
</style>

<script>
window.PROPERTY_CATALOG = @json($propertyTypes);

(function() {
    'use strict';
    const TOTAL_STEPS = 6;
    let currentStep = 1;
    let draftId = null;
    let isAutoSaving = false;
    let autoSaveTimer = null;
    let isPublishing = false;

    const form = document.getElementById('multiStepForm');
    const draftIdInput = document.getElementById('draft_id');

    function $(id) { return document.getElementById(id); }

    // --- Purpose Card Selection Handling ---
    function updatePurposeCards() {
        const selectedPurpose = document.querySelector('input[name="purpose"]:checked')?.value || 'rent';
        const isSell = (selectedPurpose === 'sell');

        document.querySelectorAll('.purpose-card').forEach(card => {
            const radio = card.querySelector('input[name="purpose"]');
            const iconWrap = card.querySelector('div:first-of-type');
            const checkIcon = card.querySelector('.purpose-check');

            if (radio.value === selectedPurpose) {
                card.classList.add('border-indigo-600', 'bg-indigo-50/40');
                card.classList.remove('border-slate-200', 'bg-white');
                iconWrap.classList.add('bg-indigo-600', 'text-white');
                iconWrap.classList.remove('bg-slate-100', 'text-slate-600');
                if (checkIcon) checkIcon.classList.remove('hidden');
            } else {
                card.classList.remove('border-indigo-600', 'bg-indigo-50/40');
                card.classList.add('border-slate-200', 'bg-white');
                iconWrap.classList.remove('bg-indigo-600', 'text-white');
                iconWrap.classList.add('bg-slate-100', 'text-slate-600');
                if (checkIcon) checkIcon.classList.add('hidden');
            }
        });

        // Toggle Pricing Sections
        const rentPricing = $('rentPricingGroup');
        const sellPricing = $('sellPricingGroup');
        if (rentPricing && sellPricing) {
            rentPricing.classList.toggle('hidden', isSell);
            sellPricing.classList.toggle('hidden', !isSell);
        }

        // Toggle Rental Preferences (Hide when Sell)
        const rentalPreferences = $('rentalPreferencesGroup');
        if (rentalPreferences) {
            rentalPreferences.classList.toggle('hidden', isSell);
        }

        const step4Title = $('step4Title');
        const step4Subtitle = $('step4Subtitle');
        if (step4Title && step4Subtitle) {
            if (isSell) {
                step4Title.textContent = 'Amenities & Features';
                step4Subtitle.textContent = 'Select society and property amenities.';
            } else {
                step4Title.textContent = 'Amenities & Tenant Preferences';
                step4Subtitle.textContent = 'Select facilities provided and preferred occupant guidelines.';
            }
        }
    }

    document.querySelectorAll('.purpose-card').forEach(card => {
        card.addEventListener('click', () => {
            const radio = card.querySelector('input[name="purpose"]');
            if (radio) {
                radio.checked = true;
                updatePurposeCards();
            }
        });
    });

    // --- Property Type & Category Filtering ---
    const typeSelect = $('propertyTypeSelect');
    const categorySelect = $('propertyCategorySelect');

    function syncPropertyTypes(selectedCategoryId = null) {
        const typeId = parseInt(typeSelect.value, 10);
        categorySelect.innerHTML = '<option value="">-- Select Category --</option>';

        const selectedType = (window.PROPERTY_CATALOG || []).find(t => t.id === typeId);
        const slug = selectedType ? selectedType.slug : '';

        if (selectedType && selectedType.categories && selectedType.categories.length > 0) {
            selectedType.categories.forEach(cat => {
                const opt = document.createElement('option');
                opt.value = cat.id;
                opt.textContent = cat.name;
                if (selectedCategoryId && parseInt(selectedCategoryId, 10) === cat.id) {
                    opt.selected = true;
                }
                categorySelect.appendChild(opt);
            });
        }

        // Check if commercial, plot or residential
        const isCommercial = ['shop', 'office', 'showroom', 'warehouse'].includes(slug);
        const isPlot = ['plot-land', 'plot', 'land'].includes(slug);
        const selectedPurpose = document.querySelector('input[name="purpose"]:checked')?.value || 'rent';
        const isSell = (selectedPurpose === 'sell');

        $('commercialSpecsGroup').classList.toggle('hidden', !isCommercial);
        $('plotSpecsGroup').classList.toggle('hidden', !isPlot);
        $('residentialSpecsGroup').classList.toggle('hidden', isCommercial || isPlot);
        $('roomTypeContainer').classList.toggle('hidden', isPlot);
        
        if ($('furnishingContainer')) {
            $('furnishingContainer').classList.toggle('hidden', isPlot);
        }
        if ($('rentalPreferencesGroup')) {
            $('rentalPreferencesGroup').classList.toggle('hidden', isPlot || isSell);
        }
    }

    typeSelect.addEventListener('change', () => syncPropertyTypes());

    // Listing Type Broker Toggle
    const listingSelect = $('listingTypeSelect');
    listingSelect.addEventListener('change', () => {
        $('brokerFeeField').classList.toggle('hidden', listingSelect.value !== 'broker');
    });

    // --- Stepper Navigation ---
    function showStep(step) {
        document.querySelectorAll('.step-pane').forEach(p => p.classList.add('hidden'));
        document.querySelector(`.step-pane[data-step="${step}"]`).classList.remove('hidden');

        document.querySelectorAll('.step-btn').forEach((btn, idx) => {
            const num = idx + 1;
            const circle = btn.querySelector('.step-circle');
            const label = btn.querySelector('.step-label');
            circle.classList.remove('active', 'completed');
            label.classList.remove('active', 'completed');
            if (num < step) { 
                circle.classList.add('completed'); 
                label.classList.add('completed'); 
                circle.innerHTML = '<i class="fas fa-check text-[10px]"></i>'; 
            }
            else if (num === step) { 
                circle.classList.add('active'); 
                label.classList.add('active'); 
                circle.innerHTML = num; 
            }
            else { 
                circle.innerHTML = num; 
            }
        });

        for (let i = 1; i < TOTAL_STEPS; i++) {
            const conn = document.querySelector(`.step-connector[data-connector="${i}"]`);
            if (conn) conn.classList.toggle('completed', i < step);
        }

        $('prevBtn').classList.toggle('hidden', step === 1);
        $('nextBtn').classList.toggle('hidden', step === TOTAL_STEPS);
        $('publishBtn').classList.toggle('hidden', step !== TOTAL_STEPS);
        $('currentStepLabel').textContent = step;

        if (step === TOTAL_STEPS) renderReview();

        document.querySelector('.step-pane:not(.hidden)').scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function collectStepData(step) {
        const fd = new FormData(form);
        const data = {};
        for (const [key, value] of fd.entries()) {
            if (key === '_token' || key === 'draft_id' || key.startsWith('photos')) continue;
            if (key.endsWith('[]')) {
                const clean = key.slice(0, -2);
                if (!data[clean]) data[clean] = [];
                data[clean].push(value);
            } else {
                data[key] = value;
            }
        }
        return data;
    }

    function setSaveStatus(text, spinning = false) {
        $('saveStatus').textContent = text;
        $('saveSpinner').classList.toggle('hidden', !spinning);
    }

    async function saveDraft(step) {
        if (isAutoSaving) return;
        isAutoSaving = true;
        setSaveStatus('Saving...', true);
        try {
            const data = collectStepData(step);
            const fd = new FormData();
            fd.append('_token', document.querySelector('meta[name="csrf-token"]').content);
            fd.append('step', step);
            fd.append('data', JSON.stringify(data));
            fd.append('title', data.title || '');
            if (draftId) fd.append('draft_id', draftId);

            const res = await fetch('{{ route("owner.rooms.drafts.save") }}', {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body: fd,
                credentials: 'same-origin'
            });
            const json = await res.json();
            if (json.success) {
                draftId = json.draft.id;
                draftIdInput.value = draftId;
                localStorage.setItem('room_draft_id', draftId);
                const time = new Date(json.draft.last_saved_at).toLocaleTimeString();
                setSaveStatus('All changes saved at ' + time, false);
            } else {
                setSaveStatus('Save failed', false);
            }
        } catch (err) {
            setSaveStatus('Network error. Kept locally.', false);
            saveLocalOnly(step);
        } finally {
            isAutoSaving = false;
        }
    }

    function saveLocalOnly(step) {
        const data = collectStepData(step);
        localStorage.setItem('room_draft_data', JSON.stringify({ step, data, ts: Date.now() }));
    }

    function loadLocalBackup() {
        try {
            const raw = localStorage.getItem('room_draft_data');
            return raw ? JSON.parse(raw) : null;
        } catch { return null; }
    }

    function applyDataToForm(data) {
        if (!data) return;
        Object.keys(data).forEach(key => {
            const inputs = form.querySelectorAll(`[name="${key}"], [name="${key}[]"]`);
            const val = data[key];
            if (!inputs.length) return;
            if (Array.isArray(val)) {
                inputs.forEach(input => {
                    if (input.type === 'checkbox' || input.type === 'radio') {
                        input.checked = val.includes(input.value);
                    }
                });
            } else {
                const input = inputs[0];
                if (input && input.type !== 'checkbox' && input.type !== 'radio') {
                    input.value = val;
                } else if (input) {
                    input.checked = (input.value === val);
                }
            }
        });
        updatePurposeCards();
        if (data.property_type_id) {
            syncPropertyTypes(data.property_category_id);
        }
    }

    async function checkExistingDraft() {
        try {
            const res = await fetch('{{ route("owner.rooms.drafts.latest") }}', {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin'
            });
            const json = await res.json();
            if (json.success && json.draft) {
                const d = json.draft;
                $('resumeTitle').textContent = d.title || 'Untitled';
                $('resumeStep').textContent = d.step + ' (' + d.step_name + ')';
                $('resumeTime').textContent = new Date(d.last_saved_at).toLocaleString();
                $('resumeBanner').classList.remove('hidden');

                $('resumeDraftBtn').onclick = async () => {
                    const r = await fetch('{{ route("owner.rooms.drafts.load", ["id" => "__ID__"]) }}'.replace('__ID__', d.id), {
                        headers: { 'Accept': 'application/json' },
                        credentials: 'same-origin'
                    });
                    const j = await r.json();
                    if (j.success) {
                        draftId = j.draft.id;
                        draftIdInput.value = draftId;
                        applyDataToForm(j.draft.data);
                        currentStep = j.draft.step;
                        showStep(currentStep);
                        $('resumeBanner').classList.add('hidden');
                        setSaveStatus('Draft resumed from ' + new Date(j.draft.last_saved_at).toLocaleString());
                    }
                };
                $('discardDraftBtn').onclick = async () => {
                    if (!confirm('Discard this draft?')) return;
                    await fetch('{{ route("owner.rooms.drafts.destroy", ["id" => "__ID__"]) }}'.replace('__ID__', d.id), {
                        method: 'DELETE',
                        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
                        credentials: 'same-origin'
                    });
                    localStorage.removeItem('room_draft_id');
                    localStorage.removeItem('room_draft_data');
                    $('resumeBanner').classList.add('hidden');
                };
            }
        } catch (e) {
            const local = loadLocalBackup();
            if (local && local.data) {
                if (confirm('We found a locally-saved draft. Restore it?')) {
                    applyDataToForm(local.data);
                    currentStep = local.step || 1;
                    showStep(currentStep);
                }
            }
        }
    }

    function renderReview() {
        const data = collectStepData(TOTAL_STEPS);
        const isSell = (data.purpose === 'sell');
        const rows = [
            ['Listing Purpose', isSell ? '<span class="px-2.5 py-1 rounded-lg bg-emerald-100 text-emerald-800 font-black text-xs">FOR SALE</span>' : '<span class="px-2.5 py-1 rounded-lg bg-indigo-100 text-indigo-800 font-black text-xs">FOR RENT</span>'],
            ['Property Title', data.title],
            ['City & Locality', (data.city || '') + (data.landmark ? ' (Near ' + data.landmark + ')' : '')],
            ['Pricing', isSell ? ('₹' + (data.price ? Number(data.price).toLocaleString('en-IN') : '—')) : ('₹' + (data.rent ? Number(data.rent).toLocaleString('en-IN') + '/month' : '—'))],
            ['Terms / Status', isSell ? (data.possession_status === 'ready_to_move' ? 'Ready to Move' : 'Under Construction') : ('Deposit: ₹' + (data.deposit || '0'))],
            ['Furnishing', data.furnishing_type || '—'],
        ];

        if (isSell) {
            rows.push(['Ownership Title', data.ownership_type || 'Freehold']);
            if (data.property_age) rows.push(['Property Age', data.property_age]);
            if (data.possession_date) rows.push(['Possession Target', data.possession_date]);
            if (data.rera_id) rows.push(['RERA Registered ID', data.rera_id]);
            rows.push(['Price Negotiable', data.price_negotiable == '1' ? 'Yes' : 'Fixed Price']);
        } else {
            rows.push(['Preferred Occupant', data.tenant_type || 'Anyone']);
            if (data.maintenance_charges) rows.push(['Maintenance', '₹' + data.maintenance_charges + ' (' + (data.maintenance_type || 'extra') + ')']);
            if (data.food_preference) rows.push(['Food Preference', data.food_preference]);
            if (data.lockin_period_months) rows.push(['Lock-in Period', data.lockin_period_months + ' Months']);
        }

        let html = '<div class="bg-slate-50 rounded-2xl p-5 space-y-3">';
        rows.forEach(([k, v]) => {
            html += `<div class="flex items-center justify-between border-b border-slate-200/60 pb-2 text-sm"><span class="text-slate-500 font-medium">${k}</span><span class="text-slate-900 font-bold">${v || '—'}</span></div>`;
        });
        html += '</div>';
        $('reviewSummary').innerHTML = html;
    }

    function validateAllSteps() {
        const purpose = document.querySelector('input[name="purpose"]:checked')?.value || 'rent';
        const requiredFields = [
            { step: 1, name: 'title',            label: 'Property Title' },
            { step: 1, name: 'property_type_id', label: 'Property Type' },
            { step: 1, name: 'description',      label: 'Description (min 20 chars)', minlength: 20 },
            { step: 2, name: 'address',          label: 'Complete Address' },
            { step: 2, name: 'city',             label: 'City' },
            { step: 2, name: 'pincode',          label: 'Pincode' },
        ];

        if (purpose === 'sell') {
            requiredFields.push({ step: 5, name: 'price', label: 'Expected Sale Price' });
        } else {
            requiredFields.push({ step: 5, name: 'rent', label: 'Monthly Rent' });
        }

        const errors = [];
        requiredFields.forEach(f => {
            const el = form.querySelector(`[name="${f.name}"]`);
            if (!el) return;
            const val = (el.value || '').toString().trim();
            if (!val) {
                errors.push(`Step ${f.step}: ${f.label} is required`);
                el.classList.add('ring-2', 'ring-rose-400');
            } else if (f.minlength && val.length < f.minlength) {
                errors.push(`Step ${f.step}: ${f.label} must be at least ${f.minlength} characters`);
                el.classList.add('ring-2', 'ring-rose-400');
            } else {
                el.classList.remove('ring-2', 'ring-rose-400');
            }
        });

        const photoInput = form.querySelector('[name="photos[]"]');
        if (photoInput && photoInput.files && photoInput.files.length === 0) {
            const draftPhotosJson = localStorage.getItem('room_draft_photos');
            if (!draftPhotosJson) {
                errors.push('Step 5: At least one property photo is required');
            }
        }

        return errors;
    }

    function focusStep(step) {
        currentStep = step;
        showStep(step);
        const pane = document.querySelector(`.step-pane[data-step="${step}"]`);
        if (pane) {
            const firstError = pane.querySelector('.ring-rose-400');
            if (firstError) {
                setTimeout(() => firstError.focus(), 200);
            }
        }
    }

    $('nextBtn').addEventListener('click', async () => {
        await saveDraft(currentStep);
        if (currentStep < TOTAL_STEPS) {
            currentStep++;
            showStep(currentStep);
        }
    });

    $('prevBtn').addEventListener('click', () => {
        if (currentStep > 1) {
            currentStep--;
            showStep(currentStep);
        }
    });

    $('saveDraftBtn').addEventListener('click', async () => {
        await saveDraft(currentStep);
        toastr && toastr.success('Draft saved!', 'Success');
    });

    document.querySelectorAll('.step-btn').forEach((btn) => {
        btn.addEventListener('click', () => {
            const target = parseInt(btn.dataset.step, 10);
            if (target <= currentStep) {
                showStep(target);
            } else {
                toastr && toastr.info('Please complete earlier steps first');
            }
        });
    });

    form.addEventListener('input', () => {
        setSaveStatus('Unsaved changes...', false);
        clearTimeout(autoSaveTimer);
        autoSaveTimer = setTimeout(() => saveDraft(currentStep), 30000);
    });

    form.addEventListener('submit', async (e) => {
        if (isPublishing) return;
        e.preventDefault();

        const errors = validateAllSteps();
        if (errors.length > 0) {
            toastr && toastr.error(errors[0] + (errors.length > 1 ? ` (+${errors.length - 1} more)` : ''), 'Missing Information');
            const firstErrorEl = form.querySelector('.ring-rose-400');
            if (firstErrorEl) {
                const stepPane = firstErrorEl.closest('.step-pane');
                if (stepPane) {
                    focusStep(parseInt(stepPane.dataset.step, 10));
                }
            }
            return;
        }

        isPublishing = true;
        $('publishBtn').disabled = true;
        $('publishBtn').innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Publishing...';
        await saveDraft(currentStep);

        const fd = new FormData(form);
        try {
            const res = await fetch(form.action, {
                method: 'POST',
                body: fd,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                credentials: 'same-origin'
            });
            const data = await res.json().catch(() => ({}));
            if (res.ok && (data.success || data.room_id)) {
                localStorage.removeItem('room_draft_id');
                localStorage.removeItem('room_draft_data');
                const msg = data.message || 'Property published successfully!';
                if (data.payment_required || data.payment_id) {
                    toastr && toastr.info(msg + ' Redirecting to payment...', 'Success');
                    setTimeout(() => {
                        window.location.href = '/owner/rooms/' + (data.room_id || '') + '/payment';
                    }, 1500);
                } else {
                    toastr && toastr.success(msg, 'Success');
                    setTimeout(() => {
                        window.location.href = '/owner/rooms';
                    }, 1500);
                }
            } else {
                toastr && toastr.error(data.message || 'Could not publish. Please check required fields.', 'Error');
                isPublishing = false;
                $('publishBtn').disabled = false;
                $('publishBtn').innerHTML = '<i class="fas fa-rocket text-xs"></i> Publish Property';
            }
        } catch (err) {
            toastr && toastr.error('Network error. Please try again.', 'Error');
            isPublishing = false;
            $('publishBtn').disabled = false;
            $('publishBtn').innerHTML = '<i class="fas fa-rocket text-xs"></i> Publish Property';
        }
    });

    const desc = form.querySelector('[name="description"]');
    if (desc) {
        desc.addEventListener('input', () => { $('descCount').textContent = desc.value.length; });
    }

    const photoInput = $('photoInput');
    const photoDrop = $('photoDropZone');
    const photoPreview = $('photoPreview');

    photoDrop.addEventListener('click', () => photoInput.click());
    photoDrop.addEventListener('dragover', (e) => { e.preventDefault(); photoDrop.classList.add('border-indigo-500'); });
    photoDrop.addEventListener('dragleave', () => photoDrop.classList.remove('border-indigo-500'));
    photoDrop.addEventListener('drop', (e) => {
        e.preventDefault();
        photoDrop.classList.remove('border-indigo-500');
        photoInput.files = e.dataTransfer.files;
        handlePhotoPreview();
    });
    photoInput.addEventListener('change', handlePhotoPreview);

    function handlePhotoPreview() {
        photoPreview.innerHTML = '';
        Array.from(photoInput.files).forEach((file, i) => {
            const reader = new FileReader();
            reader.onload = (e) => {
                const div = document.createElement('div');
                div.className = 'relative aspect-square rounded-xl overflow-hidden border border-slate-200 shadow-sm';
                div.innerHTML = `<img src="${e.target.result}" class="w-full h-full object-cover">${i === 0 ? '<span class="absolute top-1 left-1 bg-indigo-600 text-white text-[9px] font-black px-1.5 py-0.5 rounded shadow">COVER</span>' : ''}`;
                photoPreview.appendChild(div);
            };
            reader.readAsDataURL(file);
        });
    }

    updatePurposeCards();
    showStep(1);
    checkExistingDraft();
})();
</script>
@endsection
