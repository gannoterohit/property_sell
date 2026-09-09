@php
    static $footerData;
    if (!isset($footerData)) {
        $websiteName = \App\Models\Setting::get('website_name', 'ApnaNest');
        $footerLogo = \App\Models\Setting::get('footer_logo');
        $contactPhone = \App\Models\Setting::get('contact_phone', '');
        $contactEmail = \App\Models\Setting::get('contact_email', '');
        $businessHours = \App\Models\Setting::get('business_hours', '');
        $playStoreUrl = trim((string) \App\Models\Setting::get('play_store_url'));
        $appStoreUrl = trim((string) \App\Models\Setting::get('app_store_url'));

        $social = [];
        foreach ([
            'facebook' => \App\Models\Setting::get('facebook_url'),
            'twitter' => \App\Models\Setting::get('twitter_url'),
            'instagram' => \App\Models\Setting::get('instagram_url'),
            'linkedin' => \App\Models\Setting::get('linkedin_url'),
        ] as $platform => $url) {
            $url = trim((string) $url);
            if ($url && $url !== '#') {
                $social[$platform] = $url;
            }
        }

        try {
            $allCmsPages = \App\Models\CmsPage::published()->orderBy('title')->get();
        } catch (\Throwable $e) {
            $allCmsPages = collect();
        }

        $footerData = compact(
            'websiteName', 'footerLogo', 'contactPhone', 'contactEmail',
            'businessHours', 'playStoreUrl', 'appStoreUrl', 'social', 'allCmsPages'
        );
    }
    extract($footerData);
@endphp

<footer class="bg-slate-900 text-slate-400 pt-12 pb-6">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-10">

            <!-- Brand -->
            <div class="space-y-4">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-2">
                    @if($footerLogo)
                        <img src="{{ \App\Models\Setting::mediaUrl($footerLogo) }}" alt="{{ $websiteName }}" class="h-10 w-auto">
                    @else
                        <div class="flex items-center gap-2">
                            <div class="w-10 h-10 rounded-xl bg-indigo-600 flex items-center justify-center">
                                <i class="fas fa-home text-white text-lg"></i>
                            </div>
                            <span class="text-xl font-black text-white">{{ $websiteName }}</span>
                        </div>
                    @endif
                </a>
                <p class="text-sm leading-relaxed">
                    India's most trusted real estate platform to rent and buy verified properties. Connect directly with verified owners and trusted agents with zero hassle.
                </p>
                @if(count($social))
                <div class="flex gap-3">
                    @foreach($social as $platform => $url)
                        <a href="{{ $url }}" target="_blank" rel="noopener noreferrer"
                           class="w-9 h-9 rounded-lg bg-white/5 border border-white/10 flex items-center justify-center hover:bg-white/10 transition"
                           aria-label="{{ ucfirst($platform) }}">
                            <i class="fab fa-{{ $platform === 'facebook' ? 'facebook-f' : ($platform === 'linkedin' ? 'linkedin-in' : $platform) }}"></i>
                        </a>
                    @endforeach
                </div>
                @endif
            </div>

            <!-- Quick Links -->
            <div>
                <h3 class="text-white font-bold mb-4 text-sm uppercase tracking-wider">Quick Links</h3>
                <ul class="space-y-2.5 text-sm">
                    <li><a href="{{ route('rooms.index', ['purpose' => 'sell']) }}" class="text-purple-400 hover:text-white transition font-bold flex items-center gap-1.5"><i class="fas fa-tag text-xs"></i> Properties For Sale (Buy)</a></li>
                    <li><a href="{{ route('rooms.index', ['purpose' => 'rent']) }}" class="text-slate-400 hover:text-white transition flex items-center gap-1.5"><i class="fas fa-key text-xs"></i> Properties For Rent</a></li>
                    <li><a href="{{ route('rooms.index') }}" class="text-slate-400 hover:text-white transition">All Properties Directory</a></li>
                    <li><a href="{{ route('pages.how-it-works') }}" class="{{ request()->routeIs('pages.how-it-works') ? 'text-indigo-400 font-semibold' : 'text-slate-400' }} hover:text-white transition">How It Works</a></li>
                    <li><a href="{{ route('pages.faq') }}" class="{{ request()->routeIs('pages.faq') ? 'text-indigo-400 font-semibold' : 'text-slate-400' }} hover:text-white transition">FAQ</a></li>
                    <li><a href="{{ route('blogs.index') }}" class="text-slate-400 hover:text-white transition">Blog</a></li>
                </ul>
            </div>

            <!-- Information (CMS Pages) -->
            @if($allCmsPages->count() > 0)
            <div>
                <h3 class="text-white font-bold mb-4 text-sm uppercase tracking-wider">Information</h3>
                <ul class="space-y-2.5 text-sm">
                    @forelse($allCmsPages as $cmsPage)
                        <li>
                            <a href="{{ route('pages.show', ['slug' => $cmsPage->slug]) }}" class="{{ request()->is('page/'.$cmsPage->slug) ? 'text-indigo-400 font-semibold' : 'text-slate-400' }} hover:text-white transition">
                                {{ $cmsPage->title }}
                            </a>
                        </li>
                    @empty
                        <li class="text-slate-500 text-xs">No pages available</li>
                    @endforelse
                </ul>
            </div>
            @endif

            <!-- Contact -->
            <div>
                <h3 class="text-white font-bold mb-4 text-sm uppercase tracking-wider">Contact Us</h3>
                <ul class="space-y-3 text-sm">
                    @if($contactPhone)
                        <li class="flex items-center gap-3">
                            <i class="fas fa-phone-alt text-indigo-400 w-4"></i>
                            <a href="tel:{{ $contactPhone }}" class="text-slate-400 hover:text-white transition">{{ $contactPhone }}</a>
                        </li>
                    @endif
                    @if($contactEmail)
                        <li class="flex items-center gap-3">
                            <i class="far fa-envelope text-indigo-400 w-4"></i>
                            <a href="mailto:{{ $contactEmail }}" class="text-slate-400 hover:text-white transition">{{ $contactEmail }}</a>
                        </li>
                    @endif
                    @if($businessHours)
                        <li class="flex items-center gap-3 text-slate-400">
                            <i class="far fa-clock text-indigo-400 w-4"></i>
                            <span>{{ $businessHours }}</span>
                        </li>
                    @endif
                </ul>

                @if($playStoreUrl || $appStoreUrl)
                <div class="mt-6">
                    <h4 class="text-white font-bold mb-3 text-xs uppercase tracking-wider">Get the App</h4>
                    <div class="flex flex-wrap gap-2">
                        @if($playStoreUrl)
                            <a href="{{ $playStoreUrl }}" target="_blank" rel="noopener noreferrer"
                               class="inline-flex items-center gap-2 rounded-lg border border-white/15 bg-white/5 px-3 py-2 text-xs font-bold text-white hover:bg-white/10 transition">
                                <i class="fab fa-google-play"></i> Google Play
                            </a>
                        @endif
                        @if($appStoreUrl)
                            <a href="{{ $appStoreUrl }}" target="_blank" rel="noopener noreferrer"
                               class="inline-flex items-center gap-2 rounded-lg border border-white/15 bg-white/5 px-3 py-2 text-xs font-bold text-white hover:bg-white/10 transition">
                                <i class="fab fa-apple"></i> App Store
                            </a>
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Bottom Bar -->
        <div class="pt-6 border-t border-slate-800 flex flex-col md:flex-row justify-between items-center gap-4">
            <p class="text-sm">
                &copy; {{ date('Y') }} {{ $websiteName }}. All rights reserved.
            </p>
            <div class="flex items-center gap-4 text-xs">
                <span class="flex items-center gap-1.5">
                    <i class="fas fa-shield-alt text-emerald-500"></i> Secure Payments
                </span>
                <span class="flex items-center gap-1.5">
                    <i class="fas fa-check-circle text-indigo-400"></i> Verified Listings
                </span>
            </div>
        </div>
    </div>
</footer>
