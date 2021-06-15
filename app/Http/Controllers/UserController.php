<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserGroup;

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
}
