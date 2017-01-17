<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\AuthenticateUser;
use Illuminate\Http\Request;

class SocialAuthController extends Controller {

	public function login(AuthenticateUser $authenticateUser, Request $request, $provider = null) {
		return $authenticateUser->execute($request->all(), $this, $provider);
	}

}
