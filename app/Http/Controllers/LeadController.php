<?php

namespace App\Http\Controllers;

use App\Models\FileM;
use App\Models\Lead;
use Illuminate\Support\Facades\Mail;
use App\Mail\GeneralNotification;
use App\Mail\LeadMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LeadController extends Controller {

    public function getLanding(Request $request, $type) {
        $data = [];
        $data['key'] = env('GOOGLE_CAPTCHA_PUBLIC');
        if ($type == "bla") {
            return view(config("app.views") . ".leads.leads")->with('data', $data);
        } else if ($type == "sale") {
            return view(config("app.views") . ".leads.sale-lead")->with('data', $data);
        } else if ($type == "vets") {
            return view(config("app.views") . ".leads.vet-lead")->with('data', $data);
        } else if ($type == "shops") {
            return view(config("app.views") . ".leads.shop-lead")->with('data', $data);
        } else if ($type == "lost") {
            return view(config("app.views") . ".leads.lost-lead")->with('data', $data);
        } else if ($type == "delete_account") {
            return view(config("app.views") . ".leads.delete_account")->with('data', $data);
        }
    }

    private function validateCaptcha($token, $ip) {
        $url = "https://www.google.com/recaptcha/api/siteverify";
        $data = [
            "secret" => env("GOOGLE_CAPTCHA_PRIVATE"),
            "response" => $token,
            "remoteip" => $ip
        ];
        //dd($data);
        $data_string = http_build_query($data);
        // $curl = curl_init("https://ecommerce.test.mipaquete.com" . $query);
        $curl = curl_init($url);
        //dd($data);
        $headers = array(
            'Content-Type: application/x-www-form-urlencoded',
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
        $results = $this->validateCaptcha($data['captcha'], $ip);
        if ($results['success']) {
            if ($results['score'] > 0.5) {
                unset($data['captcha']);
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
                    if ($request->file($name) && $request->file($name)->isValid()) {
                        unset($data[$name]);
                        array_push($files, $name);
                    }
                }
                if (isset($data['type'])) {
                    if ($data['type'] == "Trabaja" || $data['type'] == "veterinarios" ||
                            $data['type'] == "pet-shops" || $data['type'] == "pet-sale" || $data['type'] == "perdidos") {
                        $lead = new Lead();
                        $lead->fill($data);
                        $lead->save();
                    }
                }

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
                if (isset($data['email'])) {
                    Mail::to($data['email'])->send(new GeneralNotification("Hemos recibido tu mensaje", "Gracias por contactarnos! Alguien de nuestro equipo se pondra en contacto contigo"));
                }
                $mail = Mail::to("harredon01@gmail.com")->send(new LeadMail("Mensaje recibido", $data));
                return ["status" => "success", "message" => "message received"];
            }
        }
        return ["status" => "error", "message" => "bot identified"];
    }

}
