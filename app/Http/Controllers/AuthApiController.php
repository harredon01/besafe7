<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Contracts\Auth\Guard;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use App\Services\EditAlerts;
use App\Services\Security;

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
    public function __construct(Guard $auth, EditAlerts $editAlerts,Security $security) {
        //$this->registrar = $registrar;
        $this->auth = $auth;
        $this->editAlerts = $editAlerts;
        $this->security = $security;
        $this->middleware('auth:api');
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
        return response()->json($this->security->verifyTwoFactorToken($user,$data));
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
}
