<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Auth\Guard;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Jobs\RequestPing;
use App\Jobs\ReplyPing;
use App\Jobs\PostEmergency;
use App\Jobs\PostEmergencyEnd;
use App\Jobs\PostMarkAsDownloaded;
use App\Services\EditAlerts;
use App\Services\CleanSearch;
use App\Querybuilders\NotificationQueryBuilder;
use App\Models\Notification;

class AlertsApiController extends Controller {

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
     * The edit alerts implementation.
     *
     */
    protected $cleanSearch;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(Guard $auth, EditAlerts $editAlerts, CleanSearch $cleanSearch) {
        $this->editAlerts = $editAlerts;
        $this->cleanSearch = $cleanSearch;
        $this->auth = $auth;
        $this->middleware('auth:api');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function postMarkAsDownloaded(Request $request) {
        $user = $request->user();
        dispatch(new PostMarkAsDownloaded($user, $request->all()));
        return response()->json(['status' => 'success', 'message' => 'postMarkAsDownloaded queued']);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function getRequestPing($pingee, Request $request) {
        $user = $request->user();
        dispatch(new RequestPing($user, $pingee));
        return response()->json(['status' => 'success', 'message' => 'getRequestPing queued']);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function postReplyPing(Request $request) {
        $user = $request->user();
        dispatch(new ReplyPing($user, $request->all()));
        return response()->json(['status' => 'success', 'message' => 'postReplyPing queued']);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function getCountNotificationsUnread(Request $request) {
        $user = $request->user();
        return response()->json($this->editAlerts->countNotificationsUnread($user));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function readAllNotifications(Request $request) {
        $user = $request->user();
        return response()->json($this->editAlerts->readAllNotifications($user));
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
    public function openNotifications(Request $request) {
        $user = $request->user();
        return response()->json($this->editAlerts->readNotifications($user, $request->all(), "open"));
    }

    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function postEmergency(Request $request) {
        $user = $request->user();
        dispatch(new PostEmergency($user, $request->all("type"), false));
        return response()->json(['status' => 'success', 'message' => 'postEmergency queued']);
    }

    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function postStopEmergency(Request $request) {
        $user = $request->user();
        dispatch(new PostEmergencyEnd($user, $request->all("code")));
        return response()->json(['status' => 'success', 'message' => 'postStopEmergency queued']);
    }

    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getChats(Request $request) {
        $user = $request->user();
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
    public function deleteNotification($id, Request $request) {
        $user = $request->user();
        return $this->editAlerts->deleteNotification($user, $id);
    }

}
