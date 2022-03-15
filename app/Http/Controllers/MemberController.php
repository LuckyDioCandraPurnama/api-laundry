<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Member;
// use JWTAuth;
use Tymon\JWTAuth\Facades\JWTAuth;


class MemberController extends Controller
{
    public $user;
    
    public function __construct()
    {
        $this->user = JWTAuth::parseToken()->authenticate();   
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string',
            'alamat' => 'required|string',
            'jk' => 'required|string',
            'telp' => 'required|string',
        ]);

        if($validator->fails()) {
            return response()->json($validator->errors());
        }
        
        $member = new Member();
        $member->nama = $request->nama;
        $member->alamat = $request->alamat;
        $member->jk = $request->jk;
        $member->telp = $request->telp;
        
        $member->save();
        
        $data = Member::where('id', '=', $member->id)->first();

        return response()->json([
            'success' => true,
            'message' => 'Data member berhasil ditambahkan',
            'data' => $data
        ]);
    }
    
    public function getAll()
    {
        $data = Member::get();        
        return response()->json($data);
    }
    
    public function getById($id)
    {
        $data = Member::where('id', '=', $id)->first();        
        return response()->json($data);
    }
    
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string',
            'alamat' => 'required|string',
            'jk' => 'required|string',
            'telp' => 'required|string',
        ]);
    
        if($validator->fails()) {
            return response()->json($validator->errors());
        }

        $member = Member::where('id', '=', $id)->first();
        $member->nama = $request->nama;
        $member->alamat = $request->alamat;
        $member->jk = $request->jk;
        $member->telp = $request->telp;
        
        $member->save();

        return response()->json(['message' => 'Data member berhasil diubah']);        
    }

    public function delete($id)
    {
        $delete = Member::where('id', '=', $id)->delete();

        if($delete) {
            return response()->json([
                'success' => true,
                'message' => 'Data member berhasil dihapus'
            ]);
        } else {
            return response()->json([
                'success' => true,
                'message' => 'Data member gagal dihapus'
            ]);            
        }
    }
}
