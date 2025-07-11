<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Provider extends Model
{
    use HasFactory;
    protected $guarded = []; // make all fields fillable

    public function placements()
    {
        return $this->hasMany(Placement::class);
    }
}
