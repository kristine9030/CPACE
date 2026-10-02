<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** A file attached to a Program Chair announcement (see Communication). */
class CommunicationAttachment extends Model
{
    /** Extensions an announcement may carry. */
    public const ALLOWED_EXTENSIONS = 'pdf,doc,docx,ppt,pptx,xls,xlsx,csv,txt,rtf,odt,jpg,jpeg,png,gif,webp,zip,rar';

    public const MAX_FILES = 5;

    public const MAX_KILOBYTES = 10240;

    protected $fillable = ['communication_id', 'path', 'original_name', 'size', 'category'];

    protected function casts(): array
    {
        return ['size' => 'integer'];
    }

    public function communication()
    {
        return $this->belongsTo(Communication::class);
    }

    public function isImage(): bool
    {
        return $this->category === 'image';
    }

    /** Human-readable size, e.g. "1.4 MB". */
    public function humanSize(): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $size = (float) $this->size;
        $i = 0;
        while ($size >= 1024 && $i < count($units) - 1) {
            $size /= 1024;
            $i++;
        }

        return round($size, $size < 10 && $i > 0 ? 1 : 0) . ' ' . $units[$i];
    }

    /** Font Awesome icon + accent colour for the file's category. */
    public function iconMeta(): array
    {
        return match ($this->category) {
            'pdf' => ['icon' => 'fa-file-pdf', 'color' => '#e2483d'],
            'word' => ['icon' => 'fa-file-word', 'color' => '#2b579a'],
            'excel' => ['icon' => 'fa-file-excel', 'color' => '#217346'],
            'powerpoint' => ['icon' => 'fa-file-powerpoint', 'color' => '#d24726'],
            'image' => ['icon' => 'fa-file-image', 'color' => '#8e5bd0'],
            'archive' => ['icon' => 'fa-file-zipper', 'color' => '#e8910b'],
            'text' => ['icon' => 'fa-file-lines', 'color' => '#607d8b'],
            default => ['icon' => 'fa-file', 'color' => '#6b7280'],
        };
    }

    /** What the board and the notification list show for this file. */
    public function forDisplay(): array
    {
        $meta = $this->iconMeta();

        return [
            'name' => $this->original_name,
            'size' => $this->humanSize(),
            'icon' => $meta['icon'],
            'color' => $meta['color'],
            'is_image' => $this->isImage(),
            'url' => route('communications.attachments.download', $this->id),
        ];
    }
}
