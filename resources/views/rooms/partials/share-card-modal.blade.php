@php
    $isSell = $room->isForSell();
    $shareTitle = $room->title;
    $sharePriceText = $isSell ? "💰 *Price:* " . $room->displayPrice() . "\n" : "💰 *Rent:* ₹" . number_format((float)$room->rent) . "/month\n";
    $shareDepositText = $isSell ? "" : "🔒 *Security Deposit:* ₹" . number_format((float)($room->deposit ?? 0)) . "\n";
    $shareLocality = ($room->locality ? $room->locality . ', ' : '') . $room->city;
    $shareFurnishing = ucfirst(str_replace('_', ' ', $room->furnishing_type ?? 'Semi-Furnished'));
    $shareType = $room->roomTypeOption?->label ?? ($isSell ? 'Property for Sale' : 'Rental Property');
    $shareUrl = route('rooms.show', $room->slug ?: $room->id);

    $cardMessage = "🏠 *{$shareTitle}*\n"
        . ($isSell ? "🏷️ *Purpose:* Property For Sale\n" : "")
        . $sharePriceText
        . $shareDepositText
        . "📍 *Location:* {$shareLocality}\n"
        . "🛋️ *Furnishing:* {$shareFurnishing}\n"
        . "🛏️ *Type:* {$shareType}\n\n"
        . "📸 *View Photos & Full Details:* \n{$shareUrl}";
    $waCardUrl = "https://api.whatsapp.com/send?text=" . rawurlencode($cardMessage);
@endphp

<!-- Share Property Modal -->
<div id="sharePropertyModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-xs p-4 hidden" onclick="if(event.target===this) closeShareModal()">
    <div class="w-full max-w-lg rounded-3xl bg-white p-6 shadow-2xl transition-all duration-300 transform scale-95 opacity-0" id="shareModalCard">
        <div class="flex items-center justify-between border-b border-slate-100 pb-4">
            <div class="flex items-center gap-2.5">
                <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600">
                    <i class="fa-brands fa-whatsapp text-xl"></i>
                </span>
                <div>
                    <h3 class="text-base font-extrabold text-slate-900">Share Property Card</h3>
                    <p class="text-xs text-slate-500">Share complete details directly with family or roommates</p>
                </div>
            </div>
            <button type="button" onclick="closeShareModal()" class="flex h-8 w-8 items-center justify-center rounded-xl bg-slate-100 text-slate-500 hover:bg-slate-200 transition cursor-pointer">
                <i class="fas fa-times text-xs"></i>
            </button>
        </div>

        <!-- Live Message Preview Box -->
        <div class="my-4 rounded-2xl border border-emerald-200 bg-emerald-50/40 p-4 font-sans">
            <p class="text-[10px] font-black uppercase tracking-wider text-emerald-800 mb-2 flex items-center gap-1.5">
                <i class="fa-brands fa-whatsapp text-emerald-600"></i> WhatsApp Message Preview
            </p>
            <div class="rounded-xl bg-white p-3.5 shadow-2xs border border-emerald-100 text-xs text-slate-800 whitespace-pre-line leading-relaxed select-all" id="shareCardContent">{{ $cardMessage }}</div>
        </div>

        <!-- Action Buttons -->
        <div class="space-y-2.5">
            <a href="{{ $waCardUrl }}" target="_blank" rel="noopener noreferrer" class="flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 py-3 text-xs font-black text-white shadow-md shadow-emerald-600/20 transition cursor-pointer">
                <i class="fa-brands fa-whatsapp text-base"></i> Share on WhatsApp
            </a>

            <div class="grid grid-cols-2 gap-2.5">
                <button type="button" onclick="copyShareCardText()" id="copyShareCardBtn" class="flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-slate-50 hover:bg-slate-100 py-2.5 text-xs font-bold text-slate-700 transition cursor-pointer">
                    <i class="fas fa-copy text-indigo-600" id="copyBtnIcon"></i>
                    <span id="copyBtnText">Copy Card Text</span>
                </button>

                <button type="button" onclick="copyPropertyUrl()" id="copyPropertyUrlBtn" class="flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-slate-50 hover:bg-slate-100 py-2.5 text-xs font-bold text-slate-700 transition cursor-pointer">
                    <i class="fas fa-link text-indigo-600" id="copyUrlIcon"></i>
                    <span id="copyUrlText">Copy Link Only</span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function openShareModal() {
    const modal = document.getElementById('sharePropertyModal');
    const card = document.getElementById('shareModalCard');
    if (!modal || !card) return;
    modal.classList.remove('hidden');
    requestAnimationFrame(() => {
        card.classList.remove('scale-95', 'opacity-0');
        card.classList.add('scale-100', 'opacity-100');
    });
}

function closeShareModal() {
    const modal = document.getElementById('sharePropertyModal');
    const card = document.getElementById('shareModalCard');
    if (!modal || !card) return;
    card.classList.remove('scale-100', 'opacity-100');
    card.classList.add('scale-95', 'opacity-0');
    setTimeout(() => {
        modal.classList.add('hidden');
    }, 200);
}

async function copyShareCardText() {
    const text = document.getElementById('shareCardContent')?.innerText;
    if (!text) return;
    try {
        await navigator.clipboard.writeText(text);
        const btnText = document.getElementById('copyBtnText');
        const icon = document.getElementById('copyBtnIcon');
        if (btnText && icon) {
            btnText.textContent = 'Copied!';
            icon.className = 'fas fa-check text-emerald-600';
            setTimeout(() => {
                btnText.textContent = 'Copy Card Text';
                icon.className = 'fas fa-copy text-indigo-600';
            }, 2500);
        }
        if (typeof toastr !== 'undefined') toastr.success('Property summary copied to clipboard!');
    } catch (e) {
        console.error('Clipboard copy failed:', e);
    }
}

async function copyPropertyUrl() {
    const url = '{{ $shareUrl }}';
    try {
        await navigator.clipboard.writeText(url);
        const btnText = document.getElementById('copyUrlText');
        const icon = document.getElementById('copyUrlIcon');
        if (btnText && icon) {
            btnText.textContent = 'Link Copied!';
            icon.className = 'fas fa-check text-emerald-600';
            setTimeout(() => {
                btnText.textContent = 'Copy Link Only';
                icon.className = 'fas fa-link text-indigo-600';
            }, 2500);
        }
        if (typeof toastr !== 'undefined') toastr.success('Property link copied!');
    } catch (e) {
        console.error('Link copy failed:', e);
    }
}
</script>
