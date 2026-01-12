<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EResource extends Model
{
    protected $fillable = [
        'title',
        'description',
        'author_id',
        'category_id',
        'library_id',
        'file_path',
        'file_type',
        'file_size',
        'isbn',
        'publisher',
        'publication_year',
        'access_level',
        'allowed_roles',
        'download_count',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'publication_year' => 'integer',
            'file_size' => 'integer',
            'download_count' => 'integer',
            'allowed_roles' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(Author::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function library(): BelongsTo
    {
        return $this->belongsTo(Library::class);
    }

    public function canAccess($user)
    {
        if ($this->access_level === 'public') {
            return true;
        }
        
        if ($this->access_level === 'member' && $user) {
            return true;
        }
        
        if ($this->access_level === 'restricted' && $user && $this->allowed_roles) {
            return in_array($user->role, $this->allowed_roles);
        }
        
        return false;
    }
}
