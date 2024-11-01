@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">{{ $subtitle }}</h2>
        <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
            <a href="{{ route('library.management.index') }}" class="add_btn btn btn-primary shadow-md mr-2">Back To Management</a>
        </div>
    </div>

    <!-- BEGIN: Settings Page Content -->
    <div class="grid grid-cols-12 gap-6">
        <div class="col-span-12 lg:col-span-4 2xl:col-span-3 flex lg:block flex-col-reverse">
            <!-- BEGIN: Profile Info -->
            @include('pages.library.sidebar')
            <!-- END: Profile Info -->
        </div>

        <div class="col-span-12 lg:col-span-8 2xl:col-span-9">
            <!-- BEGIN: Display Information -->
            <div class="intro-y box lg:mt-5">
                <div class="flex items-center p-5 border-b border-slate-200/60 dark:border-darkmode-400">
                    <h2 class="font-medium text-base mr-auto">Add Book</h2>
                    <a href="{{ route('library.books') }}" class="btn btn-primary shadow-md">Back To List</a>
                </div>
                <!-- BEGIN: HTML Table Data -->
                <div class="p-5">
                    <form method="post" action="#" id="addAmazonBookInfoForm" enctype="multipart/form-data">
                        <div class="grid grid-cols-12 gap-4 mb-4" id="locationFieldRow">
                            <div class="col-span-5">
                                <!--<label for="the_location_name" class="form-label inline-flex">Location Name <span class="text-danger ml-2">*</span></label>-->
                                <input id="the_location_name" type="text" class="form-control require" placeholder="Location Name" name="the_location_name">
                                <div class="acc__input-error error-the_location_name text-danger mt-2"></div>
                            </div>
                            <div class="col-span-7"><!-- pt-7 -->
                                <button id="scanLocationBtn" type="button" class="btn btn-secondary w-auto">
                                    <i data-lucide="search" class="w-4 h-4 mr-2"></i>Scan Location
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
                                </button>
                            </div>
                        </div>
                        <div class="grid grid-cols-12 gap-4 mb-4" id="isbnFieldRow" style="display: none;">
                            <div class="col-span-5">
                                <!--<label for="location_name" class="form-label inline-flex">ISBN <span class="text-danger ml-2">*</span></label>-->
                                <input id="isbn_no" type="text" class="form-control require" placeholder="ISBN NO" name="isbn_no">
                                <div class="acc__input-error error-isbn_no text-danger mt-2"></div>
                            </div>
                            <div class="col-span-7"><!-- pt-7 -->
                                <button id="scanISBNBtn" type="button" class="btn btn-secondary w-auto">
                                    <i data-lucide="search" class="w-4 h-4 mr-2"></i>Scan ISBN
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
                                </button>
                            </div>
                            <div class="col-span-12" id="isbnBooksWrap">
                                <table class="table table-sm table-bordered" id="amazonAddBooksTable">
                                    <thead>
                                        <tr>
                                            <th>&nbsp</th>
                                            <th>Picture</th>
                                            <th>Author</th>
                                            <th>Title</th>
                                            <th>Publisher</th>
                                            <th>ISBN</th>
                                            <th>Details</th>
                                            <th>Price</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="8">
                                                <div class="alert alert-pending-soft show flex items-center mb-2" role="alert">
                                                    <i data-lucide="alert-triangle" class="w-6 h-6 mr-2"></i> Books not found for this ISBN.
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="grid grid-cols-12 gap-4 pt-4 mb-4" id="bookBarCodeWrap" style="display: none;">
                            <div class="col-span-5">
                                <!--<label for="location_name" class="form-label inline-flex">Barcode <span class="text-danger ml-2">*</span></label>-->
                                <input id="book_bar_code" type="text" class="form-control require" placeholder="Bar Code" name="book_bar_code">
                                <div class="acc__input-error error-book_bar_code text-danger mt-2"></div>
                            </div>
                            <div class="col-span-7"><!-- pt-7 -->
                                <button id="scanBarCodeBtn" type="button" class="btn btn-secondary w-auto">
                                    <i data-lucide="search" class="w-4 h-4 mr-2"></i>Scan Barcode
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
                                </button>
                                <button id="saveBookBtn" type="submit" class="btn btn-success text-white w-auto ml-2" style="display: none;">
                                    <i data-lucide="plus-circle" class="w-4 h-4 mr-2"></i>Add Book
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
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                <!-- END: HTML Table Data -->
            </div>
        </div>
    </div>
    <!-- END: Settings Page Content -->

    <!-- BEGIN: Success Modal Content -->
    <div id="successModal" class="modal" tabindex="-1" aria-hidden="true" data-tw-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="check-circle" class="w-16 h-16 text-success mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 successModalTitle"></div>
                        <div class="text-slate-500 mt-2 successModalDesc"></div>
                    </div>
                    <div class="px-5 pb-8 text-center">
                        <button type="button" class="successCloserBtn btn btn-primary w-24">Close</button>
                        <button type="button" class="successInsertBtn btn btn-Success ml-2 w-auto">Create Again</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Success Modal Content -->

    <!-- BEGIN: Success Modal Content -->
    <div id="warningModal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="check-circle" class="w-16 h-16 text-danger mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 warningModalTitle"></div>
                        <div class="text-slate-500 mt-2 warningModalDesc"></div>
                    </div>
                    <div class="px-5 pb-8 text-center">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-primary w-24">OK</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Success Modal Content -->

    <!-- BEGIN: Delete Confirm Modal Content -->
    <div id="confirmModal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="x-circle" class="w-16 h-16 text-danger mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 confModTitle">Are you sure?</div>
                        <div class="text-slate-500 mt-2 confModDesc"></div>
                    </div>
                    <div class="px-5 pb-8 text-center">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-24 mr-1">No, Cancel</button>
                        <button type="button" data-id="0" data-action="none" class="agreeWith btn btn-danger w-auto">Yes, I agree</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Delete Confirm Modal Content -->
@endsection

@section('script')
    @vite('resources/js/settings.js')
    @vite('resources/js/amazon-add-book-information.js')
@endsection