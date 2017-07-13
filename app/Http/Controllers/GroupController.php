<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\EditGroup;
use App\Services\CleanSearch;
use App\Querybuilders\GroupQueryBuilder;
use App\Services\EditAlerts;
use App\Models\Group;
use App\Jobs\InviteUsers;
use App\Jobs\LeaveGroup;
use App\Jobs\AdminGroup;

class GroupController extends Controller {

    /**
     * The edit profile implementation.
     *
     */
    protected $editGroup;

    /**
     * The edit profile implementation.
     *
     */
    protected $editAlerts;
    
    /**
     * The edit profile implementation.
     *
     */
    protected $cleanSearch;

    public function __construct(EditGroup $editGroup, EditAlerts $editAlerts, CleanSearch $cleanSearch) {
        $this->editGroup = $editGroup;
        $this->editAlerts = $editAlerts;
        $this->cleanSearch = $cleanSearch;
        $this->middleware('auth:api');
        $this->middleware('location.group', ['only' => 'show']);
    }

    public function index(Request $request) {
        $user = $request->user();
        $request2 = $this->cleanSearch->handle($user,$request);
        if ($request2) {
            $data = array();
            $queryBuilder = new GroupQueryBuilder(new Group, $request2);
            $result = $queryBuilder->build()->paginate();
            foreach ($result->items() as $group){
                $group->admin_id = 0;
                if(!$group->is_public){
                    $group->users;
                } else {
                    $results = $this->editGroup->checkAdminGroup($user->id, $group->id);
                    if(count($results)>0){
                        $group->admin_id = 1;
                    }
                    $results = $this->editGroup->checkUserGroup($user->id, $group->id);
                    if(count($results)>0){
                        $group->is_authorized = true;
                    } else {
                        $group->is_authorized = false;
                    }
                }
                array_push($data,$group);
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
                        ], 401);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create() {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function leaveGroup($dagroup,Request $request) {
        $user = $request->user();
        $group = array();
        dispatch(new LeaveGroup($user, $dagroup));
        //$group = $this->editGroup->leaveGroup($user, $dagroup);
        return response()->json(compact('group'));
    }
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function changeStatusGroup(Request $request) { 
        $user = $request->user();
        $results = $this->editGroup->requestChangeStatusGroup($user, $request->all());
        return response()->json($results);
    }
    
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function getAdminGroups(Request $request) {
        $user = $request->user();
        $groups = $this->editGroup->getActiveAdminGroups($user);
        return response()->json(compact('groups'));
    }
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function getAdminGroupUsers(Request $request) {
        $user = $request->user();
        $groups = $this->editGroup->getAdminGroupUsers($user,$request->all());
        return response()->json(compact('groups'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id) {
        $group = $this->editGroup->getGroup($id);
        return response()->json(compact('group'));
    }

    public function inviteUsers(Request $request) {
        $user = $request->user();
        dispatch(new InviteUsers($user, $request->all(),false));
        return response()->json(['status' => 'success','message' => 'inviteUsers queued']);
    }
    public function getGroupByCode($code) {
        $response = $this->editGroup->getGroupByCode($code);
        return response()->json($response);
    }
    
    public function joinGroupByCode(Request $request, $code) {
        $user = $request->user();
        $response = $this->editGroup->joinGroupByCode($user,$code);
        return response()->json($response);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request) {
        $user = $request->user();
        return response()->json($this->editGroup->saveOrCreateGroup($request->all(), $user));
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
