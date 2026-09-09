<section class="market-hero">
    <div class="market-hero-box">
        <div class="market-hero-slider" id="marketHeroSlider">
            @foreach($heroImages as $idx => $img)
                <div class="market-hero-slide {{ $idx === 0 ? 'is-active' : '' }}" style="background-image:url('{{ $img }}')"></div>
            @endforeach
        </div>
        <div class="market-hero-container">
            <div class="market-hero-top">
                <div class="market-hero-copy">
                    <span class="market-eyebrow"><i class="fas fa-shield-halved"></i>100% Verified Properties · Direct Owners &amp; Trusted Agents</span>
                    <h1>Find your perfect property <span>@if($displayCity)in {{ $displayCity }}@else near you @endif</span></h1>
                    <p>{{ $heroDescription }}</p>
                    <div class="market-benefits">
                        <span><i class="fas fa-shield-halved"></i>Verified Listings</span>
                        <span><i class="fas fa-house-chimney-user"></i>Direct Owners &amp; Agents</span>
                        <span><i class="fas fa-unlock-keyhole"></i>Instant Unlock</span>
                    </div>
                </div>
                <div class="market-city-card"><small><i class="fas fa-location-arrow"></i> Currently available in</small><strong>{{ $displayCity ?: 'Your city' }}</strong><span>More cities coming soon!</span></div>
            </div>
            <div class="market-search-wrap">
                <!-- Purpose Switcher Tabs (Rent vs Buy) -->
                <div class="hero-purpose-tabs" style="display:flex;gap:8px;margin-bottom:12px;">
                    <button type="button" id="heroTabRent" onclick="setHeroPurpose('rent')" class="hero-purpose-btn" style="padding:9px 20px;border-radius:12px;font-size:13px;font-weight:800;cursor:pointer;border:none;background:#2563eb;color:#fff;display:inline-flex;align-items:center;gap:7px;transition:all 0.2s;box-shadow:0 2px 10px rgba(37,99,235,0.35);">
                        <i class="fas fa-key text-xs"></i> Rent
                    </button>
                    <button type="button" id="heroTabSell" onclick="setHeroPurpose('sell')" class="hero-purpose-btn" style="padding:9px 20px;border-radius:12px;font-size:13px;font-weight:800;cursor:pointer;border:none;background:rgba(255,255,255,0.9);color:#475569;display:inline-flex;align-items:center;gap:7px;transition:all 0.2s;">
                        <i class="fas fa-tag text-xs" style="color:#7c3aed;"></i> Buy / Sale <span style="background:#f3e8ff;color:#7c3aed;font-size:9px;padding:2px 7px;border-radius:999px;font-weight:900;margin-left:3px;">NEW</span>
                    </button>
                </div>

                <form action="{{ route('rooms.index') }}" method="GET" class="market-search" id="heroSearchForm">
                    <input type="hidden" name="purpose" id="heroPurposeInput" value="rent">
                    <div class="market-search-grid">
                         <div class="market-field market-field-location"><i class="market-field-icon fas fa-location-dot"></i><label for="city">Location</label><input id="city" name="city" value="{{ $displayCity }}" placeholder="City or locality"></div>
                         <div class="market-field"><i class="market-field-icon fas fa-house"></i><label for="property_type_id">Property Type</label><select id="property_type_id" name="property_type_id"><option value="">Any type</option>@foreach($propertyTypes as $type)<option value="{{ $type->id }}" @selected(request('property_type_id') == $type->id)>{{ $type->name }}</option>@endforeach</select></div>
                         
                         {{-- Budget field: Rent vs Sell --}}
                         <div class="market-field" id="heroBudgetRent">
                             <i class="market-field-icon fas fa-indian-rupee-sign"></i>
                             <label for="max_rent">Monthly Budget</label>
                             <input id="max_rent" type="number" min="0" name="max_rent" placeholder="Max rent (e.g. 15000)">
                         </div>
                         <div class="market-field" id="heroBudgetSell" style="display:none;">
                             <i class="market-field-icon fas fa-tag" style="color:#7c3aed;"></i>
                             <label for="max_price">Total Budget (₹)</label>
                             <input id="max_price" type="number" min="0" name="max_price" placeholder="Max price (e.g. 5000000)" disabled>
                         </div>

                         {{-- 4th field: Tenant (Rent) / Possession (Sell) --}}
                         <div class="market-field" id="heroFieldTenant">
                             <i class="market-field-icon fas fa-user"></i>
                             <label for="tenant_type">Preferred For</label>
                             <select id="tenant_type" name="tenant_type[]">
                                 <option value="">Anyone</option>
                                 @foreach(\App\Models\RoomOption::optionsFor('tenant_type') as $option)
                                     <option value="{{ $option->id }}">{{ $option->label }}</option>
                                 @endforeach
                             </select>
                         </div>
                         <div class="market-field" id="heroFieldPossession" style="display:none;">
                             <i class="market-field-icon fas fa-clock" style="color:#7c3aed;"></i>
                             <label for="hero_possession_status">Possession</label>
                             <select id="hero_possession_status" name="possession_status" disabled>
                                 <option value="">Any status</option>
                                 <option value="ready_to_move">Ready to Move</option>
                                 <option value="under_construction">Under Construction</option>
                             </select>
                         </div>

                        <button type="submit" id="heroSearchBtn"><i class="fas fa-magnifying-glass"></i><span id="heroSearchBtnText">{{ $text('home_search_button','Search Properties') }}</span></button>
                    </div>
                </form>
            </div>
<script>
function setHeroPurpose(purpose) {
    const isSell = purpose === 'sell';
    document.getElementById('heroPurposeInput').value = purpose;
    const btnRent = document.getElementById('heroTabRent');
    const btnSell = document.getElementById('heroTabSell');
    if (btnRent && btnSell) {
        if (isSell) {
            btnSell.style.background = '#7c3aed';
            btnSell.style.color = '#fff';
            btnSell.style.boxShadow = '0 2px 10px rgba(124,58,237,0.35)';
            btnRent.style.background = 'rgba(255,255,255,0.9)';
            btnRent.style.color = '#475569';
            btnRent.style.boxShadow = 'none';
        } else {
            btnRent.style.background = '#2563eb';
            btnRent.style.color = '#fff';
            btnRent.style.boxShadow = '0 2px 10px rgba(37,99,235,0.35)';
            btnSell.style.background = 'rgba(255,255,255,0.9)';
            btnSell.style.color = '#475569';
            btnSell.style.boxShadow = 'none';
        }
    }
    const rentBudget = document.getElementById('heroBudgetRent');
    const sellBudget = document.getElementById('heroBudgetSell');
    const rentInput = document.getElementById('max_rent');
    const sellInput = document.getElementById('max_price');
    if (rentBudget && sellBudget) {
        rentBudget.style.display = isSell ? 'none' : 'flex';
        sellBudget.style.display = isSell ? 'flex' : 'none';
        rentInput.disabled = isSell;
        sellInput.disabled = !isSell;
    }
    const tenantField = document.getElementById('heroFieldTenant');
    const possessionField = document.getElementById('heroFieldPossession');
    const tenantInput = document.getElementById('tenant_type');
    const possessionInput = document.getElementById('hero_possession_status');
    if (tenantField && possessionField) {
        tenantField.style.display = isSell ? 'none' : 'flex';
        possessionField.style.display = isSell ? 'flex' : 'none';
        tenantInput.disabled = isSell;
        possessionInput.disabled = !isSell;
    }
    const btnText = document.getElementById('heroSearchBtnText');
    if (btnText) {
        btnText.textContent = isSell ? 'Search for Sale' : 'Search Properties';
    }
}
</script>
        </div>
    </div>
    <div class="market-wrap">
        @include('partials.adsense-slot', ['placement' => 'home_top'])
        <div class="market-stats">
            @foreach([['fa-house-circle-check',number_format($totalRooms).'+','Verified rooms'],['fa-user-check',number_format($totalOwners).'+','Verified owners'],['fa-location-dot',number_format($totalAreas).'+','Popular areas'],['fa-clock','24/7','Customer support']] as $stat)
                <div class="market-stat"><span><i class="fas {{ $stat[0] }}"></i></span><div><strong>{{ $stat[1] }}</strong><small>{{ $stat[2] }}</small></div></div>
            @endforeach
        </div>
        @if($cityContext['isFallback'])
            <div class="launch-banner">
                <div><strong>Launching soon in {{ $cityContext['launchingSoonCityName'] }}</strong><span>We're currently active in {{ $cityContext['activeCityName'] }}. Showing verified {{ $cityContext['activeCityName'] }} properties for now.</span></div>
                <a href="{{ route('rooms.index', ['city' => $cityContext['activeCityName']]) }}">View {{ $cityContext['activeCityName'] }}</a>
            </div>
        @endif
    </div>
</section>
