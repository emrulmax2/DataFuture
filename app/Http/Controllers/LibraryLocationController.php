<?php

namespace App\Http\Controllers;

use App\Models\LibraryLocation;
use App\Http\Requests\StoreLibraryLocationRequest;
use App\Http\Requests\UpdateLibraryLocationRequest;
use App\Models\Option;
use App\Models\Venue;
use Illuminate\Http\Request;

class LibraryLocationController extends Controller
{
    public function index()
    {
        return view('pages.library.location.index', [
            'title' => 'Library Locations - London Churchill College',
            'subtitle' => 'Library Settings',
            'breadcrumbs' => [
                ['label' => 'Library Settings', 'href' => route('library.settings')],
                ['label' => 'Library Locations', 'href' => 'javascript:void(0);']
            ],
            'opt' => Option::where('category', 'SITE_SETTINGS')->pluck('value', 'name')->toArray(),
            'venues' => Venue::all()
            
        ]);
    }
    /**
     * Display a listing of the resource.
     */
    public function list(Request $request)
    {
        $queryStr = (isset($request->querystr) && !empty($request->querystr) ? $request->querystr : '');
        $status = (isset($request->status) && $request->status > 0 ? $request->status : 1);

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $query = LibraryLocation::orderByRaw(implode(',', $sorts));
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
                if(!empty($list->venue)):
                    $roles = '<span class="btn btn-secondary px-2 py-0 rounded-0 mr-1 mb-1">'.$list->venue->name.'</span>';
                  
                endif;
                $data[] = [
                    'id' => $list->id,
                    'sl' => $i,
                    'name' => $list->name,
                    'description' => !empty($list->description) ? $list->description : $this->convertShortCodeToDescription($list->name) ,
                    'venue' => $roles,
                    'deleted_at' => $list->deleted_at
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }

    /**
     * Convert a short code to a full description.
     *
     * @param  string  $shortCode
     * @return \Illuminate\Http\JsonResponse
     */
    public function convertShortCodeToDescription($shortCode)
    {
        // Use regular expressions to extract components
        preg_match('/L(\d+)S(\d+)E(\d+)/', $shortCode, $matches);

        if (count($matches) === 4) {
            $library = $matches[1];
            $shelf = $matches[2];
            $elevation = $matches[3];

            // Construct the full description
            $fullDescription = "Library Location $library Shelf $shelf Elevation $elevation";
            return $fullDescription;
        } else {
            return 'Unknown';
        }
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreLibraryLocationRequest $request)
    {
        try {
            // Create a new library location
            $libraryLocation = LibraryLocation::create([
                'name' => $request->name,
                'description' => $request->description,
                'venue_id' => $request->venue_id,
                'created_by' => auth()->user()->id,
            ]);

            // Return a success response
            return response()->json([
                'success' => true,
                'message' => 'Library location created successfully.',
                'data' => $libraryLocation,
            ], 201);

        } catch (\Exception $e) {
            // Return an error response
            return response()->json([
                'success' => false,
                'message' => 'Failed to create library location.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    
    /**
     * Display the specified resource.
     *
     * @param  \App\Models\LibraryLocation  $libraryLocation
     * @return \Illuminate\Http\JsonResponse
     */
    public function edit(LibraryLocation $libraryLocation)
    {
        $libraryLocation->load('venue');
        return response()->json($libraryLocation);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateLibraryLocationRequest $request, LibraryLocation $libraryLocation)
    {
        try {
            $libraryLocation->update([
                'name' => $request->name,
                'description' => $request->description,
                'venue_id' => $request->venue_id,
                'updated_by' => auth()->user()->id,
            ]);
    
            return response()->json([
                'success' => true,
                'message' => 'Library location updated successfully.',
                'data' => $libraryLocation,
            ]);
    
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update library location.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LibraryLocation $libraryLocation)
    {
        try {
            $libraryLocation->delete();
    
            return response()->json([
                'success' => true,
                'message' => 'Library location deleted successfully.',
            ]);
    
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete library location.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
