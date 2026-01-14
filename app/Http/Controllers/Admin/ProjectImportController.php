<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\ImportProjectsJob;
use App\Services\ProjectImportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ProjectImportController extends Controller
{
    public function __construct(
        private readonly ProjectImportService $importService
    ) {}

    public function showForm()
    {
        return view('admin.project-import');
    }

    public function import(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'csv_file' => 'required_without:csv_content|file|mimes:csv,txt|max:10240',
                'csv_content' => 'required_without:csv_file|string|max:100000',
                'mode' => 'required|in:create,update,create_or_update',
            ], [
                'csv_file.required_without' => 'Please upload a CSV file or paste CSV content',
                'csv_content.required_without' => 'Please upload a CSV file or paste CSV content',
                'csv_file.mimes' => 'File must be a CSV or TXT file',
                'csv_file.max' => 'File size must not exceed 10MB',
                'mode.required' => 'Import mode is required',
                'mode.in' => 'Invalid import mode',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first(),
                    'errors' => $validator->errors()->toArray(),
                ], 422);
            }

            $filePath = null;

            // Handle uploaded file
            if ($request->hasFile('csv_file')) {
                $file = $request->file('csv_file');
                $filePath = $file->storeAs('temp', 'import_' . time() . '_' . uniqid() . '.csv');
                $filePath = storage_path('app/' . $filePath);
            }
            // Handle pasted content
            elseif ($request->filled('csv_content')) {
                $content = $request->input('csv_content');
                $filePath = storage_path('app/temp/import_' . time() . '_' . uniqid() . '.csv');
                
                // Ensure temp directory exists
                $tempDir = dirname($filePath);
                if (!is_dir($tempDir)) {
                    mkdir($tempDir, 0755, true);
                }
                
                file_put_contents($filePath, $content);
            }

            if (!$filePath) {
                return response()->json([
                    'success' => false,
                    'message' => 'No file or content provided',
                ], 422);
            }

            $mode = $request->input('mode', 'create_or_update');

            // Count rows to determine if we should use queue
            $rowCount = $this->countCsvRows($filePath);

            if ($rowCount > 100) {
                // Dispatch to queue for large files
                ImportProjectsJob::dispatch($filePath, $mode, auth('moonshine')->id());

                return response()->json([
                    'success' => true,
                    'message' => "Import job queued for processing. File contains {$rowCount} rows.",
                    'queued' => true,
                ]);
            }

            // Process synchronously for small files
            $results = $this->importService->import($filePath, $mode);

            // Clean up temp file
            if (file_exists($filePath)) {
                unlink($filePath);
            }

            return response()->json([
                'success' => true,
                'message' => $this->formatResultMessage($results),
                'results' => $results,
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function countCsvRows(string $filePath): int
    {
        try {
            $handle = fopen($filePath, 'r');
            $count = 0;
            
            while (fgets($handle) !== false) {
                $count++;
            }
            
            fclose($handle);
            
            // Subtract 1 for header row
            return max(0, $count - 1);
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function formatResultMessage(array $results): string
    {
        $message = "Import completed: ";
        $parts = [];

        if ($results['created'] > 0) {
            $parts[] = "{$results['created']} created";
        }

        if ($results['updated'] > 0) {
            $parts[] = "{$results['updated']} updated";
        }

        if ($results['failed'] > 0) {
            $parts[] = "{$results['failed']} failed";
        }

        if (count($results['warnings']) > 0) {
            $parts[] = count($results['warnings']) . " warnings";
        }

        return $message . implode(', ', $parts);
    }
}
