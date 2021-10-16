<?php

namespace App\Models\Externe\Parametre;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Restaurant extends Model
{
    use SoftDeletes;
    protected $guarded = [];
    protected $dates = ['created_at', 'deleted_at'];
    protected $table = 'restaurants_externes';

    const RULES = [
        'location' => 'required|max:50',
        'nom' => 'required|max:100',
        'contact' => 'required|max:20|unique:restaurants_externes,contact',
        'email' => 'required|email|unique:restaurants_externes,email',
    ];

    public static function regle(int $id)
    {
        return [
            'location' => 'required|max:50',
            'nom' => 'required|max:100',
            'contact' => 'required|max:20|unique:restaurants_externes,contact,' . $id,
            'email' => 'required|email|unique:restaurants_externes,email,' . $id,
        ];
    }
}
