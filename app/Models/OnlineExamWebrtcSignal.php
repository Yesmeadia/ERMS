<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OnlineExamWebrtcSignal extends Model
{
    use HasFactory;

    protected $table = 'online_exam_webrtc_signals';

    protected $fillable = [
        'online_exam_session_id',
        'admin_id',
        'sender_type',
        'recipient_type',
        'type',
        'payload',
        'is_consumed',
    ];

    protected $casts = [
        'is_consumed' => 'boolean',
        'payload' => 'array',
    ];

    /**
     * The exam session associated with this signal.
     */
    public function session(): BelongsTo
    {
        return $this->belongsTo(OnlineExamSession::class, 'online_exam_session_id');
    }

    /**
     * The admin user who initiated or received this signal.
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    /**
     * Scope for unconsumed signals intended for a specific recipient type.
     */
    public function scopeForRecipient($query, string $recipientType)
    {
        return $query->where('recipient_type', $recipientType)->where('is_consumed', false);
    }

    /**
     * Prune stale signals older than given minutes (defaults to 2 minutes).
     */
    public static function pruneStale(int $minutes = 2): int
    {
        return static::where('created_at', '<', now()->subMinutes($minutes))->delete();
    }
}
