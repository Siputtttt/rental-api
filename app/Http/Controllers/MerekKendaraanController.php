<?php

namespace App\Http\Controllers;
use Illuminate\Support\Str;
use App\Models\MerekKendaraan;
use App\Services\Core\ConfigService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;


use function Pest\Laravel\json;

class MerekKendaraanController extends Controller
{
    public $configService;
    public $access;
    public $config;

    private $data;

    public function __construct(ConfigService $configService, Request $request)
    {

        $this->configService = $configService;
        /* Prepare all configuration cruds */
        $this->config = $configService->prepareSystem('MerekKendaraan', new MerekKendaraan());
        /* Prepare all access to module */
        $this->access = $this->configService->setupAccess($request->user()->group_id);

        $this->data = [];
    }

    public function index(Request $request)
    {
        if (!$this->access->is_view)
            return $this->configService->restricted();

        $this->data['merek_kendaraan'] = MerekKendaraan::getDataMerek();

        return response()->json([
            'status' => 1,
            'message' => 'successfull to get data',
            'data' => $this->data
        ], 200);
    }

    public function store(Request $request)
    {
        $task = $request->input('action_task');
        switch ($task) {
            case 'save':
                return $this->save($request);

            case 'delete':
                return $this->delete($request);

            default:
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid Task',
                ]);
        }
    }

    public function save($request)
    {

        $validation = Validator::make($request->all(), [
            'nama' => 'required',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($validation->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $oldData = null;
            if ($request->id) {
                $oldData = DB::table('m_merek_kendaraan')->where('id', $request->id)->first();
            }
            $filename = null;
            if ($request->hasFile('gambar')) {
                if ($oldData && $oldData->gambar && Storage::disk('public')->exists('uploads/merek/' . $oldData->gambar)) {
                    Storage::disk('public')->delete('uploads/merek/' . $oldData->gambar);
                }

                $extension = $request->file('gambar')->getClientOriginalExtension();
                $filename = $request->nama . '.' . $extension;
                $request->file('gambar')->storeAs('uploads/merek', $filename, 'public');
            } else {
                $filename = $oldData->gambar;
            }

            $data = [
                'nama' => $request->nama,
                'gambar' => $filename,
            ];

            if ($request->id) {
                DB::table('m_merek_kendaraan')->where('id', $request->id)->update($data);
            } else {
                DB::table('m_merek_kendaraan')->insert($data);
            }

            return response()->json([
                'status' => 1,
                'message' => 'successfull to get data'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function delete($request)
    {
        $id = $request->id;
        $data = DB::table('m_merek_kendaraan')->where('id', $id)->first();

        if ($data) {
            if ($data->gambar && Storage::disk('public')->exists('uploads/merek/' . $data->gambar)) {
                Storage::disk('public')->delete('uploads/merek/' . $data->gambar);
            }
            DB::table('m_merek_kendaraan')->where('id', $id)->delete();
        }

        return response()->json([
            'status' => 1,
            'message' => 'successfull to delete data'
        ], 200);
    }
}
