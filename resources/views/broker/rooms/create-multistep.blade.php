@extends('layouts.agent')

@section('title', 'Post Property - Multi Step')

@section('broker-content')
<div class="max-w-4xl mx-auto px-4 py-6">
    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-black text-slate-900">Post Your Property</h1>
            <p class="text-sm text-slate-500 mt-1">Fill the steps below. Your progress is auto-saved.</p>
        </div>
        <a href="{{ $draftsIndex ?? route('agent.rooms.drafts') }}" class="hidden sm:inline-flex items-center gap-2 text-sm font-bold text-indigo-600 hover:text-indigo-700">
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
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-4 mb-4 sticky top-0 z-10">
        <div class="flex items-center justify-between gap-1 overflow-x-auto" id="stepper">
            @for ($i = 1; $i <= 6; $i++)
                <button type="button" class="step-btn flex flex-col items-center gap-1 min-w-[80px] flex-1 py-2 transition-all" data-step="{{ $i }}">
                    <div class="w-9 h-9 rounded-full flex items-center justify-center text-xs font-bold border-2 border-slate-200 bg-white text-slate-400 step-circle transition-all">
                        {{ $i }}
                    </div>
                    <span class="text-[10px] sm:text-xs font-bold text-slate-400 step-label">
                        @switch($i)
                            @case(1) Basic @break
                            @case(2) Location @break
                            @case(3) Details @break
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
        <div class="text-slate-400">
            Step <span id="currentStepLabel">1</span> of 6
        </div>
    </div>

    {{-- Form --}}
    <form id="multiStepForm" method="POST" action="{{ $storeRoute ?? route('agent.rooms.store') }}" enctype="multipart/form-data" class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
        @csrf
        <input type="hidden" name="draft_id" id="draft_id" value="">

        {{-- ========== STEP 1: BASIC DETAILS ========== --}}
        <div class="step-pane" data-step="1">
            <h2 class="text-lg font-black text-slate-900 mb-1">Basic Details</h2>
            <p class="text-sm text-slate-500 mb-5">Tell us what you're listing</p>

            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wide">Property Title *</label>
                    <input type="text" name="title" maxlength="120" placeholder="e.g. Spacious 2BHK Apartment in Indiranagar"
                        class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition text-sm font-medium">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wide">Property Type *</label>
                        <select name="property_type_id" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition text-sm font-semibold">
                            <option value="">-- Select --</option>
                            @foreach($propertyTypes as $pt)
                                <option value="{{ $pt->id }}">{{ $pt->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wide">Room Type *</label>
                        <select name="room_type" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition text-sm font-semibold">
                            <option value="">-- Select --</option>
                            <option value="1RK">1 RK</option>
                            <option value="1BHK">1 BHK</option>
                            <option value="2BHK">2 BHK</option>
                            <option value="3BHK">3 BHK</option>
                            <option value="4BHK+">4 BHK+</option>
                            <option value="PG">PG</option>
                            <option value="Hostel">Hostel</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wide">Description *</label>
                    <textarea name="description" rows="4" maxlength="2000" placeholder="Describe your property, unique features, nearby facilities..."
                        class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition text-sm"></textarea>
                    <p class="text-[10px] text-slate-400 mt-1 text-right"><span id="descCount">0</span>/2000</p>
                </div>
            </div>
        </div>

        {{-- ========== STEP 2: LOCATION ========== --}}
        <div class="step-pane hidden" data-step="2">
            <h2 class="text-lg font-black text-slate-900 mb-1">Location</h2>
            <p class="text-sm text-slate-500 mb-5">Where is the property located?</p>

            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wide">Full Address *</label>
                    <input type="text" name="address" id="location_address" placeholder="House no, building, street, area"
                        class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition text-sm">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wide">City *</label>
                        <input type="text" name="city" id="cityInput" placeholder="City"
                            class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition text-sm font-semibold">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wide">State *</label>
                        <input type="text" name="state" id="stateInput" placeholder="State"
                            class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition text-sm font-semibold">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wide">Pincode *</label>
                        <input type="text" name="pincode" pattern="[0-9]{6}" placeholder="6-digit pincode"
                            class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition text-sm font-semibold">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wide">Landmark (optional)</label>
                    <input type="text" name="landmark" placeholder="Near metro station, mall, hospital..."
                        class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition text-sm">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wide">Latitude</label>
                        <input type="text" name="latitude" id="latitude" readonly placeholder="Click on map to set"
                            class="w-full px-4 py-3 bg-slate-100 border border-slate-200 rounded-xl text-sm font-mono">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wide">Longitude</label>
                        <input type="text" name="longitude" id="longitude" readonly placeholder="Click on map to set"
                            class="w-full px-4 py-3 bg-slate-100 border border-slate-200 rounded-xl text-sm font-mono">
                    </div>
                </div>

                <div id="miniMap" class="w-full h-48 rounded-xl border border-slate-200 bg-slate-50 flex items-center justify-center text-sm text-slate-400">
                    <i class="fas fa-map-marked-alt mr-2"></i> Map will load here (optional - skip if not needed)
                </div>
            </div>
        </div>

        {{-- ========== STEP 3: PROPERTY DETAILS ========== --}}
        <div class="step-pane hidden" data-step="3">
            <h2 class="text-lg font-black text-slate-900 mb-1">Property Details</h2>
            <p class="text-sm text-slate-500 mb-5">Specifications and dimensions</p>

            <div class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wide">Furnishing *</label>
                        <select name="furnishing_type" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition text-sm font-semibold">
                            <option value="">-- Select --</option>
                            <option value="Fully Furnished">Fully Furnished</option>
                            <option value="Semi Furnished">Semi Furnished</option>
                            <option value="Unfurnished">Unfurnished</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wide">Preferred Tenant *</label>
                        <select name="tenant_type" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition text-sm font-semibold">
                            <option value="">-- Select --</option>
                            <option value="Family">Family</option>
                            <option value="Bachelor">Bachelor</option>
                            <option value="Girls">Girls</option>
                            <option value="Boys">Boys</option>
                            <option value="Anyone">Anyone</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wide">Floor</label>
                        <input type="number" name="floor" min="0" max="100" placeholder="0"
                            class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition text-sm font-semibold">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wide">Total Floors</label>
                        <input type="number" name="total_floors" min="0" max="100" placeholder="0"
                            class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition text-sm font-semibold">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wide">Bathrooms</label>
                        <input type="number" name="bathrooms" min="0" max="10" placeholder="1"
                            class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition text-sm font-semibold">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wide">Balconies</label>
                        <input type="number" name="balconies" min="0" max="10" placeholder="1"
                            class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition text-sm font-semibold">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wide">Built-up Area (sq ft)</label>
                        <input type="number" name="built_up_area" min="0" placeholder="e.g. 850"
                            class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition text-sm font-semibold">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wide">Carpet Area (sq ft)</label>
                        <input type="number" name="carpet_area" min="0" placeholder="e.g. 700"
                            class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition text-sm font-semibold">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wide">Available From *</label>
                    <input type="date" name="available_from" min="{{ date('Y-m-d') }}"
                        class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition text-sm font-semibold">
                </div>
            </div>
        </div>

        {{-- ========== STEP 4: AMENITIES & RULES ========== --}}
        <div class="step-pane hidden" data-step="4">
            <h2 class="text-lg font-black text-slate-900 mb-1">Amenities & Rules</h2>
            <p class="text-sm text-slate-500 mb-5">What does your property offer?</p>

            <div class="space-y-5">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wide">Amenities</label>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                        @foreach($amenities as $amenity)
                            <label class="flex items-center gap-2 p-2.5 rounded-lg border border-slate-200 hover:border-indigo-300 hover:bg-indigo-50/30 cursor-pointer transition text-xs font-semibold text-slate-700">
                                <input type="checkbox" name="amenities[]" value="{{ $amenity }}" class="w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500 border-slate-300 shrink-0">
                                <span>{{ $amenity }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wide">Pets Allowed?</label>
                        <select name="pets_allowed" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition text-sm font-semibold">
                            <option value="0">No</option>
                            <option value="1">Yes</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wide">Smoking/Drinking?</label>
                        <select name="smoking_drinking" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition text-sm font-semibold">
                            <option value="Not Allowed">Not Allowed</option>
                            <option value="Allowed">Allowed</option>
                            <option value="Outside Only">Outside Only</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wide">Entry Time Restrictions</label>
                    <input type="text" name="entry_time" placeholder="e.g. No restrictions / 10 PM to 6 AM"
                        class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition text-sm">
                </div>
            </div>
        </div>

        {{-- ========== STEP 5: PRICING & MEDIA ========== --}}
        <div class="step-pane hidden" data-step="5">
            <h2 class="text-lg font-black text-slate-900 mb-1">Pricing & Photos</h2>
            <p class="text-sm text-slate-500 mb-5">Set your price and add photos</p>

            <div class="space-y-5">
                <!-- Purpose Toggle -->
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200">
                    <label class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wide">Listing Purpose *</label>
                    <div class="flex items-center gap-6">
                        <label class="inline-flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="purpose" value="rent" checked class="broker-purpose-radio h-4 w-4 text-indigo-600 focus:ring-indigo-500">
                            <span class="text-sm font-bold text-slate-800"><i class="fas fa-key text-indigo-500 mr-1"></i> For Rent</span>
                        </label>
                        <label class="inline-flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="purpose" value="sell" class="broker-purpose-radio h-4 w-4 text-purple-600 focus:ring-purple-500">
                            <span class="text-sm font-bold text-slate-800"><i class="fas fa-tag text-purple-500 mr-1"></i> For Sale / Buy</span>
                        </label>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div id="brokerRentField">
                        <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wide">Monthly Rent (₹) *</label>
                        <input type="number" name="rent" id="broker_rent_input" min="0" placeholder="e.g. 15000"
                            class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition text-sm font-bold">
                    </div>
                    <div id="brokerPriceField" class="hidden">
                        <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wide">Total Sale Price (₹) *</label>
                        <input type="number" name="price" id="broker_price_input" min="0" placeholder="e.g. 4500000"
                            class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-purple-500/20 focus:border-purple-600 transition text-sm font-bold">
                    </div>
                    <div id="brokerPossessionField" class="hidden">
                        <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wide">Possession Status</label>
                        <select name="possession_status" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-purple-500/20 focus:border-purple-600 transition text-sm font-semibold">
                            <option value="">-- Select --</option>
                            <option value="ready_to_move">Ready to Move</option>
                            <option value="under_construction">Under Construction</option>
                        </select>
                    </div>
                    <div id="brokerOwnershipField" class="hidden">
                        <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wide">Ownership Type</label>
                        <select name="ownership_type" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-purple-500/20 focus:border-purple-600 transition text-sm font-semibold">
                            <option value="freehold">Freehold</option>
                            <option value="leasehold">Leasehold</option>
                            <option value="power_of_attorney">Power of Attorney</option>
                            <option value="cooperative_society">Cooperative Society</option>
                        </select>
                    </div>
                    <div id="brokerDepositField">
                        <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wide">Security Deposit (₹)</label>
                        <input type="number" name="deposit" min="0" placeholder="e.g. 30000"
                            class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition text-sm font-semibold">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wide">Maintenance (₹/mo)</label>
                        <input type="number" name="maintenance" min="0" placeholder="e.g. 1500"
                            class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition text-sm font-semibold">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wide">Brokerage Fee (₹)</label>
                    <input type="number" name="broker_fee" min="0" placeholder="Leave 0 if no brokerage"
                        class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition text-sm font-semibold">
                    <p class="text-[10px] text-slate-400 mt-1">Payable only after deal finalization</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wide">Property Photos *</label>
                    <div class="border-2 border-dashed border-slate-200 rounded-xl p-6 text-center bg-slate-50 hover:bg-slate-100 transition cursor-pointer" id="photoDropZone">
                        <input type="file" name="photos[]" id="photoInput" accept="image/*" multiple class="hidden">
                        <i class="fas fa-cloud-arrow-up text-3xl text-slate-400 mb-2"></i>
                        <p class="text-sm font-bold text-slate-700">Click or drag photos here</p>
                        <p class="text-xs text-slate-500 mt-1">JPG, PNG up to 5MB each. First photo will be the cover.</p>
                    </div>
                    <div id="photoPreview" class="grid grid-cols-3 sm:grid-cols-4 gap-2 mt-3"></div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wide">Video URL (optional)</label>
                    <input type="url" name="video_url" placeholder="https://www.youtube.com/watch?v=..."
                        class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition text-sm">
                </div>
            </div>
        </div>

        {{-- ========== STEP 6: REVIEW ========== --}}
        <div class="step-pane hidden" data-step="6">
            <h2 class="text-lg font-black text-slate-900 mb-1">Review & Publish</h2>
            <p class="text-sm text-slate-500 mb-5">Check everything before publishing</p>

            <div id="reviewSummary" class="space-y-4">
                <div class="bg-slate-50 rounded-xl p-4 text-sm text-slate-600 text-center">
                    <i class="fas fa-info-circle text-indigo-500 mr-1"></i> Fill the previous steps to see a summary here.
                </div>
            </div>

            <div class="mt-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200">
                <p class="text-xs text-emerald-800 font-semibold flex items-center gap-2">
                    <i class="fas fa-shield-check text-emerald-600"></i>
                    After publishing, your property will be reviewed by our team and go live within 2-4 hours.
                </p>
            </div>
        </div>

        {{-- ========== NAVIGATION ========== --}}
        <div class="flex items-center justify-between mt-6 pt-5 border-t border-slate-200">
            <button type="button" id="prevBtn" class="hidden px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 font-bold text-sm hover:bg-slate-50 transition inline-flex items-center gap-2">
                <i class="fas fa-arrow-left text-xs"></i> Back
            </button>

            <button type="button" id="saveDraftBtn" class="px-4 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-700 font-bold text-sm hover:bg-slate-50 transition inline-flex items-center gap-2">
                <i class="fas fa-save text-xs"></i> Save Draft
            </button>

            <div class="flex gap-2">
                <button type="button" id="nextBtn" class="px-5 py-2.5 rounded-xl bg-indigo-600 text-white font-bold text-sm hover:bg-indigo-700 transition inline-flex items-center gap-2 shadow-md">
                    Next <i class="fas fa-arrow-right text-xs"></i>
                </button>
                <button type="submit" id="publishBtn" class="hidden px-5 py-2.5 rounded-xl bg-emerald-600 text-white font-bold text-sm hover:bg-emerald-700 transition inline-flex items-center gap-2 shadow-md">
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

    function showStep(step) {
        document.querySelectorAll('.step-pane').forEach(p => p.classList.add('hidden'));
        document.querySelector(`.step-pane[data-step="${step}"]`).classList.remove('hidden');

        document.querySelectorAll('.step-btn').forEach((btn, idx) => {
            const num = idx + 1;
            const circle = btn.querySelector('.step-circle');
            const label = btn.querySelector('.step-label');
            circle.classList.remove('active', 'completed');
            label.classList.remove('active', 'completed');
            if (num < step) { circle.classList.add('completed'); label.classList.add('completed'); circle.innerHTML = '<i class="fas fa-check text-[10px]"></i>'; }
            else if (num === step) { circle.classList.add('active'); label.classList.add('active'); circle.innerHTML = num; }
            else { circle.innerHTML = num; }
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

            const res = await fetch('{{ route("agent.rooms.drafts.save") }}', {
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
                setSaveStatus('Save failed: ' + (json.message || 'unknown'), false);
            }
        } catch (err) {
            setSaveStatus('Network error. Changes kept locally.', false);
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
            if (!raw) return null;
            return JSON.parse(raw);
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
        toggleBrokerPurpose();
    }

    async function checkExistingDraft() {
        const storedId = localStorage.getItem('room_draft_id');
        try {
            const res = await fetch('{{ route("agent.rooms.drafts.latest") }}', {
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
                    const r = await fetch('{{ route("agent.rooms.drafts.load", ["id" => "__ID__"]) }}'.replace('__ID__', d.id), {
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
                    if (!confirm('Delete this draft? This cannot be undone.')) return;
                    await fetch('{{ route("agent.rooms.drafts.destroy", ["id" => "__ID__"]) }}'.replace('__ID__', d.id), {
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

    function toggleBrokerPurpose() {
        const purposeEl = form.querySelector('input[name="purpose"]:checked');
        const isSell = purposeEl && purposeEl.value === 'sell';
        const rentField = $('brokerRentField');
        const priceField = $('brokerPriceField');
        const possessionField = $('brokerPossessionField');
        const ownershipField = $('brokerOwnershipField');
        const depositField = $('brokerDepositField');

        if (rentField) rentField.classList.toggle('hidden', isSell);
        if (priceField) priceField.classList.toggle('hidden', !isSell);
        if (possessionField) possessionField.classList.toggle('hidden', !isSell);
        if (ownershipField) ownershipField.classList.toggle('hidden', !isSell);
        if (depositField) depositField.classList.toggle('hidden', isSell);
    }

    function renderReview() {
        const data = collectStepData(TOTAL_STEPS);
        const isSell = (data.purpose === 'sell');
        const rows = [
            ['Listing Purpose', isSell ? 'For Sale / Buy' : 'For Rent'],
            ['Title', data.title],
            ['Address', data.address],
            ['City', data.city],
            isSell ? ['Sale Price', data.price ? '₹' + Number(data.price).toLocaleString('en-IN') : '—'] : ['Rent', data.rent ? '₹' + Number(data.rent).toLocaleString('en-IN') + ' / mo' : '—'],
            isSell ? ['Possession', data.possession_status ? (data.possession_status === 'ready_to_move' ? 'Ready to Move' : 'Under Construction') : '—'] : ['Deposit', data.deposit ? '₹' + Number(data.deposit).toLocaleString('en-IN') : '—'],
            ['Furnishing', data.furnishing_type],
        ];
        let html = '<div class="space-y-2 text-sm">';
        rows.forEach(([k, v]) => {
            html += `<div class="flex justify-between border-b border-slate-100 py-2"><span class="text-slate-500 font-semibold">${k}</span><span class="text-slate-900 font-bold">${v || '—'}</span></div>`;
        });
        html += '</div>';
        $('reviewSummary').innerHTML = html;
    }

    function validateAllSteps() {
        const purposeEl = form.querySelector('input[name="purpose"]:checked');
        const isSell = purposeEl && purposeEl.value === 'sell';
        const requiredFields = [
            { step: 1, name: 'title',            label: 'Property Title' },
            { step: 1, name: 'property_type_id', label: 'Property Type' },
            { step: 1, name: 'room_type',        label: 'Room Type' },
            { step: 1, name: 'description',      label: 'Description (min 30 chars)', minlength: 30 },
            { step: 2, name: 'address',          label: 'Full Address' },
            { step: 2, name: 'city',             label: 'City' },
            { step: 2, name: 'state',            label: 'State' },
            { step: 2, name: 'pincode',          label: 'Pincode' },
            { step: 3, name: 'furnishing_type',  label: 'Furnishing' },
            { step: 3, name: 'tenant_type',      label: 'Preferred Tenant' },
            { step: 3, name: 'available_from',   label: 'Available From' },
            ...(isSell
                ? [{ step: 5, name: 'price', label: 'Total Sale Price' }]
                : [{ step: 5, name: 'rent',  label: 'Monthly Rent' }]),
        ];

        const errors = [];
        requiredFields.forEach(f => {
            const el = form.querySelector(`[name="${f.name}"]`);
            if (!el) return;
            const val = (el.value || '').toString().trim();
            if (!val) {
                errors.push(`Step ${f.step}: ${f.label} is required`);
                el.classList.add('ring-2', 'ring-rose-300');
            } else if (f.minlength && val.length < f.minlength) {
                errors.push(`Step ${f.step}: ${f.label} must be at least ${f.minlength} characters`);
                el.classList.add('ring-2', 'ring-rose-300');
            } else {
                el.classList.remove('ring-2', 'ring-rose-300');
            }
        });

        const photoInput = form.querySelector('[name="photos[]"]');
        if (photoInput && photoInput.files && photoInput.files.length === 0) {
            const draftPhotosJson = localStorage.getItem('room_draft_photos');
            if (!draftPhotosJson) {
                errors.push('Step 5: At least one photo is required');
            }
        }

        return errors;
    }

    function focusStep(step) {
        currentStep = step;
        showStep(step);
        const pane = document.querySelector(`.step-pane[data-step="${step}"]`);
        if (pane) {
            const firstError = pane.querySelector('.ring-rose-300');
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
            toastr && toastr.error(errors[0] + (errors.length > 1 ? ` (+${errors.length - 1} more)` : ''), 'Please complete these fields');
            const firstErrorEl = form.querySelector('.ring-rose-300');
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
                        window.location.href = '/agent/rooms/' + (data.room_id || '') + '/payment';
                    }, 1500);
                } else {
                    toastr && toastr.success(msg, 'Success');
                    setTimeout(() => {
                        window.location.href = '/agent/rooms';
                    }, 1500);
                }
            } else {
                toastr && toastr.error(data.message || 'Could not publish. Please try again.', 'Error');
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
                div.className = 'relative aspect-square rounded-lg overflow-hidden border border-slate-200';
                div.innerHTML = `<img src="${e.target.result}" class="w-full h-full object-cover">${i === 0 ? '<span class="absolute top-1 left-1 bg-indigo-600 text-white text-[9px] font-bold px-1.5 py-0.5 rounded">COVER</span>' : ''}`;
                photoPreview.appendChild(div);
            };
            reader.readAsDataURL(file);
        });
    }

    document.querySelectorAll('.broker-purpose-radio').forEach(r => {
        r.addEventListener('change', toggleBrokerPurpose);
    });
    toggleBrokerPurpose();

    showStep(1);
    checkExistingDraft();
})();
</script>
@endsection

