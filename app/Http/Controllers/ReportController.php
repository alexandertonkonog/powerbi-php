<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ReportGroup;
use App\Models\Report;
use App\Models\Settings;
use App\Models\User;
use App\Models\UserGroup;
use GuzzleHttp\Client;
use App\Utils\ExternalApi;

class ReportController extends Controller
{
    public function getReports() {
        return json_encode(Report::all());
    }

    public function getReportsForUser(Request $request) {
        $data = $request->all();
        $id = intval($data['user_id']);
        $user = User::with('groups', 'reportGroups')->find($id);
        $reportGroupsList = [];
        $isAdmin = false;
        if ($user) {
            foreach($user->groups as $group) {
                if ($group->id == 1) {
                    $isAdmin = true;
                    break;
                }
                $array = $group->reportGroups;
                foreach($array as $repGroup) {
                    $id = $repGroup->id;
                    if (!in_array($id, $reportGroupsList)) {
                        $reportGroupsList[] = $id; 
                    }
                }
            }
            if ($isAdmin) {
                return ReportGroup::with('reports')->get();
            } else {
                foreach($user->reportGroups as $group) {
                    $id = $group->id;
                    if (!in_array($id, $reportGroupsList)) {
                        $reportGroupsList[] = $id; 
                    }
                }
                return ReportGroup::with('reports')->whereIn('id', $reportGroupsList)->get();
            }
        } else {
            return response()->json(['error' => 'Нет такого пользователя'], 404);
        }
    }

    public function getReportGroups() {
        return json_encode(ReportGroup::with('reports')->get());
    }

    public function setReports() {
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
        return json_encode($updateReports);
    }

    public function setReportIntoGroup(Request $request) {
        $groupId = $request->input('group');
        $reportIds = $request->input('entities');
        $reports = [];

        foreach($reportIds as $value) {
            $reports[] = ['report_group_id' => $groupId, 'report_id' => $value];
        }

        $group = ReportGroup::find(intval($groupId));
        $group->reports()->attach($reports);

        return json_encode(ReportGroup::with('reports')->get());
    }
    
    public function removeReportFromGroup(Request $request) {
        $groupId = $request->input('group');
        $reportIds = $request->input('entities');
        $group = ReportGroup::find(intval($groupId));
        if (count($reportIds) > 0) {
            $group->reports()->detach($reportIds);
        } else {
            $group->reports()->detach();
        }
    
        return json_encode(ReportGroup::with('reports')->get());
    }

    public function removeReportGroup(Request $request) {
        $groupIds = $request->input('groups');
        ReportGroup::destroy($groupIds);
        return json_encode(ReportGroup::with('reports')->get());
    }

    public function setReportGroup(Request $request) {
        $group = new ReportGroup();
        $group->name = $request->input('name');
        $group->description =$request->input('description');
        $group->save();

        return json_encode(ReportGroup::with('reports')->get());
    }
}
