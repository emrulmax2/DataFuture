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
            'lavel' => 1,
            
            'inflow_parents' => AccCategory::where('trans_type', 0)->where('status', 1)->where('parent_id', 0)->orderBy('category_name', 'ASC')->get(),
            'outflow_parents' => AccCategory::where('trans_type', 1)->where('status', 1)->where('parent_id', 0)->orderBy('category_name', 'ASC')->get()
        ]);
    }

    public function filterDropdown(Request $request){
        $trans_type = $request->trans_type;
        $categories = $this->catTree(0, $trans_type);
        return response()->json(['cats' => $categories], 200);

        /*$options = '<option value="">Select Parent Category</option>';
        if(!empty($categories)):
            foreach($categories as $cat):
                $options .= '<option value="'.$cat['id'].'">'.$cat['category_name'].'</option>';
            endforeach;
        endif;

        return response()->json(['html' => $options], 200);*/
    }

    public function catTree($id = 0, $type = 0){
        static $categs = array ();
        static $level = 0;
        $level ++;

        $categories = AccCategory::where('trans_type', $type)->where('parent_id', $id)->orderBy('category_name', 'ASC')->get();

        if($categories):
            foreach ($categories as $cat):
                $categs[$cat['id']]['category_name'] = str_repeat('|___', $level-1) . '|__'. $cat['category_name'];
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


    public function edit(Request $request){
        $row_id = $request->row_id;
        $category = AccCategory::find($row_id);
        $trans_type = $category->trans_type;

        $categoryOptions = $this->catTree(0, $trans_type);

        /*$options = '<option value="">Select Parent Category</option>';
        if(!empty($categoryOptions)):
            foreach($categoryOptions as $cat):
                $options .= '<option value="'.$cat['id'].'">'.$cat['category_name'].'</option>';
            endforeach;
        endif;*/

        return response()->json(['row' => $category, 'options' => $categoryOptions], 200);
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
        // Node markup mirrors the branches rendered in pages/settings/accounts/category.blade.php —
        // keep the two in step so lazily loaded children look identical to the roots.
        if($categories->count() > 0):
            $html .= '<ul class="theChild">';
                foreach($categories as $cat):
                    $childCount = (isset($cat->activechildrens) ? $cat->activechildrens->count() : 0);
                    $name = e($cat->category_name);

                    $html .= '<li class="'.($childCount > 0 ? 'hasChildren' : 'notHasChild').' relative">';
                        $html .= '<a href="javascript:void(0);" data-type="'.e($type).'" data-category="'.$cat->id.'" class="'.($childCount > 0 ? 'parent_category' : '').' ss-cat-node">';
                            $html .= '<span class="ss-cat-node__label">'.$name.'</span>';
                            $html .= ($childCount > 0 ? '<span class="ss-cat-node__count">'.$childCount.'</span>' : '');
                            $html .= (!empty($cat->code) ? '<span class="ss-cat-node__code">'.e($cat->code).'</span>' : '');
                            $html .= '<i data-loading-icon="oval" class="ss-cat-node__spinner"></i>';
                        $html .= '</a>';
                        $html .= '<div class="settingBtns ss-cat-node__actions">';
                            $html .= '<button data-id="'.$cat->id.'" data-tw-toggle="modal" data-tw-target="#editCategoryModal" type="button" class="edit_btn ss-row-action ss-row-action--edit" aria-label="Edit '.$name.'"><i data-lucide="pencil"></i></button>';
                            $html .= '<button data-id="'.$cat->id.'" type="button" class="delete_btn ss-row-action ss-row-action--delete" aria-label="Delete '.$name.'"><i data-lucide="trash-2"></i></button>';
                        $html .= '</div>';
                    $html .= '</li>';
                endforeach;
            $html .= '</ul>';
        else:
            $html .= '<ul class="errorUL theChild">';
                $html .= '<li><div class="ss-cat-branch-empty"><i data-lucide="alert-triangle"></i> Child categories not found</div></li>';
            $html .= '</ul>';
        endif;

        return response()->json(['htm' => $html], 200);
    }
}
