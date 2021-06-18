<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Settings;
use Illuminate\Support\Facades\DB;

class SettingsContoller extends Controller
{
    public function getSettings() {
        return json_encode(Settings::where('visible', 1)->get());
    }

    public function setSettings(Request $request) {
        $array = array_filter($request->json()->all(), function($v) {
            return $v != 'user_id';
        }, ARRAY_FILTER_USE_KEY);
        DB::beginTransaction();
        foreach ($array as $key => $value) {
            DB::table('settings')
               ->where('serviceName', $key)
               ->update(['value' => $value]);
        }
        DB::commit();
        return json_encode(Settings::where('visible', 1)->get());
    }
}
