<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ReportGroup;
use App\Models\Report;
use App\Models\Settings;
use GuzzleHttp\Client;

function getToken() {
    $settings = Settings::all();
    $settings = $settings->toArray();
    $username = array_search('username', array_column($settings, 'serviceName'));
    $password = array_search('password', array_column($settings, 'serviceName'));
    $client_secret = array_search('client_secret', array_column($settings, 'serviceName'));
    $client_id = array_search('client_id', array_column($settings, 'serviceName'));
    $client = new Client();
    $res = $client->request('POST', 'https://login.microsoftonline.com/organizations/oauth2/v2.0/token', [
        'form_params' => [
            'client_id' => $settings[$client_id]["value"],
            'client_secret' => $settings[$client_secret]["value"],
            'username' => $settings[$username]["value"],
            'password' => $settings[$password]["value"],
            'grant_type' => 'password',
            'scope' => 'https://analysis.windows.net/powerbi/api/Report.Read.All',
        ],
        'headers' => [
            'Content-Type' => 'application/x-www-form-urlencoded',
        ]
    ]);
    $body = json_decode($res->getBody(), true);
    $token = $body["access_token"];
    $updateReports = Settings::upsert(
        [
            [
                'name' => 'Время получения токена', 
                'serviceName' => 'tokenTime',
                'value' => date("Y-m-d H:i:s"),
            ],
            [
                'name' => 'Токен доступа', 
                'serviceName' => 'token',
                'value' => $token,
            ]
        ], 
        ['id', 'serviceName'], 
        ['id', 'name', 'value', 'serviceName']
    );
}

function needRecieveToken() {
    $settings = Settings::where('serviceName', 'tokenTime')->first();
    $was = date_create($settings->value);
    $now = date_create(date("Y-m-d H:i:s"));
    $interval = date_diff($was, $now);
    $year = $interval->format('%y');
    $month = $interval->format('%m');
    $day = $interval->format('%d');
    $hour = $interval->format('%h');
    $minute = $interval->format('%r%i');
    return $year > 0 || $month > 0 || $day > 0 || $hour > 0 || $minute >= 9; 
}

class ReportController extends Controller
{
    public function getReports() {
        return json_encode(Report::all());
    }

    public function getReportGroups() {
        return json_encode(ReportGroup::all());
    }

    public function setReports() {
        if (needRecieveToken()) {
            getToken();
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
        return json_encode($updateReports);
    }

    public function setReportIntoGroup(Request $request) {
        $groupId = $request->input('group');
        $reportIds = $request->input('reports');
        $reports = [];

        foreach($reportIds as $value) {
            $reports[] = ['report_group_id' => $groupId, 'report_id' => $value];
        }

        $group = ReportGroup::find(intval($groupId));
        $group->reportGroups()->attach($reports);

        return $user->jsonReports();
    }

    public function setReportGroup(Request $request) {
        $group = new ReportGroup();
        $group->name = $request->input('name');
        $group->description =$request->input('description');
        $group->save();

        return $group->toJson();
    }
}
