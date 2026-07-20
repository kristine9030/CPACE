<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Material extends Model
{
    protected $fillable = [
        'topic_id', 'uploaded_by', 'title', 'description', 'kind',
        'file_category', 'file_path', 'original_name', 'file_size',
        'external_url', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'file_size' => 'integer',
    ];

    /** Map a file extension to the coarse category used for icons/filtering. */
    public static function categoryFor(string $extension): string
    {
        $extension = strtolower($extension);

        return match (true) {
            $extension === 'pdf'                                   => 'pdf',
            in_array($extension, ['doc', 'docx', 'rtf', 'odt'])    => 'word',
            in_array($extension, ['xls', 'xlsx', 'csv', 'ods'])    => 'excel',
            in_array($extension, ['ppt', 'pptx', 'odp'])           => 'powerpoint',
            in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg']) => 'image',
            in_array($extension, ['mp4', 'mov', 'avi', 'mkv', 'webm'])         => 'video',
            in_array($extension, ['zip', 'rar', '7z', 'tar', 'gz'])            => 'archive',
            in_array($extension, ['txt', 'md'])                    => 'text',
            default                                                => 'other',
        };
    }

    public function topic()
    {
        return $this->belongsTo(Topic::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /** Public URL a student uses to open/download the material. */
    public function url(): ?string
    {
        if ($this->kind === 'link') {
            return $this->external_url;
        }

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

    /** Font Awesome icon + accent colour for this material's category. */
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
            'link'       => ['icon' => 'fa-link', 'color' => '#3b7ddd'],
            default      => ['icon' => 'fa-file', 'color' => '#6b7280'],
        };
    }
}
