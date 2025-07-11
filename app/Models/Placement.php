<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Placement extends Model
{
    use HasFactory;
    protected $guarded = []; // make all fields fillable

    public function units()
    {
        return $this->hasMany(Unit::class);
    }

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }
}
