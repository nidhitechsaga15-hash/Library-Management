<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Library extends Model
{
    protected $fillable = [
        'name',
        'code',
        'address',
        'phone',
        'email',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function staff(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'library_staff', 'library_id', 'staff_id')
            ->where('role', 'staff')
            ->withTimestamps();
    }

    public function settings(): HasOne
    {
        return $this->hasOne(LibrarySetting::class);
    }

    public function books(): HasMany
    {
        return $this->hasMany(Book::class);
    }
}
