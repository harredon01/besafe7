<?php

namespace App\Services;

use Validator;
use App\Models\Document;
use App\Models\User;

class EditDocument {

    public function saveOrCreate(User $user, array $data) {
        if (array_key_exists('id', $data)) {
            if ($data['id']) {
                $document = Document::where('id',$data['id'])->with(['files','user','author'])->first();
                if ($document) {
                    if ($document->is_signed) {
                        return ["status" => "error", "message" => "forbidden"];
                    } else {
                        if ($document->author_id == $user->id) {
                            $document->fill($data);
                            $document->user_id = $data['user_id'];
                            $document->save();
                            return ["status" => "success", "message" => "document edited", "document" => $document];
                        } else {
                            return ["status" => "error", "message" => "forbidden"];
                        }
                    }
                } else {
                    return ["status" => "error", "message" => "not_found"];
                }
            }
        }
        $user->id;
        $document = new Document;
        if (array_key_exists('files', $data)) {
            unset($data['files']);
        }
        if (array_key_exists('signatures', $data)) {
            unset($data['signatures']);
        }
        //$data['body'] = json_encode($data['body']);
        $document->fill($data);
        $document->author_id =$user->id;
        //dd($document);
        $document->save();
        return ["status" => "success", "message" => "document created", "document" => $document];
    }

}
