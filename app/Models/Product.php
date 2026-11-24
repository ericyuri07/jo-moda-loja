<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory, HasUuids;

    public const MAX_SUGGESTIONS = 6;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'promotional_price',
        'category_id',
        'image_url',
        'featured',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'promotional_price' => 'decimal:2',
            'featured' => 'boolean',
            'active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    /** Produtos escolhidos pelo admin para o "Veja também", na ordem definida. */
    public function suggestions(): BelongsToMany
    {
        return $this->belongsToMany(
            self::class,
            'product_suggestions',
            'product_id',
            'suggested_product_id'
        )->withPivot('position')->orderByPivot('position');
    }
}
