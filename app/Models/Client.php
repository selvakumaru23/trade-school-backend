<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;
    protected $guarded = []; // make all fields fillable

    public function campaigns()
    {
        return $this->hasMany(Campaign::class);
    }
}
