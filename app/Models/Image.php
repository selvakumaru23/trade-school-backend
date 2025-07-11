<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    use HasFactory;
    protected $guarded = []; // make all fields fillable

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
    /*
     * An Image can have multiple output images generated,
     * for example if we are creating multiple options.
     */
    public function outputimages()
    {
        return $this->hasMany(Outputimage::class);
    }
}
