<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
// use JWTAuth;

class UserController extends Controller
{
	public function login(Request $request)
	{
		$credentials = $request->only('username', 'password');

		try {
			if (!$token = JWTAuth::attempt($credentials)) {
				return response()->json(['message' => 'Invalid username and password']);
			}
		} catch (JWTException $e) {
			return response()->json(['message' => 'Generate Token Failed']);
		}

		$user = JWTAuth::user();

		return response()->json([
			'success' => true,
			'message' => 'Login berhasil',
			'token' => $token,
			'user' => $user
		]);
	}

	public function getUser()
	{
		$user = JWTAuth::user();
		return response()->json($user);
	}

	public function register(Request $request)
	{
		$validator = Validator::make($request->all(), [
			'name' => 'required',
			'username' => 'required',
			'password' => 'required|string|min:6',
			'role' => 'required',
			'id_outlet' => 'required'
		]);

		if ($validator->fails()) {
			return response()->json($validator->errors());
		}

		$user = new User();
		$user->name 	= $request->name;
		$user->username = $request->username;
		$user->password = Hash::make($request->password);
		$user->role 	= $request->role;
		$user->id_outlet = $request->id_outlet;

		$user->save();

		$token = JWTAuth::fromUser($user);

		$data = User::where('username', '=', $request->username)->first();
		return response()->json([
			'message' => 'Berhasil menambah user',
			'data' => $data
		]);
	}

	public function loginCheck()
	{
		try {
			if (!$user = JWTAuth::parseToken()->authenticate()) {
				return response()->json(['message' => 'Invalid Token']);
			}
		} catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
			return response()->json(['message' => 'Token expired!']);
		} catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
			return response()->json(['message' => 'Invalid Token!']);
		} catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
			return response()->json(['message' => 'Token Absent']);
		}

		return response()->json([
			'success' => true,
			'message' => 'Success']);
	}

	public function logout(Request $request)
	{
		if (JWTAuth::invalidate(JWTAuth::getToken())) {
			return response()->json(['message' => 'You are logged out']);
		} else {
			return response()->json(['message' => 'Failed']);
		}
	}

	public function getAll()
    {
        $data = User::get();
        return response()->json($data);
    }

    public function getById($id)
    {
        $data = User::where('id', '=', $id)->first();
        
        return response()->json($data);
    }
	public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
			'username' => 'required',
			'password' => 'required|string|min:6',
			'role' => 'required',
			'id_outlet' => 'required'
        ]);
    
        if($validator->fails()) {
            return response()->json($validator->errors());
        }

        $user = User::where('id', '=', $id)->first();
		$user->name 	= $request->name;
		$user->username = $request->username;
		$user->password = Hash::make($request->password);
		$user->role 	= $request->role;
		$user->id_outlet = $request->id_outlet;

		$user->save();

        return response()->json(['message' => 'Data member berhasil diubah']);        
    }
	public function delete($id)
    {
        $delete = User::where('id', '=', $id)->delete();

        if($delete) {
            return response()->json([
                'success' => true,
                'message' => 'Data user berhasil dihapus'
            ]);
        } else {
            return response()->json([
                'success' => true,
                'message' => 'Data user gagal dihapus'
            ]);            
        }
    }
}
