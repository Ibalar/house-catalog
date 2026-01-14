<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'external_id',
        'title',
        'slug',
        'description',
        'category_id',
        'price_from',
        'price_to',
        'area',
        'floors',
        'bedrooms',
        'bathrooms',
        'has_garage',
        'roof_type',
        'style',
        'main_image',
        'is_featured',
        'is_published',
        'sort_order',
        'meta_title',
        'meta_description',
    ];

    protected $casts = [
        'external_id' => 'string',
        'slug' => 'string',
        'category_id' => 'integer',
        'price_from' => 'decimal:2',
        'price_to' => 'decimal:2',
        'area' => 'decimal:2',
        'floors' => 'integer',
        'bedrooms' => 'integer',
        'bathrooms' => 'integer',
        'has_garage' => 'boolean',
        'is_featured' => 'boolean',
        'is_published' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProjectCategory::class, 'category_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProjectImage::class, 'project_id')->orderBy('sort_order');
    }
}
