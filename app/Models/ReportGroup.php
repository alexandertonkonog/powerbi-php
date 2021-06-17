<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\UserGroup;
use App\Models\Report;

class ReportGroup extends Model
{
    public function userGroups()
    {
        return $this->belongsToMany(UserGroup::class);
    }
    public function reports()
    {
        return $this->belongsToMany(Report::class);
    }
    public function jsonReports()
    {
        $elem = $this;
        $elem->reports = $this->reports;
        return json_decode($elem, true);
    }
    public static function boot() {
        parent::boot();

        static::deleting(function($reportGroup) { // before delete() method call this
             $reportGroup->reports()->detach();
             $reportGroup->userGroups()->detach();
        });
    }
    use HasFactory;
}
