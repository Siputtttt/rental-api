<?php

namespace App\Http\Controllers;
use App\Models\TipeKendaraan;
use App\Services\Core\ConfigService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Exception;

class TipeKendaraanController extends Controller
{
    public $configService;
    public $access;
    public $config;
    public $data;

    public function __construct(ConfigService $configService, Request $request)
    {

        $this->configService = $configService;
        /* Prepare all configuration cruds */
        $this->config = $configService->prepareSystem('TipeKendaraan', new TipeKendaraan());
        /* Prepare all access to module */
        $this->access = $this->configService->setupAccess($request->user()->group_id);

        $this->data = [];
    }

    public function index(Request $request)
    {
        if (!$this->access->is_view)
            return $this->configService->restricted();

        $this->data['tipe_kendaraan'] = DB::table('m_tipe_kendaraan')->get();

        return response()->json([
            'status' => 1,
            'data' => $this->data,
            'message' => 'Berhasil mengambil data'
        ], 200);
    }

    public function create()
    {
        if (!$this->access->is_add)
            return $this->configService->restricted();

        return response()->json([], 200);
    }
    public function store(Request $request)
    {
        if (!$this->access->is_add || !$this->access->is_edit)
            return $this->configService->restricted();

        $task = $request->input('action_task');
        switch ($task) {
            case 'save':
                return $this->saveData($request);

            case 'delete':
                return $this->deleteData($request);

            default:
                return response()->json(['error' => 'Invalid task'], 400);
        }
    }

    public function saveData($request)
    {
        // validasi inputan
        $validator = Validator::make($request->all(), [
            'tipe' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            // kembalikan response error jika validasi gagal
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = [
            'tipe' => $request->input('tipe'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($request->id != null) {
            // update data
            $tipeKendaraan = TipeKendaraan::find($request->id)->update($data);
        } else {
            // simpan data baru
            $tipeKendaraan = TipeKendaraan::create($data);
        }

        return response()->json([
            'status' => 1,
            'message' => 'Data berhasil disimpan'
        ], 200);
    }

    public function deleteData($request){

        // hapus data
        $tipeKendaraan = TipeKendaraan::find($request->id);
        if (!$tipeKendaraan) {
            return response()->json(['error' => 'Tipe Kendaraan tidak ada'], 404);
        }

        $tipeKendaraan->delete();

        return response()->json([
            'status' => 1,
            'message' => 'Data berhasil dihapus'
        ], 200);
    }

    public function destroy($ids)
    {
        if( !$this->access->is_delete )
            return $this->configService->restricted();

        try {
            TipeKendaraan::deleteData( $ids );
            return ['status'=> 1 ,   'message' => 'Data has been deleted' ,'data' => $ids   ];
        } catch(Exception $e) {

        }
    }
}
