<?php

namespace Sharif\LaravelSmartSearch\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Sharif\LaravelSmartSearch\Tests\Models\User;
use Sharif\LaravelSmartSearch\Traits\SmartSearch;
use Sharif\LaravelSmartSearch\Tests\Models\Category;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Sharif\LaravelSmartSearch\Tests\Database\Factories\ProductFactory;

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
