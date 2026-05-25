<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use RuntimeException;
use ZipArchive;

class FileArchiveService
{
    public static function ensureZipExtensionIsLoaded(): void
    {
        if (!class_exists(ZipArchive::class)) {
            throw new RuntimeException('Ekstensi PHP zip belum aktif. Aktifkan extension=zip di php.ini lalu restart server.');
        }
    }

    public static function createZipFromUpload(UploadedFile $file, string $displayName): string
    {
        self::ensureZipExtensionIsLoaded();

        $tempZipPath = tempnam(sys_get_temp_dir(), 'zip');
        $zip = new ZipArchive();

        if ($zip->open($tempZipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Gagal mengompresi file ke ZIP.');
        }

        $zip->addFile($file->getRealPath(), basename($displayName));
        $zip->close();

        return $tempZipPath;
    }

    /**
     * Extracts the first regular file from an archive to a uniquely named temp file.
     *
     * @return array{path: string, name: string}
     */
    public static function extractFirstFileToTemp(string $zipAbsolutePath): array
    {
        self::ensureZipExtensionIsLoaded();

        $zip = new ZipArchive();
        if ($zip->open($zipAbsolutePath) !== true) {
            throw new RuntimeException('Gagal membuka file arsip.');
        }

        $entryName = null;
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $candidate = $zip->getNameIndex($i);
            if ($candidate && !str_ends_with($candidate, '/')) {
                $entryName = $candidate;
                break;
            }
        }

        if (!$entryName) {
            $zip->close();
            throw new RuntimeException('Arsip ZIP tidak berisi file.');
        }

        $stream = $zip->getStream($entryName);
        if (!$stream) {
            $zip->close();
            throw new RuntimeException('Gagal membaca file di dalam arsip.');
        }

        $tempDir = storage_path('app/temp');
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0777, true);
        }

        $extension = pathinfo($entryName, PATHINFO_EXTENSION);
        do {
            $tempPath = $tempDir . DIRECTORY_SEPARATOR . 'extract_' . bin2hex(random_bytes(8));
            if ($extension) {
                $tempPath .= '.' . $extension;
            }
        } while (file_exists($tempPath));

        $target = fopen($tempPath, 'wb');
        if (!$target) {
            fclose($stream);
            $zip->close();
            throw new RuntimeException('Gagal membuat file sementara.');
        }

        stream_copy_to_stream($stream, $target);
        fclose($target);
        fclose($stream);
        $zip->close();

        return [
            'path' => $tempPath,
            'name' => basename($entryName),
        ];
    }
}
