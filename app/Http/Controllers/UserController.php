<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserGroup;
use App\Models\Settings;
use App\Utils\ExternalApi;
use DateTime;
use DateTimeZone;

class UserController extends Controller {    
    public function setUserGroup(Request $request) {
        $user = new UserGroup();
        $user->name = $request->input('name');
        $user->description =$request->input('description');
        $user->save();

        return json_encode(UserGroup::with('reportGroups', 'users')->get());
    }

    public function setReportsIntoGroup(Request $request) {
        $groupId = $request->input('group');
        $userIds = $request->input('entities');
        $users = [];

        foreach($userIds as $value) {
            $users[] = ['report_group_id' => $value, 'user_group_id' => $groupId];
        }

        $group = UserGroup::find(intval($groupId));
        $group->reportGroups()->attach($users);

        return json_encode(UserGroup::with('reportGroups', 'users')->get());
    }

    public function setReportIntoUser(Request $request) {
        $userId = $request->input('group');
        $reportIds = $request->input('entities');
        $reports = [];

        foreach($reportIds as $value) {
            $reports[] = ['user_id' => $userId, 'report_group_id' => $value];
        }

        $user = User::find(intval($userId));
        $user->reportGroups()->attach($reports);

        return json_encode(User::with('reportGroups', 'groups')->get());
    }

    public function setUserIntoUserGroup(Request $request) {
        $userId = $request->input('group');
        $reportIds = $request->input('entities');
        $reports = [];

        foreach($reportIds as $value) {
            $reports[] = ['user_group_id' => $userId, 'user_id' => $value];
        }

        $group = UserGroup::find(intval($userId));
        $group->users()->attach($reports);

        return json_encode(UserGroup::with('reportGroups', 'users')->get());
    }

    public function setUsers(Request $request) {
        $users = $request->input('users');
        User::upsert($users, ['id'], ['id', 'name']);
        return json_encode(User::with('reportGroups', 'groups')->get());
    }

    public function removeReportFromGroup(Request $request) {
        $groupId = $request->input('group');
        $reportIds = $request->input('entities');
        $group = UserGroup::find(intval($groupId));
        if (count($reportIds) > 0) {
            $group->reportGroups()->detach($reportIds);
        } else {
            $group->reportGroups()->detach();
        }
    
        return json_encode(UserGroup::with('reportGroups', 'users')->get());
    }

    public function removeReportFromUser(Request $request) {
        $groupId = $request->input('group');
        $reportIds = $request->input('entities');
        $group = User::find(intval($groupId));
        if (count($reportIds) > 0) {
            $group->reportGroups()->detach($reportIds);
        } else {
            $group->reportGroups()->detach();
        }
    
        return json_encode(User::with('reportGroups', 'groups')->get());
    }

    public function removeUserFromUserGroup(Request $request) {
        $groupId = $request->input('group');
        $reportIds = $request->input('entities');
        $group = UserGroup::find(intval($groupId));
        if ($groupId = 1) {
            $count = $group->users()->count();
            if ($count == count($reportIds) || count($reportIds) == 0) {
                return response()->json(['error' => 'Нет такого пользователя'], 400);
            }
        }
        if (count($reportIds) > 0) {
            $group->users()->detach($reportIds);
        } else {
            $group->users()->detach();
        } 
        return json_encode(UserGroup::with('reportGroups', 'users')->get());
    }

    public function removeUserGroup(Request $request) {
        
        $groupIds = $request->input('groups');
        $groupIds = array_filter($groupIds, function ($var) {
            return $var != 1;
        });
        UserGroup::destroy($groupIds);
        return json_encode(UserGroup::with('reportGroups', 'users')->get());
    }

    public function getUsers() {
        return json_encode(User::with('reportGroups', 'groups')->get());
    }

    public function getUserGroups() {
        return json_encode(UserGroup::with('reportGroups', 'users')->get());
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
        $lastRefresh = array_search('lastRefresh', array_column($settings, 'serviceName'));
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
                    'tokenTime' => $settings[$tokenTime]['value'],
                    
                ],
                'isAdmin' => $isAdmin,
                'lastRefresh' => $settings[$lastRefresh]['value'],
            ];
        } else {
            return response()->json(['error' => 'Нет такого пользователя'], 404);
        }
    }
}
