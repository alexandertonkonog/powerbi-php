<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserGroup;
use App\Models\Settings;
use App\Utils\ExternalApi;
use DateTime;
use DateTimeZone;

class UserController extends Controller
{    
    public function setUserGroup(Request $request) {
        $user = new UserGroup();
        $user->name = $request->input('name');
        $user->description =$request->input('description');
        $user->save();

        return $user->toJson();
    }

    public function setUsersIntoGroup(Request $request) {
        $groupId = $request->input('group');
        $userIds = $request->input('users');
        $users = [];

        foreach($userIds as $value) {
            $users[] = ['user_id' => $value, 'user_group_id' => $groupId];
        }

        $group = UserGroup::find(intval($groupId));
        $group->users()->attach($users);

        return $group->json();
    }

    public function setReportIntoUser(Request $request) {
        $userId = $request->input('user');
        $reportIds = $request->input('reports');
        $reports = [];

        foreach($reportIds as $value) {
            $reports[] = ['user_id' => $userId, 'report_group_id' => $value];
        }

        $user = User::find(intval($groupId));
        $user->reportGroups()->attach($reports);

        return $user->jsonReports();
    }

    public function setReportIntoUserGroup(Request $request) {
        $userId = $request->input('user');
        $reportIds = $request->input('reports');
        $reports = [];

        foreach($reportIds as $value) {
            $reports[] = ['user_group_id' => $userId, 'report_group_id' => $value];
        }

        $group = UserGroup::find(intval($userId));
        $group->reportGroups()->attach($reports);

        return $user->jsonReports();
    }

    public function setUsers(Request $request) {
        $users = $request->input('users');
        $updateUsers = User::upsert($users, ['id'], ['id', 'name']);
        return json_encode($updateUsers);
    }

    public function getUsers() {
        return json_encode(User::all());
    }

    public function getUserGroups() {
        return json_encode(UserGroup::all());
    }

    public function getToken(Request $request) {
        if (ExternalApi::needRecieveToken()) {
            ExternalApi::getToken();
        }
        $data = $request->all();
        $id = intval($data['user_id']);
        $settings = Settings::all();
        $settings = $settings->toArray();
        $token = array_search('token', array_column($settings, 'serviceName'));
        $tokenTime = array_search('tokenTime', array_column($settings, 'serviceName'));
        $user = User::with('groups', 'reportGroups')->find($id);
        $isAdmin = false;
        if ($user) {
            foreach($user->groups as $group) {
                $groupId = $group->id;
                if ($groupId == 1) {
                    $isAdmin = true;
                }
            }
            return [
                'token' => [
                    'token' => $settings[$token]['value'],
                    'tokenTime' => $settings[$tokenTime]['value']
                ],
                'isAdmin' => $isAdmin
            ];
        } else {
            return response()->json(['error' => 'Нет такого пользователя'], 404);
        }
    }
}
