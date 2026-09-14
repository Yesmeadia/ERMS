<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class OnlineQuestionImage extends Model
{
    protected $table = 'online_question_images';

    protected $fillable = [
        'question_id',
        'image_path',
        'disk',
        'file_name',
        'mime_type',
        'file_size',
    ];

    protected $appends = ['url'];

    public function question(): BelongsTo
    {
        return $this->belongsTo(OnlineQuestion::class, 'question_id');
    }

    public function getUrlAttribute(): string
    {
        if (empty($this->image_path)) {
            return '';
        }

        if (str_starts_with($this->image_path, 'http://') || str_starts_with($this->image_path, 'https://')) {
            return $this->image_path;
        }

        $cleanPath = ltrim($this->image_path, '/');
        if (str_starts_with($cleanPath, 'public/')) {
            $cleanPath = substr($cleanPath, 7);
        }
        if (str_starts_with($cleanPath, 'storage/')) {
            $cleanPath = substr($cleanPath, 8);
        }

        if ($this->disk === 'public' || $this->disk === 'local' || empty($this->disk)) {
            return asset('storage/'.$cleanPath);
        }

        return Storage::disk($this->disk)->url($this->image_path);
    }
}
