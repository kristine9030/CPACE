<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class CommunityResource extends Model
{
    protected $fillable = [
        'uploader_id', 'subject_id', 'title', 'description',
        'file_path', 'original_name', 'file_category', 'file_size', 'downloads_count',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'downloads_count' => 'integer',
    ];

    /** Map a file extension to the coarse category used for icons/filtering. */
    public static function categoryFor(string $extension): string
    {
        $extension = strtolower($extension);

        return match (true) {
            $extension === 'pdf'                                             => 'pdf',
            in_array($extension, ['doc', 'docx', 'rtf', 'odt'])             => 'word',
            in_array($extension, ['xls', 'xlsx', 'csv', 'ods'])             => 'excel',
            in_array($extension, ['ppt', 'pptx', 'odp'])                    => 'powerpoint',
            in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg']) => 'image',
            in_array($extension, ['zip', 'rar', '7z', 'tar', 'gz'])            => 'archive',
            in_array($extension, ['txt', 'md'])                             => 'text',
            default                                                          => 'other',
        };
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploader_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function posts()
    {
        return $this->hasMany(CommunityPost::class, 'resource_id');
    }

    /** Guarded route the file is streamed from — never a direct storage URL. */
    public function url(): ?string
    {
        return $this->file_path ? route('community.resources.file', $this->id) : null;
    }

    /** Short-lived signed URL for viewers that can't carry our session (Office Online). */
    public function previewUrl(int $minutes = 10): ?string
    {
        return $this->file_path
            ? URL::temporarySignedRoute('community.resources.file', now()->addMinutes($minutes), $this->id)
            : null;
    }

    /** What the View button opens: Office files go through Office Online, everything else streams inline. */
    public function viewerUrl(): ?string
    {
        if (! $this->file_path) {
            return null;
        }

        if (in_array($this->file_category, ['word', 'excel', 'powerpoint'], true)) {
            return 'https://view.officeapps.live.com/op/view.aspx?src=' . urlencode($this->previewUrl());
        }

        return $this->url();
    }

    /** Disk holding the file: private storage, or the legacy public disk until migrated. */
    public function storageDisk(): ?string
    {
        if (! $this->file_path) {
            return null;
        }

        foreach (['local', 'public'] as $disk) {
            if (Storage::disk($disk)->exists($this->file_path)) {
                return $disk;
            }
        }

        return null;
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

    /** Font Awesome icon + accent colour for this resource's category. */
    public function iconMeta(): array
    {
        return match ($this->file_category) {
            'pdf'        => ['icon' => 'fa-file-pdf', 'color' => '#e2483d'],
            'word'       => ['icon' => 'fa-file-word', 'color' => '#2b579a'],
            'excel'      => ['icon' => 'fa-file-excel', 'color' => '#217346'],
            'powerpoint' => ['icon' => 'fa-file-powerpoint', 'color' => '#d24726'],
            'image'      => ['icon' => 'fa-file-image', 'color' => '#8e5bd0'],
            'archive'    => ['icon' => 'fa-file-zipper', 'color' => '#e8910b'],
            'text'       => ['icon' => 'fa-file-lines', 'color' => '#607d8b'],
            default      => ['icon' => 'fa-file', 'color' => '#6b7280'],
        };
    }
}
