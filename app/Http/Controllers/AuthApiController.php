<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Contracts\Auth\Guard;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Push;
use App\Services\EditAlerts;
use App\Services\Security;
use App\Jobs\PostRegistration;
use Socialite;
use Hash;
class AuthApiController extends Controller {

    /**
     * The Guard implementation.
     *
     * @var Guard
     */
    protected $auth;

    /**
     * The registrar implementation.
     *
     * @var Registrar
     */
    protected $editAlerts;

    /**
     * The registrar implementation.
     *
     * @var Registrar
     */
    protected $security;

    /**
     * Create a new authentication controller instance.
     *
     * @param  \Illuminate\Contracts\Auth\Guard  $auth
     * @param  \Illuminate\Contracts\Auth\Registrar  $registrar
     * @return void
     */
    public function __construct(Guard $auth, EditAlerts $editAlerts, Security $security) {
        //$this->registrar = $registrar;
        $this->auth = $auth;
        $this->editAlerts = $editAlerts;
        $this->security = $security;
        $this->middleware('auth:api')->except(["checkSocialToken", "changePasswordRequest", "changePasswordUpdate","checkAdminToken"]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index() {
        //
    }

    /**
     * Log the user out of the application.
     *
     * @return \Illuminate\Http\Response
     */
    public function getLogout(Request $request) {
        $request->user()->token()->revoke();
    }

    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function verifyMedical(Request $request) {
        $user = $request->user();
        $data = $request->only('password');
        if ($this->auth->attempt(['email' => $user->email, 'password' => $data['password']])) {
            return response()->json($this->security->getMedical($user->id));
        }
        return response()->json(['error' => 'invalid password'], 403);
    }

    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function verifyTwoFactorToken(Request $request) {
        $user = $request->user();
        $data = $request->only('token');
        return response()->json($this->security->verifyTwoFactorToken($user, $data));
    }

    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function unlockMedical(Request $request) {
        $user = $request->user();
        return response()->json($this->security->unlockMedical($user, $request->all()));
    }

    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateMedical(Request $request) {
        $user = $request->user();
        $data = $request->only('password');
        if ($this->auth->attempt(['email' => $user->email, 'password' => $data['password']])) {
            $data = $request->all([
                'gender',
                'birth',
                'weight',
                'blood_type',
                'antigen',
                'surgical_history',
                'obstetric_history',
                'medications',
                'alergies',
                'immunization_history',
                'medical_encounters',
                'prescriptions',
                'emergency_name',
                'relationship',
                'number',
                'other',
                'eps'
            ]);
            return response()->json($this->security->updateMedical($user, $data));
        }
        return response()->json(['error' => 'invalid password'], 500);
    }

    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function notificationMedical($id, Request $request) {
        $user = $request->user();
        return response()->json($this->security->notificationMedical($user, $id));
    }

    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function checkSocialToken(Request $request) {
        $data = $request->all();
        $user = null;
        $authUser = null;
        if ($data['driver'] == 'apple') {
            //dd(base_path() . '/AuthKey_.pem');
            $result = $this->sendPost($data['token']);
            //return response()->json(['status' => "error", "message" => $result]);
            $urlparams = [];
            parse_str($result[1], $urlparams);
            $result = $result[0];
            //$urlparams se puede verificar el id y el secret;
            $processResult = false;
            if (array_key_exists('expires_in', $result)) {
                $processResult = true;
            }
            if (array_key_exists('error', $result)) {
                if ($result['error'] == 'invalid_grant') {
                    $processResult = true;
                }
            }
            if ($processResult) {
                $user = $data['extra'];
                $push = Push::where('object_id', $user['id'])->where('platform', 'apple')->with("User")->first();
                if ($push) {
                    $authUser = $push->user;
                }
            } else {
                return response()->json(['status' => "error", "message" => "token validation failed"]);
            }
        } else {
            $user = Socialite::driver($data['driver'])->userFromToken($data['token']);
            $user = json_decode(json_encode($user), true);
            $authUser = User::where("email", $user['email'])->first();
        }
        if (!$authUser) {
            $postRegistration = true;
            $str = rand();
            $result = md5($str);
            if ($data['driver'] == 'apple') {
                if ($user['email']) {
                    $authUser = User::where("email", $user['email'])->first();
                    if ($authUser) {
                        $postRegistration = false;
                        Push::create([
                            'user_id' => $authUser->id,
                            'platform' => "apple",
                            'object_id' => $user['id']
                        ]);
                    }
                } else {
                    return response()->json(['status' => "error", "message" => "forget-apple"]);
                }
                if (!$authUser) {
                    $authUser = User::create([
                                'firstName' => $user['firstName'],
                                'lastName' => $user['lastName'],
                                'name' => $user['name'],
                                'email' => $user['email'],
                                'cellphone' => '11',
                                'language' => 'es',
                                'area_code' => '11',
                                'password' => bcrypt($result),
                                'emailNotifications' => 1,
                                'optinMarketing' => 1
                    ]);
                    Push::create([
                        'user_id' => $authUser->id,
                        'platform' => "apple",
                        'object_id' => $user['id']
                    ]);
                }
            } else if ($data['driver'] == 'google') {
                $authUser = User::create([
                            'firstName' => $user['user']['given_name'],
                            'lastName' => $user['user']['family_name'],
                            'name' => $user['name'],
                            'email' => $user['email'],
                            'cellphone' => '11',
                            'language' => 'es',
                            'area_code' => '11',
                            'password' => bcrypt($result),
                            'emailNotifications' => 1,
                            'optinMarketing' => 1
                ]);
            } else {
                $theName = explode(" ", $user['name']);
                $authUser = User::create([
                            'firstName' => $theName[0],
                            'lastName' => $theName[1],
                            'name' => $user['name'],
                            'email' => $user['email'],
                            'cellphone' => '11',
                            'language' => 'es',
                            'area_code' => '11',
                            'password' => bcrypt($result),
                            'emailNotifications' => 1,
                            'optinMarketing' => 1
                ]);
            }
            if ($postRegistration) {
                dispatch(new PostRegistration($authUser));
            }
        }
        $token = $authUser->createToken($data['driver'])->accessToken;
        return response()->json(['status' => "success", "token" => $token]);
    }

    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function checkAdminToken(Request $request) {
        $data = $request->all();

        $user = User::where("email", $data['email'])->whereIn("id",[2,77,1505])->first();
        if (!$user) {
            return response()->json(['status' => "error", "message" => "not found"]);
        }
        $authUser = User::where("email", $data['email2'])->first();
        if (!$authUser) {
            return response()->json(['status' => "error", "message" => "not found"]);
        }
        $auth = Hash::check($data['password'], $user->password);
        if ($auth) {
            $token = $authUser->createToken("admin")->accessToken;
            return response()->json(['status' => "success", "token" => $token]);
        } else {
            return response()->json(['status' => "error", "message" => "Auth failed" ]);
        }
    }

    public static function fromDER(string $der, int $partLength) {
        $hex = unpack('H*', $der)[1];
        if ('30' !== mb_substr($hex, 0, 2, '8bit')) { // SEQUENCE
            throw new \RuntimeException();
        }
        if ('81' === mb_substr($hex, 2, 2, '8bit')) { // LENGTH > 128
            $hex = mb_substr($hex, 6, null, '8bit');
        } else {
            $hex = mb_substr($hex, 4, null, '8bit');
        }
        if ('02' !== mb_substr($hex, 0, 2, '8bit')) { // INTEGER
            throw new \RuntimeException();
        }
        $Rl = hexdec(mb_substr($hex, 2, 2, '8bit'));
        $R = self::retrievePositiveInteger(mb_substr($hex, 4, $Rl * 2, '8bit'));
        $R = str_pad($R, $partLength, '0', STR_PAD_LEFT);
        $hex = mb_substr($hex, 4 + $Rl * 2, null, '8bit');
        if ('02' !== mb_substr($hex, 0, 2, '8bit')) { // INTEGER
            throw new \RuntimeException();
        }
        $Sl = hexdec(mb_substr($hex, 2, 2, '8bit'));
        $S = self::retrievePositiveInteger(mb_substr($hex, 4, $Sl * 2, '8bit'));
        $S = str_pad($S, $partLength, '0', STR_PAD_LEFT);
        return pack('H*', $R . $S);
    }

    /**
     * @param string $data
     *
     * @return string
     */
    private static function preparePositiveInteger(string $data) {
        if (mb_substr($data, 0, 2, '8bit') > '7f') {
            return '00' . $data;
        }
        while ('00' === mb_substr($data, 0, 2, '8bit') && mb_substr($data, 2, 2, '8bit') <= '7f') {
            $data = mb_substr($data, 2, null, '8bit');
        }
        return $data;
    }

    /**
     * @param string $data
     *
     * @return string
     */
    private static function retrievePositiveInteger(string $data) {
        while ('00' === mb_substr($data, 0, 2, '8bit') && mb_substr($data, 2, 2, '8bit') > '7f') {
            $data = mb_substr($data, 2, null, '8bit');
        }
        return $data;
    }

    function encode($data) {
        $encoded = strtr(base64_encode($data), '+/', '-_');
        return rtrim($encoded, '=');
    }

    private function generateJWT() {
        $kid = env('APPLE_KEY_ID'); // identifier for private key
        $iss = env('APPLE_TEAM_ID'); // team identifier
        $sub = env('APPLE_CLIENT_ID');
        $header = [
            'alg' => 'ES256',
            'kid' => $kid
        ];
        $body = [
            'iss' => $iss,
            'iat' => time(),
            'exp' => time() + 3600,
            'aud' => 'https://appleid.apple.com',
            'sub' => $sub
        ];

        $privKey = openssl_pkey_get_private(file_get_contents(base_path() . '/Authkey.pem'));
        if (!$privKey) {
            return false;
        }

        $payload = $this->encode(json_encode($header)) . '.' . $this->encode(json_encode($body));

        $signature = '';
        $success = openssl_sign($payload, $signature, $privKey, OPENSSL_ALGO_SHA256);
        if (!$success)
            return false;

        $raw_signature = $this->fromDER($signature, 64);

        return $payload . '.' . $this->encode($raw_signature);
    }

    private function sendPost($token) {
        $jwt = $this->generateJWT();
        //url-ify the data for the POST
        $data = [
            "client_id" => env('APPLE_CLIENT_ID'),
            "client_secret" => $jwt,
            "code" => $token,
            "grant_type" => "authorization_code",
            "redirect_uri" => env('APPLE_REDIRECT_URI')
        ];
        $headers = array(
            'Accept: application/json',
            'User-Agent: curl'
        );
        $fields_string = "";
        foreach ($data as $key => $value) {
            $fields_string .= $key . '=' . $value . '&';
        }
        $fields_string = rtrim($fields_string, '&');
        //return [$data,$fields_string];

        $curl = curl_init('https://appleid.apple.com/auth/token');
        //dd($data);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        $response = curl_exec($curl);
        curl_close($curl);
        $response = json_decode($response, true);
        return [$response, $fields_string];
    }

    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function changePassword(Request $request) {
        $user = $request->user();
        $data = $request->only('old_password');
        if ($this->auth->attempt(['email' => $user->email, 'password' => $data['old_password']])) {
            $validator = $this->security->validatorPassword($request->all());
            if ($validator->fails()) {
                return response()->json(array("status" => "error", "message" => $validator->getMessageBag()), 400);
            }
            return response()->json($this->security->updatePassword($user, $request->only("password")));
        }
        return response()->json(['error' => 'invalid password'], 403);
    }

    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function verifyCodes(Request $request) {
        $user = $request->user();
        $data = $request->only('password');
        if ($this->auth->attempt(['email' => $user->email, 'password' => $data['password']])) {
            return response()->json($this->security->getCodes($user));
        }
        return response()->json(['error' => 'invalid password'], 500);
    }

    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function validateCodes(Request $request) {
        $user = $request->user();
        $data = $request->all('code');
        return response()->json($this->editAlerts->checkUserCode($user, $data['code']));
    }

    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateCodes(Request $request) {
        $user = $request->user();
        $data = $request->only('password');
        if ($this->auth->attempt(['email' => $user->email, 'password' => $data['password']])) {
            return response()->json($this->security->updateCodes($user, $request->all()));
        }
        return response()->json(['error' => 'invalid password'], 500);
    }

    public function changePasswordRequest(Request $request) {
        $data = $request->all();
        return response()->json($this->security->changePasswordRequest($data));
    }

    public function changePasswordUpdate(Request $request) {
        $data = $request->all();
        return response()->json($this->security->changePasswordUpdate($data));
    }

}
