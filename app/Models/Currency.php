<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    use HasFactory;

    protected $fillable = ['char_code', 'name', 'nominal', 'is_fetch_enabled', 'is_widget_visible'];

    public function rates()
    {
        return $this->hasMany(Rate::class);
    }
}
