<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Auth\Guard;
use DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Services\Registrar;
use App\Services\EditUserData;
use App\Services\EditAlerts;

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
    protected $registrar;

    /**
     * The registrar implementation.
     *
     * @var Registrar
     */
    protected $editUserData;
    
    /**
     * The registrar implementation.
     *
     * @var Registrar
     */
    protected $editAlerts;

    /**
     * Create a new authentication controller instance.
     *
     * @param  \Illuminate\Contracts\Auth\Guard  $auth
     * @param  \Illuminate\Contracts\Auth\Registrar  $registrar
     * @return void
     */
    public function __construct(Guard $auth, Registrar $registrar, EditUserData $editUserData, EditAlerts $editAlerts) {
        $this->auth = $auth;
        $this->registrar = $registrar;
        $this->editUserData = $editUserData;
        $this->editAlerts = $editAlerts;
        $this->middleware('jwt.auth', ['except' => ['authenticate', 'create']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index() {
        //
    }

    public function authenticate(Request $request) {
        // grab credentials from the request
        $credentials = $request->only('email', 'password');
        try {
            // attempt to verify the credentials and create a token for the user
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'invalid_credentials'], 401);
            }
        } catch (JWTException $e) {
            // something went wrong whilst attempting to encode the token
            return response()->json(['error' => 'could_not_create_token'], 500);
        }
        $date = date("Y-m-d H:i:s");
        // all good so return the token
        $data['user'] = $this->auth->user();
        $data['token'] = $token;
        $data['current_time'] = date("Y-m-d H:i:s");
        return response()->json(compact('data'));
    }

    /**
     * Log the user out of the application.
     *
     * @return \Illuminate\Http\Response
     */
    public function getLogout() {
        JWTAuth::invalidate(JWTAuth::getToken()); 
    }

    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request) {
        $credentials = $request->only('area_code', 'cellphone', 'email');
        $validator = $this->registrar->validator($request->all());

        if ($validator->fails()) {
            return response()->json(['statuss' => 'error', 'message' => $validator->getMessageBag()]);
        }
        $verifyemail = DB::select('select * from users where email = ?', [ $credentials['email']]);
        if ($verifyemail) {
            return response()->json(['statuss' => 'error', 'message' => "Ese correo ya existe"], 200);
        }
        $verifycel = DB::select('select * from users where cellphone = ? and area_code = ? ', [ $credentials['cellphone'], $credentials['area_code']]);
        if ($verifycel) {
            return response()->json(['statuss' => 'error', 'message' => "Ese celular en ese pais ya existe"], 200);
        }
        $user = $this->registrar->create($request->all());
        try {
            // attempt to verify the credentials and create a token for the user
            if (!$token = JWTAuth::fromUser($user)) {
                return response()->json(['error' => 'invalid_credentials'], 401);
            }
        } catch (JWTException $e) {
            // something went wrong whilst attempting to encode the token
            return response()->json(['error' => 'could_not_create_token'], 500);
        }

        return response()->json(['status' => 'success', 'token' => $token]);
    }

    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function verifyMedical(Request $request) {
        $user = $this->auth->user();
        $data = $request->only('password');
        if ($this->auth->attempt(['email' => $user->email, 'password' => $data['password']])) {
            return response()->json($this->editUserData->getMedical($user->id));
        }
        return response()->json(['error' => 'invalid password'], 500);
    }

    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function unlockMedical(Request $request) {
        $user = $this->auth->user();
        return response()->json($this->editUserData->unlockMedical($user, $request->all()));
    }

    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateMedical(Request $request) {
        $user = $this->auth->user();
        $data = $request->only('password');
        if ($this->auth->attempt(['email' => $user->email, 'password' => $data['password']])) {
            return response()->json($this->editUserData->updateMedical($user, $request->all()));
        }
        return response()->json(['error' => 'invalid password'], 500);
    }

    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function verifyCodes(Request $request) {
        $user = $this->auth->user();
        $data = $request->only('password');
        if ($this->auth->attempt(['email' => $user->email, 'password' => $data['password']])) {
            return response()->json($this->editUserData->getCodes($user));
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
        $user = $this->auth->user();
        $data = $request->only('code');
        return response()->json($this->editAlerts->checkUserCode($user, $data['code']));
    }

    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateCodes(Request $request) {
        $user = $this->auth->user();
        $data = $request->only('password');
        if ($this->auth->attempt(['email' => $user->email, 'password' => $data['password']])) {
            return response()->json($this->editUserData->updateCodes($user, $request->all()));
        }
        return response()->json(['error' => 'invalid password'], 500);
    }

    public function getAuthenticatedUser() {
        try {

            if (!$user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['user_not_found'], 404);
            }
        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {

            return response()->json(['token_expired'], $e->getStatusCode());
        } catch (Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {

            return response()->json(['token_invalid'], $e->getStatusCode());
        } catch (Tymon\JWTAuth\Exceptions\JWTException $e) {

            return response()->json(['token_absent'], $e->getStatusCode());
        }

        // the token is valid and we have found the user via the sub claim
        return response()->json(compact('user'));
    }

}
