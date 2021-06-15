<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use App\Models\Settings;


class ApiController extends Controller
{
    public function getToken() {
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
        return json_encode($body);
    }
}
