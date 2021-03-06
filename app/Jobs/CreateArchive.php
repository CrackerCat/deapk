<?php

namespace App\Jobs;

use Alchemy\Zippy\Zippy;
use App\Events\ArchiveCreated;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Http\File;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Storage;

class CreateArchive implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var string
     */
    private $id;

    /**
     * Create a new job instance.
     *
     * @param string $id
     * @return void
     */
    public function __construct($id)
    {
        $this->id = $id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $source = storage_path("app/decompiled/{$this->id}");
        if (is_dir($source)) {
            $target = storage_path("app/archives/{$this->id}.zip");
            Zippy::load()->create($target, [$source], true);
            $path = Storage::disk('spaces')->putFile('archives', new File($target));
            unlink($target);
            $url = Storage::disk('spaces')->temporaryUrl($path, Carbon::now()->addHours(24));
            event(new ArchiveCreated($this->id, $url));
        }
    }
}
