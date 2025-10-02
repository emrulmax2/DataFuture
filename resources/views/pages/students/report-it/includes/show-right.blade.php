<div class="intro-y box mt-5">
    <div class="flex flex-col sm:flex-row items-center p-5 border-b border-slate-200/60 dark:border-darkmode-400">
        <h2 class="font-medium text-base mr-auto">
            Report details
        </h2>
    </div>
    <div class="p-5">
        <div class="grid grid-cols-12 gap-5">
            <div class="col-span-12 lg:col-span-6">
                @if(isset($reportItAll))
                <!-- Display report details only show data needed no input text-->

                <div class="mb-3">
                    <div class="font-medium text-md">Issue Type</div>
                    <div class="my-2 mx-3">{{ $reportItAll->issueType->name ?? 'N/A' }}</div>
                </div>
                <div class="mb-3">
                    <div  class="font-medium text-md">Venue</div>
                    <div  class="my-2 mx-3">{{ $reportItAll->venue->name ?? 'N/A' }}</div>
                </div>
                <div class="mb-3">
                    <div class="font-medium text-md">Description</div>
                    <div class="my-2 mx-3">{{ $reportItAll->description }}</div>
                </div>
                <div class="mb-3">
                    <div class="font-medium text-md">Created At</div>
                    <div class="my-2 mx-3">{{ $reportItAll->created_at }}</div>
                </div>
                <div class="mb-3">
                    <div class="font-medium text-md">Last Modified By</div>
                    <div class="my-2 mx-3"><span class="inline-block px-2 py-1 text-xs font-semibold text-blue-800 bg-blue-200 rounded"> {{ $reportItAll->employee_name }}</span></div>
                </div>
                
                @endif
            </div>
        </div>
    </div>
</div>
<div class="intro-y box my-5">
    <div class="flex flex-col sm:flex-row items-center p-5 border-b border-slate-200/60 dark:border-darkmode-400">
        <h2 class="font-medium text-base mr-auto">
            Attachments
        </h2>
    </div>
    <div class="p-5">
        <div class="grid grid-cols-12 gap-5">
            <div class="col-span-12">
                <div id="addItems" class="col-span-12 w-full mt-3 xl:mt-0 flex-1 border-2 border-dashed dark:border-darkmode-400 rounded-md py-4">
                    <div id="AddItemBox" class="grid grid-cols-10 gap-5 pl-4 pr-5">
                        
                    @foreach($reportItAll->uploads as $upload)
                            
                            @if($upload->file_type == 'image' )
                                
                                <div class="col-span-5 h-28 relative image-fit cursor-pointer zoom-in">
                                    <img class="rounded-md w-full h-full object-cover" data-action="zoom" alt="{{ $upload->file_name }}" src="{{ isset($upload->file_image_url) ? $upload->file_image_url : asset('dist/images/profile-10.jpg') }}">
                                </div>

                            @elseif($upload->file_type == 'document')
                             <div class="col-span-10 h-10 relative flex items-center border border-gray-300 rounded-md p-2">
                                <i data-lucide="file-text" class="w-6 h-6 text-gray-500 mr-2"></i>
                                <a href="{{ asset('storage/'.$upload->file_path) }}" target="_blank" class="text-blue-600 hover:underline">{{ $upload->file_name }}</a>
                             </div>
                            @else
                                <div class="col-span-10 h-10 relative flex items-center border border-gray-300 rounded-md p-2">
                                <i data-lucide="file" class="w-6 h-6 text-gray-500 mr-2"></i>
                                <a href="{{ asset('storage/'.$upload->file_path) }}" target="_blank" class="text-blue-600 hover:underline">{{ $upload->file_name }}</a>
                                </div>
                            @endif
                    @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>