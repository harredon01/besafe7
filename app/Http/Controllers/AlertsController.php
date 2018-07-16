<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Auth\Guard;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\EditAlerts;

class AlertsController extends Controller {

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
    protected $editAlerts;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(Guard $auth, EditAlerts $editAlerts) {
        $this->editAlerts = $editAlerts;
        $this->auth = $auth;
        $this->middleware('auth:api');
    }
    
    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getChat(Request $request) {
        $user = $this->auth->user();
        $validator = $this->editAlerts->validatorGetMessage($request->all());
        if ($validator->fails()) {
            return response()->json(array("status" => "error", "message" => $validator->getMessageBag()), 400);
        }
        return response()->json($this->editAlerts->getChat($user, $request->all()));
    }
    
   
    
    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function downloadNotifications(Request $request) {
        $user = $this->auth->user();
        return response()->json($this->editAlerts->readNotifications($user, $request->all(),"download"));
    }
    
    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function openNotifications(Request $request) {
        $user = $this->auth->user();
        return response()->json($this->editAlerts->readNotifications($user, $request->all(),"open"));
    }
    
    
    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getMessagesUser($id) {
        $user = $this->auth->user();
        $data['to_id'] =  $id;
        $data["type"]="user";
        return response()->json($this->editAlerts->getChat($user, $data));
    }
    
    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getMessagesGroup($id) {
        $user = $this->auth->user();
        $data['recipient_id'] =  $id;
        $data["type"]="group";
        return response()->json($this->editAlerts->getChat($user, $data));
    }
    
    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getChats(Request $request) {
        $user = $this->auth->user();
        return response()->json($this->editAlerts->getChats($user));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create(Request $request) {
        
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store() {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id) {
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
    public function deleteNotification($id) {
        $user = $this->auth->user();
        return $this->editAlerts->deleteNotification($user,$id);
    }

}
