<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Services\ProjectImportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ImportProjectsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 3600; // 1 hour
    public int $tries = 1;
    public array $backoff = [300]; // 5 minutes

    public function __construct(
        private readonly string $filePath,
        private readonly string $mode,
        private readonly ?int $userId = null
    ) {}

    public function handle(ProjectImportService $importService): void
    {
        Log::info('Starting project import job', [
            'file' => $this->filePath,
            'mode' => $this->mode,
            'user_id' => $this->userId,
        ]);

        try {
            $results = $importService->import($this->filePath, $this->mode);

            Log::info('Project import job completed', $results);

            // Clean up temporary file
            if (file_exists($this->filePath)) {
                unlink($this->filePath);
            }

            // TODO: Send notification to user
            // This can be implemented using Laravel notifications
            // or by storing results in database for user to view
        } catch (\Exception $e) {
            Log::error('Project import job failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Clean up temporary file even on failure
            if (file_exists($this->filePath)) {
                unlink($this->filePath);
            }

            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Project import job failed permanently', [
            'file' => $this->filePath,
            'mode' => $this->mode,
            'user_id' => $this->userId,
            'error' => $exception->getMessage(),
        ]);

        // Clean up temporary file
        if (file_exists($this->filePath)) {
            unlink($this->filePath);
        }
    }
}
