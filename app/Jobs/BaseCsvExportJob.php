<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Base class for CSV export jobs.
 * 
 * Provides common functionality for generating CSV files with:
 * - Chunked processing for large datasets
 * - UTF-8 BOM encoding for Excel/pt-BR compatibility
 * - Temporary file storage with unique filenames
 * - Automatic cleanup of old files
 */
abstract class BaseCsvExportJob implements ShouldQueue
{
    use Queueable;

    /**
     * The user ID requesting the export.
     */
    public int $userId;

    /**
     * Company ID for filtering data.
     */
    public ?int $companyId;

    /**
     * Project ID for filtering data (optional).
     */
    public ?int $projectId;

    /**
     * Additional filters for the export.
     *
     * @var array<string, mixed>
     */
    public array $filters;

    /**
     * Create a new job instance.
     *
     * @param  int  $userId
     * @param  int|null  $companyId
     * @param  int|null  $projectId
     * @param  array<string, mixed>  $filters
     */
    public function __construct(
        int $userId,
        ?int $companyId = null,
        ?int $projectId = null,
        array $filters = []
    ) {
        $this->userId = $userId;
        $this->companyId = $companyId;
        $this->projectId = $projectId;
        $this->filters = $filters;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $startTime = microtime(true);
        $disk = $this->getStorageDisk();
        $filename = $this->generateFilename();
        $filePath = "exports/{$filename}";

        try {
            Log::info('Starting CSV export', [
                'type' => $this->getReportType(),
                'user_id' => $this->userId,
                'filename' => $filename,
            ]);

            // Open file handle for writing
            $handle = fopen('php://temp', 'r+');

            if ($handle === false) {
                throw new \RuntimeException('Failed to open temporary file for CSV export');
            }

            // Write UTF-8 BOM for Excel/pt-BR compatibility
            fwrite($handle, "\xEF\xBB\xBF");

            // Write headers
            $headers = $this->getHeaders();
            fputcsv($handle, $headers, ';');

            // Process data in chunks
            $rowCount = 0;
            $chunkSize = $this->getChunkSize();

            $this->processDataInChunks(function ($rows) use ($handle, &$rowCount) {
                foreach ($rows as $row) {
                    $csvRow = $this->formatRow($row);
                    fputcsv($handle, $csvRow, ';');
                    $rowCount++;
                }
            }, $chunkSize);

            // Save to storage
            rewind($handle);
            $content = stream_get_contents($handle);
            fclose($handle);

            Storage::disk($disk)->put($filePath, $content);

            $duration = round(microtime(true) - $startTime, 2);

            Log::info('CSV export completed', [
                'type' => $this->getReportType(),
                'user_id' => $this->userId,
                'filename' => $filename,
                'rows' => $rowCount,
                'duration_seconds' => $duration,
            ]);

            // Generate temporary URL and send notification
            $this->notifyUser($filePath, $rowCount);

            // Cleanup old exports (older than 7 days)
            $this->cleanupOldExports();
        } catch (\Exception $e) {
            Log::error('CSV export failed', [
                'type' => $this->getReportType(),
                'user_id' => $this->userId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Get the report type identifier.
     * Used for logging and filename generation.
     */
    abstract protected function getReportType(): string;

    /**
     * Get CSV headers in Portuguese (pt-BR).
     *
     * @return array<int, string>
     */
    abstract protected function getHeaders(): array;

    /**
     * Process data in chunks and call the callback for each chunk.
     *
     * @param  callable  $callback
     * @param  int  $chunkSize
     */
    abstract protected function processDataInChunks(callable $callback, int $chunkSize): void;

    /**
     * Format a data row for CSV output.
     *
     * @param  mixed  $row
     * @return array<int, mixed>
     */
    abstract protected function formatRow($row): array;

    /**
     * Get the storage disk to use for exports.
     */
    protected function getStorageDisk(): string
    {
        return config('filesystems.default', 'local');
    }

    /**
     * Get the chunk size for processing data.
     */
    protected function getChunkSize(): int
    {
        return 1000;
    }

    /**
     * Generate a unique filename for the export.
     */
    protected function generateFilename(): string
    {
        $type = $this->getReportType();
        $timestamp = now()->format('Y-m-d_His');
        $uuid = Str::random(8);

        return "{$type}_{$timestamp}_{$uuid}.csv";
    }

    /**
     * Notify the user that the export is ready.
     *
     * @param  string  $filePath
     * @param  int  $rowCount
     */
    protected function notifyUser(string $filePath, int $rowCount): void
    {
        // Generate download URL using the reports download endpoint
        // The endpoint will handle authentication and file serving
        $filename = basename($filePath);
        $downloadUrl = url("/api/v1/reports/download/{$filename}");

        // Get user for notifiable relationship
        $user = \App\Models\User::find($this->userId);
        
        if (!$user) {
            Log::warning('User not found for export notification', [
                'user_id' => $this->userId,
                'file_path' => $filePath,
            ]);
            return;
        }

        \App\Models\Notification::create([
            'user_id' => $this->userId,
            'notifiable_id' => $this->userId, // Use user as notifiable for export notifications
            'notifiable_type' => \App\Models\User::class,
            'type' => 'export.completed',
            'data' => [
                'export_type' => $this->getReportType(),
                'file_path' => $filePath,
                'filename' => $filename,
                'download_url' => $downloadUrl,
                'row_count' => $rowCount,
                'expires_at' => now()->addDays(7)->toIso8601String(),
            ],
            'channels' => ['database'],
        ]);

        Log::info('Export notification sent', [
            'user_id' => $this->userId,
            'export_type' => $this->getReportType(),
            'file_path' => $filePath,
        ]);
    }

    /**
     * Clean up old export files (older than 7 days).
     */
    protected function cleanupOldExports(): void
    {
        $disk = $this->getStorageDisk();
        $cutoffDate = now()->subDays(7);

        try {
            $files = Storage::disk($disk)->files('exports');

            foreach ($files as $file) {
                $lastModified = Storage::disk($disk)->lastModified($file);

                if ($lastModified && $lastModified < $cutoffDate->timestamp) {
                    Storage::disk($disk)->delete($file);
                    Log::debug('Deleted old export file', ['file' => $file]);
                }
            }
        } catch (\Exception $e) {
            Log::warning('Failed to cleanup old exports', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}

