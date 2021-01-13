<?php

namespace App\Http\Controllers;

use App\Models\Merchant;
use App\Models\Report;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Services\EditProduct;
use App\Services\Security;
use App\Services\CleanSearch;
use View;
use DB;
class SitemapController extends Controller {

    /**
     * The Guard implementation.
     *
     * @var \Illuminate\Contracts\Auth\Guard
     */
    protected $auth;

    /**
     * The edit alerts implementation.
     *
     */
    protected $editProduct;
    /**
     * The edit alerts implementation.
     *
     */
    protected $security;

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
    public function __construct(EditProduct $editProduct, CleanSearch $cleanSearch, Security $security) {
        $this->security = $security;
        $this->editProduct = $editProduct;
        $this->cleanSearch = $cleanSearch;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request) {
        $merchants = Merchant::all();
        $reports = Report::all();
        $cats = Category::where("level",0)->with("children")->get();
        $categories = [];
        foreach ($merchants as $value) {
            $res = $this->editProduct->getActiveCategoriesMerchant($value->id);
            $value->cats = $res['data'];
        }
        foreach ($cats as $value) {
            $categories = array_merge($categories, $value->children->toArray());
        }
        return response()->view('welcome2', ["merchants"=>$merchants,"categories"=>$categories,"reports"=>$reports])
          ->header('Content-Type', 'text/xml');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create() {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {
        $user = $request->user();
        return response()->json($this->editDocument->saveOrCreate($user, $request->all()));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Document  $document
     * @return \Illuminate\Http\Response
     */
    public function show(Document $document, Request $request) {
        $user = $request->user();
        if ($document->author_id == $user->id || $document->user_id == $user->id) {
            $document->signatures;
            $document->user;
            $document->author;
            $document->files;
            return response()->json(['status' => "success", "message" => "", "document" => $document]);
        } else {
            return response()->json(['status' => "error", "message" => "forbidden"]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Document  $document
     * @return \Illuminate\Http\Response
     */
    public function edit(Document $document) {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Document  $document
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Document $document) {
        $user = $request->user();
        if ($document->is_signed) {
            return response()->json(["status" => "error", "message" => "forbidden"]);
        } else {
            if ($document->author_id == $user->id) {
                $document->fill($request->all());
                $document->save();
                return response()->json(["status" => "success", "message" => "document edited", "document" => $document]);
            } else {
                return response()->json(["status" => "error", "message" => "forbidden"]);
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Document  $document
     * @return \Illuminate\Http\Response
     */
    public function destroy(Document $document,Request $request) {
        $user = $request->user();
        if ($document->is_signed) {
            return response()->json(["status" => "error", "message" => "forbidden"]);
        } else {
            if ($document->author_id == $user->id || $document->user_id == $user->id) {
                $document->delete();
                return response()->json(["status" => "success", "message" => "document deleted", "document" => $document]);
            } else {
                return response()->json(["status" => "error", "message" => "forbidden"]);
            }
        }
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Document  $document
     * @return \Illuminate\Http\Response
     */
    public function signDocument(Document $document,Request $request) {
        $user = $request->user();
        $data = $request->all(['private_key']);
        return response()->json($this->security->sign($user, $document, $data['private_key']));
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Document  $document
     * @return \Illuminate\Http\Response
     */
    public function verifySignatures(Document $document) {
        return response()->json($this->security->validate($document));
    }

}
