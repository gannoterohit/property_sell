@extends('layouts.owner')

@section('title', 'Edit Property - RoomRental')

@section('owner-content')
  @php
                    $logo = \App\Models\Setting::get('website_logo');
                @endphp
<div class="owner-workspace room-editor min-h-screen bg-slate-50">
    <!-- Mobile App Header -->
    <div class="lg:hidden bg-white px-4 py-4 flex items-center justify-between sticky top-0 z-40 border-b">
        <div class="flex items-center gap-3">
            <a href="{{ route('owner.dashboard') }}" class="text-gray-900">
                <i class="fas fa-arrow-left text-xl"></i>
            </a>
            <h1 class="text-lg font-black text-gray-900">Edit Property</h1>
        </div>
        <div class="w-8"></div>
    </div>

    <div class="owner-form-shell">

        <!-- Main Content -->
        <main class="flex-1">
            <!-- Desktop Header -->
            <div class="owner-page-header owner-form-page-header hidden lg:block bg-white border-b border-slate-200">
                <div class="max-w-6xl mx-auto flex items-center justify-between">
                    <div>
                        <h1 class="font-black text-slate-950">Update Property Listing</h1>
                        <p class="text-slate-500">Modify your property details to keep your listing accurate.</p>
                    </div>
                </div>
            </div>

            <div class="room-editor-content max-w-6xl mx-auto p-4 sm:p-6 lg:p-8 pb-12 lg:pb-16">
                <!-- Status Alert -->
                <div class="bg-indigo-50 border border-indigo-100 rounded-[2rem] p-6 mb-8 flex items-start gap-4">
                    <div class="w-10 h-10 bg-white rounded-2xl flex items-center justify-center text-indigo-600 shadow-sm shrink-0">
                        <i class="fas fa-info-circle"></i>
                    </div>
                    <div>
                        <p class="font-bold text-indigo-900">Property Status: {{ ucfirst($room->status) }}</p>
                        <p class="text-sm text-indigo-700">Need to update pricing or photos? You can do it here anytime.</p>
                    </div>
                </div>


                <form id="editRoomForm" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    
                    <div class="owner-room-form-grid">
                        <!-- Basic Details Card -->
                        <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 p-6">
                            <h3 class="text-lg font-black text-gray-900 mb-6 flex items-center gap-2">
                                <span class="w-8 h-8 bg-indigo-100 rounded-xl flex items-center justify-center text-indigo-600 text-sm">
                                    <i class="fas fa-home"></i>
                                </span>
                                Basic Details
                            </h3>
                            
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-xs font-black text-gray-500 uppercase tracking-wider mb-1 ml-1">Property Title</label>
                                    <input type="text" name="title" required value="{{ $room->title }}" placeholder="e.g. Luxury Single Room near IIT"
                                           class="w-full px-4 py-3 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-indigo-500 transition text-sm font-medium">
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-black text-gray-500 uppercase tracking-wider mb-1 ml-1">Property Type</label>
                                        <select name="property_type_id" id="property_type_id" required class="w-full px-4 py-3 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-indigo-500 transition text-sm font-medium">
                                            <option value="">Select property type</option>
                                            @foreach(\App\Models\PropertyType::where('status', true)->orderBy('name')->get() as $type)
                                                <option value="{{ $type->id }}" {{ $room->property_type_id == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-black text-gray-500 uppercase tracking-wider mb-1 ml-1">Property Category</label>
                                        <select name="property_category_id" id="property_category_id" data-selected-category-id="{{ $room->property_category_id }}" required class="w-full px-4 py-3 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-indigo-500 transition text-sm font-medium">
                                            <option value="">Select category</option>
                                            @foreach(\App\Models\PropertyCategory::where('property_type_id', $room->property_type_id)->where('status', true)->orderBy('name')->get() as $category)
                                                <option value="{{ $category->id }}" {{ $room->property_category_id == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs font-black text-gray-500 uppercase tracking-wider mb-1 ml-1">Furnishing</label>
                                    <select name="furnishing_type" required class="w-full px-4 py-3 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-indigo-500 transition text-sm font-medium">
                                        @foreach(App\Models\RoomOption::optionsFor('furnishing_type', $room->furnishing_option_id) as $option)
                                            <option value="{{ $option->id }}" {{ $room->furnishing_option_id == $option->id ? 'selected' : '' }}>{{ $option->label }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-xs font-black text-gray-500 uppercase tracking-wider mb-2 ml-1">Purpose</label>
                                    <div class="grid grid-cols-2 gap-3">
                                        <label class="flex items-center gap-2.5 p-3 rounded-2xl border-2 {{ ($room->purpose ?? 'rent') !== 'sell' ? 'border-indigo-600 bg-indigo-50/50 text-indigo-900 font-black' : 'border-slate-200 bg-white text-slate-700 font-bold' }} cursor-pointer transition">
                                            <input type="radio" name="purpose" value="rent" class="edit-purpose-radio sr-only" {{ ($room->purpose ?? 'rent') !== 'sell' ? 'checked' : '' }}>
                                            <i class="fas fa-key text-indigo-600"></i>
                                            <span>For Rent</span>
                                        </label>
                                        <label class="flex items-center gap-2.5 p-3 rounded-2xl border-2 {{ ($room->purpose ?? '') === 'sell' ? 'border-emerald-600 bg-emerald-50/50 text-emerald-900 font-black' : 'border-slate-200 bg-white text-slate-700 font-bold' }} cursor-pointer transition">
                                            <input type="radio" name="purpose" value="sell" class="edit-purpose-radio sr-only" {{ ($room->purpose ?? '') === 'sell' ? 'checked' : '' }}>
                                            <i class="fas fa-tags text-emerald-600"></i>
                                            <span>For Sale</span>
                                        </label>
                                    </div>
                                </div>

                                {{-- RENT PRICING FIELDS --}}
                                <div id="editRentSection" class="{{ ($room->purpose ?? 'rent') === 'sell' ? 'hidden' : '' }} space-y-4 p-4 rounded-2xl bg-indigo-50/30 border border-indigo-100">
                                    <div class="text-xs font-black text-indigo-900 uppercase tracking-wider flex items-center gap-1.5">
                                        <i class="fas fa-indian-rupee-sign"></i> Rental Terms
                                    </div>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-xs font-black text-gray-500 uppercase tracking-wider mb-1 ml-1">Monthly Rent (₹)</label>
                                            <input type="number" name="rent" min="0" value="{{ $room->rent }}" placeholder="e.g. 15000"
                                                   class="w-full px-4 py-3 bg-white border-none rounded-2xl focus:ring-2 focus:ring-indigo-500 transition text-sm font-bold text-indigo-700">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-black text-gray-500 uppercase tracking-wider mb-1 ml-1">Deposit (₹)</label>
                                            <input type="number" name="deposit" min="0" value="{{ $room->deposit }}" placeholder="e.g. 30000"
                                                   class="w-full px-4 py-3 bg-white border-none rounded-2xl focus:ring-2 focus:ring-indigo-500 transition text-sm font-medium">
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-xs font-black text-gray-500 uppercase tracking-wider mb-1 ml-1">Maintenance (₹/mo)</label>
                                            <input type="number" name="maintenance_charges" min="0" value="{{ $room->feature('maintenance_charges', $room->maintenance) }}" placeholder="e.g. 1500"
                                                   class="w-full px-4 py-3 bg-white border-none rounded-2xl focus:ring-2 focus:ring-indigo-500 transition text-sm font-medium">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-black text-gray-500 uppercase tracking-wider mb-1 ml-1">Maintenance Type</label>
                                            <select name="maintenance_type" class="w-full px-4 py-3 bg-white border-none rounded-2xl focus:ring-2 focus:ring-indigo-500 transition text-sm font-medium">
                                                <option value="extra" {{ $room->feature('maintenance_type') === 'extra' ? 'selected' : '' }}>Extra Charges</option>
                                                <option value="included" {{ $room->feature('maintenance_type') === 'included' ? 'selected' : '' }}>Included in Rent</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-xs font-black text-gray-500 uppercase tracking-wider mb-1 ml-1">Lock-in Period (Mos)</label>
                                            <input type="number" name="lockin_period_months" min="0" value="{{ $room->feature('lockin_period_months') }}" placeholder="e.g. 6"
                                                   class="w-full px-4 py-3 bg-white border-none rounded-2xl focus:ring-2 focus:ring-indigo-500 transition text-sm font-medium">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-black text-gray-500 uppercase tracking-wider mb-1 ml-1">Notice Period (Days)</label>
                                            <input type="number" name="notice_period_days" min="0" value="{{ $room->feature('notice_period_days') }}" placeholder="e.g. 30"
                                                   class="w-full px-4 py-3 bg-white border-none rounded-2xl focus:ring-2 focus:ring-indigo-500 transition text-sm font-medium">
                                        </div>
                                    </div>
                                </div>

                                {{-- SELL PRICING FIELDS --}}
                                <div id="editSellSection" class="{{ ($room->purpose ?? 'rent') === 'sell' ? '' : 'hidden' }} space-y-4 p-4 rounded-2xl bg-emerald-50/40 border border-emerald-100">
                                    <div class="text-xs font-black text-emerald-900 uppercase tracking-wider flex items-center gap-1.5">
                                        <i class="fas fa-hand-holding-dollar"></i> Sale Pricing & Legal Details
                                    </div>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-xs font-black text-gray-500 uppercase tracking-wider mb-1 ml-1">Sale Price (₹) *</label>
                                            <input type="number" name="price" min="0" value="{{ $room->price }}" placeholder="e.g. 4500000"
                                                   class="w-full px-4 py-3 bg-white border-none rounded-2xl focus:ring-2 focus:ring-emerald-500 transition text-sm font-bold text-emerald-700">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-black text-gray-500 uppercase tracking-wider mb-1 ml-1">Price Negotiable?</label>
                                            <select name="price_negotiable" class="w-full px-4 py-3 bg-white border-none rounded-2xl focus:ring-2 focus:ring-emerald-500 transition text-sm font-medium">
                                                <option value="1" {{ $room->feature('price_negotiable', 1) ? 'selected' : '' }}>Yes (Negotiable)</option>
                                                <option value="0" {{ !$room->feature('price_negotiable', 1) ? 'selected' : '' }}>Fixed Price</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-xs font-black text-gray-500 uppercase tracking-wider mb-1 ml-1">Possession Status</label>
                                            <select name="possession_status" class="w-full px-4 py-3 bg-white border-none rounded-2xl focus:ring-2 focus:ring-emerald-500 transition text-sm font-medium">
                                                <option value="ready_to_move" {{ $room->possession_status === 'ready_to_move' ? 'selected' : '' }}>Ready to Move</option>
                                                <option value="under_construction" {{ $room->possession_status === 'under_construction' ? 'selected' : '' }}>Under Construction</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-black text-gray-500 uppercase tracking-wider mb-1 ml-1">Possession Date</label>
                                            <input type="text" name="possession_date" value="{{ $room->feature('possession_date') }}" placeholder="e.g. Immediate / Dec 2026"
                                                   class="w-full px-4 py-3 bg-white border-none rounded-2xl focus:ring-2 focus:ring-emerald-500 transition text-sm font-medium">
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-xs font-black text-gray-500 uppercase tracking-wider mb-1 ml-1">Property Age</label>
                                            <select name="property_age" class="w-full px-4 py-3 bg-white border-none rounded-2xl focus:ring-2 focus:ring-emerald-500 transition text-sm font-medium">
                                                <option value="Brand New" {{ $room->feature('property_age') === 'Brand New' ? 'selected' : '' }}>Brand New</option>
                                                <option value="0-1 Year" {{ $room->feature('property_age') === '0-1 Year' ? 'selected' : '' }}>0-1 Year</option>
                                                <option value="1-5 Years" {{ $room->feature('property_age') === '1-5 Years' ? 'selected' : '' }}>1-5 Years</option>
                                                <option value="5-10 Years" {{ $room->feature('property_age') === '5-10 Years' ? 'selected' : '' }}>5-10 Years</option>
                                                <option value="10+ Years" {{ $room->feature('property_age') === '10+ Years' ? 'selected' : '' }}>10+ Years</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-black text-gray-500 uppercase tracking-wider mb-1 ml-1">Ownership Type</label>
                                            <select name="ownership_type" class="w-full px-4 py-3 bg-white border-none rounded-2xl focus:ring-2 focus:ring-emerald-500 transition text-sm font-medium">
                                                <option value="Freehold" {{ ($room->feature('ownership_type') ?: $room->ownership_type) === 'Freehold' ? 'selected' : '' }}>Freehold</option>
                                                <option value="Leasehold" {{ ($room->feature('ownership_type') ?: $room->ownership_type) === 'Leasehold' ? 'selected' : '' }}>Leasehold</option>
                                                <option value="Co-operative" {{ ($room->feature('ownership_type') ?: $room->ownership_type) === 'Co-operative' ? 'selected' : '' }}>Co-operative Society</option>
                                                <option value="Power of Attorney" {{ ($room->feature('ownership_type') ?: $room->ownership_type) === 'Power of Attorney' ? 'selected' : '' }}>Power of Attorney</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-xs font-black text-gray-500 uppercase tracking-wider mb-1 ml-1">RERA Registered ID</label>
                                            <input type="text" name="rera_id" value="{{ $room->feature('rera_id') }}" placeholder="e.g. P52100012345"
                                                   class="w-full px-4 py-3 bg-white border-none rounded-2xl focus:ring-2 focus:ring-emerald-500 transition text-sm font-mono">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-black text-gray-500 uppercase tracking-wider mb-1 ml-1">Bank Loan Approved?</label>
                                            <select name="is_bank_loan_approved" class="w-full px-4 py-3 bg-white border-none rounded-2xl focus:ring-2 focus:ring-emerald-500 transition text-sm font-medium">
                                                <option value="1" {{ $room->feature('is_bank_loan_approved', 1) ? 'selected' : '' }}>Yes (Available)</option>
                                                <option value="0" {{ !$room->feature('is_bank_loan_approved', 1) ? 'selected' : '' }}>No</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                {{-- DIMENSIONS & AREA --}}
                                <div class="grid grid-cols-3 gap-3">
                                    <div>
                                        <label class="block text-xs font-black text-gray-500 uppercase tracking-wider mb-1 ml-1">Built-up Area (sqft)</label>
                                        <input type="number" name="area_sqft" min="0" step="0.01" value="{{ $room->area_sqft }}"
                                               class="w-full px-4 py-3 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-indigo-500 transition text-sm font-medium">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-black text-gray-500 uppercase tracking-wider mb-1 ml-1">Carpet Area (sqft)</label>
                                        <input type="number" name="carpet_area" min="0" value="{{ $room->feature('carpet_area') }}" placeholder="e.g. 750"
                                               class="w-full px-4 py-3 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-indigo-500 transition text-sm font-medium">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-black text-gray-500 uppercase tracking-wider mb-1 ml-1">Super Builtup (sqft)</label>
                                        <input type="number" name="super_builtup_area" min="0" value="{{ $room->feature('super_builtup_area') }}" placeholder="e.g. 1000"
                                               class="w-full px-4 py-3 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-indigo-500 transition text-sm font-medium">
                                    </div>
                                </div>

                                {{-- STRUCTURE & FLOOR DETAILS --}}
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                                    <div>
                                        <label class="block text-xs font-black text-gray-500 uppercase tracking-wider mb-1 ml-1">Bathrooms</label>
                                        <input type="number" name="bathrooms" min="0" max="20" value="{{ $room->feature('bathrooms', $room->bathrooms) }}" placeholder="e.g. 2"
                                               class="w-full px-4 py-3 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-indigo-500 transition text-sm font-medium">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-black text-gray-500 uppercase tracking-wider mb-1 ml-1">Balconies</label>
                                        <input type="number" name="balconies" min="0" max="10" value="{{ $room->feature('balconies', $room->balconies) }}" placeholder="e.g. 1"
                                               class="w-full px-4 py-3 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-indigo-500 transition text-sm font-medium">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-black text-gray-500 uppercase tracking-wider mb-1 ml-1">Floor No</label>
                                        <input type="number" name="floor_no" min="-2" max="150" value="{{ $room->feature('floor_no', $room->floor) }}" placeholder="e.g. 3"
                                               class="w-full px-4 py-3 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-indigo-500 transition text-sm font-medium">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-black text-gray-500 uppercase tracking-wider mb-1 ml-1">Total Floors</label>
                                        <input type="number" name="total_floors" min="0" max="150" value="{{ $room->feature('total_floors', $room->total_floors) }}" placeholder="e.g. 10"
                                               class="w-full px-4 py-3 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-indigo-500 transition text-sm font-medium">
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-black text-gray-500 uppercase tracking-wider mb-1 ml-1">Facing / Vastu</label>
                                        <select name="facing" class="w-full px-4 py-3 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-indigo-500 transition text-sm font-medium">
                                            <option value="">Select Facing</option>
                                            @foreach(['East', 'North', 'North-East', 'West', 'South', 'North-West', 'South-East', 'South-West'] as $face)
                                                <option value="{{ $face }}" {{ $room->feature('facing') === $face ? 'selected' : '' }}>{{ $face }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-black text-gray-500 uppercase tracking-wider mb-1 ml-1">Parking Type</label>
                                        <select name="parking_type" class="w-full px-4 py-3 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-indigo-500 transition text-sm font-medium">
                                            <option value="">Select Parking</option>
                                            @foreach(['Covered Car + Bike', 'Open Car Parking', 'Bike Only', 'No Parking'] as $park)
                                                <option value="{{ $park }}" {{ $room->feature('parking_type') === $park ? 'selected' : '' }}>{{ $park }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                {{-- COMMERCIAL SPECIFIC ATTRIBUTES --}}
                                <div class="p-4 rounded-2xl bg-amber-50/50 border border-amber-200/80 space-y-4">
                                    <div class="text-xs font-black text-amber-900 uppercase tracking-wider flex items-center gap-1.5">
                                        <i class="fas fa-briefcase"></i> Commercial & Shop Features
                                    </div>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-xs font-black text-gray-500 uppercase tracking-wider mb-1 ml-1">Road Frontage (Ft)</label>
                                            <input type="number" name="frontage_width_ft" min="0" value="{{ $room->feature('frontage_width_ft') }}" placeholder="e.g. 20"
                                                   class="w-full px-4 py-3 bg-white border-none rounded-2xl focus:ring-2 focus:ring-amber-500 transition text-sm font-medium">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-black text-gray-500 uppercase tracking-wider mb-1 ml-1">Main Road Facing?</label>
                                            <select name="is_main_road_facing" class="w-full px-4 py-3 bg-white border-none rounded-2xl focus:ring-2 focus:ring-amber-500 transition text-sm font-medium">
                                                <option value="1" {{ $room->feature('is_main_road_facing') ? 'selected' : '' }}>Yes</option>
                                                <option value="0" {{ !$room->feature('is_main_road_facing') ? 'selected' : '' }}>No</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-xs font-black text-gray-500 uppercase tracking-wider mb-1 ml-1">Corner Property?</label>
                                            <select name="is_corner_property" class="w-full px-4 py-3 bg-white border-none rounded-2xl focus:ring-2 focus:ring-amber-500 transition text-sm font-medium">
                                                <option value="0" {{ !$room->feature('is_corner_property') ? 'selected' : '' }}>No</option>
                                                <option value="1" {{ $room->feature('is_corner_property') ? 'selected' : '' }}>Yes (2 Sides Open)</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-black text-gray-500 uppercase tracking-wider mb-1 ml-1">Washroom Type</label>
                                            <select name="washroom_type" class="w-full px-4 py-3 bg-white border-none rounded-2xl focus:ring-2 focus:ring-amber-500 transition text-sm font-medium">
                                                <option value="Private Washroom" {{ $room->feature('washroom_type') === 'Private Washroom' ? 'selected' : '' }}>Private Washroom</option>
                                                <option value="Shared Washroom" {{ $room->feature('washroom_type') === 'Shared Washroom' ? 'selected' : '' }}>Shared Complex Washroom</option>
                                                <option value="No Washroom" {{ $room->feature('washroom_type') === 'No Washroom' ? 'selected' : '' }}>None</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <script>
                                document.querySelectorAll('.edit-purpose-radio').forEach(function(r){
                                    r.addEventListener('change', function(){
                                        var isSell = (this.value === 'sell');
                                        var rentSec = document.getElementById('editRentSection');
                                        var sellSec = document.getElementById('editSellSection');
                                        if (rentSec) rentSec.classList.toggle('hidden', isSell);
                                        if (sellSec) sellSec.classList.toggle('hidden', !isSell);
                                    });
                                });
                                </script>

                                <div>
                                    <label class="block text-xs font-black text-gray-500 uppercase tracking-wider mb-1 ml-1">Preferred Tenant</label>
                                    <select name="tenant_type" required class="w-full px-4 py-3 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-indigo-500 transition text-sm font-medium">
                                        @foreach(App\Models\RoomOption::optionsFor('tenant_type', $room->tenant_option_id) as $option)
                                            <option value="{{ $option->id }}" {{ $room->tenant_option_id == $option->id ? 'selected' : '' }}>{{ $option->label }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-xs font-black text-gray-500 uppercase tracking-wider mb-1 ml-1">Listing Type</label>
                                    <select name="listing_type" id="listing_type" required onchange="toggleBrokerFee(this.value)" class="w-full px-4 py-3 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-indigo-500 transition text-sm font-medium">
                                        <option value="owner" {{ $room->listing_type == 'owner' ? 'selected' : '' }}>Direct Owner</option>
                                        <option value="broker" {{ $room->listing_type == 'broker' ? 'selected' : '' }}>Verified Broker</option>
                                    </select>
                                </div>

                                <div id="broker_fee_container" class="{{ $room->listing_type == 'broker' ? '' : 'hidden' }}">
                                    <label class="block text-xs font-black text-gray-500 uppercase tracking-wider mb-1 ml-1">Broker Fee (₹)</label>
                                    <input type="number" name="broker_fee" id="broker_fee" min="0" value="{{ $room->broker_fee ?? 0 }}"
                                           class="w-full px-4 py-3 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-indigo-500 transition text-sm font-medium"
                                           {{ $room->listing_type == 'broker' ? 'required' : '' }}>
                                </div>

                                <div>
                                    <label class="block text-xs font-black text-gray-500 uppercase tracking-wider mb-1 ml-1">Description</label>
                                    <textarea name="description" rows="3" placeholder="Describe your property, rules, etc."
                                              class="w-full px-4 py-3 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-indigo-500 transition resize-none text-sm font-medium">{{ $room->description }}</textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Media Assets Card -->
                        <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 p-6">
                            <h3 class="text-lg font-black text-gray-900 mb-6 flex items-center gap-2">
                                <span class="w-8 h-8 bg-green-100 rounded-xl flex items-center justify-center text-green-600 text-sm">
                                    <i class="fas fa-camera"></i>
                                </span>
                                Media Assets
                            </h3>
                            
                            <div class="space-y-6">
                                <div>
                                    <label class="block text-xs font-black text-gray-500 uppercase tracking-wider mb-3 ml-1">Property Photos (Up to 5)</label>
                                    
                                    <!-- Current Photos -->
                                    @if(count($room->photo_urls) > 0)
                                    <div class="current-photo-grid mb-4">
                                        @foreach($room->photo_urls as $photoUrl)
                                            <div class="current-photo-item">
                                                <img src="{{ $photoUrl }}" alt="Current photo of {{ $room->title }}" class="w-full h-full object-cover" onerror="this.closest('.current-photo-item').style.display='none'">
                                                @if($loop->first)<span>Cover photo</span>@endif
                                            </div>
                                        @endforeach
                                    </div>
                                    @endif

                                    <div class="relative">
                                        <input type="file" name="photos[]" accept="image/*" multiple id="photosInput" class="hidden" onchange="handlePhotosUpload(event)">
                                        <label for="photosInput" class="flex flex-col items-center justify-center w-full h-32 bg-gray-50 border-2 border-dashed border-gray-200 rounded-3xl cursor-pointer hover:bg-gray-100 transition-all group">
                                            <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                                <i class="fas fa-plus text-2xl text-gray-300 group-hover:text-indigo-400 transition-colors mb-2"></i>
                                                <p class="text-xs font-black text-gray-400 uppercase tracking-widest">Choose new photos</p>
                                                <small class="mt-1 text-[10px] font-medium normal-case tracking-normal text-gray-400">Selecting files will replace all current photos</small>
                                            </div>
                                        </label>
                                    </div>
                                    <div id="photosPreview" class="grid grid-cols-3 gap-3 mt-4 hidden"></div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-4 px-1">Property Video (Optional)</label>
                                        <input type="file" name="video" accept="video/*" class="hidden" id="videoInput" onchange="handleVideoUpload(event)">
                                        <label for="videoInput" class="cursor-pointer block border-2 border-dashed border-gray-200 rounded-[2rem] p-8 text-center hover:border-indigo-400 hover:bg-indigo-50 transition-all group">
                                            <i class="fas fa-video text-3xl text-gray-300 group-hover:text-indigo-400 transition mb-3"></i>
                                            <p class="text-xs font-bold text-gray-500 group-hover:text-indigo-600 transition">Update virtual tour</p>
                                        </label>
                                        <video id="videoPreview" src="{{ $room->video ? asset('storage/'.$room->video) : '' }}" controls class="{{ $room->video ? '' : 'hidden' }} mt-4 max-h-40 mx-auto rounded-2xl w-full object-cover"></video>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-4 px-1">Or YouTube Video URL</label>
                                        <input type="url" name="video_url" value="{{ $room->video_url }}" placeholder="https://www.youtube.com/watch?v=..."
                                               class="w-full bg-gray-50 border-none rounded-2xl px-5 py-4 focus:ring-2 focus:ring-indigo-500 transition font-bold text-gray-700">
                                        <p class="text-[10px] text-gray-400 mt-2 px-1">Paste a YouTube or Vimeo link to show a video tour.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                            
                        <!-- Facilities Card -->
                        <div class="owner-form-wide bg-white rounded-[2rem] shadow-sm border border-gray-100 p-6">
                            <h3 class="text-lg font-black text-gray-900 mb-6 flex items-center gap-2">
                                <span class="w-8 h-8 bg-orange-100 rounded-xl flex items-center justify-center text-orange-600 text-sm">
                                    <i class="fas fa-wifi"></i>
                                </span>
                                Facilities
                            </h3>
                            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                                @php
                                    $amenityOptions = \App\Models\RoomOption::optionsFor('amenity');
                                @endphp
                                @foreach($amenityOptions->pluck('label')->unique() as $amenity)
                                <label class="flex items-center gap-3 p-4 bg-gray-50 rounded-2xl cursor-pointer hover:bg-indigo-50 transition border border-transparent hover:border-indigo-100">
                                    <input type="checkbox" name="amenities[]" value="{{ $amenity }}" 
                                           {{ in_array($amenity, $room->amenities ?? []) ? 'checked' : '' }}
                                           class="w-5 h-5 rounded-lg text-indigo-600 focus:ring-indigo-500 border-gray-300">
                                    <span class="font-bold text-gray-700 text-sm">{{ $amenity }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>

                        <!-- Landmarks (SEO) Card -->
                        <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 p-6">
                            <h3 class="text-lg font-black text-gray-900 mb-4 flex items-center gap-2">
                                <span class="w-8 h-8 bg-purple-100 rounded-xl flex items-center justify-center text-purple-600 text-sm">
                                    <i class="fas fa-university"></i>
                                </span>
                                Nearby Landmarks
                            </h3>
                            <p class="text-xs text-gray-500 mb-6 ml-10">Add colleges, malls, or tech parks nearby to improve search rankings.</p>
                            
                            <div class="space-y-4">
                                <div id="landmark-container" class="flex flex-wrap gap-2 p-4 bg-gray-50 rounded-2xl min-h-[60px]">
                                    @if($room->landmarks)
                                        @foreach($room->landmarks as $landmark)
                                        <div class="bg-indigo-100 text-indigo-700 font-bold px-4 py-2 rounded-xl flex items-center gap-2 text-sm shadow-sm border border-indigo-200">
                                            <span>{{ $landmark }}</span>
                                            <button type="button" onclick="this.parentElement.remove()" class="hover:text-red-500 transition-colors">
                                                <i class="fas fa-times"></i>
                                            </button>
                                            <input type="hidden" name="landmarks[]" value="{{ $landmark }}">
                                        </div>
                                        @endforeach
                                    @endif
                                    <input type="text" id="landmark-input" placeholder="Type and press Enter..." 
                                           class="flex-1 bg-transparent border-none focus:ring-0 text-sm font-medium min-w-[150px]">
                                </div>
                            </div>
                        </div>

                        <!-- Location Information Card -->
                        <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 p-6">
                            <h3 class="text-lg font-black text-gray-900 mb-6 flex items-center gap-2">
                                <span class="w-8 h-8 bg-red-100 rounded-xl flex items-center justify-center text-red-600 text-sm">
                                    <i class="fas fa-map-marker-alt"></i>
                                </span>
                                Location Details
                            </h3>
                            
                            <div class="space-y-6">
                                <div class="relative group">
                                    <input type="text" id="locationSearch" placeholder="Search for area or landmark..." 
                                           class="w-full pl-12 pr-4 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-indigo-500 transition text-sm font-medium">
                                    <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                                </div>

                                <div class="relative rounded-[2rem] overflow-hidden border border-gray-100 shadow-inner group">
                                    <div id="map" class="w-full h-80"></div>
                                    <button type="button" id="getCurrentLocationBtn" 
                                            class="absolute bottom-6 right-6 px-6 py-4 bg-white text-gray-900 rounded-2xl text-xs font-black shadow-2xl hover:bg-gray-50 flex items-center gap-2 transition-all active:scale-95 uppercase tracking-widest border border-gray-100">
                                        <i class="fas fa-crosshairs text-indigo-600 text-base"></i> Locate Me
                                    </button>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="p-4 bg-gray-50 rounded-2xl border border-gray-100">
                                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">City</p>
                                        <p id="city-text" class="text-sm font-black text-gray-900">{{ $room->city ?? '–' }}</p>
                                    </div>
                                    <div class="p-4 bg-gray-50 rounded-2xl border border-gray-100">
                                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">State</p>
                                        <p id="state-text" class="text-sm font-black text-gray-900">{{ $room->state ?? '–' }}</p>
                                    </div>
                                </div>

                                <div class="p-5 bg-gradient-to-br from-indigo-600 to-blue-700 rounded-3xl text-white shadow-lg overflow-hidden relative">
                                    <div class="relative z-10">
                                        <p class="text-[10px] font-black uppercase tracking-widest opacity-70 mb-1">Verified Address</p>
                                        <p id="full-address-text" class="text-sm font-bold leading-relaxed line-clamp-2">
                                            {{ $room->address ?? 'No address selected...' }}
                                        </p>
                                    </div>
                                    <i class="fas fa-map-marked-alt absolute -right-4 -bottom-4 text-8xl opacity-10 -rotate-12"></i>
                                </div>
                                
                                <div class="hidden">
                                    <input type="text" name="country" id="countryInput" value="{{ $room->country }}">
                                    <input type="text" name="state" id="stateInput" value="{{ $room->state }}">
                                    <input type="text" name="city" id="cityInput" value="{{ $room->city }}">
                                    <input type="text" name="address" id="location_address" value="{{ $room->address }}">
                                    <input type="hidden" name="latitude" id="latitude" value="{{ $room->latitude }}">
                                    <input type="hidden" name="longitude" id="longitude" value="{{ $room->longitude }}">
                                </div>
                            </div>
                        </div>

                        <!-- Action Button -->
                        <div class="owner-form-wide pt-2 px-2">
                            <button type="submit" 
                                    class="w-full bg-gradient-to-r from-indigo-600 to-blue-700 hover:from-indigo-700 hover:to-blue-800 text-white font-black py-5 px-4 rounded-3xl transition-all duration-300 shadow-xl hover:shadow-2xl transform active:scale-[0.98] flex items-center justify-center gap-3 text-lg uppercase tracking-widest">
                                <i class="fas fa-check-circle"></i> Save Changes
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </main>
    </div>
</div>

@include('owner.rooms.partials.editor-styles')

<!-- Location Instructions Modal -->
<div id="locationModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4 hidden">
    <div class="bg-white rounded-lg max-w-md w-full p-6">
        <h3 class="text-lg font-bold text-gray-800 mb-4">Enable Location Access</h3>
        <div class="space-y-4">
            <div>
                <p class="font-semibold text-gray-700">Chrome/Edge:</p>
                <ol class="list-decimal list-inside text-sm text-gray-600 ml-4">
                    <li>Click the lock icon <i class="fas fa-lock text-gray-400"></i> in the address bar</li>
                    <li>Set "Location" to "Allow"</li>
                    <li>Refresh the page and try again</li>
                </ol>
            </div>
            <div>
                <p class="font-semibold text-gray-700">Firefox:</p>
                <ol class="list-decimal list-inside text-sm text-gray-600 ml-4">
                    <li>Click the padlock icon <i class="fas fa-lock text-gray-400"></i> in the address bar</li>
                    <li>Set "Location" to "Allow"</li>
                    <li>Refresh the page and try again</li>
                </ol>
            </div>
            <div>
                <p class="font-semibold text-gray-700">Safari:</p>
                <ol class="list-decimal list-inside text-sm text-gray-600 ml-4">
                    <li>Go to Settings > Privacy > Location Services</li>
                    <li>Enable location for this website</li>
                    <li>Refresh the page and try again</li>
                </ol>
            </div>
        </div>
        <button onclick="closeLocationModal()" 
                class="mt-4 w-full bg-indigo-600 hover:bg-indigo-700 text-white py-2 px-4 rounded-lg">
            Got it, thanks!
        </button>
    </div>
</div>

@push('scripts')
<script>
const ROOM_PRIMARY_COLOR = '{{ \App\Models\Setting::get("primary_color", "#4F46E5") }}';
const razorpayKey = '{{ \App\Models\Setting::get("razorpay_key", "") }}';
const googleMapsKey = '{{ trim(\App\Models\Setting::get("google_maps_api_key", "")) }}';

window.gm_authFailure = function () {
    const mapElement = document.getElementById('map');
    if (mapElement) {
        mapElement.innerHTML = '<div class="h-full min-h-[260px] flex flex-col items-center justify-center bg-slate-50 p-8 text-center"><span class="w-12 h-12 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center mb-3"><i class="fas fa-map-location-dot"></i></span><p class="font-bold text-slate-800">Map is temporarily unavailable</p><p class="mt-1 text-xs text-slate-500">You can still search and enter the property address.</p></div>';
    }
};

// Initialize Map
let map;
let marker;
let geocoder;
let autocomplete;

// Function to extract and fill address components
function fillAddressComponents(place) {
    let country = '';
    let state = '';
    let city = '';
    
    if (place.address_components) {
        for (const component of place.address_components) {
            const types = component.types;
            
            if (types.includes('country')) country = component.long_name;
            if (types.includes('administrative_area_level_1')) state = component.long_name;
            if (types.includes('locality')) city = component.long_name;
            else if (types.includes('administrative_area_level_2') && !city) city = component.long_name;
        }
    }
    
    document.getElementById('city-text').textContent = city || '–';
    document.getElementById('state-text').textContent = state || '–';
    document.getElementById('full-address-text').textContent = place.formatted_address || '–';
    
    document.getElementById('countryInput').value = country || '';
    document.getElementById('stateInput').value = state || '';
    document.getElementById('cityInput').value = city || '';
    document.getElementById('location_address').value = place.formatted_address || '';
}

// Helper function to update location on map
function updateLocation(lat, lng, address) {
    document.getElementById('latitude').value = lat;
    document.getElementById('longitude').value = lng;
    
    if (marker) {
        marker.setPosition({ lat, lng });
    } else {
        marker = new google.maps.Marker({
            position: { lat, lng },
            map: map,
            draggable: true,
            icon: {
                path: google.maps.SymbolPath.CIRCLE,
                scale: 10,
                fillColor: ROOM_PRIMARY_COLOR,
                fillOpacity: 1,
                strokeWeight: 2,
                strokeColor: "#FFFFFF",
            }
        });
        
        marker.addListener('dragend', function(event) {
            const draggedLat = event.latLng.lat();
            const draggedLng = event.latLng.lng();
            document.getElementById('latitude').value = draggedLat;
            document.getElementById('longitude').value = draggedLng;
            
            geocoder.geocode({ location: { lat: draggedLat, lng: draggedLng } }, function(results, status) {
                if (status === 'OK' && results[0]) {
                    document.getElementById('full-address-text').textContent = results[0].formatted_address;
                    fillAddressComponents(results[0]);
                }
            });
        });
    }
    
    map.setCenter({ lat, lng });
    map.setZoom(16);
    
    if (address) {
        document.getElementById('location_address').value = address; // Update hidden input
        document.getElementById('full-address-text').textContent = address;
    } else {
        geocoder.geocode({ location: { lat, lng } }, function(results, status) {
            if (status === 'OK' && results[0]) {
                document.getElementById('location_address').value = results[0].formatted_address; // Update hidden input
                document.getElementById('full-address-text').textContent = results[0].formatted_address;
                fillAddressComponents(results[0]);
            }
        });
    }
}

// Function to get location using IP-based geolocation as fallback
function getLocationByIP() {
    const btn = document.getElementById('getCurrentLocationBtn');
    if (!btn) return;
    const originalText = btn.innerHTML;
    
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Getting location...';
    
    fetch('https://ipapi.co/json/')
        .then(response => response.json())
        .then(data => {
            if (data.latitude && data.longitude) {
                updateLocation(data.latitude, data.longitude, data.city + ', ' + data.region);
                
                document.getElementById('state-text').textContent = data.region || 'Not set';
                document.getElementById('city-text').textContent = data.city || 'Not set';
                document.getElementById('full-address-text').textContent = data.city + ', ' + data.region + ', ' + data.country_name;
                
                document.getElementById('countryInput').value = data.country_name || '';
                document.getElementById('stateInput').value = data.region || '';
                document.getElementById('cityInput').value = data.city || '';
                document.getElementById('location_address').value = data.city + ', ' + data.region + ', ' + data.country_name;
                
                btn.disabled = false;
                btn.innerHTML = originalText;
            } else {
                throw new Error('Could not determine location from IP');
            }
        })
        .catch(error => {
            console.error('Error getting location by IP:', error);
            alert('Could not determine your location. Please try selecting it manually on the map.');
            btn.disabled = false;
            btn.innerHTML = originalText;
        });
}

// Function to get current location
function getCurrentLocation() {
    const btn = document.getElementById('getCurrentLocationBtn');
    if (!btn) return;
    const originalText = btn.innerHTML;
    
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Getting location...';
    
    // IP-based fallback (immediate but less accurate)
    getLocationByIP();
    
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            function(position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                const accuracy = position.coords.accuracy;
                
                console.log(`GPS precision: ${accuracy} meters`);

                // Update with GPS (usually more accurate than IP)
                geocoder.geocode({ location: { lat, lng } }, function(results, status) {
                    if (status === 'OK' && results[0]) {
                        updateLocation(lat, lng, results[0].formatted_address);
                        fillAddressComponents(results[0]);
                        toastr.success('Precise location detected via GPS!');
                    }
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                });
            },
            function(error) {
                console.error('GPS failed:', error);
                // If IP fallback already succeeded, button might have been re-enabled
                // but we check here just in case.
                if (btn.disabled) {
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                    toastr.info('Using approximate IP-based location.');
                }
            },
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0
            }
        );
    } else {
        toastr.error('Geolocation is not supported by your browser.');
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
}

function initMap() {
    if (!googleMapsKey) {
        document.getElementById('map').innerHTML = '<div class="p-8 text-center text-gray-500"><i class="fas fa-exclamation-triangle text-3xl mb-3"></i><p>Google Maps API key not configured. Please add it in Business Settings.</p></div>';
        return;
    }

    const existingLat = parseFloat(document.getElementById('latitude').value) || 20.5937;
    const existingLng = parseFloat(document.getElementById('longitude').value) || 78.9629;
    const initialLocation = { lat: existingLat, lng: existingLng };
    
    map = new google.maps.Map(document.getElementById('map'), {
        center: initialLocation,
        zoom: existingLat !== 20.5937 ? 16 : 6,
        mapTypeControl: true,
        streetViewControl: true,
        fullscreenControl: true,
        styles: [
            {
                "featureType": "poi",
                "elementType": "labels",
                "stylers": [
                    { "visibility": "off" }
                ]
            }
        ]
    });

    geocoder = new google.maps.Geocoder();

    const searchInput = document.getElementById('locationSearch');
    if (searchInput) {
        autocomplete = new google.maps.places.Autocomplete(searchInput, {
            componentRestrictions: { country: 'in' },
            fields: ['geometry', 'formatted_address', 'name', 'address_components'],
            types: ['establishment', 'geocode']
        });

        autocomplete.addListener('place_changed', function() {
            const place = autocomplete.getPlace();
            if (!place.geometry || !place.geometry.location) {
                toastr.warning('No details available for the selected location');
                return;
            }
            const lat = place.geometry.location.lat();
            const lng = place.geometry.location.lng();
            const address = place.formatted_address || place.name;
            updateLocation(lat, lng, address);
            fillAddressComponents(place);
        });
    }

    if (existingLat !== 20.5937 && existingLng !== 78.9629) {
        marker = new google.maps.Marker({
            position: initialLocation,
            map: map,
            draggable: true,
            animation: google.maps.Animation.DROP
        });

        marker.addListener('dragend', function(event) {
            const draggedLat = event.latLng.lat();
            const draggedLng = event.latLng.lng();
            document.getElementById('latitude').value = draggedLat;
            document.getElementById('longitude').value = draggedLng;
            
            geocoder.geocode({ location: { lat: draggedLat, lng: draggedLng } }, function(results, status) {
                if (status === 'OK' && results[0]) {
                    fillAddressComponents(results[0]);
                }
            });
        });
    }

    map.addListener('click', function(event) {
        const lat = event.latLng.lat();
        const lng = event.latLng.lng();
        updateLocation(lat, lng);
    });

    if (currentLocationBtn) {
        currentLocationBtn.addEventListener('click', getCurrentLocation);
    }
}
window.initMap = initMap;

function loadGoogleMapsScript() {
    if (typeof google === 'undefined' || typeof google.maps === 'undefined') {
        if (googleMapsKey) {
            const script = document.createElement('script');
            script.src = `https://maps.googleapis.com/maps/api/js?key=${googleMapsKey}&libraries=places&callback=initMap&loading=async`;
            script.async = true;
            script.defer = true;
            script.onerror = function() {
                document.getElementById('map').innerHTML = '<div class="p-8 text-center text-red-500"><i class="fas fa-exclamation-triangle text-3xl mb-3"></i><p>Failed to load Google Maps.</p></div>';
            };
            document.head.appendChild(script);
        } else {
            document.getElementById('map').innerHTML = '<div class="p-8 text-center text-gray-500"><i class="fas fa-exclamation-triangle text-3xl mb-3"></i><p>Google Maps API key not configured.</p></div>';
        }
    } else {
        initMap();
    }
}

if (document.getElementById('map')) {
    loadGoogleMapsScript();
}

// Landmark Tag Logic
const landmarkInput = document.getElementById('landmark-input');
const landmarkContainer = document.getElementById('landmark-container');

if (landmarkInput) {
    landmarkInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            const value = this.value.trim();
            if (value && value.length > 0) {
                addLandmarkTag(value);
                this.value = '';
            }
        }
    });
}

function addLandmarkTag(text) {
    const tag = document.createElement('div');
    tag.className = 'bg-indigo-100 text-indigo-700 font-bold px-4 py-2 rounded-xl flex items-center gap-2 text-sm shadow-sm border border-indigo-200 animate-in fade-in zoom-in duration-300';
    const label = document.createElement('span');
    label.textContent = text;
    const remove = document.createElement('button');
    remove.type = 'button';
    remove.className = 'hover:text-red-500 transition-colors';
    remove.setAttribute('aria-label', 'Remove landmark');
    remove.innerHTML = '<i class="fas fa-times"></i>';
    remove.addEventListener('click', () => tag.remove());
    const hidden = document.createElement('input');
    hidden.type = 'hidden';
    hidden.name = 'landmarks[]';
    hidden.value = text;
    tag.append(label, remove, hidden);
    landmarkContainer.insertBefore(tag, landmarkInput);
}

// Media Handling
function handlePhotosUpload(event) {
    const files = event.target.files;
    const preview = document.getElementById('photosPreview');
    preview.innerHTML = '';
    preview.classList.remove('hidden');
    
    if (files.length > 5) {
        toastr.warning('Please select up to 5 photos only');
        event.target.value = '';
        return;
    }

    Array.from(files).forEach(file => {
        const reader = new FileReader();
        reader.onload = function(e) {
            const div = document.createElement('div');
            div.className = 'relative aspect-square rounded-2xl overflow-hidden border-2 border-white shadow-sm';
            div.innerHTML = `<img src="${e.target.result}" class="w-full h-full object-cover">`;
            preview.appendChild(div);
        }
        reader.readAsDataURL(file);
    });
}

function handleVideoUpload(event) {
    const file = event.target.files[0];
    const preview = document.getElementById('videoPreview');
    
    if (file) {
        if (file.size > 20 * 1024 * 1024) {
            toastr.error('Video size must be less than 20MB');
            event.target.value = '';
            return;
        }
        
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.classList.remove('hidden');
        }
        reader.readAsDataURL(file);
    }
}

// Form Submission
function showRoomFormErrors(form, payload, fallback) {
    const messages = payload?.errors
        ? Object.values(payload.errors).flat().filter(Boolean)
        : [];

    window.renderFormErrors?.(form, payload?.errors || {});

    if (messages.length) {
        messages.forEach(message => toastr.error(message, 'Please check the form'));
        return messages[0];
    }

    return payload?.message || fallback;
}

document.getElementById('editRoomForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const submitBtn = this.querySelector('button[type="submit"]');

    // Auto-tag current landmark input if not empty
    const lInput = document.getElementById('landmark-input');
    if (lInput && lInput.value.trim()) {
        const val = lInput.value.trim();
        addLandmarkTag(val);
        lInput.value = '';
    }

    const latitude = document.getElementById('latitude').value;
    const longitude = document.getElementById('longitude').value;
    
    if (!latitude || !longitude) {
        toastr.error('Please select a location on the map or use your current location');
        return;
    }
    
    const formData = new FormData(this);
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Updating...';
    
    try {
        const response = await fetch('{{ route("owner.rooms.update", $room->id) }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        });
        
        if (!response.ok) {
            const errorData = await response.json().catch(() => ({ message: 'Failed to update room' }));
            const requestError = new Error(showRoomFormErrors(this, errorData, 'Failed to update property'));
            requestError.alreadyShown = Boolean(errorData?.errors && Object.keys(errorData.errors).length);
            throw requestError;
        }
        
        const data = await response.json();
        
        if (data.success) {
            toastr.success(data.message || 'Property updated successfully!');
            setTimeout(() => {
                window.location.href = '{{ route("owner.rooms") }}';
            }, 1500);
        } else {
            showRoomFormErrors(this, data, 'Failed to update room');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    } catch (error) {
        console.error('Error:', error);
        if (!error.alreadyShown) {
            toastr.error(error.message || 'Something went wrong. Please try again.');
        }
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }
});

function toggleBrokerFee(type) {
    const container = document.getElementById('broker_fee_container');
    const input = document.getElementById('broker_fee');
    if (type === 'broker') {
        container.classList.remove('hidden');
        input.required = true;
    } else {
        container.classList.add('hidden');
        input.required = false;
        input.value = 0;
    }
}

// Mobile Menu Toggle
document.addEventListener('DOMContentLoaded', function() {
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const mobileMenu = document.getElementById('mobileMenu');
    
    if (mobileMenuBtn && mobileMenu) {
        mobileMenuBtn.addEventListener('click', function() {
            mobileMenu.classList.toggle('hidden');
        });
    }
});
</script>
@endpush
@endsection
