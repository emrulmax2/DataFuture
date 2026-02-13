@extends('../layout/' . $layout)

@section('subhead')
    <title>Document / ID Card Replacement request / Printer Balance Top up</title>
@endsection

@section('subcontent')
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg flex font-medium mr-auto">Document / ID Card Replacement request / Printer Balance Top up</h2>
        <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
            <a href="{{ route('students.document-request-form.index') }}" class=" btn btn-primary text-white shadow-md ml-1 relative"><i data-lucide="file-box" class="w-4 h-4 mr-2"></i> My Orders 
                @if ($countPendingOrders > 0)
                <span id="orderCountBadge" class="absolute -top-2 -right-2 bg-red-500 text-white text-xs font-bold rounded-full w-6 h-6 flex items-center justify-center">
                {{ $countPendingOrders }}
                </span>
                @endif
            </a>
        </div>
    </div>
    <div class="grid grid-cols-12 gap-6 mt-5">
        <!-- BEGIN: Users Layout -->
        @foreach ($letter_sets as $letter_set)
            <form id="LetterFormRequest{{ $letter_set->id }}"  class="LetterFormRequest col-span-12 md:col-span-6 lg:col-span-4 xl:col-span-3" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="letter_set_id" value="{{ $letter_set->id }}">
                <input type="hidden" name="student_id" value="{{ $student->id }}">
                <input type="hidden" name="description" value="{{ $letter_set->letter_title }}" />
                <input type="hidden" name="student_consent" value="1" />
                <input type="hidden" name="term_declaration_id" value="{{ $current_term_id->id }}">
                <input type="hidden" name="status" value="Pending">
                @if($letter_set->id != 165)
                <input type="hidden" name="sub_amount" value="10.00">
                <input type="hidden" name="tax_amount" value="0.00">
                <input type="hidden" name="total_amount" value="10.00">
                @else
                <input type="hidden" name="sub_amount" value="5.00">
                <input type="hidden" name="tax_amount" value="0.00">
                <input type="hidden" name="total_amount" value="5.00">
                @endif
                <div class="intro-y ">
                    <div class="box">
                        <div class="p-5">
                            <div class="h-40 2xl:h-56 image-fit rounded-md overflow-hidden before:block before:absolute before:w-full before:h-full before:top-0 before:left-0 before:z-10 before:bg-gradient-to-t before:from-black before:to-black/10">
                                <img alt="London Churchill College" class="rounded-md" src="{{ file_exists(public_path('build/assets/images/products/'.$letter_set->letter_title.'.png')) 
        ? asset('build/assets/images/products/'.$letter_set->letter_title.'.png') 
        : asset('build/assets/images/products/student_general.png') }}">
                                
                                    <span class="absolute top-0 bg-pending/80 text-white text-xs m-5 px-2 py-1 rounded z-10 ">{{ $letter_set->letter_type }}</span>
                                
                                <div class="absolute bottom-0 text-white px-5 pb-6 z-10">
                                    <a href="" class="block font-medium text-base">{{ $letter_set->letter_title }}</a>
                                    <span class="text-white/90 text-xs mt-3">{{ $letter_set->letter_type }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="flex flex-col justify-between items-center p-5 border-t border-slate-200/60 dark:border-darkmode-400 w-full">
                            @if($letter_set->id != 159 && $letter_set->id != 165) 
                            <button type="button" data-letterid="{{ $letter_set->id }}" data-service_type="3 Working Days (Free)" data-studentid={{ $student->id }} class="add-tofree-cart ml-auto flex items-center btn btn-secondary text-slate-500 mr-auto shadow-md w-full  justify-center mb-5" href="javascript:; ">
                                <i data-lucide="shopping-basket" class="w-5 h-5 mr-2"></i>
                                 3 working days (Free)
                                <i data-loading-icon="puff" class="w-5 h-5 ml-2 hidden" ></i>
                            </button>
                            @endif
                            @if($letter_set->id != 159 && $letter_set->id != 165) 
                            <button type="button" data-letterid="{{ $letter_set->id }}" data-service_type="Same Day (cost £10.00)" data-studentid={{ $student->id }} class="add-topaid-cart ml-auto flex items-center btn btn-success text-white mr-auto shadow-md w-full  justify-center" href="javascript:; ">
                                <i data-lucide="shopping-cart" class="w-5 h-5 mr-2"></i>
                                 Same Day  (£10.00)
                                <i data-loading-icon="puff" class="w-5 h-5 ml-2 hidden"></i>
                            </button>
                            @elseif($letter_set->id == 165)
                            <button type="button" data-letterid="{{ $letter_set->id }}" data-service_type="Printer Top Up (cost £5.00)" data-studentid={{ $student->id }} class="add-topaid-cart ml-auto flex items-center btn btn-success text-white mr-auto shadow-md w-full  justify-center" href="javascript:; ">
                                <i data-lucide="shopping-cart" class="w-5 h-5 mr-2"></i>
                                 Printer Top Up (cost £5.00)
                                <i data-loading-icon="puff" class="w-5 h-5 ml-2 hidden"></i>
                            </button>
                            @else
                            <button type="button" data-letterid="{{ $letter_set->id }}" data-service_type="3 Working Days (cost £10.00)" data-studentid={{ $student->id }} class="add-topaid-cart ml-auto flex items-center btn btn-success text-white mr-auto shadow-md w-full  justify-center" href="javascript:; ">
                                <i data-lucide="shopping-cart" class="w-5 h-5 mr-2"></i>
                                 3 Working Days (cost £10.00)
                                <i data-loading-icon="puff" class="w-5 h-5 ml-2 hidden"></i>
                            </button>
                            @endif
                        </div>
                    </div>
                </div>
            </form>
        @endforeach
        <!-- END: Users Layout -->
    </div>
    
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
                        <button type="button" data-tw-dismiss="modal" class="btn btn-primary w-24">Ok</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Success Modal Content -->

        <!-- BEGIN: Success Modal Content -->
    
    <!-- BEGIN: Success Modal Content -->
    <div id="errorModal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="x-octagon" class="w-16 h-16 text-danger mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 text-danger errorModalTitle"></div>
                        <div class="text-slate-500 mt-2 errorModalDesc"></div>
                    </div>
                    
                    <div class="px-5 pb-8 text-center">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-danger w-24">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Success Modal Content -->
        <!-- END: Success Modal Content -->
@endsection


@section('script')
 @vite(['resources/js/document-requests.js'])
 @vite(['resources/js/add-to-cart.js'])
@endsection