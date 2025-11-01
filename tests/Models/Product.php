<?php

namespace Sharifuddin\LaravelSmartSearch\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Sharifuddin\LaravelSmartSearch\Tests\Models\User;
use Sharifuddin\LaravelSmartSearch\Traits\SmartSearch;
use Sharifuddin\LaravelSmartSearch\Tests\Models\Category;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Sharifuddin\LaravelSmartSearch\Tests\Database\Factories\ProductFactory;

class Product extends Model
{
    use HasFactory, SmartSearch;

    protected $fillable = [
        'name',
        'sku',
        'description',
        'category_id',
        'user_id',
        'status'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected static function newFactory()
    {
        return ProductFactory::new();
    }
}
