<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Contracts\Auth\Guard;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
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
    public function __construct(Guard $auth, EditUserData $editUserData, EditAlerts $editAlerts) {
        //$this->registrar = $registrar;
        $this->auth = $auth;
        $this->editUserData = $editUserData;
        $this->editAlerts = $editAlerts;
        $this->middleware('auth:api', ['except' => ['authenticate', 'create']]);
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
    public function create(Request $request) {
        $credentials = $request->all('area_code', 'cellphone', 'email');
        $validator = $this->editUserData->validatorRegister($request->all());

        if ($validator->fails()) {
            return response()->json(['statuss' => 'error', 'message' => $validator->getMessageBag()]);
        }
        $verifyemail = DB::select('select * from users where email = ?', [$credentials['email']]);
        if ($verifyemail) {
            return response()->json(['statuss' => 'error', 'message' => "Ese correo ya existe"], 200);
        }
        $verifycel = DB::select('select * from users where cellphone = ? and area_code = ? ', [$credentials['cellphone'], $credentials['area_code']]);
        if ($verifycel) {
            return response()->json(['statuss' => 'error', 'message' => "Ese celular en ese pais ya existe"], 200);
        }
        $data = $request->all([
            'firstName',
            'lastName',
            'area_code',
            'cellphone',
            'email',
            'password',
            'password_confirmation',
            'language',
            'city_id',
            'region_id',
            'country_id',
        ]);
        return response()->json($this->editUserData->create($data));
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
        $user = $request->user();
        return response()->json($this->editUserData->unlockMedical($user, $request->all()));
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
            return response()->json($this->editUserData->updateMedical($user, $data));
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
        $user = $request->user();
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
            return response()->json($this->editUserData->updateCodes($user, $request->all()));
        }
        return response()->json(['error' => 'invalid password'], 500);
    }

}
