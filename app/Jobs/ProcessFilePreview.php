<?php

namespace App\Jobs;

use App\Models\Gallery;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ProcessFilePreview implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 120;

    protected $galleryId;

    /**
     * Create a new job instance.
     */
    public function __construct($galleryId)
    {
        $this->galleryId = $galleryId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $gallery = Gallery::find($this->galleryId);
        if (!$gallery) return;

        // Ensure we are processing a valid state
        if ($gallery->conversion_status === 'done') return;

        $gallery->update(['conversion_status' => 'processing']);

        try {
            $user_id = $gallery->user_id;
            $uuid = pathinfo($gallery->file, PATHINFO_FILENAME);
            $originalPath = $gallery->path;

            // Get absolute path of the original file
            $originalAbsolutePath = Storage::disk('local')->path($originalPath);

            if (!file_exists($originalAbsolutePath)) {
                throw new \Exception("Original file not found: " . $originalAbsolutePath);
            }

            if ($gallery->preview_type === 'image') {
                $this->processImage($gallery, $originalAbsolutePath, $user_id, $uuid);
            } elseif ($gallery->preview_type === 'video') {
                $this->processVideo($gallery, $originalAbsolutePath, $user_id, $uuid);
            } elseif ($gallery->preview_type === 'office') {
                $this->processOffice($gallery, $originalAbsolutePath, $user_id, $uuid);
            } else {
                // If it's a type that doesn't need external conversion but was queued somehow
                $gallery->update([
                    'conversion_status' => 'done',
                    'preview_ready_at' => now(),
                ]);
            }

        } catch (\Exception $e) {
            \Log::error("ProcessFilePreview Failed for Gallery ID {$this->galleryId}: " . $e->getMessage());
            $gallery->update(['conversion_status' => 'failed']);
        }
    }

    private function processImage(Gallery $gallery, string $originalAbsolutePath, $user_id, $uuid)
    {
        // 3. THUMBNAIL (HYBRID APPROACH): Optional but recommended "public" for thumbnails for performance.
        // I will use disk('public') for thumbnails.
        $thumbnailRelPath = "users/{$user_id}/thumbnails/{$uuid}.webp";
        // Intervention image processing using memory
        $manager = new ImageManager(new Driver());
        // Read file
        $image = $manager->read($originalAbsolutePath);
        
        // Resize down if wider than 300px
        $image->scaleDown(width: 300);

        // Encode to webp
        $encoded = $image->toWebp();
        
        // Save to public disk
        Storage::disk('public')->put($thumbnailRelPath, $encoded->toString());

        $gallery->update([
            'thumbnail_path' => $thumbnailRelPath,
            'conversion_status' => 'done',
            'preview_ready_at' => now(),
        ]);
    }

    private function processVideo(Gallery $gallery, string $originalAbsolutePath, $user_id, $uuid)
    {
        $ffmpegPath = env('FFMPEG_PATH', 'ffmpeg');
        
        // Thumbnail path to public disk
        $thumbnailRelPath = "users/{$user_id}/thumbnails/{$uuid}.jpg";
        $thumbnailAbsolutePath = Storage::disk('public')->path($thumbnailRelPath);
        
        // Ensure directory exists
        Storage::disk('public')->makeDirectory("users/{$user_id}/thumbnails");

        // Command: ffmpeg -i input.mp4 -ss 00:00:01.000 -vframes 1 output.jpg
        $process = new Process([
            $ffmpegPath, 
            '-y', 
            '-i', $originalAbsolutePath, 
            '-ss', '00:00:01.000', 
            '-vframes', '1', 
            $thumbnailAbsolutePath
        ]);
        $process->setTimeout($this->timeout);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $gallery->update([
            'thumbnail_path' => $thumbnailRelPath,
            'conversion_status' => 'done',
            'preview_ready_at' => now(),
        ]);
    }

    private function processOffice(Gallery $gallery, string $originalAbsolutePath, $user_id, $uuid)
    {
        $libreOfficePath = env('LIBREOFFICE_PATH', 'soffice');
        
        // Converted path to local disk
        $convertedRelPath = "users/{$user_id}/converted/{$uuid}.pdf";
        $convertedAbsoluteDir = Storage::disk('local')->path("users/{$user_id}/converted");
        
        // Ensure directory exists in local storage
        Storage::disk('local')->makeDirectory("users/{$user_id}/converted");

        // Command: soffice --headless --convert-to pdf --outdir /path/to/converted /path/to/input.docx
        $process = new Process([
            $libreOfficePath,
            '--headless',
            '--convert-to', 'pdf',
            '--outdir', $convertedAbsoluteDir,
            $originalAbsolutePath
        ]);
        
        $process->setTimeout($this->timeout);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $gallery->update([
            'preview_path' => $convertedRelPath,
            'preview_type' => 'pdf', // It's now a PDF
            'conversion_status' => 'done',
            'preview_ready_at' => now(),
        ]);
    }
}
