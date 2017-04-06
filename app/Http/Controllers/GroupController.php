<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\EditGroup;
use App\Services\CleanSearch;
use App\Querybuilders\GroupQueryBuilder;
use App\Services\EditAlerts;
use App\Models\Group;

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
                if(!$group->is_public){
                    $group->users;
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
    public function leaveGroup($group,Request $request) {
        $user = $request->user();
        $group = $this->editGroup->leaveGroup($user, $group);
        return response()->json(compact('group'));
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
