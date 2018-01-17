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
            $data = $request->all("user_id");
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
            $data = $request->all("user_id");
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
            $finalString = $mystring . "?order_by=report_time,asc&limit=30&user_id=" . $user->id;
        } else {
            $check = explode("?", $mystring);
            if (count($check) != 2) {
                return null;
            }
            $data = $request->all("user_id");
            if (!$data['user_id']) {
                if ($user) {
                    $finalString = $mystring . "&user_id=" . $user->id;
                } else {
                    $data = $request->all("hash_id");
                    if (!$data['hash_id']) {
                        return null;
                    } else {
                        $mystring = $mystring . "&order_by=report_time,asc";
                        $finalString = $mystring;
                    }
                }
            } else {
                return null;
            }
            $data = $request->all("order_by");
            if (!$data['order_by']) {
                $finalString = $finalString . "&order_by=report_time,asc";
            }
            $data = $request->all("limit");
            if (!$data['limit']) {
                $finalString = $finalString . "&limit=30";
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
            $data = $request->all("user_id");
            if (!$data['user_id']) {
                $finalString = $mystring . "&user_id=" . $user->id;
            } else {

                return null;
            }
            $data = $request->all("target_id");
            if (!$data['target_id']) {

                return null;
            }
            $data = $request->all("trip_id");
            if (!$data['trip_id']) {
                return null;
            }
            $data = $request->all("order_by");
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
            $data = $request->all("user_id");
            if (!$data['user_id']) {
                $finalString = $mystring . "&user_id=" . $user->id;
            } else {
                return null;
            }
            $data = $request->all("order_by");
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
            $data = $request->all("user_id");
            if (!$data['user_id']) {
                $data = $request->all("group_id");
                if ($data['group_id']) {
                    $members = DB::select('select user_id as id, is_admin from group_user where user_id  = ? and group_id = ? and status <> "blocked" ', [$user->id, $data['group_id']]);
                    if (sizeof($members) == 0) {
                        return null;
                    } else {
                        if ($members[0]->is_admin) {
                            $data = $request->all("status");
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
                            $data = $request->all("status");
                            if (!$data['status']) {
                                $finalString = $mystring . "&status=active";
                            } else {
                                return null;
                            }
                        }
                    }
                } else {
                    $data = $request->all("shared_id");
                    if (!$data['shared_id']) {
                        
                    } else {
                        return null;
                    }
                    $data = $request->all("favorite_id");
                    if (!$data['favorite_id']) {
                        
                    } else {
                        return null;
                    }
                    $data = $request->all("shared","favorite");
                    if (!$data['shared']&&!$data['favorite']) {
                        $finalString = $mystring . "&user_id=" . $user->id;
                    } else {
                        if ($data['shared'] == 'true') {
                            $mystring = str_replace("shared=true", "", $mystring);
                            $mystring = $mystring . "shared_id=" . $user->id;
                        } else {
                            return null;
                        }
                        if ($data['favorite'] == 'true') {
                            $mystring = str_replace("favorite=true", "", $mystring);
                            $mystring = $mystring . "favorite_id=" . $user->id . "&status=active";
                        } else {
                            return null;
                        }
                        $finalString = $mystring . "&status=active";
                    }
                }
            } else {
                return null;
            }
            $data = $request->all("order_by");
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
