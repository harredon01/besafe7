<?php

namespace App\Services;

use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use App\Models\User;

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
            $findme = 'user_id';
            $pos = strpos($mystring, $findme);
            if ($pos === false) {
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
            $findme = 'user_id';
            $pos = strpos($mystring, $findme);
            if ($pos === false) {
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
            $finalString = $mystring . "?order_by=report_time,desc&user_id=" . $user->id;
        } else {
            $check = explode("?", $mystring);
            if (count($check) != 2) {
                return null;
            }
            $findme = 'user_id';
            $pos = strpos($mystring, $findme);
            if ($pos === false) {
                if ($user) {
                    $finalString = $mystring . "&user_id=" . $user->id;
                } else {
                    $findme = 'hash_id=';
                    $pos = strpos($mystring, $findme);
                    if ($pos === false) {
                        return null;
                    } else {
                        $mystring = $mystring . "&order_by=report_time,asc";
                        $finalString = $mystring;
                    }
                }
            } else {
                return null;
            }
            $findme = 'order_by';
            $pos = strpos($mystring, $findme);
            if ($pos === false) {
                $finalString = $finalString . "&order_by=report_time,desc";
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
            $findme = 'user_id';
            $pos = strpos($mystring, $findme);
            if ($pos === false) {
                $finalString = $mystring . "&user_id=" . $user->id;
            } else {

                return null;
            }
            $findme = 'target_id=';
            $pos = strpos($mystring, $findme);
            if ($pos === false) {

                return null;
            }
            $findme = 'trip_id=';
            $pos = strpos($mystring, $findme);
            if ($pos === false) {
                return null;
            }
            $findme = 'order_by';
            $pos = strpos($mystring, $findme);
            if ($pos === false) {
                $finalString = $finalString . "&order_by=report_time,desc";
            } else {
                
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
            $findme = 'user_id';
            $pos = strpos($mystring, $findme);
            if ($pos === false) {
                $finalString = $mystring . "&user_id=" . $user->id;
            } else {
                return null;
            }
            $findme = 'order_by';
            $pos = strpos($mystring, $findme);
            if ($pos === false) {
                $finalString = $finalString . "&order_by=users.id,desc";
            } else {
                
            }
        }
        $request2 = Request::create($finalString, 'GET');
        return $request2;
    }

    public function handleReport($request) {
        $user = $request->user();
        $mystring = $request->getRequestUri();
        $findme = '?';
        $finalString = "";
        $pos = strpos($mystring, $findme);
        if ($pos === false) {
            $finalString = $mystring . "?order_by=reports.id,desc&user_id=" . $user->id;
        } else {
            $check = explode("?", $mystring);
            if (count($check) != 2) {
                return null;
            }
            $findme = 'user_id';
            $pos = strpos($mystring, $findme);
            if ($pos === false) {
                $findme = 'private=0';
                $pos = strpos($mystring, $findme);
                if ($pos === false) {
                    $findme = 'group_id';
                    $pos = strpos($mystring, $findme);
                    if ($pos === true) {
                        $group_id = $request->only("group_id");
                        $members = DB::select('select user_id as id from group_user where user_id  = ? and group_id = ? ', [$user->id, $group_id]);
                        if (sizeof($members) == 0) {
                            return null;
                        } else {
                            $finalString = $mystring;
                        }
                    } else {
                        $findme = 'shared_id=';
                        $pos = strpos($mystring, $findme);
                        if ($pos === false) {
                            $findme = 'shared=true';
                            $pos = strpos($mystring, $findme);
                            if ($pos === false) {
                                $finalString = $mystring . "&user_id=" . $user->id . "&order_by=id,asc";
                            } else {
                                $mystring = str_replace("shared=true", "", $mystring);
                                $finalString = $mystring . "shared_id=" . $user->id;
                            }
                        } else {
                            return null;
                        }
                    }
                } else {
                    $finalString = $mystring . "&order_by=created_at,desc";
                }
            } else {
                return null;
            }
            $findme = 'order_by';
            $pos = strpos($finalString, $findme);
            if ($pos === false) {
                $finalString = $finalString . "&order_by=reports.id,desc";
            } else {
                
            }
        }
        $request2 = Request::create($finalString, 'GET');
        return $request2;
    }

}
