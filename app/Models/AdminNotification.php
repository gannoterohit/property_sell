<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminNotification extends Model
{
    protected $fillable = [
        'type',
        'title',
        'message',
        'link',
        'icon',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    /**
     * Create and send a new admin notification
     */
    public static function send(string $type, string $title, ?string $message = null, ?string $link = null, string $icon = 'fa-bell'): self
    {
        return self::create([
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'link' => $link,
            'icon' => $icon,
            'is_read' => false,
        ]);
    }

    /**
     * Mark this notification as read
     */
    public function markAsRead(): bool
    {
        return $this->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    /**
     * Human-friendly type label.
     */
    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'payment_received'       => 'Payment Received',
            'new_user_registration'  => 'New User',
            'new_broker_registration'=> 'New Broker',
            'room_posted'            => 'Property Listing',
            'complaint_submitted'    => 'New Complaint',
            'complaint_reply'        => 'Complaint Reply',
            'contact_inquiry'        => 'Contact Inquiry',
            'lead_unlock'            => 'Lead Unlock',
            'broadcast'              => 'Broadcast',
            default                  => ucwords(str_replace('_', ' ', $this->type ?? 'Notification')),
        };
    }

    /**
     * Tailwind badge style class.
     */
    public function getBadgeClassAttribute(): string
    {
        return match ($this->type) {
            'payment_received'       => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'new_user_registration'  => 'bg-teal-50 text-teal-700 border-teal-200',
            'new_broker_registration'=> 'bg-indigo-50 text-indigo-700 border-indigo-200',
            'room_posted'            => 'bg-sky-50 text-sky-700 border-sky-200',
            'complaint_submitted'    => 'bg-rose-50 text-rose-700 border-rose-200',
            'complaint_reply'        => 'bg-amber-50 text-amber-700 border-amber-200',
            'contact_inquiry'        => 'bg-blue-50 text-blue-700 border-blue-200',
            'lead_unlock'            => 'bg-purple-50 text-purple-700 border-purple-200',
            'broadcast'              => 'bg-fuchsia-50 text-fuchsia-700 border-fuchsia-200',
            default                  => 'bg-slate-100 text-slate-700 border-slate-200',
        };
    }

    /**
     * Default FontAwesome icon class for this type.
     */
    public function getIconClassAttribute(): string
    {
        if ($this->icon) {
            return $this->icon;
        }

        return match ($this->type) {
            'payment_received'       => 'fa-credit-card',
            'new_user_registration'  => 'fa-user-plus',
            'new_broker_registration'=> 'fa-id-badge',
            'room_posted'            => 'fa-home',
            'complaint_submitted'    => 'fa-exclamation-triangle',
            'complaint_reply'        => 'fa-comments',
            'contact_inquiry'        => 'fa-envelope',
            'lead_unlock'            => 'fa-unlock-alt',
            'broadcast'              => 'fa-bullhorn',
            default                  => 'fa-bell',
        };
    }
}
