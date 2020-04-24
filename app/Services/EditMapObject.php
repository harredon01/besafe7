<?php

namespace App\Services;

use Validator;
use App\Models\FileM;
use App\Models\Merchant;
use App\Models\Category;
use App\Models\User;
use App\Jobs\SaveGroupsObject;
use App\Jobs\CreateMerchant;
use App\Models\Group;
use App\Models\Report;
use App\Models\Favorite;
use App\Models\Rating;
use App\Models\Availability;
use Illuminate\Http\Response;
use DB;
use Cache;

class EditMapObject {

    const MODEL_PATH = 'App\\Models\\';
    const OBJECT_REPORT_GROUP = 'Report_Group';
    const OBJECT_MERCHANT_GROUP = 'Merchant_Group';
    const OBJECT_REPORT_ACCESS_GROUP = 'Report_Access_Group';
    const OBJECT_MERCHANT_ACCESS_GROUP = 'Merchant_Access_Group';
    const OBJECT_REPORT = 'Report';
    const OBJECT_GROUP = 'Group';
    const OBJECT_USER = 'User';
    const OBJECT_MERCHANT = 'Merchant';
    const GROUP_PENDING = 'group_pending';
    const GROUP_BLOCKED = 'group_blocked';
    const GROUP_MERCHANT_TABLE = 'group_merchant';
    const GROUP_REPORT_TABLE = 'group_report';
    const CONTACT_BLOCKED = 'contact_blocked';

    /**
     * The EditAlert implementation.
     *
     */
    protected $notifications;

    /**
     * The EditAlert implementation.
     *
     */
    protected $editGroup;

    /**
     * Create a new class instance.
     *
     * @param  EventPusher  $pusher
     * @return void
     */
    public function __construct(EditGroup $editGroup) {
        $this->notifications = app('Notifications');
        $this->editGroup = $editGroup;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function getMerchant($merchant_id) {
        $users = DB::select('SELECT me.*, ca.name as categoryname, pr.id as productid, pr.name as productname, pr.description as productdesc, pr.total as producttotal'
                        . ' FROM merchants me join categories ca on me.id = ca.merchant_id '
                        . 'join products pr on ca.id = pr.category_id where me.id = :id', ['id' => $merchant_id]);
        $target = array();
        foreach ($users as $user) {
            $target[$user->name]["name"] = $user->name;
            $target[$user->name]["description"] = $user->description;
            $target[$user->name]["delivery_time"] = $user->delivery_time;
            $target[$user->name]["delivery_price"] = $user->delivery_price;
            $target[$user->name]["categories"]["$user->categoryname"]["name"] = $user->categoryname;
            $target[$user->name]["categories"]["$user->categoryname"]["products"] = array('ProductName' => $user->productname, 'ProductDescription' => $user->productdesc, 'total' => $user->producttotal);
        }
        return $target;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function getPaymentMethodsMerchant($id) {
        $merchant = Merchant::find($id);
        $merchant->paymentMethods;
        return ['status' => "success", "data" => $merchant->paymentMethods];
    }

    public function getCategoriesMerchant($id) {
        $categories = Category::where('type', "Product")->where(function($query) {
                $query->where('merchant_id', $id)
                      ->orWhereNull('merchant_id');
            })->get();
        return ['status' => "success", "data" => $categories];
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function deleteObject(User $user, $reportId, $type) {
        $type = "App\\Models\\" . $type;
        $object = $type::find($reportId);
        if ($user->id == $object->user_id) {
            $object->delete();
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function deleteObjectFromGroup(User $user, $objectId, $groupId, $type) {
        $type = "App\\Models\\" . $type;
        $object = $type::find($objectId);
        if ($object) {
            $members = DB::select('select user_id as id from group_user where user_id  = ? and group_id = ? and level <> "' . self::GROUP_BLOCKED . '"  && level <> "' . self::GROUP_PENDING . '" and is_admin = true ', [$user->id, $object->group_id]);
            if (sizeof($members) == 0) {
                return null;
            } else {
                if ($type == "Report") {
                    DB::table('group_report')->where('report_id', $objectId)->where('group_id', $groupId)->delete();
                } else if ($type == "Merchant") {
                    DB::table('group_merchant')->where('merchant_id', $objectId)->where('group_id', $groupId)->delete();
                }
            }
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function getObjectUser($user, $objectId, $type) {
        if (false) {
            $data = Cache::remember($type . '_' . $objectId, 100, function ()use ($type, $objectId) {
                        $target = "App\\Models\\" . $type;
                        $object = $target::find($objectId);
                        $files = FileM::where("type", $type)->where("trigger_id", $object->id)->get();
                        $ratings = Rating::where("type", $type)->where("object_id", $object->id)->orderBy('id', 'desc')->limit(20)->get();
                        if ($type == self::OBJECT_REPORT) {
                            if ($object->private == true && $type == "Report") {
                                $object->email == "";
                                $object->telephone == "";
                            }
                            $data = [
                                "report" => $object,
                                "files" => $files,
                                "ratings" => $ratings
                            ];
                        } else if ($type == self::OBJECT_MERCHANT) {
                            $availability = Availability::where("bookable_type", self::MODEL_PATH . $type)->where("bookable_id", $object->id)->limit(25)->get();
                            $data = [
                                "merchant" => $object,
                                "files" => $files,
                                "ratings" => $ratings,
                                "availabilities" => $availability
                            ];
                        }
                        return $data;
                    });
        } else {
            $target = "App\\Models\\" . $type;
            $object = $target::find($objectId);
            $files = FileM::where("type", $type)->where("trigger_id", $object->id)->get();
            $ratings = Rating::where("type", $type)->where("object_id", $object->id)->orderBy('id', 'desc')->limit(20)->get();
            if ($type == self::OBJECT_REPORT) {
                if ($object->private == true && $type == "Report") {
                    $object->email == "";
                    $object->telephone == "";
                }
                $data = [
                    "report" => $object,
                    "files" => $files,
                    "ratings" => $ratings
                ];
            } else if ($type == self::OBJECT_MERCHANT) {
                $availability = Availability::where("bookable_type", self::MODEL_PATH . $type)->where("bookable_id", $object->id)->limit(25)->get();
                $data = [
                    "merchant" => $object,
                    "files" => $files,
                    "ratings" => $ratings,
                    "availabilities" => $availability
                ];
            }
        }

        $object = null;
        if ($type == self::OBJECT_REPORT) {
            $object = $data['report'];
        } else if ($type == self::OBJECT_MERCHANT) {
            $object = $data['merchant'];
        }

        if ($object) {
            $send = false;
            if ($object->private) {
                if ($user) {
                    $send = $object->checkUserAccess($user);
                } else {
                    $send = false;
                }
            } else {
                $send = true;
            }
            if ($send == true) {
                $data['favorite'] = false;
                if ($user) {
                    $favor = Favorite::where('user_id', $user->id)->where("favorite_type", $type)->where("object_id", $object->id)->first();
                    if ($favor) {
                        $data['favorite'] = true;
                    }
                }
                $data['status'] = 'success';
                return $data;
            }
            return ['status' => "error", "message" => $type . ' not found for user'];
        } else {
            return ['status' => "error", "message" => $type . ' not found'];
        }
    }

    public function sendCartItem(User $user, array $data) {
        $validator = $this->validatorMerchantCartItem($data);
        if ($validator->fails()) {
            return response()->json(array("status" => "error", "message" => $validator->getMessageBag()), 400);
        }
        $target = User::find($data['customer_id']);
        $followers = [$target];
        if (count($followers) > 0) {
            $payload = [
                "name" => $data['item_name'],
                "description" => $data['item_description'],
                "price" => $data['item_price'],
                "tax" => $data['item_tax'],
                "shipping" => $data['item_shipping'],
                "quantity" => $data['item_quantity'],
                "merchant_id" => $data['object_id'],
                "merchant_name" => $data['merchant_name'],
            ];
            $data = [
                "trigger_id" => $data['object_id'],
                "message" => "",
                "subject" => "Visita tu correo para enterarte de nuestros menus de esta semana",
                "object" => "Merchant",
                "sign" => true,
                "payload" => $payload,
                "type" => 'merchant_item',
                "user_status" => "normal"
            ];
            $date = date_create();
            $date = date_format($date, "Y-m-d");
            $this->notifications->sendMassMessage($data, $followers, null, true, $date, false);
            foreach ($followers as $user) {
                Mail::to($target->email)->send(new Newsletter());
            }
        }
    }

    public function getObjectByHash($code, $type) {
        $data = Cache::remember($type . '_hash_' . $code, 100, function ()use ($type, $code) {
                    $target = "App\\Models\\" . $type;
                    $object = $target::where('hash', $code)->first();
                    if ($object) {
                        $files = FileM::where("type", $type)->where("trigger_id", $object->id)->get();
                        $ratings = Rating::where("type", $type)->where("object_id", $object->id)->orderBy('id', 'desc')->limit(20)->get();
                        if ($type == self::OBJECT_REPORT) {
                            if ($object->private == true && $type == "Report") {
                                $object->email == "";
                                $object->telephone == "";
                            }
                            $data = [
                                "report" => $object,
                                "files" => $files,
                                "ratings" => $ratings
                            ];
                        } else if ($type == self::OBJECT_MERCHANT) {
                            $data = [
                                "merchant" => $object,
                                "files" => $files,
                                "ratings" => $ratings
                            ];
                        }
                        return $data;
                    } else {
                        return ['status' => "error", "message" => $type . ' not found'];
                    }
                });
        return $data;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function approveReport(User $user, $reportId) {
        if ($user->email == "harredon01@gmail.com") {
            $reports = Report::where('id', '=', $reportId)
                            ->get()->toArray();
            if (sizeof($reports) > 0) {
                Merchant::insert($reports);
                dd($reports);
                Report::where('id', '=', $reportId)
                        ->delete();
            }
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function getMerchantOrders(User $user, $id) {
        $merchants = "";
        foreach ($user->merchants as $merchant) {
            if ($merchant->id == $id) {
                $merchant = Merchant::find($id);
                $merchant->orders;
                return $merchant;
            }
        }
        return $merchants;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function getNearby(array $data) {
        $data['type'] = "Merchant";
        $merchants = $this->getNearbyObjects($data);
        $data['type'] = "Report";
        $reports = $this->getNearbyObjects($data);
        return array("merchants" => $merchants['data'], "reports" => $reports['data']);
    }

    public function getNearbyObjects(array $data) {
        $radius = 1;
        $R = 6371;
        $lat = $data['lat'];
        $long = $data['long'];
        $type = "";
        $joins = "";
        $joinsWhere = "";
        $category = false;
        if (array_key_exists("category", $data)) {
            if ($data["category"]) {
                $category = true;
            }
        }
        $additionalFields = "";
        if ($data['type'] == "Merchant") {
            $type = "merchants";
            $additionalFields = " type, telephone, address, ";
            if ($category) {
                $joins = " join category_merchant cm on r.id = cm.merchant_id ";
                $joinsWhere = " AND cm.category_id = :category ";
            }
        } else if ($data['type'] == "Report") {
            $type = "reports";
            $additionalFields = " type, telephone, address, report_time, ";
            if ($category) {
                $joins = " join category_report cr on r.id = cm.report_id ";
                $joinsWhere = " AND cr.category_id = :category ";
            }
        }

        $maxLat = $lat + rad2deg($radius / $R);
        $minLat = $lat - rad2deg($radius / $R);
        $maxLon = $long + rad2deg(asin($radius / $R) / cos(deg2rad($lat)));
        $minLon = $long - rad2deg(asin($radius / $R) / cos(deg2rad($lat)));
        $thedata = [
            'lat' => $lat,
            'lat2' => $lat,
            'long' => $long,
            'latinf' => $minLat,
            'latsup' => $maxLat,
            'longinf' => $minLon,
            'longsup' => $maxLon,
            'radius' => $radius,
        ];
        if ($category) {
            $thedata["category"] = $data["category"];
        }
        $reports = DB::select(" "
                        . "SELECT r.id, name, description, icon, lat,`long`, " . $additionalFields . " 
			( 6371 * acos( cos( radians( :lat ) ) *
		         cos( radians( r.lat ) ) * cos( radians(  `long` ) - radians( :long ) ) +
		   sin( radians( :lat2 ) ) * sin( radians(  r.lat  ) ) ) ) AS Distance  
                   FROM
                    " . $type . " r " . $joins . "
                    WHERE
                        status = 'active'
                            AND r.private = 0
                            AND r.type <> ''
                            AND lat BETWEEN :latinf AND :latsup
                            AND `long` BETWEEN :longinf AND :longsup
                            " . $joinsWhere . "
                    HAVING distance < :radius
                    order by distance asc limit 20 "
                        . "", $thedata);
        return array("data" => $reports);
    }

    function findLonBoundary($lat, $lon, $lat1, $lat2) {

        $d = $lat - $lat1;

        $d1 = $d / cos(deg2rad($lat1));
        $d2 = $d / cos(deg2rad($lat2));

        $lon1 = min($lon - $d1, $lon - $d2);
        $lon2 = max($lon + $d1, $lon + $d2);
        return array('longinf' => $lon1, 'longsup' => $lon2);
    }

    function findLatBoundary($dist, $lat, &$lat1, &$lat2) {
        $d = ($dist / 6371.01 * 2 * M_PI) * 360;
        $lat1 = $lat - $d;
        $lat2 = $lat + $d;
        return array('latinf' => $lat1, 'latsup' => $lat2);
    }

    function checkGroupStatus(User $user, Group $group, array $data) {
        if ($group->isPublicActive()) {
            $member = $group->checkMemberType($user);
            if ($member) {
                if ($member->level == self::CONTACT_BLOCKED) {
                    return null;
                }
                if ($member->is_admin) {
                    $data['status'] = "active";
                } else {
                    $data['status'] = "pending";
                }
            } else {
                return null;
            }
        } else if (!$group->is_public) {
            $member = $group->checkMemberType($user);
            if ($member) {
                if ($member->level == self::CONTACT_BLOCKED) {
                    return null;
                }
                $data['status'] = "active";
            } else {
                return null;
            }
        } else {
            return null;
        }
        return $data;
    }

    public function notifyGroup(Group $group, User $user, array $data, $type, $object) {
        if ($group) {
            if ($group->isPublicActive()) {
                if ($data['status'] == "pending") {
                    $followers = $group->getAllAdminMembersNonUserBlockedButActive($user);
                } elseif ($data['status'] == "active") {
                    $followers = $group->getAllMembersNonUserBlockedButActive($user);
                } else {
                    return null;
                }
            } else if (!$group->is_public) {
                $followers = $group->getAllMembersNonUserBlockedButActive($user);
            } else {
                return null;
            }
            $payload = array("class" => $type, "type" => $object->type, "object_type" => $object->type, "object_name" => $object->name, "object_id" => $object->id, "first_name" => $user->firstName, "last_name" => $user->lastName, "group_name" => $group->name, "group_id" => $group->id);
            if ($type == "Report" && $data['status'] == "active") {
                $type = self::OBJECT_REPORT_GROUP;
            } else if ($type == "Report" && $data['status'] == "pending") {
                $type = self::OBJECT_REPORT_ACCESS_GROUP;
            } else if ($type == "Merchant" && $data['status'] == "active") {
                $type = self::OBJECT_MERCHANT_GROUP;
            } else if ($type == "Merchant" && $data['status'] == "pending") {
                $type = self::OBJECT_MERCHANT_ACCESS_GROUP;
            }
            $data = [
                "trigger_id" => $user->id,
                "message" => "",
                "subject" => "",
                "payload" => $payload,
                "object" => self::OBJECT_GROUP,
                "sign" => true,
                "type" => $type,
                "user_status" => $user->getUserNotifStatus()
            ];
            $this->notifications->sendMassMessage($data, $followers, $user, true, null);
        }
    }

    public function notifyGroups(User $user, array $data, $type, $object, $groups) {
        foreach ($groups as $item) {
            $group = Group::find($item);
            if ($group) {
                $this->notifyGroup($group, $user, $data, $type, $object);
            }
        }
    }

    public function saveToGroups(User $user, array $data, $type, $object) {
        if ($data['groups']) {
            $groups = $data['groups'];
            if ($type == self::OBJECT_MERCHANT) {
                DB::table(self::GROUP_MERCHANT_TABLE)->where("merchant_id", $object->id)
                        ->whereNotIn("group_id", $groups)->delete();
            } else if ($type == self::OBJECT_REPORT) {
                DB::table(self::GROUP_REPORT_TABLE)->where("report_id", $object->id)
                        ->whereNotIn("group_id", $groups)->delete();
            }

            $result = [];
            $finalgroups = [];

            foreach ($groups as $item) {
                $group = Group::find($item);
                if ($group) {
                    $total = 0;
                    if ($type == self::OBJECT_MERCHANT) {
                        $total = $group->merchants()->where('merchants.id', $object->id)->count();
                    } else if ($type == self::OBJECT_REPORT) {
                        $total = $group->reports()->where('reports.id', $object->id)->count();
                    }
                    if ($total > 0) {
                        continue;
                    }
                    $statcheck = [];
                    $statcheck = $this->checkGroupStatus($user, $group, $data);
                    if ($statcheck) {
                        $prospect['group_id'] = $item;
                        $prospect['status'] = $statcheck['status'];
                        $prospect['created_at'] = date("Y-m-d h:i:sa");
                        $prospect['updated_at'] = date("Y-m-d h:i:sa");
                        if ($type == self::OBJECT_MERCHANT) {
                            $prospect['merchant_id'] = $object->id;
                        } else if ($type == self::OBJECT_REPORT) {
                            $prospect['report_id'] = $object->id;
                        }
                        array_push($result, $prospect);
                        array_push($finalgroups, $item);
                    }
                }
            }
            if (count($result) > 0) {
                if ($type == self::OBJECT_MERCHANT) {
                    DB::table(self::GROUP_MERCHANT_TABLE)->insert($result);
                } else if ($type == self::OBJECT_REPORT) {
                    DB::table(self::GROUP_REPORT_TABLE)->insert($result);
                }
                $this->notifyGroups($user, $data, $type, $object, $finalgroups);
            }
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function saveOrCreateObject(User $user, array $data, $type) {
        $group = null;
        if ($type == self::OBJECT_REPORT) {
            if (array_key_exists('anonymous', $data)) {
                if ($data['anonymous']) {
                    if ($data['anonymous'] == true) {
                        $data['email'] = "";
                        $data['telephone'] = "";
                    }
                }
            }
        }
        $attributes = [];
        if (!array_key_exists("unit_cost", $data)) {
            $data["unit_cost"] = 0;
        }
        $fields = ["service", "experience", "specialty"];
        foreach ($fields as $value) {
            $services = [];
            for ($x = 1; $x <= 11; $x++) {
                if (array_key_exists($value . $x, $data)) {
                    if ($data[$value . $x]) {
                        $container = ["name" => $data[$value . $x]];
                        unset($data[$value . $x]);
                        array_push($services, $container);
                    }
                }
            }
            if (count($services) > 0) {
                $attributes[$value] = $services;
            }
        }

        $attributes['booking_requires_auth'] = false;
        $attributes['years_experience'] = 1;
        $attributes['max_per_hour'] = 1;
        $fields2 = ['booking_requires_auth', 'years_experience', 'max_per_hour', 'virtual_meeting', 'virtual_provider'];
        foreach ($fields2 as $value) {
            if (array_key_exists($value, $data)) {
                if ($data[$value]) {
                    $attributes[$value] = $data[$value];
                    unset($data[$value]);
                }
            }
        }

        $data['attributes'] = $attributes;
        if (!array_key_exists("unit", $data)) {
            $data['unit'] = "hour";
        }
//        if (!array_key_exists("status", $data)) {
//            $data['status'] = "pending";
//        }
        if (!array_key_exists("base_cost", $data)) {
            $data['base_cost'] = 0;
        }
        if (!array_key_exists("unit_cost", $data)) {
            $data['unit_cost'] = 0;
        }
        dispatch(new CreateMerchant($user));
        $data['currency'] = "COP";
        if (array_key_exists('id', $data)) {
            if ($data['id'] && $data['id'] > 0) {
                foreach ($data as $key => $value) {
                    if (!$value) {
                        unset($data[$key]);
                    }
                }
                $object = $this->updateObject($user, $data, $type);
                if ($object) {
                    return ['status' => 'success', "message" => "Result saved ", "object" => $object];
                } else {
                    return ['status' => 'error', "message" => "access denied"];
                }
            }
        }
        if (array_key_exists('private', $data)) {
            if (!$data["private"]) {
                $data["private"] = 0;
            }
        } else {
            $data["private"] = 0;
        }


        $data['status'] = 'active';
        if ($type == self::OBJECT_MERCHANT) {
            $validator = $this->validatorMerchant($data);
            if ($validator->fails()) {
                return response()->json(array("status" => "error", "message" => $validator->getMessageBag()), 400);
            }
        } else if ($type == self::OBJECT_REPORT) {
            $validator = $this->validatorReport($data);
            if ($validator->fails()) {
                return response()->json(array("status" => "error", "message" => $validator->getMessageBag()), 400);
            }
        }
        $object = $this->createObject($user, $data, $type);
        return ['status' => 'success', "message" => "Result saved: " . $object->name, "object" => $object];
    }

    /**
     * returns all current shared locations for the user
     *
     * @return Location
     */
    public function updateObject(User $user, array $data, $type) {
        $object = "App\\Models\\" . $type;

        $checker = $object::find($data['id']);
        if ($checker) {
            if ($checker->checkAdminAccess($user->id)) {
                Cache::forget($type . '_' . $data['id']);
                if (array_key_exists("groups", $data)) {
                    //$this->saveToGroups($user, $data, $type,$object);
                    dispatch(new SaveGroupsObject($user, $data, $type, $object));
                }
                $object::where('id', $data['id'])->whereIn('status', ['active', 'online', 'inactive', 'pending'])->update($data);
                $result = $this->getObjectUser($user, $data['id'], $type);
                if ($result) {
                    return $result;
                }
            }
        }
        return null;
    }

    /**
     * returns all current shared locations for the user
     *
     * @return Location
     */
    public function updateObjectStatus(User $user, array $data, $object) {
        $object = "App\\Models\\" . $object;
        $result = $object::find($data['id']);
        if ($result && $data['status']) {
            if (array_key_exists("group_id", $data)) {
                if ($data['group_id']) {

                    $group = Group::find($data['group_id']);
                    if ($group) {
                        $targetStatus = $data['status'];
                        $data = $this->checkGroupStatus($user, $group, $data);
                        if ($data['status'] == 'active') {
                            $attributes['status'] = $targetStatus;
                            $result->groups()->updateExistingPivot($group->id, $attributes);
                            return ['status' => 'success', "message" => "status updated"];
                        } else {
                            return ['status' => 'error', "message" => "you must own the report or be an admin in its hive"];
                        }
                    }
                } else {
                    return ['status' => 'error', "message" => "you must own the report or be an admin in its hive"];
                }
            }
        } else {
            return ['status' => 'error', "message" => "please submit valid object and status"];
        }
    }

    /**
     * returns all current shared locations for the user
     *
     * @return Location
     */
    public function createObject(User $user, array $data, $type) {
        $data["user_id"] = $user->id;
        $object = "App\\Models\\" . $type;


        $result = $object::create($data);
        $user->merchants()->save($result);
        if (array_key_exists("groups", $data)) {
            //$this->saveToGroups($user, $data, $type, $result);
            dispatch(new SaveGroupsObject($user, $data, $type, $result));
        }

        return $result;
    }

    /**
     * returns all current shared locations for the user
     *
     * @return Location
     */
    public function createUserObject(array $data) {
        $user = User::create([
                    "firstName" => $data['firstName'],
                    "lastName" => $data['lastName'],
                    "name" => $data['firstName'] . ' ' . $data['lastName'],
                    "email" => $data['lastName'],
                    "cellphone" => $data['lastName'],
                    "lastName" => $data['lastName'],
                    "area_code" => $data['area_code'],
                    "docType" => $data['docType'],
                    "docNum" => $data['docNum'],
                    'password' => bcrypt($data['password']),
        ]);

        $object = "App\\Models\\" . $data['type'];
        $result = $object::create($data);
        $user->merchants()->save($result);
        return $result;
    }

    /**
     * Get a validator for an incoming edit profile request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function validatorMerchant(array $data) {
        return Validator::make($data, [
                    'name' => 'required|max:255',
                    'telephone' => 'required|max:255',
        ]);
    }

    /**
     * Get a validator for an incoming edit profile request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function validatorReport(array $data) {
        return Validator::make($data, [
                    'name' => 'required|max:255',
                    'type' => 'required|max:255',
                    'report_time' => 'required|max:255',
                    'address' => 'required|max:255',
        ]);
    }

    /**
     * Get a validator for an incoming edit profile request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function validatorLat(array $data) {
        return Validator::make($data, [
                    'lat' => 'required',
                    'long' => 'required',
        ]);
    }

    /**
     * Get a validator for an incoming edit profile request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function validatorMerchantCartItem(array $data) {
        return Validator::make($data, [
                    "item_name" => 'required',
                    "item_description" => 'required',
                    "item_price" => 'required',
                    "item_tax" => 'required',
                    "item_quantity" => 'required',
                    "object_id" => 'required',
                    "customer_id" => 'required',
                    "merchant_name" => 'required',
        ]);
    }

    /**
     * Get the failed login message.
     *
     * @return string
     */
    protected function getFailedEditMapObjectMessage() {
        return 'There was a problem editing the merchant';
    }

}
