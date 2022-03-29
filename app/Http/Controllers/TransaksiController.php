<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Outlet;
use App\Models\Transaksi;
use App\Models\DetilTransaksi;
use Carbon\Carbon;
// use JWTAuth;
use Tymon\JWTAuth\Facades\JWTAuth;



class TransaksiController extends Controller
{
    public $user;

    public function __construct()
    {
        $this->user = JWTAuth::parseToken()->authenticate();
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_member' => 'required',
        ]);

        if($validator->fails()) {
            return response()->json($validator->errors());
        }

        $transaksi = new Transaksi();
        $transaksi->id_member = $request->id_member;
        $transaksi->tgl_order = Carbon::now()->format('Y-m-d');
        $transaksi->batas_waktu = Carbon::now()->addDays(3)->format('Y-m-d');
        $transaksi->status = 'baru';
        $transaksi->dibayar = 'belum dibayar';
        $transaksi->id_user = $this->user->id;
        // $transaksi->id_user = $request->id_user;

        $transaksi->save();

        $data = Transaksi::where('id', '=', $transaksi->id)->first();

        return response()->json(['message' => 'Data transaksi berhasil ditambahkan', 'data' => $data]);
    }

    public function getAll()
    {
        $id_user = $this->user->id;
        $data_user = User::where('id', '=', $id_user)->first();
        
        $data = DB::table('transaksi')->join('member', 'transaksi.id_member', '=', 'member.id')
                    ->join('users', 'transaksi.id_user', 'users.id')
                    ->select('transaksi.*', 'member.nama','users.name')
                    ->where('users.id_outlet', $data_user->id_outlet)
                    ->get();
        return response()->json($data);
    }
    
    public function update($id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_member' => 'required'
        ]);

        if($validator->fails()) {
            return response()->json($validator->errors());
        }

        $transaksi = Transaksi::where('id', '=', $id)->first();
        $transaksi->id_member = $request->id_member;

        $transaksi->save();

        return response()->json(['message' => 'Transaksi berhasil diubah']);
    }

    public function getById($id)
    {
        $data = Transaksi::where('id', '=', $id)->first();  
        $data = DB::table('transaksi')->join('member', 'transaksi.id_member', '=', 'member.id')      
                                      ->select('transaksi.*', 'member.nama')
                                      ->where('transaksi.id', '=', $id)
                                      ->first();
            return response()->json($data);
    }

    public function changeStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required'
        ]);
        
        if($validator->fails()) {
            return response()->json($validator->errors());
        }
        
        $transaksi = Transaksi::where('id', '=', $id)->first();
        $transaksi->status = $request->status;
        
        $transaksi->save();
        
        return response()->json(['message' => 'Status berhasil diubah', $transaksi]);
    }
    
    public function bayar($id)
    {
        $transaksi = Transaksi::where('id', '=', $id)->first();
        $total = DetilTransaksi::where('id_transaksi', $id)->sum('subtotal');

        $transaksi->tgl_bayar = Carbon::now();
        $transaksi->status = "Diambil";
        $transaksi->dibayar = "dibayar";
        $transaksi->total = $total;        
        
        $transaksi->save();
        
        return response()->json(['message' => 'Pembayaran berhasil']);
    }

    public function report(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tahun' => 'required',
            'bulan' => 'required'
        ]);
        
        if($validator->fails()) {
            return response()->json($validator->errors());
        }

        $tahun = $request->tahun;
        $bulan = $request->bulan;
        
        $data = DB::table('transaksi')->join('member', 'transaksi.id_member', '=', 'member.id')
                    ->select('transaksi.id','transaksi.tgl_order','transaksi.tgl_bayar','transaksi.total', 'member.nama')
                    ->whereYear('tgl_order', '=' , $tahun)
                    ->whereMonth('tgl_order', '=', $bulan)
                    ->get();

        return response()->json($data);
    }
    public function reportOutlet(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tahun' => 'required',
            'bulan' => 'required'
        ]);
        
        if($validator->fails()) {
            return response()->json($validator->errors());
        }

        $tahun = $request->tahun;
        $bulan = $request->bulan;

        $id_user = $this->user->id;
        $data_user = User::where('id', '=', $id_user)->first();
        
        $data = DB::table('transaksi')->join('member', 'transaksi.id_member', '=', 'member.id')
                                      ->join('users', 'transaksi.id_user', '=', 'users.id')
                                      ->select('transaksi.id', 'member.nama' , 'transaksi.tgl_order','transaksi.tgl_bayar','transaksi.total', 'users.name' )
                                      ->where('users.id_outlet', $data_user->id_outlet)
                                      ->whereYear('transaksi.tgl_order', '=' , $tahun)
                                      ->whereMonth('transaksi.tgl_order', '=', $bulan)
                                      ->get();

        return response()->json($data);
    }


    public function reportOutlet2(Request $request)
    {     
        $validator = Validator::make($request->all(), [
            'tahun' => 'required',
            'bulan' => 'required',
            'id_outlet' => 'required',
        ]);
        
        if($validator->fails()) {
            return response()->json($validator->errors());
        }

        $tahun = $request->tahun;
        $bulan = $request->bulan;
        $transaksi = new Outlet();
        $transaksi->id_outlet = $request->id_outlet;

        // $data = Transaksi::where('id', '=', $id_user)->first();
        $data = DB::table('transaksi')->join('member', 'transaksi.id_member', '=', 'member.id')
                    ->join('users', 'transaksi.id_user', '=', 'users.id')
                    ->join('outlet', 'users.id_outlet', '=', 'outlet.id')
                    ->select('transaksi.id','transaksi.tgl_order','transaksi.tgl_bayar','transaksi.total', 'transaksi.id_user','member.nama','outlet.nama_outlet')
                    // ->where('id_user', '=', $transaksi)
                    ->where('outlet.id', '=', $transaksi->id_outlet)
                    ->get();

        // return response()->json($data);
        return response()->json([
            'success' => true,
            'message' => 'Berhasil Data',
            'data' => $data
        ]);
    }


}
