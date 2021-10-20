<?php

namespace App\Models\Externe\Stock\Cocktail;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cocktail extends Model
{
    use SoftDeletes;
    protected $guarded = [];
    protected $table = 'categories_externes_cocktails';
}
