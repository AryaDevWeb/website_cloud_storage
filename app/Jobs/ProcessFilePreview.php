<?php

namespace App\Jobs;

use App\Models\Gallery;
use App\Services\FileArchiveService;
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

        $extractedAbsolutePath = null;

        try {
            $user_id = $gallery->user_id;
            $uuid = pathinfo($gallery->file, PATHINFO_FILENAME);
            $originalPath = $gallery->path;

            // Get absolute path of the original ZIP file
            $originalAbsolutePath = Storage::disk('local')->path($originalPath);

            if (!file_exists($originalAbsolutePath)) {
                throw new \Exception("Original file not found: " . $originalAbsolutePath);
            }

            // Extract the original file from the ZIP archive to a temporary file
            $extractedAbsolutePath = FileArchiveService::extractFirstFileToTemp($originalAbsolutePath)['path'];

            if ($gallery->preview_type === 'image') {
                $this->processImage($gallery, $extractedAbsolutePath, $user_id, $uuid);
            } elseif ($gallery->preview_type === 'video') {
                $this->processVideo($gallery, $extractedAbsolutePath, $user_id, $uuid);
            } elseif ($gallery->preview_type === 'office') {
                $this->processOffice($gallery, $extractedAbsolutePath, $user_id, $uuid);
            } elseif ($gallery->preview_type === 'pdf') {
                $this->processPdf($gallery, $extractedAbsolutePath, $user_id, $uuid);
            } else {
                $gallery->update([
                    'conversion_status' => 'done',
                    'preview_ready_at' => now(),
                ]);
            }

        } catch (\Exception $e) {
            \Log::error("ProcessFilePreview Failed for Gallery ID {$this->galleryId}: " . $e->getMessage());
            $gallery->update(['conversion_status' => 'failed']);
        } finally {
            // Clean up the extracted raw file
            if ($extractedAbsolutePath && file_exists($extractedAbsolutePath)) {
                @unlink($extractedAbsolutePath);
            }
        }
    }

    private function processImage(Gallery $gallery, string $extractedAbsolutePath, $user_id, $uuid)
    {
        try {
            $ext = strtolower($gallery->extension);
            $mime = strtolower($gallery->mime_type);
            
            if ($ext === 'svg' || $mime === 'image/svg+xml') {
                $thumbnailRelPath = "users/{$user_id}/thumbnails/{$uuid}.svg";
                Storage::disk('public')->put($thumbnailRelPath, file_get_contents($extractedAbsolutePath));
                
                // Copy as preview
                $previewRelPath = "users/{$user_id}/preview/{$uuid}.svg";
                Storage::disk('local')->put($previewRelPath, file_get_contents($extractedAbsolutePath));

                $gallery->update([
                    'thumbnail_path' => $thumbnailRelPath,
                    'preview_path' => $previewRelPath,
                    'preview_type' => 'image',
                    'conversion_status' => 'done',
                    'preview_ready_at' => now(),
                ]);
                return;
            }

            // Create compressed lossy version (WebP) for viewing
            $previewRelPath = "users/{$user_id}/preview/{$uuid}.webp";
            Storage::disk('local')->makeDirectory("users/{$user_id}/preview");
            
            $manager = new ImageManager(new Driver());
            $image = $manager->decodePath($extractedAbsolutePath);
            
            // Downscale to max 1200px width/height for fast web loading
            if ($image->width() > 1200 || $image->height() > 1200) {
                $image->scaleDown(width: 1200, height: 1200);
            }

            // Encode to webp at 45% quality (highly lossy compression)
            $encoded = $image->encodeUsingFileExtension('webp', 45);
            Storage::disk('local')->put($previewRelPath, $encoded->toString());

            // Create thumbnail for file manager UI grids
            $thumbnailRelPath = "users/{$user_id}/thumbnails/{$uuid}.webp";
            Storage::disk('public')->makeDirectory("users/{$user_id}/thumbnails");

            $thumbnailImage = $manager->decodePath($extractedAbsolutePath);
            $thumbnailImage->scaleDown(width: 300);
            $encodedThumb = $thumbnailImage->encodeUsingFileExtension('webp', 60);
            Storage::disk('public')->put($thumbnailRelPath, $encodedThumb->toString());

            $gallery->update([
                'preview_path' => $previewRelPath,
                'preview_type' => 'image',
                'thumbnail_path' => $thumbnailRelPath,
                'conversion_status' => 'done',
                'preview_ready_at' => now(),
            ]);
        } catch (\Exception $e) {
            \Log::warning("processImage (preview/thumbnail generation) failed for Gallery ID {$gallery->id}: " . $e->getMessage());
            $gallery->update([
                'conversion_status' => 'failed',
            ]);
        }
    }

    private function processVideo(Gallery $gallery, string $extractedAbsolutePath, $user_id, $uuid)
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
            '-i', $extractedAbsolutePath, 
            '-ss', '00:00:01.000', 
            '-vframes', '1', 
            $thumbnailAbsolutePath
        ]);
        $process->setTimeout($this->timeout);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        // For video, preview path is just the original ZIP extraction, or we can stream the preview directly
        // Usually videos can stream or we can keep it without a compressed version for now
        $gallery->update([
            'thumbnail_path' => $thumbnailRelPath,
            'conversion_status' => 'done',
            'preview_ready_at' => now(),
        ]);
    }

    private function processOffice(Gallery $gallery, string $extractedAbsolutePath, $user_id, $uuid)
    {
        $libreOfficePath = env('LIBREOFFICE_PATH', 'soffice');
        
        $previewRelPath = "users/{$user_id}/preview/{$uuid}.pdf";
        $previewAbsoluteDir = Storage::disk('local')->path("users/{$user_id}/preview");
        
        // Ensure directory exists in local storage
        Storage::disk('local')->makeDirectory("users/{$user_id}/preview");

        // Command: soffice --headless --convert-to pdf --outdir /path/to/preview /path/to/input.docx
        $process = new Process([
            $libreOfficePath,
            '--headless',
            '--convert-to', 'pdf',
            '--outdir', $previewAbsoluteDir,
            $extractedAbsolutePath
        ]);
        
        $process->setTimeout($this->timeout);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        // Rename the resulting converted PDF to {uuid}.pdf
        $extractedFileName = basename($extractedAbsolutePath);
        $outputPdfName = pathinfo($extractedFileName, PATHINFO_FILENAME) . '.pdf';
        $outputPdfPath = $previewAbsoluteDir . DIRECTORY_SEPARATOR . $outputPdfName;
        $finalPdfPath = $previewAbsoluteDir . DIRECTORY_SEPARATOR . $uuid . '.pdf';

        if (file_exists($outputPdfPath) && $outputPdfPath !== $finalPdfPath) {
            rename($outputPdfPath, $finalPdfPath);
        }

        // Generate thumbnail for the first page
        $this->generatePdfThumbnail($finalPdfPath, $user_id, $uuid);

        $gallery->update([
            'preview_path' => $previewRelPath,
            'preview_type' => 'pdf',
            'conversion_status' => 'done',
            'preview_ready_at' => now(),
        ]);
    }

    private function processPdf(Gallery $gallery, string $extractedAbsolutePath, $user_id, $uuid)
    {
        $previewRelPath = "users/{$user_id}/preview/{$uuid}.pdf";
        $previewAbsolutePath = Storage::disk('local')->path($previewRelPath);
        Storage::disk('local')->makeDirectory("users/{$user_id}/preview");

        // Compress the PDF using Ghostscript if available
        $gsPath = env('GS_PATH', 'gs');
        $process = new Process([
            $gsPath,
            '-sDEVICE=pdfwrite',
            '-dCompatibilityLevel=1.4',
            '-dPDFSETTINGS=/screen', // Web-optimized low-res (72 dpi)
            '-dNOPAUSE',
            '-dQUIET',
            '-dBATCH',
            '-sOutputFile=' . $previewAbsolutePath,
            $extractedAbsolutePath
        ]);
        $process->run();

        // Fallback: if GS is not found or fails, copy the file directly
        if (!$process->isSuccessful() || !file_exists($previewAbsolutePath) || filesize($previewAbsolutePath) === 0) {
            copy($extractedAbsolutePath, $previewAbsolutePath);
        }

        // Generate a thumbnail image for the PDF first page
        $this->generatePdfThumbnail($previewAbsolutePath, $user_id, $uuid);

        $gallery->update([
            'preview_path' => $previewRelPath,
            'preview_type' => 'pdf',
            'conversion_status' => 'done',
            'preview_ready_at' => now(),
        ]);
    }

    /**
     * Helper to render the first page of a PDF as a thumbnail image.
     */
    private function generatePdfThumbnail(string $pdfAbsolutePath, $user_id, $uuid)
    {
        try {
            $popplerPath = env('POPPLER_PATH');
            
            if (!$popplerPath) {
                if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                    $popplerPath = base_path('bin' . DIRECTORY_SEPARATOR . 'poppler' . DIRECTORY_SEPARATOR . 'poppler-24.02.0' . DIRECTORY_SEPARATOR . 'Library' . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'pdftocairo.exe');
                } else {
                    $popplerPath = 'pdftocairo';
                }
            }
            
            if (str_contains($popplerPath, DIRECTORY_SEPARATOR) && !file_exists($popplerPath)) {
                return;
            }

            $thumbnailRelPath = "users/{$user_id}/thumbnails/{$uuid}.jpg";
            $thumbnailAbsolutePrefix = Storage::disk('public')->path("users/{$user_id}/thumbnails/{$uuid}");
            Storage::disk('public')->makeDirectory("users/{$user_id}/thumbnails");

            // Command: pdftocairo -jpeg -singlefile -f 1 -l 1 -scale-to 400 input.pdf output_prefix
            $process = new Process([
                $popplerPath,
                '-jpeg',
                '-singlefile',
                '-f', '1',
                '-l', '1',
                '-scale-to', '400',
                $pdfAbsolutePath,
                $thumbnailAbsolutePrefix
            ]);
            $process->run();

            if ($process->isSuccessful()) {
                $gallery = Gallery::where('file', 'LIKE', $uuid . '%')->first();
                if ($gallery) {
                    $gallery->update(['thumbnail_path' => $thumbnailRelPath]);
                }
            }
        } catch (\Exception $e) {
            \Log::warning("generatePdfThumbnail execution failed: " . $e->getMessage());
        }
    }
}
