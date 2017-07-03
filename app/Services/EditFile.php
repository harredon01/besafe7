<?php

namespace App\Services;

use Validator;
use App\Models\FileM;
use App\Jobs\NotifyGroup;
use App\Jobs\NotifyContacts;
use App\Models\User;
use App\Models\Group;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use DB;
use Image;
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
            if ($type == "report") {
                $trigger = Report::find($intended_id);
                $path = 'images/reports/';
                if ($trigger) {
                    if ($trigger->user_id == $user->id) {
                        if ($filetype == "photo") {

                            $trigger_id = $trigger->id;
                            /* $today = date("Y-m-d_H-i-s");
                              $filename = $today . "_report-" . $trigger->id . '.' . $file->getClientOriginalExtension();
                              $path = public_path($path . $filename);
                              Image::make($file->getRealPath())->resize(800, 800)->save($path); */
                            $path = Storage::putFile('public/reports', $file);
                            $filename = $path;
                            $saved = true;
                        }
                    } else {
                        return array("status" => "error", "message" => "File does not belong to user");
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

                    $path = Storage::putFile('public/avatars', $file);
                    $filename = $path;
                    $user->avatar = $path;
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
                            $path = Storage::putFile('public/groups', $file);
                            $filename = $path;
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
