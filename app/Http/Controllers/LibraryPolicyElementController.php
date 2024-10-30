<?php

namespace App\Http\Controllers;

use App\Models\LibraryPolicyElement;
use App\Http\Requests\StoreLibraryPolicyElementRequest;
use App\Http\Requests\UpdateLibraryPolicyElementRequest;

class LibraryPolicyElementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function list()
    {
        $queryStr = (isset($request->querystr) && !empty($request->querystr) ? $request->querystr : '');
        $status = (isset($request->status) && $request->status > 0 ? $request->status : 1);

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $query = LibraryPolicyElement::orderByRaw(implode(',', $sorts));
        if(!empty($queryStr)):
            $query->where('name','LIKE','%'.$queryStr.'%');
            $query->where('description','LIKE','%'.$queryStr.'%');
        endif;
        if($status == 2):
            $query->onlyTrashed();
        endif;

        $total_rows = $query->count();
        $page = (isset($request->page) && $request->page > 0 ? $request->page : 0);
        $perpage = (isset($request->size) && $request->size == 'true' ? $total_rows : ($request->size > 0 ? $request->size : 10));
        $last_page = $total_rows > 0 ? ceil($total_rows / $perpage) : '';
        
        $limit = $perpage;
        $offset = ($page > 0 ? ($page - 1) * $perpage : 0);

        $Query= $query->skip($offset)
               ->take($limit)
               ->get();

        $data = array();

        if(!empty($Query)):
            $i = 1;
            foreach($Query as $list):
                $roles = '';
                
                $data[] = [
                    'id' => $list->id,
                    'sl' => $i,
                    'name' => $list->name,
                    'email' => $list->email,
                    'gender' => $list->gender,
                    'photo_url' => $list->photo_url,
                    'roles' => $roles,
                    'deleted_at' => $list->deleted_at
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }

   

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreLibraryPolicyElementRequest $request)
    {
        //
    }

    

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(LibraryPolicyElement $libraryPolicyElement)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateLibraryPolicyElementRequest $request, LibraryPolicyElement $libraryPolicyElement)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LibraryPolicyElement $libraryPolicyElement)
    {
        //
    }
}
