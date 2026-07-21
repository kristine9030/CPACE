<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

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
