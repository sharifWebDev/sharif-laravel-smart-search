<?php

namespace Sharif\LaravelSmartSearch\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Sharif\LaravelSmartSearch\Tests\Models\Product;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use LaravelSmartSearch\Tests\Database\Factories\UserFactory;

class User extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'email'];

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    protected static function newFactory()
    {
        return UserFactory::new();
    }
}
