<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\BrandedMessageMail;

class ContactController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'nullable|string|max:255',
            'message' => 'required|string|min:10',
        ]);

        try {
            $contactMsg = ContactMessage::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'subject' => $data['subject'] ?? 'New Inquiry from Contact Form',
                'message' => $data['message'],
                'ip_address' => $request->ip(),
                'is_read' => false,
            ]);

            try {
                \App\Models\AdminNotification::send(
                    'contact_inquiry',
                    'New Contact Enquiry',
                    'From ' . $data['name'] . ': ' . \Illuminate\Support\Str::limit($data['subject'] ?? $data['message'], 40),
                    route('admin.contact-messages.index'),
                    'fa-inbox'
                );

                \App\Services\FirebaseService::sendToAdmins(
                    'New Contact Enquiry 📩',
                    'From ' . $data['name'] . ': ' . \Illuminate\Support\Str::limit($data['subject'] ?? $data['message'], 50),
                    ['type' => 'contact_inquiry'],
                    route('admin.contact-messages.index')
                );
            } catch (\Throwable $e) {
                report($e);
            }
        } catch (\Throwable $e) {
            report($e);

            return back()->withInput()->with('error', 'We could not save your message. Please try again.');
        }

        // Attempt to send email to admin
        try {
            $adminEmail = \App\Models\Setting::get('contact_email', config('mail.from.address'));

            Mail::to($adminEmail)->send(new BrandedMessageMail(
                ($data['subject'] ?? null) ?: 'New website enquiry', 'A visitor contacted ApnaNest', $data['message'],
                'Contact enquiry', 'Open contact enquiries', route('admin.contact-messages.index'),
                ['Name' => $data['name'], 'Email' => $data['email']], 'primary'
            ));
        } catch (\Exception $e) {
            // Log error but don't stop the user
            \Log::error("Failed to send contact email: " . $e->getMessage());
        }

        return back()->with('success', 'Thank you for your message! We will get back to you soon.');
    }
}
