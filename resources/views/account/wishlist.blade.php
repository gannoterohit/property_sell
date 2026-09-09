@extends(Auth::user()->role === 'owner' ? 'layouts.owner' : (Auth::user()->role === 'broker' ? 'layouts.agent' : (Auth::user()->role === 'user' ? 'layouts.customer' : 'layouts.public')))
@section('title','My Wishlist | ApnaNest')
@php $contentSection = Auth::user()->role === 'owner' ? 'owner-content' : (Auth::user()->role === 'broker' ? 'broker-content' : (Auth::user()->role === 'user' ? 'customer-content' : 'content')); @endphp
@section($contentSection)
@php $user = Auth::user(); @endphp
<div class="account-container account-body">
    <div id="wishlist-status" class="account-flash success hidden" role="status"></div>
    @if($wishlists->contains(fn($item)=>$item->room))
        <section id="wishlist-grid" class="wishlist-grid">
            @foreach($wishlists as $wishlist)
                @if($room=$wishlist->room)
                    <article class="saved-card" id="wishlist-item-{{ $room->id }}">
                        <div class="saved-image">
                            <img src="{{ $room->photo_url }}" alt="{{ $room->title }}" width="400" height="300" loading="lazy" decoding="async" onerror="this.onerror=null;this.src='{{ asset('assets/images/default-room.svg') }}'">
                            @if($room->isForSell())<span class="featured-tag" style="background:#7c3aed;">For Sale</span>@endif
                            @if($room->is_featured)<span class="featured-tag">Featured</span>@endif
                            <button type="button" onclick="removeFromWishlist({{ $room->id }},this)" aria-label="Remove {{ $room->title }} from saved rooms"><i class="fas fa-heart"></i></button>
                        </div>
                        <div class="saved-copy">
                            <div class="saved-price">
                                @if($room->isForSell())
                                    <strong style="color: #7c3aed;">{{ $room->displayPrice() }}</strong>
                                    <span style="background:#f3e8ff;color:#7c3aed;padding:2px 6px;border-radius:4px;font-size:10px;font-weight:600;margin-left:4px;">FOR SALE</span>
                                @else
                                    <strong>&#8377;{{ number_format((float)$room->rent) }}</strong><span>/ month</span>
                                @endif
                            </div>
                            <h2>{{ $room->title }}</h2>
                            <p><i class="fas fa-location-dot"></i>{{ $room->city ?: $room->address }}</p>
                            <div class="saved-meta">
                                <span><i class="fas fa-house"></i>{{ $room->type ?: 'Room' }}</span>
                                @if($room->availability_from)<span><i class="far fa-calendar"></i>{{ \Carbon\Carbon::parse($room->availability_from)->format('d M') }}</span>@endif
                            </div>
                            <a href="{{ route('rooms.show',$room) }}">View property <i class="fas fa-arrow-right"></i></a>
                        </div>
                    </article>
                @endif
            @endforeach
        </section>
    @else
        <div id="wishlist-empty" class="account-card account-empty">
            <span><i class="far fa-heart"></i></span>
            <h2>Your saved list is empty</h2>
            <p>Save rooms while browsing to build a shortlist and compare rent, location and availability later.</p>
            <a href="{{ route('rooms.index') }}" class="account-action">Explore verified rooms</a>
        </div>
    @endif
</div>
@include('account.partials.page-styles')
<link rel="stylesheet" href="{{ asset('css/account-wishlist.css') }}">
<script>async function removeFromWishlist(roomId,button){let approved=true;if(window.Swal){const result=await Swal.fire({title:'Remove saved room?',text:'You can save this room again later.',icon:'question',showCancelButton:true,confirmButtonText:'Remove',confirmButtonColor:'#dc2626'});approved=result.isConfirmed}else approved=confirm('Remove this room from your saved list?');if(!approved)return;button.disabled=true;try{const response=await fetch(`{{ url('/wishlist/toggle') }}/${roomId}`,{method:'POST',headers:{'X-CSRF-TOKEN':@json(csrf_token()),'Accept':'application/json'}});const data=await response.json();if(!response.ok||!data.success)throw new Error(data.message||'Request failed');const item=document.getElementById(`wishlist-item-${roomId}`);item.classList.add('removing');setTimeout(()=>{item.remove();const count=document.querySelectorAll('[id^="wishlist-item-"]').length;document.getElementById('wishlist-count').textContent=count;if(!count)location.reload()},220);const status=document.getElementById('wishlist-status');status.textContent='Room removed from your saved list.';status.classList.remove('hidden')}catch(error){button.disabled=false;if(window.Swal)Swal.fire('Could not remove room',error.message,'error');else alert(error.message)}}</script>
@endsection
