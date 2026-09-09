@extends('layouts.public')

@section('title', 'Map Search - Find Rooms Near You')

@section('content')
<div class="ms-wrapper">
    {{-- ============== TOP HEADER ============== --}}
    <header class="ms-header">
        <div class="ms-header-inner">
            <div class="ms-brand">
                <button type="button" onclick="history.length > 1 ? history.back() : (window.location.href='{{ route('home') }}')" class="ms-back-btn" title="Go back" aria-label="Go back">
                    <i class="fas fa-arrow-left"></i>
                    <span class="ms-back-text">Back</span>
                </button>
                <div class="ms-brand-icon" style="background: linear-gradient(135deg, var(--primary, #6366f1) 0%, var(--secondary, #8b5cf6) 100%); box-shadow: 0 4px 12px rgba(var(--primary-rgb, 99, 102, 241), 0.35);">
                    <i class="fas fa-map-marked-alt"></i>
                </div>
                <div>
                    <h1 class="ms-title">Map Search</h1>
                    <p class="ms-subtitle">Discover rooms around you</p>
                </div>
            </div>

            <div class="ms-stats">
                <div class="ms-stat-chip">
                    <i class="fas fa-building text-indigo-500"></i>
                    <span class="ms-stat-value" id="roomCount">{{ count($markers) }}</span>
                    <span class="ms-stat-label">Properties</span>
                </div>
                <div class="ms-stat-chip ms-stat-chip-accent">
                    <i class="fas fa-map-marker-alt text-emerald-500"></i>
                    <span class="ms-stat-value" id="cityCount">{{ $cities->count() }}</span>
                    <span class="ms-stat-label">Cities</span>
                </div>
            </div>
        </div>

        {{-- ============== FILTER BAR ============== --}}
        <div class="ms-filterbar">
            <div class="ms-filter-group">
                <label class="ms-filter-label"><i class="fas fa-city"></i> City</label>
                <div class="ms-select-wrap">
                    <select id="cityFilter" class="ms-input">
                        <option value="">All Cities</option>
                        @foreach($cities as $city)
                            <option value="{{ $city->name }}">{{ $city->name }}</option>
                        @endforeach
                    </select>
                    <i class="fas fa-chevron-down ms-select-caret"></i>
                </div>
            </div>

            <div class="ms-filter-group">
                <label class="ms-filter-label"><i class="fas fa-tags"></i> Purpose</label>
                <div class="ms-select-wrap">
                    <select id="purposeFilter" class="ms-input">
                        <option value="">All (Rent &amp; Buy)</option>
                        <option value="rent">For Rent</option>
                        <option value="sell">For Sale / Buy</option>
                    </select>
                    <i class="fas fa-chevron-down ms-select-caret"></i>
                </div>
            </div>

            <div class="ms-filter-group">
                <label class="ms-filter-label"><i class="fas fa-indian-rupee-sign"></i> Min Budget</label>
                <input type="number" id="minRent" placeholder="0" class="ms-input" min="0">
            </div>

            <div class="ms-filter-group">
                <label class="ms-filter-label"><i class="fas fa-indian-rupee-sign"></i> Max Budget</label>
                <input type="number" id="maxRent" placeholder="Any" class="ms-input" min="0">
            </div>

            <div class="ms-filter-actions">
                <button id="searchBtn" type="button" class="ms-btn ms-btn-primary">
                    <i class="fas fa-search"></i> Search
                </button>
                <button id="resetBtn" type="button" class="ms-btn ms-btn-ghost">
                    <i class="fas fa-rotate"></i> Reset
                </button>
            </div>
        </div>

        <div id="loadingBar" class="ms-loading-bar">
            <div class="ms-loading-bar-inner"></div>
        </div>
    </header>

    {{-- ============== MAP ============== --}}
    <div class="ms-map-area">
        <div id="map"></div>

        {{-- Empty state --}}
        <div id="emptyState" class="ms-empty-state hidden">
            <div class="ms-empty-icon" style="background: linear-gradient(135deg, rgba(var(--primary-rgb, 99, 102, 241), 0.08) 0%, rgba(var(--secondary-rgb, 139, 92, 246), 0.08) 100%); color: var(--primary, #6366f1);">
                <i class="fas fa-map-marked-alt"></i>
            </div>
            <h3 class="ms-empty-title">No properties found</h3>
            <p class="ms-empty-text">Try changing the filters or zooming out to see more results.</p>
        </div>

        {{-- Floating info card --}}
        <div class="ms-floating-card">
            <div class="ms-floating-card-row">
                <div class="ms-floating-dot"></div>
                <span>Drag the map to explore</span>
            </div>
            <div class="ms-floating-card-row">
                <i class="fas fa-mouse-pointer text-indigo-500"></i>
                <span>Click a marker for details</span>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<style>
/* ============== PAGE LAYOUT ============== */
html, body { height: 100%; margin: 0; }
.ms-wrapper {
    display: flex;
    flex-direction: column;
    height: calc(100vh - 64px); /* subtract navbar */
    background: #f8fafc;
    margin-top: 0;
}

/* ============== HEADER ============== */
.ms-header {
    background: linear-gradient(135deg, #ffffff 0%, #f1f5f9 100%);
    border-bottom: 1px solid #e2e8f0;
    box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
    position: relative;
    z-index: 30;
}
.ms-header-inner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    padding: 14px 20px;
    flex-wrap: wrap;
}
.ms-brand {
    display: flex;
    align-items: center;
    gap: 12px;
}
.ms-back-btn {
    width: auto;
    padding: 0 14px 0 12px;
    height: 40px;
    border-radius: 10px;
    background: #ffffff;
    border: 1.5px solid #e2e8f0;
    color: #475569;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    font-size: 13px;
    font-weight: 700;
    text-decoration: none;
    transition: all 0.15s;
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
    flex-shrink: 0;
    white-space: nowrap;
}
.ms-back-btn:hover {
    background: var(--primary, #6366f1);
    border-color: var(--primary, #6366f1);
    color: #ffffff;
    transform: translateX(-2px);
    box-shadow: 0 4px 10px rgba(var(--primary-rgb, 99, 102, 241), 0.3);
}
.ms-back-btn:active { transform: translateX(0); }
.ms-brand-icon {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    box-shadow: 0 4px 12px rgba(99, 102, 241, 0.35);
}
.ms-title {
    font-size: 1.25rem;
    font-weight: 800;
    color: #0f172a;
    line-height: 1.1;
    margin: 0;
    letter-spacing: -0.01em;
}
.ms-subtitle {
    font-size: 0.75rem;
    color: #64748b;
    margin: 2px 0 0;
    font-weight: 500;
}

.ms-stats {
    display: flex;
    gap: 10px;
    align-items: center;
}
.ms-stat-chip {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    padding: 8px 14px;
    border-radius: 999px;
    font-size: 0.8125rem;
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
    transition: transform 0.15s;
}
.ms-stat-chip:hover { transform: translateY(-1px); }
.ms-stat-chip-accent { background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%); border-color: #a7f3d0; }
.ms-stat-value { font-weight: 800; color: #0f172a; }
.ms-stat-label { color: #64748b; font-weight: 600; }

/* ============== FILTER BAR ============== */
.ms-filterbar {
    display: flex;
    align-items: flex-end;
    gap: 12px;
    padding: 12px 20px 16px;
    flex-wrap: wrap;
    background: #ffffff;
    border-top: 1px solid #f1f5f9;
}
.ms-filter-group {
    display: flex;
    flex-direction: column;
    gap: 4px;
    min-width: 140px;
    flex: 1 1 140px;
    max-width: 220px;
}
.ms-filter-label {
    font-size: 0.6875rem;
    font-weight: 700;
    color: #475569;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}
.ms-filter-label i { color: var(--primary, #6366f1); font-size: 0.75rem; }

.ms-input {
    width: 100%;
    height: 40px;
    padding: 0 12px;
    background: #f8fafc;
    border: 1.5px solid #e2e8f0;
    border-radius: 10px;
    font-size: 0.875rem;
    color: #0f172a;
    font-weight: 500;
    transition: all 0.15s;
    font-family: inherit;
}
.ms-input:hover { border-color: #cbd5e1; }
.ms-input:focus { outline: none; background: #ffffff; border-color: var(--primary, #6366f1); box-shadow: 0 0 0 3px rgba(var(--primary-rgb, 99, 102, 241), 0.12); }

.ms-select-wrap { position: relative; }
.ms-select-wrap .ms-input {
    appearance: none;
    -webkit-appearance: none;
    padding-right: 32px;
    cursor: pointer;
}
.ms-select-caret {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
    font-size: 0.75rem;
    pointer-events: none;
}

.ms-filter-actions {
    display: flex;
    gap: 8px;
    align-items: flex-end;
    margin-left: auto;
}
.ms-btn {
    height: 40px;
    padding: 0 18px;
    border-radius: 10px;
    font-size: 0.875rem;
    font-weight: 700;
    cursor: pointer;
    border: 1.5px solid transparent;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.15s;
    font-family: inherit;
    white-space: nowrap;
}
.ms-btn-primary {
    background: linear-gradient(135deg, var(--primary, #6366f1) 0%, var(--secondary, #8b5cf6) 100%);
    color: #ffffff;
    box-shadow: 0 4px 12px rgba(var(--primary-rgb, 99, 102, 241), 0.3);
}
.ms-btn-primary:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 16px rgba(var(--primary-rgb, 99, 102, 241), 0.4);
}
.ms-btn-primary:active { transform: translateY(0); }
.ms-btn-ghost {
    background: #ffffff;
    color: #475569;
    border-color: #e2e8f0;
}
.ms-btn-ghost:hover {
    background: #f1f5f9;
    color: #0f172a;
}

/* ============== LOADING BAR ============== */
.ms-loading-bar {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: transparent;
    overflow: hidden;
    opacity: 0;
    transition: opacity 0.2s;
}
.ms-loading-bar:not(.hidden) { opacity: 1; }
.ms-loading-bar-inner {
    height: 100%;
    width: 40%;
    background: linear-gradient(90deg, transparent, var(--primary, #6366f1), var(--secondary, #8b5cf6), transparent);
    animation: msLoading 1.2s ease-in-out infinite;
}
@keyframes msLoading {
    0% { transform: translateX(-100%); }
    100% { transform: translateX(350%); }
}
.hidden { display: none !important; }

/* ============== MAP AREA ============== */
.ms-map-area {
    flex: 1;
    position: relative;
    background: #e2e8f0;
}
#map { width: 100%; height: 100%; }

/* ============== EMPTY STATE ============== */
.ms-empty-state {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: rgba(255, 255, 255, 0.97);
    backdrop-filter: blur(8px);
    padding: 32px 40px;
    border-radius: 16px;
    text-align: center;
    box-shadow: 0 8px 32px rgba(15, 23, 42, 0.12);
    border: 1px solid #e2e8f0;
    z-index: 500;
    max-width: 360px;
}
.ms-empty-icon {
    width: 64px;
    height: 64px;
    margin: 0 auto 16px;
    border-radius: 50%;
    background: linear-gradient(135deg, #eef2ff 0%, #f5f3ff 100%);
    color: #6366f1;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 26px;
}
.ms-empty-title {
    font-size: 1.125rem;
    font-weight: 800;
    color: #0f172a;
    margin: 0 0 6px;
}
.ms-empty-text {
    font-size: 0.875rem;
    color: #64748b;
    margin: 0;
}

/* ============== FLOATING CARD ============== */
.ms-floating-card {
    position: absolute;
    bottom: 20px;
    left: 20px;
    background: rgba(255, 255, 255, 0.97);
    backdrop-filter: blur(10px);
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 10px 14px;
    box-shadow: 0 4px 16px rgba(15, 23, 42, 0.08);
    z-index: 400;
    display: flex;
    flex-direction: column;
    gap: 6px;
}
.ms-floating-card-row {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.75rem;
    color: #475569;
    font-weight: 600;
}
.ms-floating-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #10b981;
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2);
    animation: pulse 2s infinite;
}
@keyframes pulse {
    0%, 100% { box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2); }
    50% { box-shadow: 0 0 0 6px rgba(16, 185, 129, 0.05); }
}

/* ============== CUSTOM MARKER ============== */
.custom-marker { background: transparent; border: 0; }
.ms-marker-pin {
    width: 36px;
    height: 36px;
    border-radius: 50% 50% 50% 0;
    background: linear-gradient(135deg, var(--primary, #6366f1) 0%, var(--secondary, #8b5cf6) 100%);
    transform: rotate(-45deg);
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 12px rgba(var(--primary-rgb, 99, 102, 241), 0.45), 0 0 0 3px rgba(255, 255, 255, 0.9);
    border: 2px solid #ffffff;
    position: relative;
    transition: transform 0.2s;
}
.ms-marker-pin:hover { transform: rotate(-45deg) scale(1.15); }
.ms-marker-pin i {
    transform: rotate(45deg);
    color: #ffffff;
    font-size: 13px;
}

/* ============== POPUP ============== */
.leaflet-popup-content-wrapper {
    border-radius: 14px;
    padding: 0;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(15, 23, 42, 0.18);
}
.leaflet-popup-content {
    margin: 0;
    min-width: 240px;
    max-width: 280px;
}
.ms-popup { font-family: inherit; }
.ms-popup-img {
    width: 100%;
    height: 130px;
    object-fit: cover;
    background: linear-gradient(135deg, rgba(var(--primary-rgb, 99, 102, 241), 0.1) 0%, rgba(var(--secondary-rgb, 139, 92, 246), 0.1) 100%);
    display: block;
}
.ms-popup-body { padding: 12px 14px 14px; }
.ms-popup-title {
    font-size: 0.9375rem;
    font-weight: 800;
    color: #0f172a;
    margin: 0 0 4px;
    line-height: 1.3;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.ms-popup-loc {
    font-size: 0.75rem;
    color: #64748b;
    margin: 0 0 8px;
    display: flex;
    align-items: center;
    gap: 4px;
}
.ms-popup-loc i { color: var(--primary, #6366f1); }
.ms-popup-meta {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-top: 8px;
    border-top: 1px solid #f1f5f9;
    margin-top: 8px;
}
.ms-popup-price {
    font-size: 1rem;
    font-weight: 800;
    color: var(--primary, #6366f1);
}
}
.ms-popup-price small { font-size: 0.6875rem; color: #94a3b8; font-weight: 500; }
.ms-popup-link {
    background: var(--primary, #6366f1);
    color: #ffffff;
    padding: 6px 12px;
    border-radius: 8px;
    font-size: 0.75rem;
    font-weight: 700;
    text-decoration: none;
    transition: background 0.15s;
}
.ms-popup-link:hover { filter: brightness(0.9); color: #ffffff; }
.leaflet-popup-tip { box-shadow: none; }

/* ============== RESPONSIVE ============== */
@media (max-width: 768px) {
    .ms-wrapper { height: calc(100vh - 60px); }
    .ms-header-inner { padding: 10px 14px; gap: 10px; }
    .ms-back-btn { width: 36px; height: 36px; font-size: 12px; padding: 0; }
    .ms-back-text { display: none; }
    .ms-brand-icon { width: 38px; height: 38px; font-size: 16px; }
    .ms-title { font-size: 1.0625rem; }
    .ms-subtitle { font-size: 0.6875rem; }
    .ms-stats { gap: 6px; }
    .ms-stat-chip { padding: 6px 10px; font-size: 0.75rem; }
    .ms-stat-label { display: none; }
    .ms-filterbar { padding: 10px 14px 12px; gap: 8px; }
    .ms-filter-group { min-width: 0; flex: 1 1 calc(50% - 4px); max-width: none; }
    .ms-filter-actions { width: 100%; }
    .ms-btn { flex: 1; justify-content: center; }
    .ms-floating-card { left: 10px; bottom: 10px; padding: 8px 10px; }
    .ms-empty-state { padding: 24px 20px; max-width: 280px; }
}
</style>

<script>
(function () {
    'use strict';

    const initialMarkers = @json($markers);
    const initialCities = {{ $cities->count() }};
    let map, markersLayer;
    let allMarkers = initialMarkers.slice();
    let isLoading = false;
    let moveTimer = null;

    const $ = (id) => document.getElementById(id);

    function showLoading(show) {
        const bar = $('loadingBar');
        if (show) bar.classList.remove('hidden');
        else bar.classList.add('hidden');
    }

    function setRoomCount(n) {
        $('roomCount').textContent = n;
        const empty = $('emptyState');
        if (n === 0) empty.classList.remove('hidden');
        else empty.classList.add('hidden');
    }

    function escapeHtml(s) {
        if (s == null) return '';
        return String(s).replace(/[&<>"']/g, function (c) {
            return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'})[c];
        });
    }

    function buildPopupHtml(m) {
        const title = escapeHtml(m.title || 'Untitled');
        const city = escapeHtml(m.city || '');
        const area = escapeHtml(m.area || '');
        const loc = [area, city].filter(Boolean).join(', ');
        const isSell = Boolean(m.is_for_sell || m.purpose === 'sell');
        const price = m.display_price || (m.rent ? '₹' + Number(m.rent).toLocaleString('en-IN') : '—');
        const priceSuffix = isSell ? '' : ' <small>/mo</small>';
        const badge = isSell
            ? `<span style="display:inline-block;padding:2px 8px;border-radius:6px;background:#7c3aed;color:#ffffff;font-size:10px;font-weight:800;letter-spacing:0.5px;margin-bottom:6px;">FOR SALE</span>`
            : '';
        const img = m.thumb ? `<img class="ms-popup-img" src="${escapeHtml(m.thumb)}" alt="${title}" onerror="this.style.display='none'">` : '';
        const url = m.url || '#';
        return `
            <div class="ms-popup">
                ${img}
                <div class="ms-popup-body">
                    ${badge}
                    <h4 class="ms-popup-title">${title}</h4>
                    ${loc ? `<p class="ms-popup-loc"><i class="fas fa-map-marker-alt"></i> ${loc}</p>` : ''}
                    <div class="ms-popup-meta">
                        <div class="ms-popup-price" style="${isSell ? 'color:#7c3aed;' : ''}">${price}${priceSuffix}</div>
                        <a class="ms-popup-link" href="${escapeHtml(url)}" style="${isSell ? 'background:#7c3aed;' : ''}">View <i class="fas fa-arrow-right text-[9px]"></i></a>
                    </div>
                </div>
            </div>`;
    }

    function renderMarkers(markers) {
        if (!markersLayer) return;
        markersLayer.clearLayers();
        markers.forEach(m => {
            if (!m.lat || !m.lng) return;
            const icon = L.divIcon({
                className: 'custom-marker',
                html: '<div class="ms-marker-pin"><i class="fas fa-home"></i></div>',
                iconSize: [36, 36],
                iconAnchor: [18, 36],
                popupAnchor: [0, -36],
            });
            const marker = L.marker([m.lat, m.lng], { icon }).bindPopup(buildPopupHtml(m), { maxWidth: 280 });
            markersLayer.addLayer(marker);
        });
        setRoomCount(markers.length);
    }

    function computeBounds(markers) {
        if (!markers.length) return null;
        const valid = markers.filter(m => m.lat && m.lng);
        if (!valid.length) return null;
        const lats = valid.map(m => m.lat);
        const lngs = valid.map(m => m.lng);
        return [[Math.min(...lats), Math.min(...lngs)], [Math.max(...lats), Math.max(...lngs)]];
    }

    function debounce(fn, ms) {
        let t;
        return function () { clearTimeout(t); t = setTimeout(fn, ms); };
    }

    function initMap() {
        const defaultCenter = initialMarkers.length > 0
            ? [initialMarkers[0].lat, initialMarkers[0].lng]
            : [22.7196, 75.8577];
        const defaultZoom = initialMarkers.length > 0 ? 12 : 6;

        map = L.map('map', { zoomControl: true }).setView(defaultCenter, defaultZoom);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors',
            maxZoom: 19,
        }).addTo(map);

        markersLayer = L.layerGroup().addTo(map);
        renderMarkers(allMarkers);

        if (initialMarkers.length > 0) {
            setTimeout(() => {
                const b = computeBounds(initialMarkers);
                if (b) map.fitBounds(b, { padding: [40, 40], animate: false });
            }, 100);
        }

        map.on('moveend', scheduleReload);

        $('searchBtn').addEventListener('click', manualSearch);
        $('resetBtn').addEventListener('click', resetFilters);

        ['cityFilter', 'purposeFilter'].forEach(id => {
            const el = $(id);
            if (el) el.addEventListener('change', manualSearch);
        });
        ['minRent', 'maxRent'].forEach(id => {
            const el = $(id);
            if (el) el.addEventListener('input', debounce(manualSearch, 600));
        });
    }

    function scheduleReload() {
        if (moveTimer) clearTimeout(moveTimer);
        moveTimer = setTimeout(() => fetchMarkersInView(), 600);
    }

    function fetchMarkersInView() {
        if (isLoading) return Promise.resolve();
        isLoading = true;
        showLoading(true);

        const params = new URLSearchParams();
        const city = $('cityFilter') ? $('cityFilter').value : '';
        const purpose = $('purposeFilter') ? $('purposeFilter').value : '';
        const minRent = $('minRent') ? $('minRent').value : '';
        const maxRent = $('maxRent') ? $('maxRent').value : '';
        if (city) params.set('city', city);
        if (purpose) params.set('purpose', purpose);
        if (minRent) {
            if (purpose === 'sell') params.set('min_price', minRent);
            else params.set('min_rent', minRent);
        }
        if (maxRent) {
            if (purpose === 'sell') params.set('max_price', maxRent);
            else params.set('max_rent', maxRent);
        }
        params.set('format', 'json');

        const url = `${window.location.pathname}?${params.toString()}`;

        return fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
            .then(r => {
                if (!r.ok) throw new Error('Network error');
                return r.json();
            })
            .then(data => {
                allMarkers = data.markers || [];
                renderMarkers(allMarkers);
            })
            .catch(err => {
                console.warn('Map fetch failed, keeping existing markers:', err);
            })
            .finally(() => {
                isLoading = false;
                showLoading(false);
            });
    }

    function manualSearch() {
        if (moveTimer) { clearTimeout(moveTimer); moveTimer = null; }
        fetchMarkersInView().then(() => {
            if (allMarkers.length > 0) {
                const b = computeBounds(allMarkers);
                if (b) {
                    const currentZoom = map.getZoom();
                    map.fitBounds(b, { padding: [50, 50], maxZoom: Math.max(currentZoom, 12), animate: true });
                }
            }
        });
    }

    function resetFilters() {
        if ($('cityFilter')) $('cityFilter').value = '';
        if ($('purposeFilter')) $('purposeFilter').value = '';
        if ($('minRent')) $('minRent').value = '';
        if ($('maxRent')) $('maxRent').value = '';

        if (initialMarkers.length > 0) {
            const b = computeBounds(initialMarkers);
            if (b) map.fitBounds(b, { padding: [40, 40], animate: true });
        }
        manualSearch();
    }

    document.addEventListener('DOMContentLoaded', initMap);
})();
</script>
@endsection
