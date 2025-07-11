<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    use HasFactory;
    protected $guarded = []; // make all fields fillable

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function style()
    {
        return $this->belongsTo(Style::class);
    }

    public function units()
    {
        return $this->hasMany(Unit::class);
    }

}
