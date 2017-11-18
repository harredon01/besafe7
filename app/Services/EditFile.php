<?php

namespace App\Services;

use Validator;
use App\Models\FileM;
use App\Jobs\NotifyGroup;
use App\Jobs\NotifyContacts;
use App\Models\User;
use App\Models\Group;
use App\Models\Product;
use App\Models\Merchant;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use DB;
use Cache;
use Illuminate\Support\Facades\Storage;
use File;

class EditFile {

    /**
     * The EditAlert implementation.
     *
     */
    protected $editAlerts;

    /**
     * Create a new class instance.
     *
     * @param  EventPusher  $pusher
     * @return void
     */
    public function __construct(EditAlerts $editAlerts) {
        $this->editAlerts = $editAlerts;
    }

    public function postFile(User $user, Request $request) {
        if ($request->file('photo')->isValid()) {
            $trigger_id = "";
            $intendered = $request->only("intended_id");
            $intended_id = $intendered['intended_id'];
            $path;
            $filename;
            $saved = false;
            $typeg = $request->only("type");
            $type = $typeg['type'];
            $file = $request->file('photo');
            $filetypeff = $request->only("filetype");
            $filetype = $filetypeff['filetype'];
            if ($type == "Report" || $type == "Merchant") {
                $object = "App\\Models\\" . $type;
                $trigger = $object::find($intended_id);
                $path = 'images/reports/';
                if ($trigger) {
                    if ($trigger->user_id == $user->id) {
                        if ($filetype == "photo") {
                            $trigger_id = $trigger->id;
                            Cache::forget($type . '_' . $trigger->id);
                            $path = Storage::putFile('public/' . strtolower($type), $file, 'public');
                            $filename = Storage::url($path);
                            $saved = true;
                        }
                    } else {
                        return array("status" => "error", "message" => "File does not belong to user");
                    }
                } else {
                    return array("status" => "error", "message" => "File invalid");
                }
            } else if ($type == "Product") {
                $object = "App\\Models\\" . $type;
                $trigger = $object::find($intended_id);
                $path = 'images/reports/';
                if ($trigger) {
                    $merchant = $trigger->merchant;
                    if ($merchant) {
                        if ($merchant->user_id == $user->id) {
                            if ($filetype == "photo") {
                                $trigger_id = $trigger->id;
                                $path = Storage::putFile('public/' . strtolower($type), $file, 'public');
                                $filename = Storage::url($path);
                                Cache::forget('products_merchant_' . $merchant->id);
                                $saved = true;
                            }
                        } else {
                            return array("status" => "error", "message" => "File does not belong to user");
                        }
                    } else {
                        return array("status" => "error", "message" => "No merchant with product");
                    }
                } else {
                    return array("status" => "error", "message" => "File invalid");
                }
            } else if ($type == "user_avatar") {
                if ($filetype == "photo") {
                    if ($user->avatar || $user->avatar != "") {
                        //$pathToImage = public_path($path) . $user->avatar;
                        Storage::delete($user->avatar);
                        FileM::where("file", $user->avatar)->delete();
                    }
                    /* $image = $request->file('photo'); */
                    //$filename = "user-" . time() . '.' . $image->getClientOriginalExtension();

                    $path = Storage::putFile('public/avatars', $file, 'public');
                    $filename = Storage::url($path);
                    $user->avatar = $filename;
                    $user->save();
                    $trigger_id = $user->id;
                    //$path = public_path($path . $filename);
                    //Image::make($file->getRealPath())->resize(200, 200)->save($path);
                    $saved = true;
                    dispatch(new NotifyContacts($user, $filename));
                }
            } else if ($type == "group_avatar") {
                $users = DB::select('select * from group_user where user_id = ? and is_admin = 1 and group_id = ? AND status <> "blocked" limit 2', [$user->id, $intended_id]);
                if (count($users) == 1) {
                    $trigger = Group::find($intended_id);
                    if ($trigger) {
                        if ($filetype == "photo") {
                            if ($trigger->avatar || $trigger->avatar != "") {
                                //$pathToImage = public_path($path) . $trigger->avatar;
                                FileM::where("file", $trigger->avatar)->delete();
                                Storage::delete($trigger->avatar);
                            }
                            $path = Storage::putFile('public/groups', $file, 'public');
                            $filename = Storage::url($path);
                            $trigger->avatar = $filename;
                            $trigger->save();
                            $trigger_id = $trigger->id;
                            //$path = public_path($path . $filename);
                            //Image::make($file->getRealPath())->resize(200, 200)->save($path);
                            $saved = true;
                            dispatch(new NotifyGroup($user, $trigger, $filename, $type));
                        }
                    } else {
                        return array("status" => "error", "message" => "Group invalid");
                    }
                } else {
                    return array("status" => "error", "message" => "User not admin group");
                }
            }
            if ($trigger_id && $saved) {

                $data = [
                    "type" => $type,
                    "user_id" => $user->id,
                    "trigger_id" => $trigger_id,
                    "file" => $filename,
                    "extension" => $file->getClientOriginalExtension(),
                ];
                $dafile = FileM::create($data);
                return array("status" => "success", "message" => "Image saved: ", "file" => $dafile);
            }

            return array("status" => "error", "message" => "Image not saved");
        }
        return array("status" => "error", "message" => "File invalid");
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function delete(User $user, $file_id) {

        $file = FileM::find($file_id);

        if ($file) {
            if ($file->user_id == $user->id) {
                if($file->type=="Merchant" || $file->type=="Report"){
                    Cache::forget($file->type . '_' . $file->trigger_id);
                }
                if($file->type=="Product" ){
                    $product = Product::find($file->trigger_id);
                    Cache::forget('products_merchant_' . $product->merchant_id);
                }
                Storage::delete($file->file);
                $file->delete();
                $data = [
                    "status" => "success",
                    "message" => "file deleted",
                ];
                return $data;
            }
            $data = [
                "status" => "error",
                "message" => "file does not belong to user",
            ];
            return $data;
        }
        $data = [
            "status" => "error",
            "message" => "file does not exist",
        ];
        return $data;
    }

    /**
     * Get a validator for an incoming edit profile request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function validatorFile(array $data) {
        return Validator::make($data, [
                    'type' => 'required|max:255',
                    'intended_id' => 'required|max:255',
                    'filetype' => 'required|max:255',
        ]);
    }

    /**
     * Get the failed login message.
     *
     * @return string
     */
    protected function getFailedEditMerchantMessage() {
        return 'There was a problem editing the merchant';
    }

}
