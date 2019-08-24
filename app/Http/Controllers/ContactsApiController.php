<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use App\Services\EditUserData;
use App\Services\Contacts;
use App\Services\CleanSearch;
use App\Querybuilders\ContactQueryBuilder;
use App\Models\User;
use App\Models\Address;
use App\Models\Medical;
use App\Jobs\ImportContactsId;
use App\Jobs\AddContact;
use DB;

class ContactsApiController extends Controller {

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
    protected $contacts;

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
    public function __construct(Guard $auth, EditUserData $editUserData, CleanSearch $cleanSearch, Contacts $contacts) {
        $this->cleanSearch = $cleanSearch;
        $this->editUserData = $editUserData;
        $this->contacts = $contacts;
        $this->auth = $auth;
        $this->middleware('auth:api');
    }

    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function blockContact($id, Request $request) {
        $user = $request->user();
        return response()->json($this->contacts->blockContact($user, $id));
    }

    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function unblockContact($id, Request $request) {
        $user = $request->user();
        return response()->json($this->contacts->unblockContact($user, $id));
    }

    /**
     * Get Registered addresses.
     *
     * @return \Illuminate\Http\Response
     */
    public function importContactsId(Request $request) {
        $user = $request->user();
        //$this->contacts->importContactsId($user, $request->all());
        dispatch(new ImportContactsId($user, $request->all()));
        return response()->json(['status' => 'success', 'message' => 'importContactsId queued']);
    }

    /**
     * Get Registered addresses.
     *
     * @return \Illuminate\Http\Response
     */
    public function updateContactsLevel(Request $request) {
        $user = $request->user();
        return response()->json($this->contacts->updateContactsLevel($user, $request->all()));
    }

    /**
     * Get Registered addresses.
     *
     * @return \Illuminate\Http\Response
     */
    public function checkContacts(Request $request) {
        $user = $request->user();
        $contacts = $this->contacts->checkContacts($user, $request->all());
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
            $data = array();
            $queryBuilder = new ContactQueryBuilder(new User, $request2);
            $result = $queryBuilder->build()->paginate();
            foreach ($result->items() as $user) {
                $user->last_significant = strtotime($user->last_significant);
                $user->updated_at2 = strtotime($user->updated_at);
                array_push($data, $user);
            }
            return response()->json([
                        'data' => $data,
                        "total" => $result->total(),
                        "per_page" => $result->perPage(),
                        "page" => $result->currentPage(),
                        "last_page" => $result->lastPage(),
            ]);
        }
        return response()->json([
                    'status' => "error",
                    'message' => "no user id parameter allowed"
                        ], 403);
    }

    /**
     * Get Registered addresses.
     *
     * @return \Illuminate\Http\Response
     */
    public function getContactByCode($code) {
        $request2 = Request::create("?code=" . $code . "*&limit=3", 'GET');
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
    /**
     * Get Registered addresses.
     *
     * @return \Illuminate\Http\Response
     */
    public function getContactByEmail($email) {
        $request2 = Request::create("?email=" . $email . "*&limit=3", 'GET');
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

    /**
     * Get Registered addresses.
     *
     * @return \Illuminate\Http\Response
     */
    public function deleteContact($contactId, Request $request) {
        $user = $request->user();
        return response()->json($this->contacts->deleteContact($user, $contactId));
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
        return response()->json(['status' => 'success', 'message' => 'addContact queued']);
    }



    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id) {
        return response()->json($this->contacts->getContact($id));
    }

}
