@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">{{ $subtitle }}</h2>
        <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
            <button data-tw-toggle="modal" data-tw-target="#addCategoryModal" type="button" class="btn btn-primary shadow-md">Add Category</button>
            {{--<a href="{{ route('site.setting') }}" class="add_btn btn btn-primary shadow-md">Back To Settings</a>--}}
        </div>
    </div>

    <!-- BEGIN: Settings Page Content -->
    <div class="grid grid-cols-12 gap-6">
        <div class="col-span-12 lg:col-span-4 2xl:col-span-3 flex lg:block flex-col-reverse">
            <!-- BEGIN: Profile Info -->
            @include('pages.settings.sidebar')
            <!-- END: Profile Info -->
        </div>

        <div class="col-span-12 lg:col-span-8 2xl:col-span-9">
            <div class="grid grid-cols-2 gap-6">
                <div class="intro-y box p-5 mt-5">
                    <h2 class="text-lg font-medium mr-auto pb-5">Inflow</h2>
                    <div class="overflow-x-auto scrollbar-hidden planTreeWrap categoryTreeWrap" id="inflowCategoryWrap">
                        @if(!empty($inflow_parents))
                            <ul class="classPlanTree">
                                @foreach($inflow_parents as $cat)
                                    <li class="{{ (isset($cat->activechildrens) && $cat->activechildrens->count() > 0 ? 'hasChildren' : 'notHasChild') }} relative">
                                        <a href="javascript:void(0);" data-type="0" data-category="{{ $cat->id }}" class="{{ (isset($cat->activechildrens) && $cat->activechildrens->count() > 0 ? 'parent_category' : '') }} flex items-center text-primary font-medium">{{ $cat->category_name }} {{ (isset($cat->activechildrens) && $cat->activechildrens->count() > 0 ? ' ('.$cat->activechildrens->count().')' : '') }} {{ (isset($cat->code) && !empty($cat->code) ? ' - '.$cat->code : '') }} <i data-loading-icon="oval" class="w-4 h-4 ml-2"></i></a>
                                        <div class="settingBtns flex justify-end items-center absolute"> 
                                            <button data-id="{{$cat->id}}" data-tw-toggle="modal" data-tw-target="#editCategoryModal" class="edit_btn p-0 border-0 rounded-0 text-success inline-flex"><i class="w-4 h-4" data-lucide="Pencil"></i></button>
                                            <button data-id="{{$cat->id}}" class="delete_btn p-0 border-0 rounded-0 text-danger inline-flex ml-2"><i class="w-4 h-4" data-lucide="trash-2"></i></button>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
                <div class="intro-y box p-5 mt-5">
                    <h2 class="text-lg font-medium mr-auto pb-5">Outflow</h2>
                    <div class="overflow-x-auto scrollbar-hidden planTreeWrap categoryTreeWrap" id="outflowCategoryWrap">
                        @if(!empty($outflow_parents))
                            <ul class="classPlanTree">
                                @foreach($outflow_parents as $cat)
                                    <li class="{{ (isset($cat->activechildrens) && $cat->activechildrens->count() > 0 ? 'hasChildren' : 'notHasChild') }} relative">
                                        <a href="javascript:void(0);" data-type="1" data-category="{{ $cat->id }}" class="{{ (isset($cat->activechildrens) && $cat->activechildrens->count() > 0 ? 'parent_category' : '') }} flex items-center text-primary font-medium">{{ $cat->category_name }} {{ (isset($cat->activechildrens) && $cat->activechildrens->count() > 0 ? ' ('.$cat->activechildrens->count().')' : '') }} {{ (isset($cat->code) && !empty($cat->code) ? ' - '.$cat->code : '') }} <i data-loading-icon="oval" class="w-4 h-4 ml-2"></i></a>
                                        <div class="settingBtns flex justify-end items-center absolute"> 
                                            <button data-id="{{$cat->id}}" data-tw-toggle="modal" data-tw-target="#editCategoryModal" class="edit_btn p-0 border-0 rounded-0 text-success inline-flex"><i class="w-4 h-4" data-lucide="Pencil"></i></button>
                                            <button data-id="{{$cat->id}}" class="delete_btn p-0 border-0 rounded-0 text-danger inline-flex ml-2"><i class="w-4 h-4" data-lucide="trash-2"></i></button>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Settings Page Content -->

    <!-- BEGIN: Add Storage Modal -->
    <div id="addCategoryModal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="#" id="addCategoryForm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Add Category</h2>
                    </div>
                    <div class="modal-body">
                        <div>
                            <label for="category_name" class="form-label">Category Name <span class="text-danger">*</span></label>
                            <input id="category_name" name="category_name" type="text" class="form-control" placeholder="Category Name">
                            <div class="acc__input-error error-category_name text-danger mt-2"></div>
                        </div>
                        <div class="mt-3">
                            <label for="code" class="form-label">Code</label>
                            <input id="code" name="code" type="text" class="form-control" placeholder="Code">
                            <div class="acc__input-error error-code text-danger mt-2"></div>
                        </div>
                        <div class="mt-3">
                            <div class="flex flex-col sm:flex-row mt-2">
                                <label class="mr-2">Type <span class="text-danger">*</span></label>
                                <div class="form-check mr-2">
                                    <input id="inflow" class="form-check-input" name="trans_type" type="radio" value="0">
                                    <label class="form-check-label" for="inflow">Inflow</label>
                                </div>
                                <div class="form-check mr-2 mt-2 sm:mt-0">
                                    <input id="outflow" class="form-check-input" name="trans_type" type="radio" value="1">
                                    <label class="form-check-label" for="outflow">Outflow</label>
                                </div>
                            </div>
                            <div class="acc__input-error error-trans_type text-danger mt-2"></div>
                        </div>
                        <div class="mt-3">
                            <label for="parent_id" class="form-label">Parent Category</label>
                            <select id="parent_id" name="parent_id" class="w-full tom-selects">
                                <option value="">Select Parent Category</option>
                                {{--@foreach($categories as $category)
                                    <option value="{{ $category['id'] }}">{!! $category['category_name'] !!}</option>
                                @endforeach--}}
                            </select>
                            <div class="acc__input-error error-parent_id text-danger mt-2"></div>
                        </div>
                        <div class="mt-3">
                            <label for="audit_status" class="form-label">Audit Status</label>
                            <div class="form-check form-switch">
                                <input id="audit_status" class="form-check-input" name="audit_status" value="1" type="checkbox">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div style="float: left;" class="mt-1">
                            <div class="form-check form-switch">
                                <label class="form-check-label mr-3 ml-0" for="status">Status</label>
                                <input id="status" checked class="form-check-input" name="status" value="1" type="checkbox">
                            </div>
                        </div>
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" id="saveCategory" class="btn btn-primary w-auto">
                            Save
                            <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg"
                                stroke="white" class="w-4 h-4 ml-2">
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
    </div>
    <!-- END: Add Storage Modal -->

    <!-- BEGIN: Edit Storage Modal -->
    <div id="editCategoryModal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="#" id="editCategoryForm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Edit Category</h2>
                    </div>
                    <div class="modal-body">
                        <div>
                            <label for="edit_category_name" class="form-label">Category Name <span class="text-danger">*</span></label>
                            <input id="edit_category_name" name="category_name" type="text" class="form-control" placeholder="Category Name">
                            <div class="acc__input-error error-category_name text-danger mt-2"></div>
                        </div>
                        <div class="mt-3">
                            <label for="edit_code" class="form-label">Code</label>
                            <input id="edit_code" name="code" type="text" class="form-control" placeholder="Code">
                            <div class="acc__input-error error-code text-danger mt-2"></div>
                        </div>
                        <div class="mt-3">
                            <div class="flex flex-col sm:flex-row mt-2">
                                <label class="mr-2">Type <span class="text-danger">*</span></label>
                                <div class="form-check mr-2">
                                    <input id="edit_inflow" class="form-check-input" name="trans_type" type="radio" value="0">
                                    <label class="form-check-label" for="edit_inflow">Inflow</label>
                                </div>
                                <div class="form-check mr-2 mt-2 sm:mt-0">
                                    <input id="edit_outflow" class="form-check-input" name="trans_type" type="radio" value="1">
                                    <label class="form-check-label" for="edit_outflow">Outflow</label>
                                </div>
                            </div>
                            <div class="acc__input-error error-trans_type text-danger mt-2"></div>
                        </div>
                        <div class="mt-3">
                            <label for="edit_parent_id" class="form-label">Parent Category</label>
                            <select id="edit_parent_id" name="parent_id" class="w-full tom-selects">
                                <option value="">Select Parent Category</option>
                            </select>
                        </div>
                        <div class="mt-3">
                            <label for="audit_status" class="form-label">Audit Status</label>
                            <div class="form-check form-switch">
                                <input id="audit_status" class="form-check-input" name="audit_status" value="1" type="checkbox">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div style="float: left;" class="mt-1">
                            <div class="form-check form-switch">
                                <label class="form-check-label mr-3 ml-0" for="status">Status</label>
                                <input id="status" checked class="form-check-input" name="status" value="1" type="checkbox">
                            </div>
                        </div>
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" id="updateCategory" class="btn btn-primary w-auto">
                            Update
                            <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg"
                                stroke="white" class="w-4 h-4 ml-2">
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
                        <input type="hidden" name="id" value="0"/>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Edit Storage Modal -->


    <!-- BEGIN: Success Modal Content -->
    <div id="successModal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="check-circle" class="w-16 h-16 text-success mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 successModalTitle"></div>
                        <div class="text-slate-500 mt-2 successModalDesc"></div>
                    </div>
                    <div class="px-5 pb-8 text-center">
                        <button type="button" data-action="none" class="successCloser btn btn-primary w-24">Ok</button>
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
    @vite('resources/js/acc-category.js')
@endsection