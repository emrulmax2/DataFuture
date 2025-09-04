<?php 
namespace App\Traits;

use App\Models\Option;
use Barryvdh\Debugbar\Facades\Debugbar;
use Illuminate\Support\Facades\Http;

trait SendSmsTrait{
    public function sendSms($phone, $message, $sender = 'London Churchill College'){

        // if in development or local environment then do not send sms
        if(in_array(env('APP_ENV'), ['development', 'local'])) {
                \Log::info('SMS: '.$message.' sent to '.$phone);
                Debugbar::info('SMS: '.$message.' sent to '.$phone);
                return true;
        }
        
        $active_api = Option::where('category', 'SMS')->where('name', 'active_api')->pluck('value')->first();
        $textlocal_api = Option::where('category', 'SMS')->where('name', 'textlocal_api')->pluck('value')->first();
        $smseagle_api = Option::where('category', 'SMS')->where('name', 'smseagle_api')->pluck('value')->first();
        if($active_api == 1 && !empty($textlocal_api)):
            $phone = (is_array($phone) ? implode(',', $phone) : $phone);
            $response = Http::timeout(-1)->post('https://api.textlocal.in/send/', [
                'apikey' => $textlocal_api, 
                'message' => $message, 
                'sender' => $sender, 
                'numbers' => $phone
            ]);
        elseif($active_api == 2 && !empty($smseagle_api)):
            $phone = (is_array($phone) ? $phone : [$phone]);
            $response = Http::withHeaders([
                    'access-token' => $smseagle_api,
                    'Content-Type' => 'application/json',
                ])->withoutVerifying()->withOptions([
                    "verify" => false
                ])->post('https://79.171.153.104/api/v2/messages/sms', [
                    'to' => $phone,
                    'text' => $message
                ]);
        endif;

        return true;
    }
}