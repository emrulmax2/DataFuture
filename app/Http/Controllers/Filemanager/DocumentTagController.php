<?php

namespace App\Http\Controllers\Filemanager;

use App\Http\Controllers\Controller;
use App\Http\Requests\DocumentTagRequest;
use App\Models\DocumentTag;
use Illuminate\Http\Request;

class DocumentTagController extends Controller
{
    public function store(DocumentTagRequest $request){
        $tag = DocumentTag::create([
            'name' => $request->name,
            'created_by' => auth()->user()->id,
        ]);

        $html = '';
        if($tag->id):
            $html .= '<div class="fileTag">';
                $html .= '<span>'.$request->name.'</span>';
                $html .= '<button type="button" class="removeTag"><i data-lucide="x" class="w-3 h-3"></i></button>';
                $html .= '<input type="hidden" name="tag_ids[]" value="'.$tag->id.'"/>';
            $html .= '</div>';
        endif;
        return response()->json(['htm' => $html], 200);
    }


    public function searchTags(Request $request){
        $querystr = $request->querystr;

        $html = '';
        $tags = DocumentTag::where('name', 'LIKE', '%'.$querystr.'%')->orderBy('name', 'ASC')->get();
        if($tags->count() > 0):
            foreach($tags as $tg):
                $html .= '<li>';
                    $html .= '<a href="'.$tg->id.'" class="dropdown-item selectableTag">'.$tg->name.'</a>';
                $html .= '</li>';
            endforeach;
        else:
            $html .= '<li>';
                $html .= '<a data-str="'.$querystr.'" data-tw-toggle="modal" data-tw-target="#addTagModal" href="javascript:void(0);" class="addNewTagBtn dropdown-item addable underline">Add New Tag</a>';
            $html .= '</li>';
        endif;

        return response()->json(['htm' => $html], 200);
    }
}
