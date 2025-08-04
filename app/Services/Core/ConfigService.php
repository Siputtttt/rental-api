<?php

namespace App\Services\Core;

use App\Helpers\MainHelpers;
use App\Models\Core\Modules;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class ConfigService
{
    public $configuration;
    public $tables;
    public $filled;
    public $items;
    public $model;
    public $rows;
    public $option;
    public $validation = [];
    public $primaryKey;
    public $access;
    public $download;
    public $formatter;


    public function prepareSystem($module, $model)
    {
        $this->model = $model;
        $this->primaryKey = $this->model->primaryKey;
        $this->configuration = Modules::detail($module);
        return (object) [
            'columns'    => $this->setupTables(),
            'forms'     => $this->setupForms(),
            'field'     => $this->filled,
            'option'    => $this->option,
            'items'     => $this->items,
            'setting'   => $this->configuration->setting
        ];
    }
    public function setupTables()
    {
        $tables = [];
        $download = [];
        $i = 0;
        foreach ($this->configuration->tables as $table) {
            if ($table->view == '1') {
                $i++;
                $tables[] = [
                    'key'   => $table->field,
                    'label' => $table->label,
                    'sortable' => ($table->sortable == '1' ? true : false),
                    'variant'    => '',
                    'align' => $table->align,
                    '_showDetails' => true,
                    'format_as' => ($table->format_as != '' ? $table->format_as : '')
                ];
                $download[] = $table->label;
                $this->filled[$table->field] = $table->field;
                if ($table->format_as != '') {
                    $this->formatter[$table->field] = [
                        'format_as'     => $table->format_as,
                        'format_value'  => $table->format_value,
                    ];
                }
            }
        }
        $this->download = $download;
        return $this->tables = $tables;
    }

    public function setupForms()
    {
        $forms = [];
        foreach ($this->configuration->forms as $form) {
            if ($form->view == '1') {
                $forms[$form->field] = [
                    'key'   => $form->field,
                    'label' => $form->label,
                    'type' => $form->type,
                    'format' => $form->option,
                    'size'  =>  $form->size,
                    'search'  =>  $form->search,
                    'validation' => $form->required
                ];
                $this->items[$form->field] = '';
                if (in_array($form->type, ['radio', 'select', 'checkbox']))
                    $this->setupOption($form);
                if ($form->required != '')
                    $this->validation[$form->field] = $form->required;
            }
        }
        return $this->forms = $forms;
    }
    public function setupSearch($request)
    {
        $textSearch = '';
        if ($request->s != '') {
            $toSearch = explode("|", $request->s);
            foreach ($toSearch as $inSearch) {
                //$textSearch .= $inSearch ;
                if ($inSearch != '') {
                    $search = explode(":", $inSearch);
                    if (count($search) >= 2) {
                        if ($search[1] == 'LIKE') {
                            $textSearch .= " AND " . $search[0] . " LIKE '%" . $search[2] . "%%' ";
                        } else {
                            $textSearch .= " AND " . $search[0] . " " . $search[1] . "'" . $search[2] . "' ";
                        }
                    }
                }
            }
        }
        return $textSearch;
    }

    public function setupData($search = '')
    {
        $statment = $this->model->stateSelect() .
            $this->model->stateWhere()  .
            $search .
            $this->model->stateGroup();
        $rows =  DB::select($statment);
        $data = [];
        foreach ($rows as $row) {
            $filter = [];
            foreach ($this->filled as $field) {
                if (is_array($this->formatter) && array_key_exists($field, $this->formatter)) {
                    $filter[$field] = $this->setupFormatter($this->formatter[$field],  $row->{$field});
                } else {
                    $filter[$field] = $row->{$field};
                }
            }
            $data[] = $filter;
        }
        return $this->rows = $data;
    }
    public function setupOption($form)
    {
        switch ($form->type) {
            case 'radio':
                $radios = explode(',', $form->option);
                $option = [];
                foreach ($radios as $radio) {
                    $item = explode(':', $radio);
                    $option[] = ['value' => $item[0], 'text' => $item[1]];
                }
                $this->option[$form->field] = $option;
                break;

            case 'checkbox':
                $radios = explode(',', $form->option);
                $option = [];
                foreach ($radios as $radio) {
                    $item = explode(':', $radio);
                    $option[] = ['value' => $item[0], 'text' => $item[1]];
                }
                $this->option[$form->field] = $option;
                break;

            case 'select':
                $select = explode('|', $form->option);
                // $this->option[$form->field] =  $select ;
                if (count($select) == 2) {
                    if ($select[0] == 'database') {
                        $item = explode(":", $select[1]);
                        $option[] =  ['value' => '', 'text' => 'Please Select'];;
                        $rows = \DB::table($item[0])->get();
                        foreach ($rows as $row) {
                            $option[] = ['value' => $row->{$item[1]}, 'text' =>  $row->{$item[2]}];
                        }
                        $this->option[$form->field] = $option;
                    }
                    if ($select[0] == 'custom') {
                        $rows = explode(",", $select[0]);
                        $option[] = ['value' => '', 'text' => 'Please Select'];
                        foreach ($rows as $row) {
                            $item = explode(":", $row);
                            $option[] = ['value' => $item[0], 'text' =>  $item[1]];
                        }
                        $this->option[$form->field] = $option;
                    }
                }
                break;
        }
    }
    // public function setupFormatter($format, $value)
    // {
    //     $result = '';
    //     switch ($format['format_as']) {
    //         case 'date':
    //             $result = date($format['format_value'], $value);
    //             break;

    //         case 'checkbox':
    //             $radios = explode(',', $form->option);
    //             $option = [];
    //             foreach ($radios as $radio) {
    //                 $item = explode(':', $radio);
    //                 $option[] = ['value' => $item[0], 'text' => $item[1]];
    //             }
    //             $this->option[$form->field] = $option;
    //             break;

    //         case 'select':
    //             $select = explode('|', $form->option);
    //             // $this->option[$form->field] =  $select ;
    //             if (count($select) == 2) {
    //                 if ($select[0] == 'database') {
    //                     $item = explode(":", $select[1]);
    //                     $option[] = ['value' => '', 'text' => 'Please Select'];
    //                     $rows = \DB::table($item[0])->get();
    //                     foreach ($rows as $row) {
    //                         $option[] = ['value' => $row->{$item[1]}, 'text' =>  $row->{$item[2]}];
    //                     }
    //                     $this->option[$form->field] = $option;
    //                 }
    //                 if ($select[0] == 'custom') {
    //                     $rows = explode(",", $select[0]);
    //                     $option[] = ['value' => '', 'text' => 'Please Select'];
    //                     foreach ($rows as $row) {
    //                         $item = explode(":", $row);
    //                         $option[] = ['value' => $item[0], 'text' =>  $item[1]];
    //                     }
    //                     $this->option[$form->field] = $option;
    //                 }
    //             }
    //             break;
    //     }
    // }
    public function setupFormatter($format, $value)
    {
        $result = '';
        switch ($format['format_as']) {
            case 'date':
                $result = date($format['format_value'], $value);
                break;

            case 'image':
                $images = asset('storage/uploads/no-image.png');
                if ($value != '') {

                    $images = asset('storage' . $format['format_value'] . '/' . $value);
                }
                $result = '<div class="avatar"> <img src="' . $images . '" class="rounded-5" /></div>';
                // $result  =  'uploads/'. $value  ;
                break;

            case 'function':
                //  return  $format['format_value'];
                $c = explode(":",  $format['format_value']);
                //return $c[0];//implode(",", $c);
                if (class_exists("App\Helpers\\" . $c[0], false)) {
                    $args = explode('-', $c[2]);
                    if (count($args) >= 2) {
                        $result = call_user_func(array($c[0], $c[1]), $args);
                    } else {
                        $result = call_user_func(array("App\Helpers\\" . $c[0], $c[1]), $value);
                    }
                } else {
                    $result = 'Class Doest Not Exists';
                }
                break;

            default:
                $result = $value;
                break;
        }
        return $result;
    }
    public function setupEdit($id)
    {
        $statment = $this->model->stateSelect() .
            $this->model->stateWhere()  .
            " AND {$this->model->table}.{$this->model->primaryKey}='" . $id . "'  LIMIT 1 ";
        $rows =  DB::select($statment);
        $data = [];
        foreach ($rows as $row) {
            $fields = [];
            foreach ($this->items as $key => $val) {
                if (isset($row->{$key})) {
                    $fields[$key] = $row->{$key};
                    if (in_array($this->forms[$key]['type'], ['image', 'file'])) {
                        $fields[$key] = asset('storage' . $this->forms[$key]['format'] . '/' . $row->{$key});
                    }
                }
            }
            $data  = $fields;
        }
        return $data;
    }
    public function validate()
    {
        $toValidate = [];
        foreach ($this->validation as $key => $val) {
            $toValidate[$key] =  $val;
        }
        return $toValidate;
    }
    public function validatePosts($request)
    {
        $posts = $request->all();
        $toPosts = [];
        foreach ($this->forms as $form) {
            if (array_key_exists($form['key'], $posts)) {
                // check if uploads as images 
                if ($form['type'] == 'image') {
                    $base64Image = $posts[$form['key']];
                    if (preg_match('/^data:image\/(\w+);base64,/', $base64Image, $type)) {
                        $image = substr($base64Image, strpos($base64Image, ',') + 1);
                        $image = base64_decode($image);
                        $extension = strtolower($type[1]);

                        if (in_array($extension, ['jpg', 'jpeg', 'png'])) {
                            $fileName = uniqid() . '.' . $extension;
                            $filePath = $form['format'] . '/' . $fileName;
                            Storage::disk('public')->put($filePath, $image);
                            $toPosts[$form['key']] =  $fileName;
                        }
                    }
                } else {
                    $toPosts[$form['key']] =  $posts[$form['key']];
                }
            }
        }
        // unset( $toPosts[ $this->primaryKey]);
        return $toPosts;
    }

    public function setupAccess($group_id)
    {
        return (object) Modules::permission($this->configuration->module_id, $group_id);
    }

    public function restricted()
    {
        return response()->json([
            'message'   => 'You do not have access to this page',
            'status'    => 1
        ], 403);
    }

    public function prepareDownload($array)
    {
        //$arra = []
    }
}
