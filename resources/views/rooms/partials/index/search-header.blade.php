<!-- ===== TOP SEARCH HEADER BAR ===== -->
<div class="rooms-search-shell border-b border-slate-200/80 hidden lg:block">
    <div class="w-full">
        <div class="rooms-search-panel bg-white border border-slate-200/90 px-4 py-2.5 rounded-2xl shadow-sm">
            <form action="{{ route('rooms.index') }}" method="GET" class="flex flex-wrap gap-4 items-center justify-between">
                @if(request('purpose'))
                    <input type="hidden" name="purpose" value="{{ request('purpose') }}">
                @endif
                <!-- Location -->
                <div class="flex-1 min-w-[200px] border-r border-slate-100 pr-4">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest flex items-center justify-between mb-1">
                        <span>Location</span>
                        <button type="button" onclick="detectLocation(true)" class="rooms-theme-link text-[9px] flex items-center gap-0.5 font-bold">
                            <i class="fas fa-location-crosshairs"></i> Near Me
                        </button>
                    </label>
                    <div class="relative">
                        <i class="fas fa-map-pin absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                        <input type="text" name="city" id="hero-city-input"
                               value="{{ $displayCity }}"
                               placeholder="City or area..."
                               class="w-full py-2 pl-8 pr-7 bg-slate-50 border border-slate-200 text-slate-800 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 focus:bg-white outline-none transition-all">
                        @if(request('city') || session('user_city'))
                            <a href="{{ route('rooms.index', ['clear' => 1]) }}" class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 hover:text-red-500">
                                <i class="fas fa-times-circle text-xs"></i>
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Property Type -->
                <div class="flex-1 min-w-[180px] border-r border-slate-100 pr-4">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-1">Property Type</label>
                    <div class="relative">
                        <i class="fas fa-building absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                        <select name="property_type_id" class="w-full py-2 pl-8 pr-3 bg-slate-50 border border-slate-200 text-slate-800 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 focus:bg-white outline-none appearance-none transition-all">
                            <option value="">Any Type</option>
                            @foreach($propertyTypes as $type)
                                <option value="{{ $type->id }}" {{ request('property_type_id') == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Property Category -->
                <div class="flex-1 min-w-[180px] border-r border-slate-100 pr-4">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-1">Category</label>
                    <div class="relative">
                        <i class="fas fa-layer-group absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                        <select name="property_category_id" class="w-full py-2 pl-8 pr-3 bg-slate-50 border border-slate-200 text-slate-800 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 focus:bg-white outline-none appearance-none transition-all">
                            <option value="">Any Category</option>
                            @foreach(($propertyCategories ?? collect()) as $category)
                                <option value="{{ $category->id }}" {{ request('property_category_id') == $category->id ? 'selected' : '' }}>{{ $category->propertyType?->name ? $category->propertyType->name.' - ' : '' }}{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Budget -->
                <div class="flex-1 min-w-[150px] border-r border-slate-100 pr-4">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-1">Max Budget (₹/mo)</label>
                    <div class="relative">
                        <i class="fas fa-rupee-sign absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                        <input type="number" name="max_rent" value="{{ request('max_rent') }}" placeholder="Max"
                               class="w-full py-2 pl-7 pr-3 bg-slate-50 border border-slate-200 text-slate-800 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 focus:bg-white outline-none transition-all">
                    </div>
                </div>

                <!-- Gender -->
                <div class="flex-1 min-w-[150px] border-r border-slate-100 pr-4">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-1">Preferred For</label>
                    <div class="relative">
                        <i class="fas fa-users absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                        <select name="tenant_type[]" class="w-full py-2 pl-8 pr-3 bg-slate-50 border border-slate-200 text-slate-800 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 focus:bg-white outline-none appearance-none transition-all">
                            <option value="">Any Gender</option>
                            @foreach(App\Models\RoomOption::optionsFor('tenant_type') as $option)
                                <option value="{{ $option->id }}" {{ in_array($option->id, (array)request('tenant_type')) ? 'selected' : '' }}>{{ $option->label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Search Button -->
                <div class="w-[120px] pl-1">
                    <button type="submit" class="room-theme-primary-button w-full py-2.5 font-extrabold rounded-xl transition-all shadow-md flex items-center justify-center gap-1.5 text-xs">
                        <i class="fas fa-search text-[10px]"></i> Search Properties
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>

<!-- Mobile layout search box -->
<div class="md:hidden">
    @include('partials.mobile-search')
</div>
