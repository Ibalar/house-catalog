<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Project;
use App\Models\ProjectCategory;
use App\Models\ProjectImage;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use League\Csv\Exception as CsvException;
use League\Csv\Reader;

class ProjectImportService
{
    private const REQUIRED_COLUMNS = [
        'title',
        'category_slug',
        'price_from',
        'price_to',
        'area',
        'floors',
        'bedrooms',
        'bathrooms',
        'has_garage',
        'roof_type',
        'style',
    ];

    private const OPTIONAL_COLUMNS = [
        'external_id',
        'description',
        'main_image_url',
        'gallery_urls',
        'meta_title',
        'meta_description',
    ];

    private array $categoryIdsBySlug = [];

    private int $successCount = 0;
    private int $failedCount = 0;
    private int $createdCount = 0;
    private int $updatedCount = 0;

    /** @var array<int, array{row:int, error:string}> */
    private array $errors = [];

    /** @var array<int, string> */
    private array $warnings = [];

    /**
     * @return array{success:int, failed:int, errors:array, warnings:array, created:int, updated:int}
     */
    public function import(string $filePath, string $mode = 'create_or_update'): array
    {
        $this->reset();

        if (!in_array($mode, ['create', 'update', 'create_or_update'], true)) {
            return $this->failAll('Некорректный режим импорта');
        }

        try {
            $this->validateAndNormalizeFile($filePath);

            $this->categoryIdsBySlug = ProjectCategory::query()->pluck('id', 'slug')->all();

            $csv = $this->createReader($filePath);

            $header = $this->sanitizeHeader($csv->getHeader());
            $this->validateHeader($header);

            $records = $csv->getRecords($header);

            $rowNumber = 1; // header row
            foreach ($records as $record) {
                $rowNumber++;

                try {
                    $this->processRow($record, $rowNumber, $mode);
                } catch (\Throwable $e) {
                    $this->failedCount++;
                    $this->errors[] = ['row' => $rowNumber, 'error' => $e->getMessage()];
                    Log::warning('Project import row failed', ['row' => $rowNumber, 'error' => $e->getMessage()]);
                }
            }

            Log::info('Project import finished', [
                'success' => $this->successCount,
                'failed' => $this->failedCount,
                'created' => $this->createdCount,
                'updated' => $this->updatedCount,
            ]);
        } catch (\Throwable $e) {
            Log::error('Project import failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return $this->failAll('Ошибка импорта: ' . $e->getMessage());
        }

        return [
            'success' => $this->successCount,
            'failed' => $this->failedCount,
            'errors' => $this->errors,
            'warnings' => $this->warnings,
            'created' => $this->createdCount,
            'updated' => $this->updatedCount,
        ];
    }

    private function reset(): void
    {
        $this->successCount = 0;
        $this->failedCount = 0;
        $this->createdCount = 0;
        $this->updatedCount = 0;
        $this->errors = [];
        $this->warnings = [];
        $this->categoryIdsBySlug = [];
    }

    /**
     * @return array{success:int, failed:int, errors:array, warnings:array, created:int, updated:int}
     */
    private function failAll(string $message): array
    {
        $this->errors[] = ['row' => 0, 'error' => $message];

        return [
            'success' => 0,
            'failed' => 1,
            'errors' => $this->errors,
            'warnings' => $this->warnings,
            'created' => 0,
            'updated' => 0,
        ];
    }

    private function validateAndNormalizeFile(string $filePath): void
    {
        if (!file_exists($filePath)) {
            throw new \RuntimeException('Файл не найден');
        }

        $extension = strtolower((string) pathinfo($filePath, PATHINFO_EXTENSION));
        if (!in_array($extension, ['csv', 'txt'], true)) {
            throw new \RuntimeException('Некорректный формат файла. Разрешены только .csv и .txt');
        }

        if (filesize($filePath) === 0) {
            throw new \RuntimeException('Файл пустой');
        }

        $content = file_get_contents($filePath);
        if ($content === false) {
            throw new \RuntimeException('Не удалось прочитать файл');
        }

        $encoding = mb_detect_encoding($content, ['UTF-8', 'Windows-1251', 'Windows-1252', 'ISO-8859-1'], true);
        if ($encoding !== false && $encoding !== 'UTF-8') {
            $converted = mb_convert_encoding($content, 'UTF-8', $encoding);
            file_put_contents($filePath, $converted);
            $this->warnings[] = "Кодировка файла {$encoding} была преобразована в UTF-8";
        }
    }

    private function createReader(string $filePath): Reader
    {
        try {
            $csv = Reader::createFromPath($filePath, 'r');
            $csv->setDelimiter($this->detectDelimiter($filePath));
            $csv->setHeaderOffset(0);
            return $csv;
        } catch (CsvException $e) {
            throw new \RuntimeException('Не удалось прочитать CSV: ' . $e->getMessage());
        }
    }

    private function detectDelimiter(string $filePath): string
    {
        $handle = fopen($filePath, 'r');
        $firstLine = $handle ? fgets($handle) : false;
        if ($handle) {
            fclose($handle);
        }

        $line = $firstLine ?: '';

        $delimiters = [';', ',', "\t", '|'];
        $best = ',';
        $bestCount = 0;

        foreach ($delimiters as $delimiter) {
            $count = substr_count($line, $delimiter);
            if ($count > $bestCount) {
                $bestCount = $count;
                $best = $delimiter;
            }
        }

        return $best;
    }

    /**
     * @param  list<string>  $header
     * @return list<string>
     */
    private function sanitizeHeader(array $header): array
    {
        $header = array_map(static fn (string $h): string => trim($h), $header);

        if (isset($header[0])) {
            $header[0] = ltrim($header[0], "\xEF\xBB\xBF");
        }

        return $header;
    }

    /**
     * @param  list<string>  $header
     */
    private function validateHeader(array $header): void
    {
        if ($header === []) {
            throw new \RuntimeException('Не найдена строка заголовков CSV');
        }

        $missing = [];
        foreach (self::REQUIRED_COLUMNS as $column) {
            if (!in_array($column, $header, true)) {
                $missing[] = $column;
            }
        }

        if ($missing !== []) {
            throw new \RuntimeException('Отсутствуют обязательные колонки: ' . implode(', ', $missing));
        }
    }

    /**
     * @param  array<string, mixed>  $record
     */
    private function processRow(array $record, int $rowNumber, string $mode): void
    {
        $record = $this->normalizeRecord($record);

        $validation = $this->validateRecord($record);
        if ($validation !== null) {
            $this->failedCount++;
            $this->errors[] = ['row' => $rowNumber, 'error' => $validation];
            return;
        }

        if ($this->isPriceFromGreaterThanTo($record)) {
            $this->warnings[] = "Строка {$rowNumber}: Цена ОТ больше цены ДО";
        }

        DB::transaction(function () use ($record, $mode, $rowNumber): void {
            $externalId = $record['external_id'] ?? null;
            $title = (string) $record['title'];

            $project = $this->findProjectForRow($externalId, $title);

            if ($mode === 'create' && $project !== null) {
                $this->warnings[] = "Строка {$rowNumber}: Проект уже существует, пропущено";
                return;
            }

            if ($mode === 'update' && $project === null) {
                $this->failedCount++;
                $this->errors[] = ['row' => $rowNumber, 'error' => 'Проект не найден для обновления'];
                return;
            }

            $categoryId = $this->categoryIdsBySlug[(string) $record['category_slug']];

            $attributesForCreate = $this->buildAttributes($record, $categoryId, true);

            if ($project === null) {
                $attributesForCreate['slug'] = $this->makeUniqueSlug(Str::slug($title));
                $project = Project::create($attributesForCreate);
                $this->createdCount++;
            } else {
                $attributesForUpdate = $this->buildAttributes($record, $categoryId, false);
                if ($attributesForUpdate !== []) {
                    $project->fill($attributesForUpdate)->save();
                }
                $this->updatedCount++;
            }

            $this->handleImages($project, $record, $rowNumber, $mode);

            $this->successCount++;
        });
    }

    /**
     * @param  array<string, mixed>  $record
     * @return array<string, mixed>
     */
    private function normalizeRecord(array $record): array
    {
        foreach ($record as $k => $v) {
            if (is_string($v)) {
                $record[$k] = trim($v);
            }
        }

        foreach (array_merge(self::REQUIRED_COLUMNS, self::OPTIONAL_COLUMNS) as $key) {
            if (!array_key_exists($key, $record)) {
                continue;
            }

            if ($record[$key] === '') {
                $record[$key] = null;
            }
        }

        return $record;
    }

    /**
     * @param  array<string, mixed>  $record
     */
    private function validateRecord(array $record): ?string
    {
        $validator = Validator::make($record, [
            'title' => ['required', 'string', 'max:255'],
            'category_slug' => ['required', 'string'],
            'external_id' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price_from' => ['nullable', 'numeric', 'min:0'],
            'price_to' => ['nullable', 'numeric', 'min:0'],
            'area' => ['nullable', 'numeric', 'gt:0'],
            'floors' => ['nullable', 'integer', 'between:1,3'],
            'bedrooms' => ['nullable', 'integer', 'between:0,20'],
            'bathrooms' => ['nullable', 'integer', 'between:0,20'],
            'roof_type' => ['nullable', 'string', 'max:255'],
            'style' => ['nullable', 'string', 'max:255'],
            'main_image_url' => ['nullable', 'url'],
            'gallery_urls' => ['nullable', 'string'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:255'],
        ], [
            'title.required' => 'Название обязательно',
            'title.max' => 'Название не должно превышать 255 символов',
            'category_slug.required' => 'Категория обязательна',
            'main_image_url.url' => 'Некорректный URL главного изображения',
        ]);

        if ($validator->fails()) {
            return implode('; ', Arr::flatten($validator->errors()->all()));
        }

        $categorySlug = (string) $record['category_slug'];
        if (!isset($this->categoryIdsBySlug[$categorySlug])) {
            return 'Категория не найдена';
        }

        $hasGarageRaw = $record['has_garage'] ?? null;
        if ($hasGarageRaw !== null && $this->parseBoolean($hasGarageRaw) === null) {
            return 'Поле has_garage должно быть boolean (0/1, true/false, yes/no)';
        }

        if (isset($record['gallery_urls']) && $record['gallery_urls'] !== null) {
            $urls = $this->parseGalleryUrls((string) $record['gallery_urls']);
            foreach ($urls as $url) {
                if (!filter_var($url, FILTER_VALIDATE_URL)) {
                    $this->warnings[] = 'Некорректный URL в gallery_urls: ' . $url;
                }
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $record
     */
    private function isPriceFromGreaterThanTo(array $record): bool
    {
        $from = $record['price_from'] ?? null;
        $to = $record['price_to'] ?? null;

        if ($from === null || $to === null) {
            return false;
        }

        return (float) $from > (float) $to;
    }

    private function parseBoolean(mixed $value): ?bool
    {
        if (is_bool($value)) {
            return $value;
        }

        $v = strtolower(trim((string) $value));

        $trueValues = ['1', 'true', 'yes', 'y', 'да'];
        $falseValues = ['0', 'false', 'no', 'n', 'нет'];

        if (in_array($v, $trueValues, true)) {
            return true;
        }

        if (in_array($v, $falseValues, true)) {
            return false;
        }

        return null;
    }

    private function findProjectForRow(?string $externalId, string $title): ?Project
    {
        if ($externalId !== null) {
            $project = Project::query()->where('external_id', $externalId)->first();
            if ($project !== null) {
                return $project;
            }
        }

        return Project::query()->where('title', $title)->first();
    }

    /**
     * @param  array<string, mixed>  $record
     * @return array<string, mixed>
     */
    private function buildAttributes(array $record, int $categoryId, bool $forCreate): array
    {
        $attrs = [];

        $attrs['category_id'] = $categoryId;

        // Required-ish values
        if ($forCreate || array_key_exists('title', $record)) {
            $attrs['title'] = (string) $record['title'];
        }

        if ($record['external_id'] ?? null) {
            $attrs['external_id'] = (string) $record['external_id'];
        } elseif ($forCreate) {
            $attrs['external_id'] = null;
        }

        if ($forCreate) {
            $attrs['description'] = (string) ($record['description'] ?? '');
            $attrs['is_published'] = true;
        } elseif (array_key_exists('description', $record) && $record['description'] !== null) {
            $attrs['description'] = (string) $record['description'];
        }

        $numericMap = [
            'price_from' => 'price_from',
            'price_to' => 'price_to',
            'area' => 'area',
        ];

        foreach ($numericMap as $key => $field) {
            if ($record[$key] !== null) {
                $attrs[$field] = (float) $record[$key];
            } elseif ($forCreate) {
                $attrs[$field] = null;
            }
        }

        $intMap = [
            'floors' => 'floors',
            'bedrooms' => 'bedrooms',
            'bathrooms' => 'bathrooms',
        ];

        foreach ($intMap as $key => $field) {
            if ($record[$key] !== null) {
                $attrs[$field] = (int) $record[$key];
            } elseif ($forCreate) {
                $attrs[$field] = null;
            }
        }

        if (array_key_exists('has_garage', $record) && $record['has_garage'] !== null) {
            $attrs['has_garage'] = (bool) $this->parseBoolean($record['has_garage']);
        } elseif ($forCreate) {
            $attrs['has_garage'] = false;
        }

        foreach (['roof_type', 'style', 'meta_title', 'meta_description'] as $key) {
            if ($record[$key] !== null) {
                $attrs[$key] = (string) $record[$key];
            } elseif ($forCreate) {
                $attrs[$key] = null;
            }
        }

        // For updates: do not overwrite fields with null values from CSV
        if (!$forCreate) {
            $attrs = array_filter($attrs, static fn ($v): bool => $v !== null);
        }

        return $attrs;
    }

    private function makeUniqueSlug(string $baseSlug): string
    {
        $slug = $baseSlug !== '' ? $baseSlug : Str::random(8);
        $i = 1;

        while (Project::query()->where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $i;
            $i++;
        }

        return $slug;
    }

    /**
     * @param  array<string, mixed>  $record
     */
    private function handleImages(Project $project, array $record, int $rowNumber, string $mode): void
    {
        if (($record['main_image_url'] ?? null) !== null) {
            try {
                $this->downloadAndSaveMainImage($project, (string) $record['main_image_url']);
            } catch (\Throwable $e) {
                $this->warnings[] = "Строка {$rowNumber}: Не удалось загрузить главное изображение";
                Log::warning('Main image download failed', ['row' => $rowNumber, 'url' => $record['main_image_url'], 'error' => $e->getMessage()]);
            }
        }

        if (array_key_exists('gallery_urls', $record)) {
            // Replace gallery only if column exists (even if empty)
            try {
                $this->replaceGallery($project, $record['gallery_urls'] !== null ? (string) $record['gallery_urls'] : '');
            } catch (\Throwable $e) {
                $this->warnings[] = "Строка {$rowNumber}: Не удалось загрузить изображения галереи";
                Log::warning('Gallery images download failed', ['row' => $rowNumber, 'error' => $e->getMessage()]);
            }
        }
    }

    private function downloadAndSaveMainImage(Project $project, string $url): void
    {
        $download = $this->downloadImage($url);

        $path = 'projects/' . $project->id . '_main.' . $download['extension'];

        $old = $project->main_image;
        Storage::disk('public')->put($path, $download['body']);

        $project->update(['main_image' => $path]);

        if ($old && $old !== $path) {
            Storage::disk('public')->delete($old);
        }
    }

    private function replaceGallery(Project $project, string $galleryUrlsRaw): void
    {
        $existing = ProjectImage::query()->where('project_id', $project->id)->get();
        foreach ($existing as $img) {
            if ($img->image_path) {
                Storage::disk('public')->delete($img->image_path);
            }
        }
        ProjectImage::query()->where('project_id', $project->id)->delete();

        $urls = $this->parseGalleryUrls($galleryUrlsRaw);
        $sort = 0;

        foreach ($urls as $url) {
            if (!filter_var($url, FILTER_VALIDATE_URL)) {
                continue;
            }

            try {
                $download = $this->downloadImage($url);
                $path = 'projects/' . $project->id . '_gallery_' . $sort . '.' . $download['extension'];
                Storage::disk('public')->put($path, $download['body']);

                ProjectImage::create([
                    'project_id' => $project->id,
                    'image_path' => $path,
                    'sort_order' => $sort,
                ]);
                $sort++;
            } catch (\Throwable) {
                // continue, handled as warning by caller
            }
        }
    }

    /**
     * @return list<string>
     */
    private function parseGalleryUrls(string $raw): array
    {
        $raw = trim($raw);
        if ($raw === '') {
            return [];
        }

        $parts = array_map('trim', explode(',', $raw));
        $parts = array_values(array_filter($parts, static fn (string $v): bool => $v !== ''));

        return $parts;
    }

    /**
     * @return array{body:string, extension:string}
     */
    private function downloadImage(string $url): array
    {
        $response = Http::timeout(30)->get($url);

        if (!$response->successful()) {
            throw new \RuntimeException('HTTP ' . $response->status());
        }

        $contentType = strtolower((string) $response->header('Content-Type', ''));
        $contentType = trim(explode(';', $contentType)[0]);

        $extension = match ($contentType) {
            'image/jpeg', 'image/jpg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => '',
        };

        if ($extension === '') {
            $extFromUrl = strtolower((string) pathinfo(parse_url($url, PHP_URL_PATH) ?: '', PATHINFO_EXTENSION));
            if (in_array($extFromUrl, ['jpg', 'jpeg', 'png', 'webp'], true)) {
                $extension = $extFromUrl === 'jpeg' ? 'jpg' : $extFromUrl;
            }
        }

        if ($extension === '') {
            throw new \RuntimeException('Файл не является изображением');
        }

        return ['body' => $response->body(), 'extension' => $extension];
    }
}
