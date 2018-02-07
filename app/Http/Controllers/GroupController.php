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
use DB;

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
        $request2 = $this->cleanSearch->handleGroup($user, $request);
        if ($request2) {
            $data = array();
            //DB::enableQueryLog();  
            $queryBuilder = new GroupQueryBuilder(new Group, $request2);
            $result = $queryBuilder->build()->paginate();
            //dd(DB::getQueryLog());
            foreach ($result->items() as $group) {
//                $group->admin_id = 0;
                if (!$group->is_public) {
                    $group->users;
                }
                array_push($data, $group);
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
    public function leaveGroup($dagroup, Request $request) {
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
    public function updateExpiredGroups(Request $request) {
        $results = $this->editGroup->updateExpiredGroups();
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
        $groups = $this->editGroup->getAdminGroupUsers($user, $request->all());
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
        $data = $request->all();
        $group = Group::find(intval($data["group_id"]));

        if ($group) {
            if ($group->is_public && $group->isActive()) {
                
            } else if (!$group->is_public) {
                
            } else {
                return response()->json(['status' => 'error', 'message' => 'inviteUsers failed group inactive']);
            }
            $member = $group->checkMemberType($user);
            if ($member) {
                if ($member->is_admin == false) {
                    return response()->json(['status' => 'error', 'message' => 'User not admin']);
                }
            } else {
                return response()->json(['status' => 'error', 'message' => 'User not admin']);
            }
            $i = $group->countAllMembers();
            if (array_key_exists("contacts", $data)) {
                $i = $i + count($data['contacts']);
            }
            if ($group->max_users < $i) {
                return response()->json(['status' => 'error', 'message' => "too many invites"]);
            }
            dispatch(new InviteUsers($user, $data, false, $group));
            return response()->json(['status' => 'success', 'message' => 'inviteUsers queued', 'is_public' => $group->is_public]);
        }
        return response()->json(['status' => 'error', 'message' => 'inviteUsers failed']);
    }

    public function getGroupByCode($code) {
        $response = $this->editGroup->getGroupByCode($code);
        return response()->json($response);
    }

    public function joinGroupByCode(Request $request, $code) {
        $user = $request->user();
        $response = $this->editGroup->joinGroupByCode($user, $code);
        return response()->json($response);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request) {
        $user = $request->user();
        $data = $request->all([
            'group_id',
            'contacts',
            'is_public',
            'name'
        ]);
        return response()->json($this->editGroup->saveOrCreateGroup($data, $user));
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
