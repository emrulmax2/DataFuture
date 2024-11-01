<?php

namespace App\Http\Controllers\LibraryManagement;

use App\Http\Controllers\Controller;
use App\Models\AmazonBookInformation;
use App\Models\LibraryBook;
use App\Models\LibraryLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Storage;

class AmazonBookInformationController extends Controller
{
    public function index()
    {
        return view('pages.library.books.index', [
            'title' => 'Library Book Information - London Churchill College',
            'subtitle' => 'Book Informations',
            'breadcrumbs' => [
                ['label' => 'Library Settings', 'href' => route('library.settings')],
                ['label' => 'Book Informations', 'href' => 'javascript:void(0);']
            ]
        ]);
    }

    public function list(Request $request){
        $queryStr = (isset($request->querystr) && !empty($request->querystr) ? $request->querystr : '');
        $status = (isset($request->status) && $request->status > 0 ? $request->status : 1);

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


    public function create()
    {
        return view('pages.library.books.create', [
            'title' => 'Add Book Information - London Churchill College',
            'subtitle' => 'Book Informations',
            'breadcrumbs' => [
                ['label' => 'Library Settings', 'href' => route('library.settings')],
                ['label' => 'Book Informations', 'href' => route('library.books')],
                ['label' => 'Add Book', 'href' => 'javascript:void(0);']
            ]
        ]);
    }

    public function store(Request $request){
        $books = (isset($request->book) && !empty($request->book) ? $request->book : []);
        $manual_entry = (isset($request->manual_entry) && $request->manual_entry == 1 ? true : false);
        $the_location_name = $request->the_location_name;
        $location_id = $request->location_id;
        $venue_id = $request->venue_id;
        $isbn_no = $request->isbn_no;
        $book_bar_code = $request->book_bar_code;

        if(!Storage::disk('local')->exists('public/amazon_book')){
            Storage::disk('local')->makeDirectory('public/amazon_book');
        }

        if(!empty($books)):
            foreach($books as $key => $book):
                $imageName = '';
                $price = 0;
                if($manual_entry && $request->hasFile('book_image')):
                    $book_image = $request->file('book_image');
                    $imageName = 'BKIMG_'.time().'.'.$book_image->getClientOriginalExtension();
                    $path = $book_image->storeAs('public/amazon_book', $imageName, 'local');
                elseif(isset($book['img']) && !empty($book['img'])):
                    $image_info = getimagesize($book['img']);
                    $ext = ($image_info[2] == 2 ? 'jpg' : ($image_info[2] == 3 ? 'png' : ($image_info[2] == 1 ? 'gif' : '') ));
                    $imageName = 'BKIMG_'.time().'.'.$ext;
                    copy($book['img'], Storage::disk('local')->path('public/amazon_book/'.$imageName));
                endif;

                if (is_numeric($book['price'])):
                    $price = $book['price'];
                else:
                    $str2 = substr($book['price'], 2);
                    $price = (is_numeric($str2) ? $str2 : 0);
                endif;

                $isbn13 = $book['isbn13'];
                $isbn10 = $book['isbn10'];
                $pDate = (isset($book['pDate']) && !empty($book['pDate']) ? date('Y-m-d', strtotime($book['pDate'])) : '');
                $amazonBook = AmazonBookInformation::where('isbn13', $isbn13)->where('isbn10', $isbn10)->where('publication_date', $pDate)->get()->first();
                //dd($amazonBook);
                if(isset($amazonBook->id) && $amazonBook->id > 0):
                    $amazonBookId = $amazonBook->id;
                    $quantity = (isset($amazonBook->quantity) && $amazonBook->quantity > 0 ? ($amazonBook->quantity + 1) : 1);
                    $remainingQuantity = (isset($amazonBook->remaining_qty_for_section) && $amazonBook->remaining_qty_for_section > 0 ? ($amazonBook->remaining_qty_for_section + 1) : 1);
                    $updateBook = AmazonBookInformation::where('id', $amazonBook->id)->update(['quantity' => $quantity, 'remaining_qty_for_section' => $remainingQuantity]);
                else:
                    $amazonNewBook = AmazonBookInformation::create([
                        'title' => $book['title'],
                        'author' => $book['author'],
                        'publisher' => $book['publisher'],
                        'isbn13' => $isbn13,
                        'isbn10' => $isbn10,
                        'language' => $book['languages'],
                        'number_of_pages' => $book['pages'],
                        'publication_date' => $pDate,
                        'image_name' => $imageName,
                        'edition' => $book['edition'],
                        'price' => $price,
                        'quantity' => 1,
                        'remaining_qty_for_section' => 1,
                        'created_by' => auth()->user()->id,
                    ]);
                    $amazonBookId = $amazonNewBook->id;
                endif;
                if($amazonBookId):
                    $LibraryBook = LibraryBook::create([
                        'book_location_id' => $location_id,
                        'amazon_book_information_id' => $amazonBookId,
                        'book_barcode' => $book_bar_code,
                        'book_status' => 1,
                        'created_by' => auth()->user()->id,
                    ]);
                endif;
            endforeach;
            return response()->json(['msg' => 'Book successfull inserted.', 'id' => $amazonBookId], 200);
        else:
            return response()->json(['msg' => 'Something went wrong. Please try again later'], 422);
        endif;
    }

    public function edit(Request $request){
        $row_id = $request->row_id;
        $amazonBook = AmazonBookInformation::find($row_id);

        return response()->json(['row' => $amazonBook], 200);
    }

    public function update(Request $request){
        $id = $request->id;
        $amazonNewBook = AmazonBookInformation::where('id', $id)->update([
            'title' => $request->title,
            'author' => $request->author,
            'publisher' => $request->publisher,
            'isbn13' => $request->isbn13,
            'isbn10' => $request->isbn10,
            'number_of_pages' => ($request->number_of_pages > 0 ? $request->number_of_pages : 0),
            'publication_date' => (!empty($request->publication_date) ? date('Y-m-d', strtotime($request->publication_date)) : null),
            'edition' => $request->edition,
            'price' => ($request->price > 0 ? $request->price : 0),
            'updated_by' => auth()->user()->id,
        ]);

        return response()->json(['msg' => 'updated'], 200);
    }


    public function validateLocation(Request $request){
        $location_name = $request->location_name;
        $location = LibraryLocation::with('venue')->where('name', $location_name)->where('status', 1)->get()->first();
        
        if(isset($location->id) && $location->id > 0):
            $html = '';
            $html .= '<div class="col-span-6 venuCol">';
                $html .= '<div class="grid grid-cols-12 gap-0 mb-1">';
                    $html .= '<div class="col-span-4 text-slate-500 font-medium">Location</div>';
                    $html .= '<div class="col-span-8 font-medium">'.$location->name.'<input type="hidden" name="location_id" value="'.$location->id.'"/></div>';
                $html .= '</div>';
                $html .= '<div class="grid grid-cols-12 gap-0">';
                    $html .= '<div class="col-span-4 text-slate-500 font-medium">Venue</div>';
                    $html .= '<div class="col-span-8 font-medium">'.(isset($location->venue->name) && !empty($location->venue->name) ? $location->venue->name : '').'<input type="hidden" name="venue_id" value="'.(isset($location->venue_id) && $location->venue_id > 0 ? $location->venue_id : 0).'"/></div>';
                $html .= '</div>';
            $html .= '</div>';
            $html .= '<div class="col-span-6 venuCol"></div>';
            return response()->json(['suc' => 1, 'row' => $html], 200);
        else:
            return response()->json(['suc' => 2], 422);
        endif;
    }

    public function validateIsbn(Request $request){
        $isbn = $request->isbn;
        $isbn = str_replace(["-", "ï¿½"], '', $isbn);
        $isbnKey = 'AIzaSyBhq4_TNLflalkV3ViA16kP3Yfj0MrUOXg';

        $response = Http::get('https://www.googleapis.com/books/v1/volumes?q='.$isbn.'&key='.$isbnKey);
        if($response->ok()):
            //dd($response->object());
            $result = $response->object();
            if(!empty($result) && $result->totalItems > 0):
                $html = '';
                $html .= '<thead>';
                    $html .= '<tr>';
                        $html .= '<th>&nbsp</th>';
                        $html .= '<th>Picture</th>';
                        $html .= '<th>Author</th>';
                        $html .= '<th>Title</th>';
                        $html .= '<th>Publisher</th>';
                        $html .= '<th>ISBN</th>';
                        $html .= '<th>Details</th>';
                        $html .= '<th>Price</th>';
                    $html .= '</tr>';
                $html .= '</thead>';
                $html .= '<tbody>';
                    $no = 1;
                    foreach ($result->items as $book):
                        $imagesrc = (isset($book->volumeInfo->imageLinks->thumbnail) && !empty($book->volumeInfo->imageLinks->thumbnail) ? $book->volumeInfo->imageLinks->thumbnail : '');
                        $author = '';
                        if(isset($book->volumeInfo->authors) && !empty($book->volumeInfo->authors)):
                            $author = (is_array($book->volumeInfo->authors) && count($book->volumeInfo->authors) > 1) ? implode(',', $book->volumeInfo->authors) : $book->volumeInfo->authors[0];
                        endif;
                        $title = (isset($book->volumeInfo->title) ? $book->volumeInfo->title : '');
                        $publisher = (isset($book->volumeInfo->publisher) ? $book->volumeInfo->publisher : '');
                        $ISBN10 = (isset($book->volumeInfo->industryIdentifiers[1]) && !empty($book->volumeInfo->industryIdentifiers[1]) && isset($book->volumeInfo->industryIdentifiers[1]->identifier) ? $book->volumeInfo->industryIdentifiers[1]->identifier : '');
                        $ISBN13 = (isset($book->volumeInfo->industryIdentifiers[0]) && !empty($book->volumeInfo->industryIdentifiers[0]) && isset($book->volumeInfo->industryIdentifiers[0]->identifier) ? $book->volumeInfo->industryIdentifiers[0]->identifier : '');
                        $numberOfPages = (isset($book->volumeInfo->pageCount) ? $book->volumeInfo->pageCount : '');
                        $languages = (isset($book->volumeInfo->language) ? $book->volumeInfo->language : '');
                        $binding = (isset($book->volumeInfo->Binding) ? $book->volumeInfo->Binding : '');
                        $pDate = (isset($book->volumeInfo->publishedDate) ? $book->volumeInfo->publishedDate : '');
                        $edition = (isset($book->volumeInfo->edition) ? $book->volumeInfo->edition : '');
                        $alt = $title."  by ".$author;
                        $objEditorialReviews = (isset($book->volumeInfo->editorialReviews) ? $book->volumeInfo->editorialReviews : '');
                        $price = (isset($book->retailPrice->amount) ? $book->retailPrice->amount : '');

                        $html .= '<tr class="check-book-list">';
                            $html .= '<td>';
                                $html .= '<div class="form-check mt-2">';
                                    $html .= '<input id="bookCheck_'.$no.'" name="book['.$no.'][check_book_details]" class="form-check-input m-0 bookCheck" type="checkbox" value="'.$no.'">';
                                $html .= '</div>';
                            $html .= '</td>'; 
                            $html .= '<td>';  
                                if ($imagesrc != ""):
                                    $html .= '<input type="hidden" name="book['.$no.'][img]" class="image_source" id="img_'.$no.'" value="'.$imagesrc.'">';
                                    $html .= '<img src="'.$imagesrc.'"  title="'.$alt.'" width="70px" height="70px"/>';
                                else:
                                    $html .= '<img src="'.asset('build/assets/images/placeholders/200x200.jpg').'"  title="'.$alt.'" width="70px" height="70px"/>';
                                endif;
                            $html .= '</td>';
                            $html .= '<td> <input type="hidden" name="book['.$no.'][author]" id="author_'.$no.'" value="'.$author.'">'.$author.'</td>';
                            $html .= '<td><input type="hidden" name="book['.$no.'][title]"  id="title_'.$no.'"value="'.$title.'">'.$title.'</td>';
                            $html .= '<td> <input type="hidden" name="book['.$no.'][publisher]" id="publisher_'.$no.'" value="'.$publisher.'">'.$publisher.'</td>';
                            $html .= '<td>'; 
                                $html .= '<input type="hidden" name="book['.$no.'][isbn13]" id="isbn13_'.$no.'" value="'.$ISBN13.'"><b>ISBN13:</b>'.$ISBN13.'<br><b>ISBN10:</b>'.$ISBN10;
                                $html .= '<input type="hidden" name="book['.$no.'][isbn10]"  id="isbn10_'.$no.'"value="'.$ISBN10.'">';
                            $html .= '</td>';
                            $html .= '<td><input type="hidden" name="book['.$no.'][languages]"  id="languages_'.$no.'"value="'.$languages.'">';
                                $html .= '<input type="hidden" name="book['.$no.'][pages]"  id="pages_'.$no.'"value="'.$numberOfPages.'">';
                                $html .= '<input type="hidden" name="book['.$no.'][pDate]"  id="pDate_'.$no.'"value="'.$pDate.'">'; 
                                $html .= '<input type="hidden" name="book['.$no.'][edition]"  id="edition_'.$no.'"value="'.$edition.'">'; 
                                $html .= '<b>Language:</b>'.$languages.'<br><b>Edition:</b>'.$edition.'<br><b>Number of Pages:</b>'.$numberOfPages.'<br><b>Publication Date:</b>'.$pDate;
                            $html .= '</td>';
                            $html .= '<td> <input type="hidden" name="book['.$no.'][price]" id="price'.$no.'" value="'.$price.'">'.$price.'</td>';
                        $html .= '</tr>';
                        $no++;
                    endforeach;
                $html .= '</tbody>';

                return response()->json(['suc' => 1, 'html' => $html, 'msg' => 'Books found'], 200);
            else:
                $html = '';
                $html .= '<tbody>';
                    $html .= '<tr>';
                        $html .= '<td>';
                            $html .= '<label class="form-label">Books Author <span class="text-danger ml-1">*</span></label>';
                            $html .= '<input type="text" class="w-full form-control require" name="book[1][author]" value="">';
                            $html .= '<input type="hidden" name="book[1][check_book_details]" value="1">';
                            $html .= '<input type="hidden" name="manual_entry" value="1">';
                            $html .= '<div class="acc__input-error text-danger mt-2"></div>';
                        $html .= '</td>';
                        $html .= '<td>';
                            $html .= '<label class="form-label">Books Title <span class="text-danger ml-1">*</span></label>';
                            $html .= '<input type="text" class="w-full form-control require" name="book[1][title]" value="">';
                            $html .= '<div class="acc__input-error text-danger mt-2"></div>';
                        $html .= '</td>';
                        $html .= '<td>';
                            $html .= '<label class="form-label">Books Publisher <span class="text-danger ml-1">*</span></label>';
                            $html .= '<input type="text" class="w-full form-control require" name="book[1][publisher]" value="">';
                            $html .= '<div class="acc__input-error text-danger mt-2"></div>';
                        $html .= '</td>';
                    $html .= '</tr>';
                    $html .= '<tr>';
                        $html .= '<td>';
                            $html .= '<label class="form-label">Books ISBN 13 <span class="text-danger ml-1">*</span></label>';
                            $html .= '<input type="text" class="w-full form-control require" name="book[1][isbn13]" value="">';
                            $html .= '<div class="acc__input-error text-danger mt-2"></div>';
                        $html .= '</td>';
                        $html .= '<td>';
                            $html .= '<label class="form-label">Books ISBN 10 <span class="text-danger ml-1">*</span></label>';
                            $html .= '<input type="text" class="w-full form-control require" name="book[1][isbn10]" value="">';
                            $html .= '<div class="acc__input-error text-danger mt-2"></div>';
                        $html .= '</td>';
                        $html .= '<td>';
                            $html .= '<label class="form-label">Books Language <span class="text-danger ml-1">*</span></label>';
                            $html .= '<input type="text" class="w-full form-control require" name="book[1][languages]" value="">';
                            $html .= '<div class="acc__input-error text-danger mt-2"></div>';
                        $html .= '</td>';
                    $html .= '</tr>';
                    $html .= '<tr>';
                        $html .= '<td>';
                            $html .= '<label class="form-label">Books Page Number <span class="text-danger ml-1">*</span></label>';
                            $html .= '<input type="number" step="1" class="w-full form-control require" name="book[1][pages]" value="">';
                            $html .= '<div class="acc__input-error text-danger mt-2"></div>';
                        $html .= '</td>';
                        $html .= '<td>';
                            $html .= '<label class="form-label">Books Publish Date <span class="text-danger ml-1">*</span></label>';
                            $html .= '<input type="text" class="w-full form-control require datepicker" name="book[1][pDate]" value="">';
                            $html .= '<div class="acc__input-error text-danger mt-2"></div>';
                        $html .= '</td>';
                        $html .= '<td>';
                            $html .= '<label class="form-label">Books Edition <span class="text-danger ml-1">*</span></label>';
                            $html .= '<input type="text" class="w-full form-control require" name="book[1][edition]" value="">';
                            $html .= '<div class="acc__input-error text-danger mt-2"></div>';
                        $html .= '</td>';
                    $html .= '</tr>';
                    $html .= '<tr>';
                        $html .= '<td>';
                            $html .= '<label class="form-label">Books Price <span class="text-danger ml-1">*</span></label>';
                            $html .= '<input type="number" step="any" class="w-full form-control require" name="book[1][price]" value="">';
                            $html .= '<div class="acc__input-error text-danger mt-2"></div>';
                        $html .= '</td>';
                        $html .= '<td class="relative text-right" colspan="2" style="vertical-align: bottom">';
                            $html .= '<div class="acc__input-error text-danger mb-2"></div>';
                            $html .= '<label for="book_image" class="btn btn-primary w-auto"><i data-lucide="upload" class="w-4 h-4 mr-2"></i>Upload Books Image</label>';
                            $html .= '<input style="width: 0; height: 0; position: absolute; left: 0; top: 0;" accept="image/*" type="file" step="any" id="book_image" name="book_image">';
                            $html .= '<button id="scanCustomBook" type="button" class="btn btn-secondary w-auto ml-2">
                                            <i data-lucide="search" class="w-4 h-4 mr-2"></i> Next
                                            <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg"
                                                stroke="rgb(100,116,139)" class="w-4 h-4 ml-2 theLoader">
                                                <g fill="none" fill-rule="evenodd">
                                                    <g transform="translate(1 1)" stroke-width="4">
                                                        <circle stroke-opacity=".5" cx="18" cy="18" r="18"></circle>
                                                        <path d="M36 18c0-9.94-8.06-18-18-18">
                                                            <animateTransform attributeName="transform" type="rotate" from="0 18 18"
                                                                to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform>
                                                        </path>
                                                    </g>
                                                </g>
                                            </svg>
                                        </button>';
                        $html .= '</td>';
                    $html .= '</tr>';
                $html .= '</tbody>';
                return response()->json([
                    'suc' => 2, 
                    'html' => $html, 
                    'msg' => 'Books not available for this ISBN. Please try another one.'
                ], 200);
            endif;
        else:
            return response()->json(['suc' => 2, 'msg' => 'Something went wrong. Please try again later.'], 422);
        endif;
    }

    public function validateBarcode(Request $request){
        $barcode = $request->barcode;
        $libraryBook = LibraryBook::where('book_barcode', $barcode)->get()->count();
        
        if($libraryBook > 0){
            return response()->json(['suc' => 0], 422);
        }else{
            return response()->json(['suc' => 1], 200);
        }
    }

    

    public function destroy($id){
        $libraryBook = LibraryBook::where('amazon_book_information_id', $id)->delete();
        $data = AmazonBookInformation::find($id)->delete();
        return response()->json($data);
    }

    public function restore($id) {
        $data = AmazonBookInformation::where('id', $id)->withTrashed()->restore();
        $libraryBook = LibraryBook::where('amazon_book_information_id', $id)->withTrashed()->restore();

        response()->json($data);
    }

    public function destroyLocation($id){
        $libraryBook = LibraryBook::find($id);
        $abi_id = $libraryBook->amazon_book_information_id;
        $amazonBook = AmazonBookInformation::find($abi_id);
        if(isset($amazonBook->id) && $amazonBook->id > 0):
            $remaining_qty_for_section = $amazonBook->remaining_qty_for_section > 0 ? $amazonBook->remaining_qty_for_section : 0;
            $quantity = $amazonBook->quantity > 0 ? $amazonBook->quantity : 0;
            if($amazonBook->remaining_qty_for_section > 0):
                AmazonBookInformation::where('id', $abi_id)->update([
                    'remaining_qty_for_section' => ($remaining_qty_for_section - 1),
                    'quantity' => ($quantity - 1)
                ]);
            else:
                AmazonBookInformation::where('id', $abi_id)->update([
                    'remaining_qty_for_section' => ($remaining_qty_for_section - 1),
                    'quantity' => ($quantity - 1)
                ]);
            endif;
        endif;

        LibraryBook::where('id', $id)->delete();
        return response()->json(['abi' => $abi_id], 200);
    }
}
