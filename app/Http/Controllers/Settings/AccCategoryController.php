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
            'lavel' => 1,
            
            'inflow_parents' => AccCategory::where('trans_type', 0)->where('status', 1)->where('parent_id', 0)->orderBy('category_name', 'ASC')->get(),
            'outflow_parents' => AccCategory::where('trans_type', 1)->where('status', 1)->where('parent_id', 0)->orderBy('category_name', 'ASC')->get()
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
            'code' => (isset($request->code) && !empty($request->code) ? $request->code : null),
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
            'code' => (isset($request->code) && !empty($request->code) ? $request->code : null),
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

    public function getTreeHtml(Request $request){
        $type = $request->type;
        $category_id = $request->category_id;

        $html = '';
        $categories = AccCategory::where('trans_type', $type)->where('parent_id', $category_id)->where('status', 1)->orderBy('category_name', 'ASC')->get();
        if($categories->count() > 0):
            $html .= '<ul class="theChild">';
                foreach($categories as $cat):
                    $html .= '<li class="'.(isset($cat->activechildrens) && $cat->activechildrens->count() > 0 ? 'hasChildren' : '').' relative">';
                        $html .= '<a href="javascript:void(0);" data-type="'.$type.'" data-category="'.$cat->id.'" class="'.(isset($cat->activechildrens) && $cat->activechildrens->count() > 0 ? 'parent_category' : '').' flex items-center text-primary font-medium">'.$cat->category_name.(isset($cat->activechildrens) && $cat->activechildrens->count() > 0 ? ' ('.$cat->activechildrens->count().')' : '').(isset($cat->code) && !empty($cat->code) ? ' - '.$cat->code : '').' <i data-loading-icon="oval" class="w-4 h-4 ml-2"></i></a>';
                        $html .= '<div class="settingBtns flex justify-end items-center absolute">';  
                            $html .= '<button data-id="'.$cat->id.'" data-tw-toggle="modal" data-tw-target="#editCategoryModal" class="edit_btn p-0 border-0 rounded-0 text-success inline-flex"><i class="w-4 h-4" data-lucide="Pencil"></i></button>';
                            $html .= '<button data-id="'.$cat->id.'" class="delete_btn p-0 border-0 rounded-0 text-danger inline-flex ml-2"><i class="w-4 h-4" data-lucide="trash-2"></i></button>';
                        $html .= '</div>';
                    $html .= '</li>';
                endforeach;
            $html .= '</ul>';
        else:
            $html .= '<ul class="errorUL theChild">';
                $html .= '<li><div class="alert alert-pending-soft show flex items-center mb-2" role="alert"><i data-lucide="alert-triangle" class="w-6 h-6 mr-2"></i> Child Categories not found!</div></li>';
            $html .= '</ul>';
        endif;

        return response()->json(['htm' => $html], 200);
    }
}
