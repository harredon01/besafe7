<?php

namespace App\Services;

use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use App\Models\User;
use DB;

class CleanSearch {

    public function handle(User $user, Request $request) {
        $mystring = $request->getRequestUri();
        $findme = '?';
        $pos = strpos($mystring, $findme);
        if ($pos === false) {
            $request2 = Request::create($mystring . "?user_id=" . $user->id, 'GET');
        } else {
            $check = explode("?", $mystring);
            if (count($check) != 2) {
                return null;
            }
            $data = $request->only("user_id");
            if (!$data['user_id']) {
                $request2 = Request::create($mystring . "&user_id=" . $user->id, 'GET');
            } else {
                return null;
            }
        }

        return $request2;
    }

    public function handleReportImgs(User $user, Request $request) {
        $mystring = $request->getRequestUri();
        $findme = '?';
        $pos = strpos($mystring, $findme);
        if ($pos === false) {
            $request2 = Request::create($mystring . "?user_id=" . $user->id, 'GET');
        } else {
            $check = explode("?", $mystring);
            if (count($check) != 2) {
                return null;
            }
            $data = $request->only("user_id");
            if (!$data['user_id']) {
                $request2 = Request::create($mystring . "&user_id=" . $user->id, 'GET');
            } else {
                return null;
            }
        }

        return $request2;
    }

    public function handleLocation($request) {
        $user = $request->user();
        $mystring = $request->getRequestUri();
        $findme = '?';
        $finalString = "";
        $pos = strpos($mystring, $findme);


        if ($pos === false) {
            $finalString = $mystring . "?order_by=locations.id,asc&user_id=" . $user->id;
        } else {
            $check = explode("?", $mystring);
            if (count($check) != 2) {
                return null;
            }
            $data = $request->only("user_id");
            if (!$data['user_id']) {
                if ($user) {
                    $finalString = $mystring . "&user_id=" . $user->id;
                } else {
                    $data = $request->only("hash_id");
                    if (!$data['hash_id']) {
                        return null;
                    } else {
                        $mystring = $mystring . "&order_by=locations.id,asc";
                        $finalString = $mystring;
                    }
                }
            } else {
                return null;
            }
            $data = $request->only("order_by");
            if (!$data['order_by']) {
                $finalString = $finalString . "&order_by=locations.id,asc";
            } else {
                
            }
        }
        $request2 = Request::create($finalString, 'GET');
        return $request2;
    }

    public function handleHistoricLocation($request) {
        $user = $request->user();
        $mystring = $request->getRequestUri();
        $findme = '?';
        $finalString = "";
        $pos = strpos($mystring, $findme);


        if ($pos === false) {
            return null;
        } else {

            $check = explode("?", $mystring);
            if (count($check) != 2) {
                return null;
            }
            $data = $request->only("user_id");
            if (!$data['user_id']) {
                $finalString = $mystring . "&user_id=" . $user->id;
            } else {

                return null;
            }
            $data = $request->only("target_id");
            if (!$data['target_id']) {

                return null;
            }
            $data = $request->only("trip_id");
            if (!$data['trip_id']) {
                return null;
            }
            $data = $request->only("order_by");
            if (!$data['order_by']) {
                $finalString = $finalString . "&order_by=report_time,desc";
            } 
        }
        $request2 = Request::create($finalString, 'GET');
        return $request2;
    }

    public function handleContact($request) {
        $user = $request->user();
        $mystring = $request->getRequestUri();
        $findme = '?';
        $finalString = "";
        $pos = strpos($mystring, $findme);
        if ($pos === false) {
            $finalString = $mystring . "?order_by=users.id,desc&user_id=" . $user->id;
        } else {
            $check = explode("?", $mystring);
            if (count($check) != 2) {
                return null;
            }
            $data = $request->only("user_id");
            if (!$data['user_id']) {
                $finalString = $mystring . "&user_id=" . $user->id;
            } else {
                return null;
            }
            $data = $request->only("order_by");
            if (!$data['order_by']) {
                $finalString = $finalString . "&order_by=users.id,desc";
            } else {
                
            }
        }
        $request2 = Request::create($finalString, 'GET');
        return $request2;
    }

    public function handleObject($request, $type) {
        $user = $request->user();
        $mystring = $request->getRequestUri();
        $findme = '?';
        $finalString = "";
        $pos = strpos($mystring, $findme);
        if ($pos === false) {
            $finalString = $mystring . "?order_by=$type.id,desc&user_id=" . $user->id;
        } else {
            $check = explode("?", $mystring);
            if (count($check) != 2) {
                return null;
            }
            $data = $request->only("user_id");
            if (!$data['user_id']) {
                $data = $request->only("group_id");
                if ($data['group_id']) {
                    $members = DB::select('select user_id as id, is_admin from group_user where user_id  = ? and group_id = ? and status <> "blocked" ', [$user->id, $data['group_id']]);
                    if (sizeof($members) == 0) {
                        return null;
                    } else {
                        if ($members[0]->is_admin) {
                            $data = $request->only("status");
                            if ($data['status']) {
                                if ($data['status'] == "active" || $data['status'] == "pending" || $data['status'] == "deleted") {
                                    $finalString = $mystring;
                                } else {
                                    return null;
                                }
                            } else {
                                $finalString = $mystring . "&status=active";
                            }
                        } else {
                            $data = $request->only("status");
                            if (!$data['status']) {
                                $finalString = $mystring . "&status=active";
                            } else {
                                return null;
                            }
                        }
                    }
                } else {
                    $data = $request->only("shared_id");
                    if (!$data['shared_id']) {
                        $data = $request->only("shared");
                        if (!$data['shared']) {
                            $finalString = $mystring . "&user_id=" . $user->id ;
                        } else {
                            if ($data['shared'] == 'true') {
                                $mystring = str_replace("shared=true", "", $mystring);
                                $finalString = $mystring . "shared_id=" . $user->id . "&status=active";
                            } else {
                                return null;
                            }
                        }
                    } else {
                        return null;
                    }
                }
            } else {
                return null;
            }
            $data = $request->only("order_by");
            if (!$data['order_by']) {
                $finalString = $finalString . "&order_by=$type.id,desc";
            } else {
                
            }
        }
        $request2 = Request::create($finalString, 'GET');
        return $request2;
    }

    public function handleReport($request) {
        return $this->handleObject($request, "reports");
    }

    public function handleMerchant($request) {
        return $this->handleObject($request, "merchants");
    }

}
