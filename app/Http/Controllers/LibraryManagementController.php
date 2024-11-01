<?php

namespace App\Http\Controllers;

use App\Models\AmazonBookInformation;
use App\Models\EmployeeAppraisal;
use App\Models\EmployeeEligibilites;
use App\Models\EmployeeLeave;
use App\Models\EmployeeLeaveDay;
use App\Models\LibraryBook;
use App\Models\LibraryLocation;
use App\Models\Option;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LibraryManagementController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $expireDate = Carbon::now()->addDays(60)->format('Y-m-d');

        return view('pages.library.index', [
            'title' => 'Library Managemnet - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'HR Portal', 'href' => 'javascript:void(0);']
            ],
            'locations' => LibraryLocation::orderBy('name', 'ASC')->get()
        ]);
    }


    public function settings()
    {
        return view('pages.library.settings', [
            'title' => 'Library Settings - London Churchill College',
            'subtitle' => 'Library Settings',
            'breadcrumbs' => [
                ['label' => 'Library Settings', 'href' => 'javascript:void(0);']
            ],
            'opt' => Option::where('category', 'SITE_SETTINGS')->pluck('value', 'name')->toArray(),
        ]);
    }

    public function list(Request $request){
        $queryStr = (isset($request->querystr) && !empty($request->querystr) ? $request->querystr : '');
        $location = (isset($request->location) && !empty($request->location) ? $request->location : []);

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $query = AmazonBookInformation::orderByRaw(implode(',', $sorts));
        if(!empty($queryStr)):
            $query->where(function($q) use($queryStr){
                $q->where('title','LIKE','%'.$queryStr.'%');
                $q->orWhere('author','LIKE','%'.$queryStr.'%');
                $q->orWhere('publisher','LIKE','%'.$queryStr.'%');
                $q->orWhere('isbn13','LIKE','%'.$queryStr.'%');
                $q->orWhere('isbn10','LIKE','%'.$queryStr.'%');
            });
        endif;
        if(!empty($location)){
            $query->whereHas('book', function($q) use($location){
                $q->whereIn('book_location_id', $location);
            });
        }

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
                $data[] = [
                    'id' => $list->id,
                    'sl' => $i,
                    'photo_url' => $list->photo_url,
                    'title' => $list->title,
                    'author' => $list->author,
                    'publisher' => $list->publisher,
                    'isbn13' => $list->isbn13,
                    'isbn10' => $list->isbn10,
                    'language' => $list->language,
                    'number_of_pages' => $list->number_of_pages,
                    'publication_date' => (!empty($list->publication_date) && $list->publication_date != '0000-00-00' ? date('jS M, Y', strtotime($list->publication_date)) : ''),
                    'edition' => $list->edition,
                    'price' => '£'.($list->price > 0 ? number_format($list->price, 2) : '0.00'),
                    'quantity' => ($list->quantity > 0 ? $list->quantity : 0),
                    'remaining_qty_for_section' => $list->remaining_qty_for_section,
                    'deleted_at' => $list->deleted_at
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }

    public function locationList(Request $request){
        $amazonBookId = isset($request->amazonBookId) && $request->amazonBookId > 0 ? $request->amazonBookId : 0;

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $query = LibraryBook::where('amazon_book_information_id', $request->amazonBookId)->orderByRaw(implode(',', $sorts));

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
                $data[] = [
                    'id' => $list->id,
                    'abi_id' => $list->amazon_book_information_id,
                    'sl' => $i,
                    'photo_url' => (isset($list->abi->photo_url) && !empty($list->abi->photo_url) ? $list->abi->photo_url : asset('build/assets/images/placeholders/200x200.jpg')),
                    'title' => (isset($list->abi->title) && !empty($list->abi->title) ? $list->abi->title : ''),
                    'author' => (isset($list->abi->author) && !empty($list->abi->author) ? $list->abi->author : ''),
                    'isbn13' => (isset($list->abi->isbn13) && !empty($list->abi->isbn13) ? $list->abi->isbn13 : ''),
                    'isbn10' => (isset($list->abi->isbn10) && !empty($list->abi->isbn10) ? $list->abi->isbn10 : ''),
                    'venue' => (isset($list->location->venue->name) && !empty($list->location->venue->name) ? $list->location->venue->name : ''),
                    'location' => (isset($list->location->name) && !empty($list->location->name) ? $list->location->name : ''),
                    'book_barcode' => (isset($list->book_barcode) && !empty($list->book_barcode) ? $list->book_barcode : ''),
                    'deleted_at' => $list->deleted_at
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }
}
