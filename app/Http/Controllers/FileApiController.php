<?php

namespace App\Http\Controllers;

use App\Services\EditFile;
use App\Services\CleanSearch;
use Unlu\Laravel\Api\QueryBuilder;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FileM;

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
    protected $cleanSearch;


    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(EditFile $editFile,CleanSearch $cleanSearch ) {
        $this->editFile = $editFile;
        $this->cleanSearch = $cleanSearch;
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
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getFiles(Request $request) {
        $user = $request->user();
        $request2 = $this->cleanSearch->handleFiles($user,$request);
        if ($request2) {
            $queryBuilder = new QueryBuilder(new FileM, $request2);
            $result = $queryBuilder->build()->paginate();
            return response()->json([
                        'data' => $result->items(),
                        "total" => $result->total(),
                        "per_page" => $result->perPage(),
                        "page" => $result->currentPage(),
                        "last_page" => $result->lastPage(),
            ]);
        }
        return response()->json([
                    'status' => "error",
                    'message' => "illegal parameter"
                        ], 403);
    }

}
