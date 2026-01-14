<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Block extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'title',
        'content',
        'image',
        'link',
        'is_active',
    ];

    protected $casts = [
        'name' => 'string',
        'is_active' => 'boolean',
    ];
}
