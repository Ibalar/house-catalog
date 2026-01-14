<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'full_text',
        'parent_id',
        'sort_order',
        'image',
        'is_published',
        'meta_fields',
    ];

    protected $casts = [
        'slug' => 'string',
        'parent_id' => 'integer',
        'sort_order' => 'integer',
        'is_published' => 'boolean',
        'meta_fields' => 'json',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Service::class, 'parent_id')->orderBy('sort_order');
    }
}
