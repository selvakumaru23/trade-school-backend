<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class Unit extends Model implements ShouldQueue
{
    use HasFactory, SerializesModels;

    // for easy interaction with the json data type
    // You can then use this as a PHP array: $generated_copy = $unit->generated_copy;
    protected $casts = [
        'generated_copy' => 'array',
    ];

    protected $guarded = []; // make all fields fillable

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function placement()
    {
        return $this->belongsTo(Placement::class);
    }

    public function images()
    {
        return $this->hasMany(Image::class);
    }
    public function outputimages()
    {
        return $this->hasMany(Outputimage::class);
    }

    public function latestOutputimage()
    {
        return $this->hasOne(Outputimage::class)->orderBy('created_at', 'desc');
    }
}
