<?php

namespace App\Http\Controllers\Core;

use Illuminate\Http\Request;
use App\Models\Core\Modules;
use Illuminate\Validation\Rule;
use App\Services\Core\DatabaseService;
use App\Http\Controllers\Controller;


class DatabaseController extends Controller
{
    public $sximo;
    public $data;
    public $databaseService;

    public function __construct(DatabaseService $databaseService)
    {
        $this->sximo = (object) config('sximo');
        $this->data = [];
        $this->databaseService = $databaseService;
    }

    public function index(Request $request)
    {
        $this->data['engine'] = $this->databaseService->getEngine();
        $this->data['type_data'] = $this->databaseService->getTypeData();


        $this->data['collation'] = [
            ['value' => 'utf8_general_ci', 'text' => 'utf8_general_ci'],
            ['value' => 'utf8mb4_general_ci', 'text' => 'utf8mb4_general_ci'],
            ['value' => 'latin1_swedish_ci', 'text' => 'latin1_swedish_ci'],
        ];

        $this->data['table'] = Modules::showTable();

        return response()->json([
            'status' => 1,
            'data'  => $this->data,
            'message' => 'Successfully retrieved'
        ], 200);
    }

    public function store(Request $request)
    {
        switch ($request->action_task) {
            case 'query':
                return $this->executeQuery($request);
                break;

            case 'save':
                return $this->saveTable($request);
                break;

            case 'delete':
                return $this->deleteTable($request);
                break;

            case 'editField':
                return $this->saveField($request);
                break;

            case 'deleteField':
                return $this->deleteField($request);
                break;

            default:
                break;
        }
    }

    public function show(Request $request, $task)
    {
        switch ($task) {
            case 'getTable':
                $this->data['columns'] = \DB::select("SHOW COLUMNS FROM {$request->table}");
                $this->data['data_table'] = \DB::select("SELECT * FROM {$request->table} LIMIT 1000");

                $create = \DB::select("SHOW CREATE TABLE {$request->table}");
                $tableStatus = \DB::select("SHOW TABLE STATUS WHERE Name = ?", [$request->table]);

                $this->data['table_info'] = [
                    'table_name'    => $request->table,
                    'create_table'  => $create[0]->{'Create Table'},
                    'engine'        => $tableStatus[0]->Engine ?? null
                ];
                break;

            case 'getColumns':
                $this->data['columns'] = \DB::select("SHOW COLUMNS FROM {$request->table}");
                break;

            default:
                break;
        }

        return response()->json([
            'status' => 1,
            'data'  => $this->data,
            'message' => 'get table successfully'
        ], 200);
    }

    public function saveTable(Request $request)
    {
        $request->validate([
            'table_name' => 'required',
            'engine' => 'required',
            'columns' => 'required|array',
            'columns.*.name' => 'required|string|max:255',
            'columns.*.type' => 'required'
        ]);

        $table = $request->input('table_name');
        $engine = $request->input('engine', 'InnoDB');
        $columns = $request->input('columns', []);

        if (!$table || empty($columns)) {
            return response()->json(['message' => 'Invalid data'], 400);
        }

        $sql = $this->databaseService->createTable($table, $engine, $columns);

        try {
            \DB::statement($sql);
            return response()->json([
                'status' => 1,
                'message' => 'Table created successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }

        $data = $request->all();

        return response()->json([
            'status' => 1,
            'data'  => $sql,
        ], 200);
    }

    public function executeQuery(Request $request)
    {
        $data = $request->all();
        try {
            $this->data['result'] = \DB::select($data['query_text']);

            return response()->json([
                'status' => 1,
                'data'  => $this->data,
                'message' => 'Query executed successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function deleteTable($request)
    {
        try {
            \DB::statement("DROP TABLE IF EXISTS {$request->table_name}");
            return response()->json([
                'status' => 1,
                'message' => 'Table deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function saveField($request)
    {

        $sql = $this->databaseService->fields($request->all());

        try {
            \DB::statement($sql);
            return response()->json([
                'status' => 1,
                'message' => 'Field updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function deleteField($request)
    {
        $data = $request->all();
        $sql = "ALTER TABLE {$data['table']}
            DROP COLUMN {$data['Field']};";
        try {
            \DB::statement($sql);
            return response()->json([
                'status' => 1,
                'message' => 'Field deleted successfully'
            ]);
        } catch (\Exception $e) {
            if ($e->getCode() == 42000) {
                return response()->json([
                    'status' => 0,
                    'message' => 'Minimum field length is 1.'
                ], 500);
            }
            return response()->json([
                'status' => 0,
                'message' => 'Error: ' . $e->getCode()
            ], 500);
        }
    }
}
