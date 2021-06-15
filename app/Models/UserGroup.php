<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\ReportGroup;

class UserGroup extends Model
{
    use HasFactory;
    public function users()
    {
        return $this->belongsToMany(User::class);
    }
    public function reportGroups()
    {
        return $this->belongsToMany(ReportGroup::class);
    }
    public function json()
    {
        $elem = $this;
        $elem->users = $this->users;
        return json_decode($elem, true);
    }
    public function jsonReports()
    {
        $elem = $this;
        $elem->reportGroups = $this->reportGroups;
        return json_decode($elem, true);
    }
}
