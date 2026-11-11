<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids; // <-- 1. Importe a trait
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory, HasUuids; // <-- 2. Adicione a trait aqui

    protected $fillable = [
        'name',
        'slug',
    ];
}