<?php

namespace App\Models\Concerns;

use App\Support\QuestionExhibit;

/** For models that carry a question: image_path (a picture) and table_data (a table) shown under its text. */
trait HasExhibit
{
    public function exhibitImageUrl(): ?string
    {
        return QuestionExhibit::imageUrl($this->image_path);
    }

    public function hasExhibit(): bool
    {
        return $this->exhibitImageUrl() !== null || ! empty($this->table_data['rows'] ?? null);
    }
}
