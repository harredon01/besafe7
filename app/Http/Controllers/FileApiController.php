<?php

namespace App\Http\Controllers;

use App\Services\EditFile;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FileApiController extends Controller {

    /**
     * The edit alerts implementation.
     *
     */
    protected $editFile;


    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(EditFile $editFile) {
        $this->editFile = $editFile;
        $this->middleware('auth:api');
    }

    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function postFile(Request $request) {
        $user = $request->user();
        $validator = $this->editFile->validatorFile($request->all());
        if ($validator->fails()) {
            return response()->json(array("status" => "error", "message" => $validator->getMessageBag()), 400);
        }
        return response()->json($this->editFile->postFile($user, $request));
    }
    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function delete($file_id,Request $request) {
        $user = $request->user();
        return response()->json($this->editFile->delete($user, $file_id));
    }

}
