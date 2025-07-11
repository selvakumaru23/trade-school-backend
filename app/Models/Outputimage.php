<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Outputimage extends Model
{
    use HasFactory;
    protected $guarded = []; // make all fields fillable

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
    public function image()
    {
        return $this->belongsTo(Image::class);
    }
}
