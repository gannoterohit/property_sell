<?php

namespace App\Http\Controllers\Api;

use App\Mail\BrandedMessageMail;
use App\Http\Controllers\Api\BaseApiController;
use App\Http\Resources\BlogResource;
use App\Models\Blog;
use App\Models\CityAlert;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class ApiGeneralController extends BaseApiController
{
    public function offers(Request $request)
    {
        $audience = $request->user()?->role ?? 'user';
        return $this->sendSuccess(\App\Models\Offer::active()->forAudience($audience)->latest()->get());
    }

    /**
     * Get all blogs
     */
    public function blogs(Request $request)
    {
        $query = Blog::published()->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%$search%")
                  ->orWhere('content', 'like', "%$search%");
            });
        }

        $blogs = $query->paginate(max(1, min(50, $request->integer('limit', 10))));
        return BlogResource::collection($blogs)->additional(['status' => 'success']);
    }

    /**
     * Get single blog by slug
     */
    public function blogShow($slug)
    {
        $blog = Blog::where('slug', $slug)
                    ->published()
                    ->first();

        if (!$blog) {
            return $this->sendError('Blog post not found', [], 404);
        }

        return $this->sendSuccess(new BlogResource($blog));
    }

    /**
     * Get static page content
     */
    public function page($slug)
    {
        $content = Setting::get($slug . '_content', 'Content for ' . $slug);

        return $this->sendSuccess([
            'title'   => ucwords(str_replace('-', ' ', $slug)),
            'content' => $content
        ]);
    }

    /**
     * Subscribe to city alerts
     */
    public function addCityAlert(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'city' => 'required|string|max:100',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Please check your input and try again.', $validator->errors(), 422);
        }

        $alert = CityAlert::create([
            'user_id' => Auth::id(),
            'city'    => $request->city
        ]);

        return $this->sendSuccess($alert, 'Subscribed to alerts for ' . $request->city);
    }

    /**
     * Get user's city alerts
     */
    public function getCityAlerts()
    {
        $alerts = CityAlert::where('user_id', Auth::id())->get();
        return $this->sendSuccess($alerts);
    }

    /**
     * Subscribe to newsletter
     */
    public function subscribeNewsletter(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:255',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Please check your input and try again.', $validator->errors(), 422);
        }

        \App\Models\Subscriber::updateOrCreate(['email' => $request->email]);

        return $this->sendSuccess([], 'Successfully subscribed to newsletter');
    }

    /**
     * Submit Contact Us form
     */
    public function contactSubmit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'    => 'required|string|max:255',
            'email'   => 'required|email|max:255',
            'phone'   => 'nullable|string|max:20',
            'message' => 'required|string|max:2000',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Please check your input and try again.', $validator->errors(), 422);
        }

        // Save to database for Admin Panel visibility
        $contactMessage = \App\Models\ContactMessage::create([
            'name'       => $request->name,
            'email'      => $request->email,
            'subject'    => 'Mobile App Inquiry' . ($request->phone ? " ({$request->phone})" : ''),
            'message'    => $request->message,
            'ip_address' => $request->ip(),
        ]);

        // Notify admin (API side)
        try {
            \App\Models\AdminNotification::send(
                'contact_inquiry',
                'New Contact Message (App)',
                $request->name . ': ' . \Illuminate\Support\Str::limit($request->message, 60),
                route('admin.contact-messages.index'),
                'fa-envelope'
            );

            \App\Services\FirebaseService::sendToAdmins(
                'New Contact Enquiry (App) 📩',
                'From ' . $request->name . ': ' . \Illuminate\Support\Str::limit($request->message, 60),
                ['type' => 'contact_inquiry'],
                route('admin.contact-messages.index')
            );
        } catch (\Throwable $e) {
            report($e);
        }

        $adminEmail = Setting::get('contact_email', Setting::get('business_email', null));

        if ($adminEmail) {
            try {
                Mail::to($adminEmail)->send(new BrandedMessageMail(
                    'New mobile app enquiry from ' . $request->name,
                    'A mobile app user contacted ApnaNest',
                    $request->message,
                    'Mobile enquiry',
                    'Open contact enquiries',
                    route('admin.contact-messages.index'),
                    array_filter(['Name' => $request->name, 'Email' => $request->email, 'Phone' => $request->phone]),
                    'primary'
                ));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Contact form mail failed: ' . $e->getMessage());
                // We still return success because it's saved in the DB
            }
        }

        return $this->sendSuccess([], 'Your message has been sent successfully. We will get back to you soon.');
    }

    /**
     * Validate referral code (for mobile deep links)
     */
    public function validateReferral(string $code)
    {
        $referrer = User::where('referral_code', $code)->where('is_blocked', false)->first();

        if (!$referrer) {
            return $this->sendError('Invalid or expired referral code.', [], 404);
        }

        $joinReward = (int) Setting::get('join_reward', 5);

        return $this->sendSuccess([
            'code'          => $code,
            'referrer_name' => $referrer->name,
            'join_reward'   => $joinReward,
            'message'       => "Register with this code to receive {$joinReward} bonus points!",
        ], 'Referral code is valid');
    }

    /**
     * Get FAQ content
     */
    public function faq()
    {
        $json = Setting::get('faq_content', '[]');
        $faqs = json_decode($json, true);
        if (!is_array($faqs)) {
            $faqs = [];
        }
        return $this->sendSuccess([
            'title' => 'Frequently Asked Questions',
            'faqs'  => $faqs
        ]);
    }

    /**
     * Remove city alert
     */
    public function removeCityAlert($id)
    {
        $alert = CityAlert::where('user_id', Auth::id())->find($id);
        if ($alert) {
            $alert->delete();
            return $this->sendSuccess([], 'Alert removed');
        }
        return $this->sendError('Alert not found');
    }
}
