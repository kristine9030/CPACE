<?php

namespace App\Console\Commands;

use App\Models\Material;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class SecureMaterialFiles extends Command
{
    protected $signature = 'materials:secure';

    protected $description = 'Move uploaded learning materials from the public disk to private storage so they are only reachable through the login-checked route';

    public function handle(): int
    {
        $public = Storage::disk('public');
        $private = Storage::disk('local');
        $moved = 0;

        Material::where('kind', 'file')->whereNotNull('file_path')->each(function (Material $m) use ($public, $private, &$moved) {
            if (! $public->exists($m->file_path)) {
                return;
            }

            $private->put($m->file_path, $public->get($m->file_path));

            if ($private->exists($m->file_path)) {
                $public->delete($m->file_path);
                $moved++;
            }
        });

        $this->info("Moved {$moved} material file(s) to private storage.");

        return self::SUCCESS;
    }
}
