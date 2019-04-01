<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Auth\Guard;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\EditMessages;
use App\Services\CleanSearch;
use App\Querybuilders\NotificationQueryBuilder;
use App\Querybuilders\MessageQueryBuilder;
use App\Models\Notification;
use App\Models\Message;

class MessagesApiController extends Controller {

    /**
     * The Guard implementation.
     *
     * @var \Illuminate\Contracts\Auth\Guard
     */
    protected $auth;

    /**
     * The edit alerts implementation.
     *
     */
    protected $editMessages;

    /**
     * The edit alerts implementation.
     *
     */
    protected $cleanSearch;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(Guard $auth, EditMessages $editMessages, CleanSearch $cleanSearch) {
        $this->editMessages = $editMessages;
        $this->cleanSearch = $cleanSearch;
        $this->auth = $auth;
        $this->middleware('auth:api');
    }

    public function getNotifications(Request $request) {
        $user = $request->user();
        $request2 = $this->cleanSearch->handle($user, $request);
        if ($request2) {
            $queryBuilder = new NotificationQueryBuilder(new Notification, $request2);
            $result = $queryBuilder->build()->paginate();
            return response()->json([
                        'data' => $result->items(),
                        "total" => $result->total(),
                        "per_page" => $result->perPage(),
                        "page" => $result->currentPage(),
                        "last_page" => $result->lastPage()
            ]);
        }
        return response()->json([
                    'status' => "error",
                    'message' => "no user id parameter allowed"
                        ], 403);
    }

    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getChat(Request $request) {
        $user = $request->user();
        $data = $request->all();
        $validator = $this->editMessages->validatorGetMessage($request->all());
        if ($validator->fails()) {
            return response()->json(array("status" => "error", "message" => $validator->getMessageBag()), 400);
        }
        $url = "";
        if($data['type']=='user'){
            $url = "api/messages/chat?user_chat=" . $user->id.','.$data['to_id']."order_by=id,desc";
        }
        if(array_key_exists("page", $data)){
            if(intval($data['page']>0)){
                $url = $url. "&page=".$data['page'];
            }
        }
        $request2 = Request::create($url, 'GET');
        $queryBuilder = new MessageQueryBuilder(new Message, $request2);
            $result = $queryBuilder->build()->paginate();
            return response()->json([
                        'data' => $result->items(),
                        "total" => $result->total(),
                        "per_page" => $result->perPage(),
                        "page" => $result->currentPage(),
                        "last_page" => $result->lastPage()
            ]);
    }
    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getReceivedChats(Request $request) {
        $user = $request->user();
        return response()->json($this->editMessages->getReceivedChats($user));
    }

    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function postMessage(Request $request) {
        $user = $request->user();
        $validator = $this->editMessages->validatorMessage($request->all());
        if ($validator->fails()) {
            return response()->json(array("status" => "error", "message" => $validator->getMessageBag()), 400);
        }
        return response()->json([
                    'status' => "success",
                    'message' => "message queued for sending",
                    'result' => $this->editMessages->postMessage($user, $request->all())
        ]);
    }

    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getMessagesUser($id, Request $request) {
        $user = $request->user();
        $data['to_id'] = $id;
        $data["type"] = "user";
        return response()->json($this->editMessages->getChat($user, $data));
    }

    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getMessagesGroup($id, Request $request) {
        $user = $request->user();
        $data['recipient_id'] = $id;
        $data["type"] = "group";
        return response()->json($this->editMessages->getChat($user, $data));
    }

}
