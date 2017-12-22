<?php

namespace App\Services;

use Validator;
use App\Models\FileM;
use App\Models\User;
use App\Models\Group;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Cache;
use Illuminate\Support\Facades\Storage;

class EditFile {

    public function postFile(User $user, Request $request) {
        if ($request->file('photo')->isValid()) {
            $trigger_id = "";
            $intendered = $request->only("intended_id");
            $intended_id = $intendered['intended_id'];
            $typeg = $request->only("type");
            $type = $typeg['type'];
            $file = $request->file('photo');
            if ($type == "user_avatar") {
                $trigger = $user;
            } else if ($type == "group_avatar") {
                $trigger = Group::find($intended_id);
            } else {
                $object = "App\\Models\\" . $type;
                $trigger = $object::find($intended_id);
            }
            if ($trigger) {
                $trigger_id = $trigger->checkAddImg($user,$type);
            } else {
                return array("status" => "error", "message" => "File invalid");
            }
            if ($trigger_id) {
                $path = Storage::putFile('public/' . strtolower($type), $file, 'public');
                $filename = Storage::url($path);
                $trigger->postAddImg($user,$type,$filename);
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
                if ($file->type == "Merchant" || $file->type == "Report") {
                    Cache::forget($file->type . '_' . $file->trigger_id);
                }
                if ($file->type == "Product") {
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
