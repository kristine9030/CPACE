<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Message extends Model
{
    protected $fillable = [
        'conversation_id', 'sender_id', 'body',
        'file_path', 'original_name', 'file_size', 'file_category',
    ];

    protected $casts = [
        'file_size' => 'integer',
    ];

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function url(): ?string
    {
        return $this->file_path ? Storage::disk('public')->url($this->file_path) : null;
    }

    /** Human-readable file size, e.g. "1.4 MB". */
    public function humanSize(): ?string
    {
        if (! $this->file_size) {
            return null;
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $size = $this->file_size;
        $i = 0;
        while ($size >= 1024 && $i < count($units) - 1) {
            $size /= 1024;
            $i++;
        }

        return round($size, $size < 10 && $i > 0 ? 1 : 0) . ' ' . $units[$i];
    }

    /** Font Awesome icon + accent colour for this attachment's category. */
    public function iconMeta(): array
    {
        return match ($this->file_category) {
            'pdf'        => ['icon' => 'fa-file-pdf', 'color' => '#e2483d'],
            'word'       => ['icon' => 'fa-file-word', 'color' => '#2b579a'],
            'excel'      => ['icon' => 'fa-file-excel', 'color' => '#217346'],
            'powerpoint' => ['icon' => 'fa-file-powerpoint', 'color' => '#d24726'],
            'image'      => ['icon' => 'fa-file-image', 'color' => '#8e5bd0'],
            'video'      => ['icon' => 'fa-file-video', 'color' => '#c0392b'],
            'archive'    => ['icon' => 'fa-file-zipper', 'color' => '#e8910b'],
            'text'       => ['icon' => 'fa-file-lines', 'color' => '#607d8b'],
            default      => ['icon' => 'fa-file', 'color' => '#6b7280'],
        };
    }
}
