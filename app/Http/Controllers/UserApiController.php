<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use App\Services\EditUserData;
use App\Services\CleanSearch;
use App\Services\EditCart;
use App\Querybuilders\ContactQueryBuilder;
use App\Models\User;
use App\Models\Address;
use App\Models\Medical;
use App\Jobs\ImportContactsId;
use App\Jobs\MigrateCart;
use DB;

class UserApiController extends Controller {

    const OBJECT_LOCATION = 'Location';
    const ACCESS_USER_OBJECT = 'userables';
    const ACCESS_USER_OBJECT_ID = 'userable_id';
    const ACCESS_USER_OBJECT_TYPE = 'userable_type';

    /**
     * The Guard implementation.
     *
     * @var \Illuminate\Contracts\Auth\Guard
     */
    protected $auth;

    /**
     * The edit profile implementation.
     *
     */
    protected $editUserData;

    /**
     * The edit profile implementation.
     *
     */
    protected $cleanSearch;
    
    /**
     * The edit profile implementation.
     *
     */
    protected $editCart;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(Guard $auth, EditUserData $editUserData, CleanSearch $cleanSearch, EditCart $editCart) {
        $this->cleanSearch = $cleanSearch;
        $this->editUserData = $editUserData;
        $this->auth = $auth;
        $this->editCart = $editCart;
        $this->middleware('auth:api')->except('create');
    }

    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getTokens(Request $request) {
        $tokens = $request->user()->tokens->filter(function ($token) {
                    return !$token->revoked;
                })->values();
        $clients = array();
        foreach ($tokens as $token) {
            if (array_key_exists($token->client_id, $clients)) {
                $token->client_name = $clients[$token->client_id];
            } else {
                $followers = DB::select("SELECT id,name FROM oauth_clients WHERE id= $token->client_id  limit 1;  ");
                if (sizeof($followers) > 0) {
                    $clients[$followers[0]->id] = $followers[0]->name;
                    $token->client_name = $followers[0]->name;
                }
            }
        }
        return $tokens;
    }

    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function registerToken(Request $request) {
        $user = $request->user();
        return response()->json($this->editUserData->registerToken($user, $request->all()));
    }

    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function registerPhone(Request $request) {
        $user = $request->user();
        return response()->json($this->editUserData->registerPhone($user, $request->all()));
    }

    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function checkUserCredits(Request $request) {
        $user = $request->user();
        return response()->json($this->editUserData->checkUserCredits($user, $request->all()));
    }

    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function setAddressType($address, $type, Request $request) {
        $user = $request->user();
        $addresCont = Address::find($address);
        if ($addresCont->user_id == $user->id) {
            $addresCont->type = $type;
            $addresCont->save();
            $user->addresses()->where("id", "<>", $address)->where("type", $type)->update(["type" => null]);
            return response()->json(["status" => "success", "message" => "address type changed"]);
        }
        return response()->json(["status" => "error", "message" => "address does not belong to user"]);
    }

    public function deleteAddress($address_id, Request $request) {
        $user = $request->user();
        return response()->json($this->editUserData->deleteAddress($user, $address_id));
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request) {
        $data = [];
        $user = $request->user();
        if ($user) {
            $green = false;
            if ($user->green) {
                $green = true;
            }
            //$users2 = DB::select("SELECT user_id FROM " . self::ACCESS_USER_OBJECT . " where " . self::ACCESS_USER_OBJECT_ID . " = $user->id and " . self::ACCESS_USER_OBJECT_TYPE . " = '" . self::OBJECT_LOCATION . "' ");
            $count = Medical::where('user_id', $user->id)->count();
            $data['savedCard'] = false;
            $sources = $user->sources()->where("has_default", true)->get();
            $data['savedCards'] = [];
            foreach ($sources as $value) {
                $data['savedCard'] = true;
                array_push($data['savedCards'], $value->gateway);
            }
            $data['current_time'] = date("Y-m-d H:i:s");
            $data['user'] = $user;
            $data['merchants'] = $user->merchants()->count();
            $data['push'] = $user->push()->where("platform", "Food")->first();
            $data['count'] = $count;
            $data['green'] = $green;
            //$this->editCart->migrateCart($user, $request->header('x-device-id'));
            dispatch(new MigrateCart($user, $request->header('x-device-id')));
            //$data['followers'] = count($users2);
            // the token is valid and we have found the user via the sub claim
        }

        return response()->json($data);
    }

    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request) {
        $credentials = $request->all('area_code', 'cellphone', 'email', 'docNum', 'docType');
        $validator = $this->editUserData->validatorRegister($request->all());

        if ($validator->fails()) {
            return response()->json(array("status" => "error", "message" => $validator->getMessageBag()), 400);
        }
        $verifyemail = DB::select('select * from users where email = ?', [$credentials['email']]);
        if ($verifyemail) {
            return response()->json(['status' => 'error', 'message' => "email_exists"], 400);
        }
        $verifycel = DB::select('select * from users where cellphone = ? and area_code = ? ', [$credentials['cellphone'], $credentials['area_code']]);
        if ($verifycel) {
            return response()->json(['status' => 'error', 'message' => "cel_exists"], 400);
        }
        $verifyId = DB::select('select * from users where docNum = ? and docType = ? ', [$credentials['docNum'], $credentials['docType']]);
        if ($verifyId) {
            return response()->json(['status' => 'error', 'message' => "id_exists"], 400);
        }
        $data = $request->all([
            'firstName',
            'lastName',
            'docNum',
            'docType',
            'area_code',
            'cellphone',
            'email',
            'optinMarketing',
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
    public function cleanServer(Request $request) {
        $user = $request->user();
        return response()->json($this->editUserData->cleanServer($user));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request) {
        $user = $request->user();
        $data = $request->all([
            'id',
            'docNum',
            'optinMarketing',
            'docType',
            'gender',
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

        if ($data['id']) {
            return response()->json($this->editUserData->update($user, $data));
        } else {
            $validator = $this->editUserData->validatorRegister($data);

            if ($validator->fails()) {
                return response()->json(array("status" => "error", "message" => $validator->getMessageBag()), 400);
            }
            return response()->json($this->editUserData->create($user, $data));
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id) {
        $user = User::find($id);
        $user->plan = null;
        $user->stripe_id = null;
        $user->trial_ends_at = null;
        $user->gender = null;
        $user->docType = null;
        $user->docNum = null;
        return response()->json($user);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id) {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id) {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id) {
        //
    }

}
