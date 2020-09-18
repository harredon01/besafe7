<?php

namespace App\Services;

use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use App\Models\User;
use DB;
use App\Models\Merchant;
use Illuminate\Support\Str;
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

    public function handleDocument(User $user, Request $request) {
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
            $auth = $request->all("author_id");
            if ($auth['author_id']) {
                if ($auth['author_id'] != -1) {
                    return null;
                }
            }
            $data = $request->all("user_id");
            if (!$data['user_id']) {
                if ($auth['author_id']) {
                    if(Str::contains($mystring, '&author_id=-1')){
                        $mystring = str_replace("&author_id=-1","",$mystring);
                    }
                    if(Str::contains($mystring, 'author_id=-1')){
                        $mystring = str_replace("author_id=-1","",$mystring);
                    }
                    $request2 = Request::create($mystring . "&author_id=" . $user->id, 'GET');
                } else {
                    $request2 = Request::create($mystring . "&user_id=" . $user->id, 'GET');
                }
            } else {
                return null;
            }
        }
        return $request2;
    }

    public function handleOrder(User $user, Request $request) {
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
            if ($data['user_id']) {
                return null;
            }
            $data = $request->all("merchant_id");
            if ($data['merchant_id']) {
                $members = DB::select('select merchant_id as id from merchant_user where user_id  = ? and merchant_id = ? ', [$user->id, $data['merchant_id']]);
                if (sizeof($members) > 0) {
                    $request2 = Request::create($mystring . "&merchant_id=" . $members[0]->id, 'GET');
                } else {
                    return null;
                }
            } else {
                $request2 = Request::create($mystring . "&user_id=" . $user->id, 'GET');
            }
        }

        return $request2;
    }

    public function handleFiles(User $user, Request $request) {
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
            if ($data['user_id']) {
                return null;
            }
            $data = $request->all(["type", "trigger_id"]);
            if ($data['type']) {
                if ($data['type'] == "Merchant") {
                    $members = DB::select('select merchant_id as id from merchant_user where user_id  = ? and merchant_id = ? ', [$user->id, $data['trigger_id']]);
                    if (sizeof($members) > 0) {
                        $request2 = Request::create($mystring, 'GET');
                    } else {
                        return null;
                    }
                } else if ($data['type'] == "Product") {
                    $members = DB::select('select mp.product_id as id from merchant_user mu join merchant_product mp on mu.merchant_id = mp.merchant_id where mu.user_id  = ? and mp.product_id = ? ', [$user->id, $data['trigger_id']]);
                    if (sizeof($members) > 0) {
                        $request2 = Request::create($mystring, 'GET');
                    } else {
                        return null;
                    }
                }
            } else {
                $request2 = Request::create($mystring . "&user_id=" . $user->id, 'GET');
            }
        }

        return $request2;
    }

    public function handleGroup(User $user, Request $request) {
        $mystring = $request->getRequestUri();
        $findme = '?';
        $data = $request->all("includes");
        if ($data['includes']) {
            return null;
        }
        $data = $request->all("columns");
        if ($data['columns']) {
            return null;
        }
        $data = $request->all("appends");
        if ($data['appends']) {
            return null;
        }
        $pos = strpos($mystring, $findme);
        if ($pos === false) {
            $request2 = Request::create($mystring . "?user_id=" . $user->id . "&order_by=groups.id,asc", 'GET');
        } else {
            $check = explode("?", $mystring);
            if (count($check) != 2) {
                return null;
            }
            $data = $request->all("user_id");
            if (!$data['user_id']) {
                $request2 = Request::create($mystring . "&user_id=" . $user->id . "&order_by=groups.id,asc", 'GET');
            } else {
                return null;
            }
        }
        return $request2;
    }

    public function handleReportImgs(User $user, Request $request) {
        $mystring = $request->getRequestUri();
        $findme = '?';
        $data = $request->all("includes");
        if ($data['includes']) {
            return null;
        }
        $data = $request->all("columns");
        if ($data['columns']) {
            return null;
        }
        $data = $request->all("appends");
        if ($data['appends']) {
            return null;
        }
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
        $data = $request->all("includes");
        if ($data['includes']) {
            return null;
        }
        $data = $request->all("columns");
        if ($data['columns']) {
            return null;
        }
        $data = $request->all("appends");
        if ($data['appends']) {
            return null;
        }
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
        $data = $request->all("includes");
        if ($data['includes']) {
            return null;
        }
        $data = $request->all("columns");
        if ($data['columns']) {
            return null;
        }
        $data = $request->all("appends");
        if ($data['appends']) {
            return null;
        }
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
        $data = $request->all("includes");
        if ($data['includes']) {
            return null;
        }
        $data = $request->all("columns");
        if ($data['columns']) {
            return null;
        }
        $data = $request->all("appends");
        if ($data['appends']) {
            return null;
        }
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
        $data = $request->all("includes");
        if ($data['includes']) {
            if ($data['includes'] != "availabilities") {
                return null;
            }
        }
        $data = $request->all("columns");
        if ($data['columns']) {
            return null;
        }
        $data = $request->all("appends");
        if ($data['appends']) {
            return null;
        }
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
            $data = $request->all(
                    "user_id", "group_id", "group_status", 'shared_id', "favorite_id", "shared", "favorite", "private", "order_by"
            );
            if (!$data['user_id']) {
                if ($data['group_id']) {
                    $members = DB::select('select user_id as id, is_admin from group_user where user_id  = ? and group_id = ? and level <> "group_blocked" and level <> "group_pending"', [$user->id, $data['group_id']]);
                    if (sizeof($members) == 0) {
                        return null;
                    } else {
                        if ($members[0]->is_admin) {
                            if ($data['group_status']) {
                                if ($data['group_status'] == "active" || $data['group_status'] == "pending" || $data['group_status'] == "deleted") {
                                    $finalString = $mystring;
                                } else {
                                    return null;
                                }
                            } else {
                                $finalString = $mystring . "&group_status=active";
                            }
                        } else {
                            $data = $request->all("status");
                            if (!$data['status']) {
                                $finalString = $mystring . "&group_status=active";
                            } else {
                                return null;
                            }
                        }
                    }
                } else {
                    if (!$data['shared_id']) {
                        
                    } else {
                        return null;
                    }
                    if (!$data['favorite_id']) {
                        
                    } else {
                        return null;
                    }
                    if (!$data['shared'] && !$data['favorite']) {
                        if ($data['private'] == '1') {
                            $finalString = $mystring . "&user_id=" . $user->id;
                        } else {
                            $finalString = $mystring . "&private=0";
                        }
                    } else {
                        if ($data['shared']) {
                            if ($data['shared'] == 'true') {
                                $mystring = str_replace("shared=true", "", $mystring);
                                $mystring = $mystring . "shared_id=" . $user->id;
                            } else {
                                return null;
                            }
                        }
                        if ($data['favorite']) {
                            if ($data['favorite'] == 'true') {
                                $mystring = str_replace("favorite=true", "", $mystring);
                                $mystring = $mystring . "favorites_id=" . $user->id . "&status=active";
                            } else {
                                return null;
                            }
                        }

                        $finalString = $mystring . "&status=active";
                    }
                }
            } else {
                if (!$data['private']) {
                    $finalString = $mystring . "&private=0";
                } else {
                    return null;
                }
            }
            if (!$data['order_by']) {
                $finalString = $finalString . "&order_by=$type.id,desc";
            } else {
                
            }
        }
        $request2 = Request::create($finalString, 'GET');
        return $request2;
    }

    public function handleObjectExternal($request, $type) {
        $mystring = $request->getRequestUri();
        $data = $request->all("includes");
        if ($data['includes']) {
            if ($data['includes'] != "availabilities") {
                return null;
            }
        }
        $data = $request->all("columns");
        if ($data['columns']) {
            return null;
        }
        $data = $request->all("appends");
        if ($data['appends']) {
            return null;
        }
        $findme = '?';
        $finalString = "";
        $pos = strpos($mystring, $findme);
        if ($pos === false) {
            if($type == "merchants"){
                $finalString = $mystring . "?order_by=$type.id,desc&private=0&status[]=active&status[]=online&status[]=busy";
            } else {
                $finalString = $mystring . "?order_by=$type.id,desc&status=active";
            }
            
        } else {
            $check = explode("?", $mystring);
            if (count($check) != 2) {
                return null;
            }
            $data = $request->all(
                    "user_id", "group_id", "group_status", 'shared_id', "favorite_id", "shared", "favorite", "private", "order_by", "status"
            );
            if ($data['shared_id']) {
                return null;
            }
            if ($data['favorite_id']) {
                return null;
            }
            if ($data['user_id']) {
                return null;
            }
            if ($data['private']) {
                return null;
            }
            if ($data['status']) {
                return null;
            }
            if($type == "merchants"){
                $finalString = $mystring . "&private=0&status[]=active&status[]=online&status[]=busy";
            } else {
                $finalString = $mystring . "&status=active";
            }
            
            if (!$data['order_by']) {
                $finalString = $finalString . "&order_by=$type.id,desc";
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

    public function handleReportExternal($request) {
        return $this->handleObjectExternal($request, "reports");
    }

    public function handleMerchantExternal($request) {
        return $this->handleObjectExternal($request, "merchants");
    }

}
