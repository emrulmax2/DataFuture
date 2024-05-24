<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\AccCategoryStoreRequest;
use App\Models\AccCategory;
use Illuminate\Http\Request;

class AccCategoryController extends Controller
{
    public function index(){
        return view('pages.settings.accounts.category', [
            'title' => 'Account Settings - London Churchill College',
            'subtitle' => 'Category Settings',
            'breadcrumbs' => [
                ['label' => 'Site Settings', 'href' => route('site.setting')],
                ['label' => 'Account Settings', 'href' => 'javascript:void(0);'],
                ['label' => 'Categories', 'href' => 'javascript:void(0);']
            ],
            'categories' => $this->catTree(0, 0),
            'inflows' => $this->catTreeGraphicInflow(0, 0),
            'outflows' => $this->catTreeGraphicOutflow(0, 1),
            'lavel' => 1
        ]);
    }

    public function filterDropdown(Request $request){
        $trans_type = $request->trans_type;
        $categories = $this->catTree(0, $trans_type);

        $options = '<option value="">Select Parent Category</option>';
        if(!empty($categories)):
            foreach($categories as $cat):
                $options .= '<option value="'.$cat['id'].'">'.$cat['category_name'].'</option>';
            endforeach;
        endif;

        return response()->json(['html' => $options], 200);
    }

    public function catTree($id = 0, $type = 0){
        static $categs = array ();
        static $level = 0;
        $level ++;

        $categories = AccCategory::where('trans_type', $type)->where('parent_id', $id)->orderBy('category_name', 'ASC')->get();

        if($categories):
            foreach ($categories as $cat):
                $categs[$cat['id']]['category_name'] = str_repeat('|&nbsp;&nbsp;&nbsp;', $level-1) . '|__'. $cat['category_name'];
                $categs[$cat['id']]['id'] = $cat['id'];
                $categs[$cat['id']]['status'] = $cat['status'];
    
                $this->catTree($cat['id'], $type);
            endforeach;
        endif;

        $level --;
        return $categs;
    }

    public function store(AccCategoryStoreRequest $request){
        $category = AccCategory::create([
            'category_name' => $request->category_name,
            'trans_type' => (isset($request->trans_type) && !empty($request->trans_type) ? $request->trans_type : 0),
            'parent_id' => (isset($request->parent_id) && !empty($request->parent_id) ? $request->parent_id : 0),
            'status' => (isset($request->status) && $request->status > 0 ? $request->status : 2),
            'audit_status' => (isset($request->audit_status) && $request->audit_status > 0 ? $request->audit_status : '0'),
            'created_by' => auth()->user()->id
        ]);
        return response()->json(['res' => 'Category successfully inserted.'], 200);
    }

    public function catTreeGraphicInflow($parent = 0, $type = 0){
        static $categs = array ();
        static $level = 0;
        $level ++;

        $categories = AccCategory::where('trans_type', $type)->where('parent_id', $parent)->orderBy('category_name', 'ASC')->get();
        if($categories):
            foreach ($categories as $cat):
                $status = ($cat['status'] == 1) ? 'Active' : 'Inactive';
                $audit_status = ($cat['audit_status'] == 1) ? 'Active' : 'In Active';

                $categs[$cat['id']]['graphic'] = '<tr>';
                    $categs[$cat['id']]['graphic'] .= '<td class="firstColumnLevel_'.($level).'">';
                        $categs[$cat['id']]['graphic'] .= '<span>'.$cat['category_name'].'</span>';
                    $categs[$cat['id']]['graphic'] .= '</td>';
                    $categs[$cat['id']]['graphic'] .= '<td>'.$audit_status.'</td>';
                    $categs[$cat['id']]['graphic'] .= '<td>'.$status.'</td>';
                    $categs[$cat['id']]['graphic'] .= '<td class="text-right">';
                        $categs[$cat['id']]['graphic'] .= '<button data-id="'.$cat['id'].'" data-tw-toggle="modal" data-tw-target="#editCategoryModal" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-6 h-6 mr-1"><i data-lucide="Pencil" class="w-3 h-3"></i></button>';
                        $categs[$cat['id']]['graphic'] .= '<button data-id="'.$cat['id'].'" type="button" class="delete_btn btn-rounded btn btn-danger text-white p-0 w-6 h-6"><i data-lucide="trash-2" class="w-3 h-3"></i></button>';
                    $categs[$cat['id']]['graphic'] .= '</td>';
                $categs[$cat['id']]['graphic'] .= '</tr>';
    
                $this->catTreeGraphicInflow($cat['id'], $type);
            endforeach;
        endif;

        $level --;
        return $categs;
    }

    public function catTreeGraphicOutflow($parent = 0, $type = 0){
        static $categs = array ();
        static $level = 0;
        $level ++;

        $categories = AccCategory::where('trans_type', $type)->where('parent_id', $parent)->orderBy('category_name', 'ASC')->get();
        if($categories):
            foreach ($categories as $cat):
                $status = ($cat['status'] == 1) ? 'Active' : 'Inactive';
                $audit_status = ($cat['audit_status'] == 1) ? 'Active' : 'In Active';

                $categs[$cat['id']]['graphic'] = '<tr>';
                    $categs[$cat['id']]['graphic'] .= '<td class="firstColumnLevel_'.($level).'">';
                        $categs[$cat['id']]['graphic'] .= '<span>'.$cat['category_name'].'</span>';
                    $categs[$cat['id']]['graphic'] .= '</td>';
                    $categs[$cat['id']]['graphic'] .= '<td>'.$audit_status.'</td>';
                    $categs[$cat['id']]['graphic'] .= '<td>'.$status.'</td>';
                    $categs[$cat['id']]['graphic'] .= '<td class="text-right">';
                        $categs[$cat['id']]['graphic'] .= '<button data-id="'.$cat['id'].'" data-tw-toggle="modal" data-tw-target="#editCategoryModal" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-6 h-6 mr-1"><i data-lucide="Pencil" class="w-3 h-3"></i></button>';
                        $categs[$cat['id']]['graphic'] .= '<button data-id="'.$cat['id'].'" type="button" class="delete_btn btn-rounded btn btn-danger text-white p-0 w-6 h-6"><i data-lucide="trash-2" class="w-3 h-3"></i></button>';
                    $categs[$cat['id']]['graphic'] .= '</td>';
                $categs[$cat['id']]['graphic'] .= '</tr>';
    
                $this->catTreeGraphicOutflow($cat['id'], $type);
            endforeach;
        endif;

        $level --;
        return $categs;
    }

    public function edit(Request $request){
        $row_id = $request->row_id;
        $category = AccCategory::find($row_id);
        $trans_type = $category->trans_type;

        $categoryOptions = $this->catTree(0, $trans_type);

        $options = '<option value="">Select Parent Category</option>';
        if(!empty($categoryOptions)):
            foreach($categoryOptions as $cat):
                $options .= '<option value="'.$cat['id'].'">'.$cat['category_name'].'</option>';
            endforeach;
        endif;

        return response()->json(['row' => $category, 'options' => $options], 200);
    }

    public function update(AccCategoryStoreRequest $request){
        $id = $request->id;
        $category = AccCategory::where('id', $id)->update([
            'category_name' => $request->category_name,
            'trans_type' => (isset($request->trans_type) && !empty($request->trans_type) ? $request->trans_type : 0),
            'parent_id' => (isset($request->parent_id) && !empty($request->parent_id) ? $request->parent_id : 0),
            'status' => (isset($request->status) && $request->status > 0 ? $request->status : 2),
            'audit_status' => (isset($request->audit_status) && $request->audit_status > 0 ? $request->audit_status : '0'),
            'updated_by' => auth()->user()->id
        ]);

        return response()->json(['res' => 'Category successfully updated.'], 200);
    }

    public function destroy($id){
        $data = AccCategory::find($id)->delete();
        return response()->json($data);
    }
}
