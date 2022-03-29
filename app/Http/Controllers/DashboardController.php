<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;

use App\Models\Member;
use App\Models\Transaksi;
use App\Models\User;
use App\Models\Outlet;

class DashboardController extends Controller
{
    public $user;
    public function __construct()
    {
        $this->user = JWTAuth::parseToken()->authenticate();
    }
    public function index()
    {
        $id_user = $this->user->id;
        $data_user = User::where('id', '=', $id_user)->first();

        $member = Member::count();
        $baru = Transaksi::where('status', '=' , 'Baru' )->count();        
        $proses = Transaksi::where('status', '=' , 'Proses' )->count();
        $selesai = Transaksi::where('status', '=' , 'Selesai' )->count();
        $pendapatan = Transaksi::where('dibayar' , '=' , 'dibayar')->sum('total');
        $utang = Transaksi::where('dibayar' , '=' , 'belum dibayar')->count();
        
        $member_outlet = DB::table('transaksi')->join('member', 'transaksi.id_member', '=', 'member.id')
                                      ->join('users', 'transaksi.id_user', '=', 'users.id')
                                      ->select('transaksi.id', 'member.nama','transaksi.total', 'users.name' )
                                      ->where('users.id_outlet', $data_user->id_outlet)
                                      ->count();

        $baru_outlet = DB::table('transaksi')->join('member', 'transaksi.id_member', '=', 'member.id')
                                      ->join('users', 'transaksi.id_user', '=', 'users.id')
                                      ->select('transaksi.id', 'member.nama','transaksi.total', 'users.name' )
                                      ->where('users.id_outlet', $data_user->id_outlet)
                                      ->where('status', '=' , 'Baru' )->count(); 

        $proses_outlet = DB::table('transaksi')->join('member', 'transaksi.id_member', '=', 'member.id')
                                      ->join('users', 'transaksi.id_user', '=', 'users.id')
                                      ->select('transaksi.id', 'member.nama','transaksi.total', 'users.name' )
                                      ->where('users.id_outlet', $data_user->id_outlet)
                                      ->where('status', '=' , 'Proses' )->count();

        $selesai_outlet = DB::table('transaksi')->join('member', 'transaksi.id_member', '=', 'member.id')
                                      ->join('users', 'transaksi.id_user', '=', 'users.id')
                                      ->select('transaksi.id', 'member.nama','transaksi.total', 'users.name' )
                                      ->where('users.id_outlet', $data_user->id_outlet)
                                      ->where('status', '=' , 'Selesai' )->count();
                                      
        $pendapatan_outlet = DB::table('transaksi')->join('member', 'transaksi.id_member', '=', 'member.id')
                                      ->join('users', 'transaksi.id_user', '=', 'users.id')
                                      ->select('transaksi.id', 'member.nama','transaksi.total', 'users.name' )
                                      ->where('users.id_outlet', $data_user->id_outlet)
                                      ->where('dibayar' , '=' , 'dibayar')->sum('total');

        $utang_outlet = DB::table('transaksi')->join('member', 'transaksi.id_member', '=', 'member.id')
                                      ->join('users', 'transaksi.id_user', '=', 'users.id')
                                      ->select('transaksi.id', 'member.nama','transaksi.total', 'users.name' )
                                      ->where('users.id_outlet', $data_user->id_outlet)
                                      ->where('dibayar' , '=' , 'belum dibayar')->count();

        return response()->json([
            'member' => $member,
            'baru' => $baru,
            'proses' => $proses,
            'selesai' => $selesai,
            'pendapatan' => $pendapatan,
            'utang' => $utang,

            'member_outlet' => $member_outlet,
            'baru_outlet' => $baru_outlet,
            'proses_outlet' => $proses_outlet,
            'selesai_outlet' => $selesai_outlet,
            'pendapatan_outlet' => $pendapatan_outlet,
            'utang_outlet' => $utang_outlet,
        ]);
    }
}
