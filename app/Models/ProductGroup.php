<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;

class ProductGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'title',
        'description',
        'image',
        'sort',
        'status',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $appends = ['image_url'];

    public function scopeStatus($query, $status = 'ACTIVE')
    {
        return $query->where('status', $status);
    }

    /**
     * Get all sub-groups for this product group
     */
    public function subGroups(): HasMany
    {
        return $this->hasMany(ProductSubGroup::class);
    }

    public function getImageUrlAttribute()
    {
        if (!$this->image) {
            return null;
        }

        return Storage::disk('public')->url($this->image);
    }
}
