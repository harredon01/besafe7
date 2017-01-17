<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Services\EditFile;
use Illuminate\Contracts\Auth\Guard;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FileApiController extends Controller {

    /**
     * The edit alerts implementation.
     *
     */
    protected $editFile;

    /**
     * The edit alerts implementation.
     *
     */
    protected $auth;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(Guard $auth, EditFile $editFile) {
        $this->editFile = $editFile;
        $this->auth = $auth;
        $this->middleware('jwt.auth');
    }

    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function postFile(Request $request) {
        $user = $this->auth->user();
        $validator = $this->editFile->validatorFile($request->all());
        if ($validator->fails()) {
            return response()->json(array("status" => "error", "message" => "validation failed"), 401);
        }
        return response()->json($this->editFile->postFile($user, $request));
    }
    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function delete($file_id) {
        $user = $this->auth->user();
        return response()->json($this->editFile->delete($user, $file_id));
    }

}
