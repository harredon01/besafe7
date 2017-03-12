<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use App\Services\EditUserData;
use App\Services\CleanSearch;
use App\Querybuilders\ContactQueryBuilder;
use App\Models\User;
use App\Models\Medical;
use App\Jobs\ImportContactsId;
use App\Jobs\AddContact;
use DB;

class UserApiController extends Controller {

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
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(Guard $auth, EditUserData $editUserData, CleanSearch $cleanSearch) {
        $this->cleanSearch = $cleanSearch;
        $this->editUserData = $editUserData;
        $this->auth = $auth;
        $this->middleware('auth:api');
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
            $validator = $this->editUserData->validatorPassword($request->all());
            if ($validator->fails()) {
                $this->throwValidationException(
                        $request, $validator
                );
            }
            return response()->json($this->editUserData->updatePassword($user, $request->only("password")));
        }
        return response()->json(['error' => 'invalid password'], 401);
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
    public function notificationMedical($id, Request $request) {
        $user = $request->user();

        return response()->json($this->editUserData->notificationMedical($user, $id));
    }

    /**
     * Get Registered addresses.
     *
     * @return \Illuminate\Http\Response
     */
    public function importContactsId(Request $request) {
        $user = $request->user();
        dispatch(new ImportContactsId($user, $request->all()));
        return response()->json(['status' => 'success','message' => 'importContactsId queued']);
    }

    /**
     * Get Registered addresses.
     *
     * @return \Illuminate\Http\Response
     */
    public function updateContactsLevel(Request $request) {
        $user = $request->user();
        return response()->json($this->editUserData->updateContactsLevel($user, $request->all()));
    }

    /**
     * Get Registered addresses.
     *
     * @return \Illuminate\Http\Response
     */
    public function checkContacts(Request $request) {
        $user = $request->user();
        $contacts = $this->editUserData->checkContacts($user, $request->all());
        return response()->json(compact('contacts'));
    }

    /**
     * Get Registered addresses.
     *
     * @return \Illuminate\Http\Response
     */
    public function getContacts(Request $request) {
        $request2 = $this->cleanSearch->handleContact($request);
        if ($request2) {
            $queryBuilder = new ContactQueryBuilder(new User, $request2);
            $result = $queryBuilder->build()->paginate();
            return response()->json([
                        'data' => $result->items(),
                        "total" => $result->total(),
                        "per_page" => $result->perPage(),
                        "page" => $result->currentPage(),
                        "last_page" => $result->lastPage(),
            ]);
        }
        return response()->json([
                    'status' => "error",
                    'message' => "no user id parameter allowed"
                        ], 401);
    }

    /**
     * Get Registered addresses.
     *
     * @return \Illuminate\Http\Response
     */
    public function deleteContact($contactId, Request $request) {
        $user = $request->user();
        return response()->json($this->editUserData->deleteContact($user, $contactId));
    }

    /**
     * creates or updates user address.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function importContacts(Request $request) {
        $user = $request->user();
        $validator = $this->editUserData->validatorAddress($request->all());

        if ($validator->fails()) {
            $this->throwValidationException(
                    $request, $validator
            );
        }
        return response()->json($this->editUserData->importContacts($user, $request->all()));
    }

    /**
     * creates or updates user address.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function addContact($contact_id, Request $request) {
        $user = $request->user();
        dispatch(new AddContact($user, $contact_id));
        return response()->json(['status' => 'success','message' => 'addContact queued']);
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
        $green = false;
        if ($user->green) {
            $green = true;
        }
        $count = Medical::where('user_id', $user->id)->count();
        $data['current_time'] = date("Y-m-d H:i:s");
        $data['user'] = $user;
        $data['count'] = $count;
        $data['green'] = $green;
        // the token is valid and we have found the user via the sub claim
        return response()->json($data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create(Request $request) {
        //return $request;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request) {
        $user = $request->user();
        $data = $request->all();
        if (array_key_exists("id", $data)) {
            return response()->json($this->editUserData->update($data));
        } else {
            $validator = $this->editUserData->validatorRegister($data);

            if ($validator->fails()) {
                $this->throwValidationException(
                        $request, $validator
                );
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
        return response()->json($this->editUserData->getContact($id));
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
