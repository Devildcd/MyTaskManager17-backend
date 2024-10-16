<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    public function user() {
        return $this->belongsTo(User::class);
    }

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'status'
    ];

    public static function rules() {
        return [
            'user_id' => 'required|exists:users,id',
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'status' => 'required|string|max:50'
        ];
    }
}
