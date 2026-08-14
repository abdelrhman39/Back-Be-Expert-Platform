<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademicLevel extends Model
{
    protected $fillable = [
        'program_id',
        'name_ar',
        'sort_order',
        'status',
    ];

    public function program(): BelongsTo
    {
        return $this->belongsTo(AcademicProgram::class, 'program_id');
    }

    public function courses(): HasMany
    {
        return $this->hasMany(AcademicCourse::class, 'level_id');
    }
}
