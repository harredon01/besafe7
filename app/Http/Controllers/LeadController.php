<?php

namespace App\Http\Controllers;

use App\Models\FileM;
use App\Models\Lead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LeadController extends Controller {

    public function getLanding(Request $request, $type) {
        if ($type == "bla") {
            return view(config("app.views").".leads.leads");
        } else if ($type == "bla") {
            return view(config("app.views").".leads.leads");
        } else if ($type == "bla") {
            return view(config("app.views").".leads.leads");
        } else if ($type == "bla") {
            return view(config("app.views").".leads.leads");
        }
    }
    
    private function validateCaptcha($token,$ip){
        $url = "https://www.google.com/recaptcha/api/siteverify";
        $data = [
            "secret" => env("CAPTCHA_SECRET"),
            "response" => $token,
            "remoteip" => $ip
        ];
        $data_string = json_encode($data);
        // $curl = curl_init("https://ecommerce.test.mipaquete.com" . $query);
        $curl = curl_init($url);
        //dd($data);
        $headers = array(
            'Content-Type: application/json',
        );
        curl_setopt($curl, CURLOPT_POST, true);
        //curl_setopt($curl, CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        $response = curl_exec($curl);
        curl_close($curl);
        $response = json_decode($response, true);
        return $response;
    }

    public function postLanding(Request $request) {
        $ip = $request->ip();
        $data = $request->all();
        $results = $this->validateCaptcha($data['captcha'],$ip);
        dd($results);
        $keys = array_keys($data);
        $data['attributes'] = [];
        foreach ($keys as $item) {
            if (strpos("attribute_", $item) !== false) {
                $attrname = str_replace("attribute_", "", $item);
                $data['attributes'][$attrname] = $data[$item];
                unset($data[$item]);
            }
        }

        $files = [];
        for ($x = 1; $x <= 10; $x++) {
            $name = 'file-' . $x;
            if ($request->file($name)->isValid()) {
                unset($data[$name]);
                array_push($files, $name);
            }
        }
        $lead = new Lead();
        $lead->fill($data);
        $lead->save();
        foreach ($files as $value) {
            $file = $request->file($value);
            $path = Storage::putFile('public/leads', $file, 'public');
            $filename = Storage::url($path);
            $data = [
                "type" => "App\\Models\\Lead",
                "user_id" => 1,
                "trigger_id" => $lead->id,
                "file" => $filename,
                "extension" => $file->getClientOriginalExtension(),
            ];
            FileM::create($data);
        }
    }

}
