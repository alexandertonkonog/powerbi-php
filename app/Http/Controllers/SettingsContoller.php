<?php

namespace App\Http\Controllers;

date_default_timezone_set('Europe/Moscow');

use Illuminate\Http\Request;
use App\Models\Settings;
use App\Models\Report;
use App\Models\User;
use App\Models\UserGroup;
use Illuminate\Support\Facades\DB;
use GuzzleHttp\Client;
use App\Utils\ExternalApi;

class SettingsContoller extends Controller
{
    public function getSettings() {
        return json_encode(Settings::where('visible', 1)->get());
    }

    public function firstSetSettings(Request $request) {
        /*new admin*/
        $userId = $request->input('id');
        $userName = $request->input('name');
        $user = User::find(intval($userId));
        if ($user) {
            $user->name = $userName;
            $user->save();
            $user->groups()->attach([['user_id' => $user->id, 'user_group_id' => 1]]);
        } else {
            $user = new User();
            $user->id = intval($userId);
            $user->name = $userName;
            $user->save();
            $user->groups()->attach([['user_id' => intval($userId), 'user_group_id' => 1]]);
        }
        /*new admin*/
        return json_encode(['success' => true]);
    }

    public function refreshSettings(Request $request) {
        if (ExternalApi::needRecieveToken()) {
            ExternalApi::getToken();
        }
        $token = Settings::where('serviceName', 'token')->first();
        $workspace = Settings::where('serviceName', 'workspace_id')->first();
        $client = new Client();
        $res = $client->request('GET', 'https://api.powerbi.com/v1.0/myorg/groups/' . $workspace->value .  '/reports', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token->value,
            ]
        ]);
        $body = json_decode($res->getBody(), true);
        $reports = [];
        foreach($body['value'] as $report) {
            $reports[] = ['id' => $report['id'], 'name' => $report['name'], 'url' => $report['embedUrl']];
        }
        $updateReports = Report::upsert($reports, ['id'], ['id', 'name', 'url']);
        $users = $request->input('users');
        User::upsert($users, ['id'], ['id', 'name']);
        $refresh = Settings::where('serviceName', 'lastRefresh')->first();
        $refresh->value = date("Y-m-d H:i:s");
        $refresh->save();
        $result = [
            'users' => User::with('reportGroups', 'groups')->get(),
            'reports' => Report::all(),
            'lastRefresh' => $refresh->value
        ];
        return json_encode($result);
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
