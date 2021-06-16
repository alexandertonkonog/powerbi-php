<? 
namespace App\Utils;

date_default_timezone_set('Europe/Moscow');

use App\Models\Settings;
use GuzzleHttp\Client;
use DateTime;
use DateTimeZone;

class ExternalApi {

    static public function getToken() {
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
    
    static public function needRecieveToken() {
        
        $settings = Settings::where('serviceName', 'tokenTime')->first();

        $was = date_create($settings->value);
        $now = new DateTime();

        $interval = date_diff($was, $now);

        $year = $interval->format('%y');
        $month = $interval->format('%m');
        $day = $interval->format('%d');
        $hour = $interval->format('%h');
        $minute = $interval->format('%r%i');

        return $year > 0 || $month > 0 || $day > 0 || $hour > 0 || $minute >= 9; 
    }
}