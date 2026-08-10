<?php

namespace App\Http\Controllers\CourseManagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\CourseBaseDatafutureRequests;
use App\Models\CourseBaseDatafutures;

class CourseBaseDatafutureCntroller extends Controller
{
    public function list(Request $request){
        $queryStr = (isset($request->querystr) && !empty($request->querystr) ? $request->querystr : '');
        $status = (isset($request->status) && $request->status > 0 ? $request->status : 1);
        $course = (isset($request->course) && $request->course > 0 ? $request->course : 0);
        $parentOnly = (isset($request->parent_only) && $request->parent_only > 0);

        $query = CourseBaseDatafutures::with(['field.category', 'parent.field.category'])
            ->where('course_id', $course);

        if($status == 2):
            $query->onlyTrashed();
        endif;

        if($parentOnly):
            $query->whereNull('parent_id');
        endif;

        if(!empty($queryStr)):
            $query->where(function($q) use ($queryStr) {
                $q->where('field_value','LIKE','%'.$queryStr.'%')
                    ->orWhereHas('field', function($fieldQuery) use ($queryStr) {
                        $fieldQuery->where('name','LIKE','%'.$queryStr.'%')
                            ->orWhere('type','LIKE','%'.$queryStr.'%')
                            ->orWhere('description','LIKE','%'.$queryStr.'%');
                    })
                    ->orWhereHas('field.category', function($categoryQuery) use ($queryStr) {
                        $categoryQuery->where('name','LIKE','%'.$queryStr.'%');
                    })
                    ->orWhereHas('parent', function($parentQuery) use ($queryStr) {
                        $parentQuery->where('field_value','LIKE','%'.$queryStr.'%')
                            ->orWhereHas('field', function($fieldQuery) use ($queryStr) {
                                $fieldQuery->where('name','LIKE','%'.$queryStr.'%');
                            });
                    });
            });
        endif;

        $total_rows = (clone $query)->reorder()->count();
        $page = (isset($request->page) && $request->page > 0 ? $request->page : 0);
        $perpage = (isset($request->size) && $request->size == 'true'
            ? ($total_rows > 0 ? $total_rows : 10)
            : ($request->size > 0 ? $request->size : 10));
        // 1, not '' — an empty string reaches Tabulator as NaN and breaks the pager.
        $last_page = $total_rows > 0 ? ceil($total_rows / $perpage) : 1;

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sortable = [
            'id' => 'course_base_datafutures.id',
            'datafuture_field_id' => 'course_base_datafutures.datafuture_field_id',
            'field_value' => 'course_base_datafutures.field_value',
            'parent_id' => 'course_base_datafutures.parent_id',
        ];

        $hasSort = false;
        foreach($sorters as $sort):
            if(isset($sortable[$sort['field']])):
                $direction = (isset($sort['dir']) && strtolower($sort['dir']) == 'asc') ? 'ASC' : 'DESC';
                $query->orderBy($sortable[$sort['field']], $direction);
                $hasSort = true;
            endif;
        endforeach;

        if(!$hasSort):
            $query->orderBy('course_base_datafutures.id', 'DESC');
        endif;
        
        $limit = $perpage;
        $offset = ($page > 0 ? ($page - 1) * $perpage : 0);

        $Query = $query->skip($offset)
               ->take($limit)
               ->get();

        $data = array();
        if(!empty($Query)):
            $i = 1;
            foreach($Query as $list):
                $data[] = [
                    'id' => $list->id,
                    'sl' => $i,
                    'category' => (isset($list->field->category->name) ? $list->field->category->name : ''),
                    'datafuture_field_id' => (isset($list->field->name) ? $list->field->name : ''),
                    'field_type' => (isset($list->field->type) ? $list->field->type : ''),
                    'field_value' => $list->field_value,
                    'field_desc' => (isset($list->field->description) ? $list->field->description : ''),
                    'parent_id' => $list->parent_id,
                    'parent_field' => ($list->parent ? $this->datafutureLabel($list->parent) : ''),
                    'parent_label' => $this->datafutureLabel($list),
                    'deleted_at' => $list->deleted_at
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'total' => $total_rows, 'data' => $data]);
    }

    public function store(CourseBaseDatafutureRequests $request){
        $data = [
            'course_id'=> $request->course_id,
            'datafuture_field_id'=> $request->datafuture_field_id,
            'parent_id'=> (!empty($request->parent_id) ? $request->parent_id : null),
            'field_value'=> (!empty($request->field_value) ? $request->field_value : null),
            'created_by' => auth()->user()->id
        ];
        
        $courseDF = CourseBaseDatafutures::create($data);
        
        return response()->json($courseDF);
    }

    public function edit($id){
        $data = CourseBaseDatafutures::with(['field.category', 'parent.field.category'])->find($id);

        if($data){
            return response()->json($data);
        }else{
            return response()->json(['message' => 'Something went wrong. Please try later'], 422);
        }
    }

    public function update(CourseBaseDatafutureRequests $request){
        $dfID = $request->id;
        $course_id = $request->course_id;
        $courseDF = CourseBaseDatafutures::where('id', $dfID)->where('course_id', $course_id)->update([
            'datafuture_field_id'=> $request->datafuture_field_id,
            'parent_id'=> (!empty($request->parent_id) ? $request->parent_id : null),
            'field_value'=> (!empty($request->field_value) ? $request->field_value : null),
            'updated_by' => auth()->user()->id
        ]);


        if($courseDF){
            return response()->json(['message' => 'Data updated'], 200);
        }else{
            return response()->json(['message' => 'something went wrong'], 422);
        }
    }

    public function destroy($id){
        $data = CourseBaseDatafutures::findOrFail($id);
        $visited = [];

        $this->softDeleteWithChildren($data, $visited);

        return response()->json(true);
    }

    public function restore($id) {
        $data = CourseBaseDatafutures::where('id', $id)->withTrashed()->firstOrFail();
        $visited = [];

        $this->restoreWithChildren($data, $visited);

        return response()->json(true);
    }

    private function datafutureLabel(CourseBaseDatafutures $datafuture): string
    {
        $field = (isset($datafuture->field->name) && !empty($datafuture->field->name) ? $datafuture->field->name : 'ID: '.$datafuture->datafuture_field_id);
        $value = (!empty($datafuture->field_value) ? ' - '.$datafuture->field_value : '');
        $category = (isset($datafuture->field->category->name) && !empty($datafuture->field->category->name) ? ' ('.$datafuture->field->category->name.')' : '');

        return '#'.$datafuture->id.' '.$field.$value.$category;
    }

    private function softDeleteWithChildren(CourseBaseDatafutures $datafuture, array &$visited): void
    {
        if(isset($visited[$datafuture->id])):
            return;
        endif;

        $visited[$datafuture->id] = true;

        $datafuture->children()->get()->each(function($child) use (&$visited) {
            $this->softDeleteWithChildren($child, $visited);
        });

        $datafuture->delete();
    }

    private function restoreWithChildren(CourseBaseDatafutures $datafuture, array &$visited): void
    {
        if(isset($visited[$datafuture->id])):
            return;
        endif;

        $visited[$datafuture->id] = true;

        $datafuture->restore();

        $datafuture->childrenWithTrashed()->get()->each(function($child) use (&$visited) {
            $this->restoreWithChildren($child, $visited);
        });
    }

}
