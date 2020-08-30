<?php

namespace App\Services;

use App\Models\User;
use App\Models\Medical;
use App\Models\Certificate;
use App\Models\Signature;
use App\Models\Document;
use Illuminate\Support\Facades\Mail;
use App\Mail\TwoFactorAuthMail;
use Carbon\Carbon;
use DB;
use Validator;

class Security {

    const OBJECT_USER = 'User';
    const RED_MESSAGE_TYPE = 'emergency';
    const RED_MESSAGE_END = 'emergency_end';
    const RED_MESSAGE_MEDICAL_TYPE = 'medical_emergency';

    /**
     * Get a validator for an incoming edit profile request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function validatorRegister(array $data) {
        return Validator::make($data, [
                    'firstName' => 'required|max:255',
                    'lastName' => 'required|max:255',
                    'docNum' => 'required|max:255',
                    'docType' => 'required|max:255',
                    'cellphone' => 'required|max:255',
                    'area_code' => 'required|max:255',
                    'email' => 'required|email|max:255|unique:users',
                    'password' => 'required|min:6|confirmed',
        ]);
    }

    /**
     * Get a validator for an incoming edit profile request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function validatorCredits(array $data) {
        return Validator::make($data, [
                    'email' => 'required|max:255',
                    'credits' => 'required|max:255'
        ]);
    }

    /**
     * returns all current shared locations for the user
     *
     * @return Location
     */
    public function getUserCode(User $user) {
        $hashExists = true;
        while ($hashExists) {
            $hash = str_random(20);
            $users = DB::select("SELECT * from users where code = ? ", [$hash]);
            if ($users) {
                $hashExists = true;
            } else {
                $hashExists = false;
                $user->code = $hash;
                $user->save();
            }
        }
        return $hash;
    }

    /**
     * returns all current shared locations for the user
     *
     * @return Location
     */
    public function verifyTwoFactorToken(User $user, array $data) {
        if (array_key_exists("token", $data)) {
            if ($data["token"] == $user->two_factor_token) {
                $user->two_factor_expiry = \Carbon\Carbon::now()->addMinutes(config('session.lifetime'));
                $user->save();
                return ["status" => "success", "Two factor verified"];
            }
        }
        return ["status" => "error", "Two factor verification failed"];
    }

    public function changePasswordRequest(array $data) {
        $user = User::where("email", $data["email"])->first();
        $user->two_factor_expiry = Carbon::now()->addMinutes(config('session.lifetime'));
        $user->two_factor_token = str_random(8);
        $user->save();
        Mail::to($user)->send(new TwoFactorAuthMail($user, $user->two_factor_token));
        return ['status' => 'success', 'message' => 'Two factor authentication initiated'];
    }

    public function changePasswordUpdate(array $data) {
        $validator = $this->validatorPasswordUpdate($data);
        if ($validator->fails()) {
            return array("status" => "error", "message" => $validator->getMessageBag());
        }
        $user = User::where("email", $data["email"])->first();
        if ($user->two_factor_expiry > \Carbon\Carbon::now() && $data['token'] == $user->two_factor_token) {


            $this->updatePassword($user, $data);
            $http = new \GuzzleHttp\Client;
            $response = $http->post(env('APP_URL') . '/oauth/token', [
                'form_params' => [
                    'grant_type' => 'password',
                    'client_id' => '1',
                    'client_secret' => 'nuoLagU2jqmzWqN6zHMEo82vNhiFpbsBsqcs2DPt',
                    'username' => $data['email'],
                    'password' => $data['password'],
                    'scope' => '*',
                ],
            ]);
            $json = json_decode((string) $response->getBody(), true);
            return ['status' => 'success', 'access_token' => $json['access_token']];
        }
        return ['status' => 'error', 'message' => 'verification failed'];
    }

    public function generateCertificate(User $user) {
        $config = array(
            "digest_alg" => "sha512",
            "private_key_bits" => 1024,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        );
        $today = date_add(date_create(), date_interval_create_from_date_string(date('Z') . " seconds"));


// Create the private and public key
        $res = openssl_pkey_new($config);

// Extract the private key from $res to $privKey
        $privKey = null;
        openssl_pkey_export($res, $privKey);
        echo "Private key: " . $privKey . PHP_EOL;
        $pubKey = openssl_pkey_get_details($res);
        $pubKey = $pubKey["key"];
        echo "Public key: " . $pubKey . PHP_EOL;
        $oldCert = $user->certificates()->where('is_active', true)->first();
        if ($oldCert) {
            $oldCert->is_active = false;
            $oldCert->expiration = $today;
            $oldCert->save();
        }

        $certificate = Certificate::create([
                    'user_id' => $user->id,
                    'public_key' => $pubKey,
                    'is_active' => true]);
        return array("status" => "success", "message" => "Cert created", "private_key" => $privKey, "certificate" => $certificate);
    }

    public function sign(User $user, Document $data, $privKey) {
        $signature = "";
        $save = false;
        if(!$data->is_signed){
            $data->is_signed = true;
            $save = true;
            $data->signature_date = date_add(date_create(), date_interval_create_from_date_string(date('Z') . " seconds"));
        }
        
        openssl_sign($data, $signature, $privKey, OPENSSL_ALGO_SHA256);
        $cert = $user->certificates()->where('is_active', true)->first();
        if ($cert) {
            $r = openssl_verify($data, $signature, $cert->public_key, "sha256WithRSAEncryption");
            if ($r) {
                $signatureObj = new Signature([
                    'user_id' => $user->id,
                    'signature' => $signature]);
                $data->signatures()->save($signatureObj);
                if($save){
                    $data->save();
                }
                return array("status" => "success", "message" => "Signature created", "signature" => $signatureObj);
            }
            return array("status" => "error", "message" => "Private key does not match current certificate");
        }
        return array("status" => "error", "message" => "No certificate");
    }

    public function validate(Document $doc) {
        $signatures = $doc->signatures;
        $valid_signatures = [];
        $invalid_signatures = [];
        foreach ($signatures as $sign) {
            $signer = $sign->user;
            $certificate = $signer->certificates()->where('is_active',true)->first();
            $valid = false;
            if($certificate){
                $r = openssl_verify($doc, $sign->signature, $certificate->public_key, "sha256WithRSAEncryption");
                if($r){
                    $valid = true;
                }
            }
            if($valid){
                array_push($valid_signatures,$sign);
            } else {
                array_push($invalid_signatures,$sign);
            }
            
        }
        return ['status'=>"success","message"=>"signature validations completed","valid_signatures"=>$valid_signatures,"invalid_signatures"=>$invalid_signatures];
        
    }

    /**
     * Get a validator for an incoming edit profile request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function validatorPassword(array $data) {
        return Validator::make($data, [
                    'password' => 'required|confirmed|min:6',
        ]);
    }

    /**
     * Get a validator for an incoming edit profile request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function validatorPasswordUpdate(array $data) {
        return Validator::make($data, [
                    'token' => 'required|max:255',
                    'email' => 'required|max:255',
                    'password' => 'required|confirmed|min:6',
        ]);
    }

    /**
     * Get a validator for an incoming edit profile request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function validatorAddress(array $data) {
        return Validator::make($data, [
                    'name' => 'required|max:255',
                    'address' => 'required|max:255',
                    'city_id' => 'required|max:255',
                    'region_id' => 'required|max:255',
                    'country_id' => 'required|max:255',
        ]);
    }

    /**
     * Update profile data.
     *
     * @param  User, array  $data
     * 
     */
    public function updateMedical(User $user, array $data) {
        $data["user_id"] = $user->id;
        foreach ($data as $key => $value) {
            if (!$value) {
                unset($data[$key]);
            }
        }
        Medical::updateOrCreate(["user_id" => $user->id], $data);
        return array("status" => "success", "message" => "User Profile Updated");
    }

    /**
     * Update profile data.
     *
     * @param  User, array  $data
     * 
     */
    public function unlockMedical(User $user, array $data) {
        $user = User::find($data['user_id']);
        if ($user) {
            if ($user->green == $data['code']) {
                return $this->getMedical($user->id);
            }
            return array("status" => "error", "message" => "Incorrect Code");
        }
        return array("status" => "error", "message" => "User not found");
    }

    /**
     * Update profile data.
     *
     * @param  User, array  $data
     * 
     */
    public function notificationMedical(User $user, $contact_id) {
        $emergencyUser = User::find($contact_id);
        if ($emergencyUser) {
            $followers = DB::select("SELECT * FROM contacts WHERE contact_id= $user->id and level='" . self::RED_MESSAGE_TYPE . "' and user_id = ? limit 1;  ", [$emergencyUser->id]);
            if (sizeof($followers) == 1) {
                if ($emergencyUser->is_alerting == true && $emergencyUser->alert_type == "medical_emergency") {
                    return $this->getMedical($emergencyUser->id);
                }
                return array("status" => "error", "message" => "User not in medical emergency");
            }
        }

        return array("status" => "error", "message" => "User not found");
    }

    /**
     * Update profile data.
     *
     * @param  User, array  $data
     * 
     */
    public function getMedical($user_id) {
        $medical = Medical::where("user_id", $user_id)->first();
        if ($medical) {
            $data["age"] = date_diff(date_create($medical->birth), date_create('now'))->y;
            $data["gender"] = $medical->gender;
            $data["weight"] = $medical->weight;
            $data["blood_type"] = $medical->blood_type;
            $data["antigen"] = $medical->antigen;
            $data["birth"] = $medical->birth;
            $data["surgical_history"] = $medical->surgical_history;
            $data["obstetric_history"] = $medical->obstetric_history;
            $data["medications"] = $medical->medications;
            $data["alergies"] = $medical->alergies;
            $data["immunization_history"] = $medical->immunization_history;
            $data["medical_encounters"] = $medical->medical_encounters;
            $data["prescriptions"] = $medical->prescriptions;
            $data["emergency_name"] = $medical->emergency_name;
            $data["relationship"] = $medical->relationship;
            $data["number"] = $medical->number;
            $data["other"] = $medical->other;
            $data["eps"] = $medical->eps;
            return $data;
        }

//        $medical = DB::select("SELECT DATEDIFF(curdate(),birth) / 365.25 as age, m.*, u.firstName, u.lastName "
//                        . " from  medicals m join users u on u.id= m.user_id where u.id = ?", [$user_id]);
//        if (sizeof($medical) > 0) {
//            return $medical[0];
//        }
        return array("status" => "error", "message" => "Not found");
    }

    /**
     * Update profile data.
     *
     * @param  User, array  $data
     * 
     */
    public function updateCodes(User $user, array $data) {
        if (array_key_exists("green", $data)) {
            $user->green = $data['green'];
        }
        if (array_key_exists("red", $data)) {
            $user->red = $data['red'];
        }
        $user->save();
        return array("status" => "success", "message" => "User Codes Updated");
    }

    /**
     * Update profile data.
     *
     * @param  User, array  $data
     * 
     */
    public function getCodes(User $user) {
        $result = ["green" => $user->green, "red" => $user->red];
        return $result;
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  User, array  $data
     * 
     */
    public function updatePassword(User $user, $password) {
        $user->password = bcrypt($password['password']);
        $user->save();
        return array("status" => "success", "message" => "User Profile Updated");
    }

}
