@extends('emails.layout', ['title' => 'New room in '.$city, 'preheader' => 'A new listing matches your city alert.'])
@section('content')
<span class="badge badge-primary">New city match</span><h2 style="margin-top:16px;">A new room is available in {{ $city }}</h2>
<p>You asked us to notify you about new listings in <strong>{{ $city }}</strong>. This property has just been approved.</p>
<table role="presentation" width="100%" style="margin:22px 0;background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;"><tr><td style="padding:18px;"><strong style="color:#0f172a;font-size:17px;">{{ $room->title }}</strong><br><span style="color:#64748b;">{{ $room->address }}</span><br><span style="color:#15803d;font-size:18px;font-weight:700;">{{ $room->isForSell() ? $room->displayPrice() . ' (For Sale)' : '&#8377;' . number_format((float)$room->rent) . ' / month' }}</span></td></tr></table>
<p>Availability can change quickly. Open the listing to review its latest details.</p><a href="{{ url('/rooms/'.$room->id) }}" class="btn">View room details</a>
<p class="fallback-link" style="margin-top:24px;">You received this email because you subscribed to alerts for {{ $city }}.</p>
@endsection
