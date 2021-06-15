<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ReportGroup;

class Report extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';
    

    public function groups()
    {
        return $this->belongsToMany(ReportGroup::class);
    }
}
