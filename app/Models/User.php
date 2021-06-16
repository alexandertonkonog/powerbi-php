<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ReportGroup;
use App\Models\UserGroup;

class User extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'integer';
    protected $fillable = ['id'];
    
    public function groups()
    {
        return $this->belongsToMany(UserGroup::class);
    }
    public function reportGroups()
    {
        return $this->belongsToMany(ReportGroup::class);
    }
    public function jsonReports()
    {
        $elem = $this;
        $elem->reportGroups = $this->reportGroups;
        return json_decode($elem, true);
    }
    public function jsonGroups()
    {
        $elem = $this;
        $elem->groups = $this->groups;
        return json_decode($elem, true);
    }
}

