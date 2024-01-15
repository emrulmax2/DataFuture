<div class="col-span-12 2xl:col-span-3">
    <div class="2xl:border-l -mb-10 pb-10">
        <div class="2xl:pl-6 grid grid-cols-12 gap-x-6 2xl:gap-x-0 gap-y-6">
            <div class="col-span-12 flex lg:block flex-col-reverse mt-5">
                <div class="intro-y box p-5 bg-primary text-white mt-5">
                    <div class="flex items-center">
                        <div class="font-medium text-lg">Important Update</div>
                        <div class="text-xs bg-white dark:bg-primary dark:text-white text-slate-700 px-1 rounded-md ml-auto">New</div>
                    </div>
                    <div class="mt-4">Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s.</div>
                    <div class="font-medium flex mt-5">
                        <button type="button" class="btn py-1 px-2 border-white text-white dark:text-slate-300 dark:bg-darkmode-400 dark:border-darkmode-400">Take Action</button>
                        <button type="button" class="btn py-1 px-2 border-transparent text-white dark:border-transparent ml-auto">Dismiss</button>
                    </div>
                </div>
            </div>
            <!-- BEGIN: Important Notes -->
            <div class="col-span-12 md:col-span-6 xl:col-span-12 xl:col-start-1 xl:row-start-1 2xl:col-start-auto 2xl:row-start-auto mt-3 hidden">
                <div class="intro-x flex items-center h-10">
                    <h2 class="text-lg font-medium truncate mr-auto">Important Notes</h2>
                    <button data-carousel="important-notes" data-target="prev" class="tiny-slider-navigator btn px-2 border-slate-300 text-slate-600 dark:text-slate-300 mr-2">
                        <i data-lucide="chevron-left" class="w-4 h-4"></i>
                    </button>
                    <button data-carousel="important-notes" data-target="next" class="tiny-slider-navigator btn px-2 border-slate-300 text-slate-600 dark:text-slate-300 mr-2">
                        <i data-lucide="chevron-right" class="w-4 h-4"></i>
                    </button>
                </div>
                <div class="mt-5 intro-x">
                    <div class="box zoom-in">
                        <div class="tiny-slider" id="important-notes">
                            <div class="p-5">
                                <div class="text-base font-medium truncate">Lorem Ipsum is simply dummy text</div>
                                <div class="text-slate-400 mt-1">20 Hours ago</div>
                                <div class="text-slate-500 text-justify mt-1">Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s.</div>
                                <div class="font-medium flex mt-5">
                                    <button type="button" class="btn btn-secondary py-1 px-2">View Notes</button>
                                    <button type="button" class="btn btn-outline-secondary py-1 px-2 ml-auto ml-auto">Dismiss</button>
                                </div>
                            </div>
                            <div class="p-5">
                                <div class="text-base font-medium truncate">Lorem Ipsum is simply dummy text</div>
                                <div class="text-slate-400 mt-1">20 Hours ago</div>
                                <div class="text-slate-500 text-justify mt-1">Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s.</div>
                                <div class="font-medium flex mt-5">
                                    <button type="button" class="btn btn-secondary py-1 px-2">View Notes</button>
                                    <button type="button" class="btn btn-outline-secondary py-1 px-2 ml-auto ml-auto">Dismiss</button>
                                </div>
                            </div>
                            <div class="p-5">
                                <div class="text-base font-medium truncate">Lorem Ipsum is simply dummy text</div>
                                <div class="text-slate-400 mt-1">20 Hours ago</div>
                                <div class="text-slate-500 text-justify mt-1">Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s.</div>
                                <div class="font-medium flex mt-5">
                                    <button type="button" class="btn btn-secondary py-1 px-2">View Notes</button>
                                    <button type="button" class="btn btn-outline-secondary py-1 px-2 ml-auto ml-auto">Dismiss</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- END: Important Notes -->
            <!-- BEGIN: Transactions -->
            <div class="col-span-12 md:col-span-6 xl:col-span-4 2xl:col-span-12 mt-3 2xl:mt-8">
                <div class="intro-x flex items-center h-10">
                    <h2 class="text-lg font-medium truncate mr-5">Upcomming Classes</h2>
                    <a href="" class="ml-auto text-primary truncate">Show More</a>
                </div>
                <div class="mt-5">
                    @foreach (array_slice($fakers, 0, 5) as $faker)
                        <div class="intro-x">
                            <div class="box px-5 py-3 mb-3 flex items-center zoom-in">
                                <div class="w-10 h-10 flex-none image-fit rounded-full overflow-hidden">
                                    <img alt="London Churchill College" src="{{ asset('build/assets/images/' . $faker['photos'][0]) }}">
                                </div>
                                <div class="ml-4 mr-auto">
                                    <div class="font-medium">{{ $faker['users'][0]['name'] }}</div>
                                    <div class="text-slate-500 text-xs mt-0.5">{{ $faker['dates'][0] }}</div>
                                </div>
                                <div class="{{ $faker['true_false'][0] ? 'text-success' : 'text-danger' }}">{{ $faker['true_false'][0] ? '+' : '-' }}${{ $faker['totals'][0] }}</div>
                            </div>
                        </div>
                    @endforeach
                    <a href="" class="intro-x w-full block text-center rounded-md py-3 border border-dotted border-slate-400 dark:border-darkmode-300 text-slate-500">View More</a>
                </div>
            </div>
            <!-- END: Transactions -->
            <!-- BEGIN: Recent Activities -->
            <div class="col-span-12 md:col-span-6 xl:col-span-4 2xl:col-span-12 mt-3">
                <div class="intro-x flex items-center h-10">
                    <h2 class="text-lg font-medium truncate mr-5">Recent Activities</h2>
                    <a href="" class="ml-auto text-primary truncate">Show More</a>
                </div>
                <div class="mt-5 relative before:block before:absolute before:w-px before:h-[85%] before:bg-slate-200 before:dark:bg-darkmode-400 before:ml-5 before:mt-5">
                    <div class="intro-x relative flex items-center mb-3">
                        <div class="before:block before:absolute before:w-20 before:h-px before:bg-slate-200 before:dark:bg-darkmode-400 before:mt-5 before:ml-5">
                            <div class="w-10 h-10 flex-none image-fit rounded-full overflow-hidden">
                                <img alt="London Churchill College" src="{{ asset('build/assets/images/' . $fakers[9]['photos'][0]) }}">
                            </div>
                        </div>
                        <div class="box px-5 py-3 ml-4 flex-1 zoom-in">
                            <div class="flex items-center">
                                <div class="font-medium">{{ $fakers[9]['users'][0]['name'] }}</div>
                                <div class="text-xs text-slate-500 ml-auto">07:00 PM</div>
                            </div>
                            <div class="text-slate-500 mt-1">Has joined the team</div>
                        </div>
                    </div>
                    <div class="intro-x relative flex items-center mb-3">
                        <div class="before:block before:absolute before:w-20 before:h-px before:bg-slate-200 before:dark:bg-darkmode-400 before:mt-5 before:ml-5">
                            <div class="w-10 h-10 flex-none image-fit rounded-full overflow-hidden">
                                <img alt="London Churchill College" src="{{ asset('build/assets/images/' . $fakers[8]['photos'][0]) }}">
                            </div>
                        </div>
                        <div class="box px-5 py-3 ml-4 flex-1 zoom-in">
                            <div class="flex items-center">
                                <div class="font-medium">{{ $fakers[8]['users'][0]['name'] }}</div>
                                <div class="text-xs text-slate-500 ml-auto">07:00 PM</div>
                            </div>
                            <div class="text-slate-500">
                                <div class="mt-1">Added 3 new photos</div>
                                <div class="flex mt-2">
                                    <div class="tooltip w-8 h-8 image-fit mr-1 zoom-in" title="{{ $fakers[0]['products'][0]['name'] }}">
                                        <img alt="London Churchill College" class="rounded-md border border-white" src="{{ asset('build/assets/images/' . $fakers[8]['images'][0]) }}">
                                    </div>
                                    <div class="tooltip w-8 h-8 image-fit mr-1 zoom-in" title="{{ $fakers[1]['products'][0]['name'] }}">
                                        <img alt="London Churchill College" class="rounded-md border border-white" src="{{ asset('build/assets/images/' . $fakers[8]['images'][1]) }}">
                                    </div>
                                    <div class="tooltip w-8 h-8 image-fit mr-1 zoom-in" title="{{ $fakers[2]['products'][0]['name'] }}">
                                        <img alt="London Churchill College" class="rounded-md border border-white" src="{{ asset('build/assets/images/' . $fakers[8]['images'][2]) }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="intro-x text-slate-500 text-xs text-center my-4">12 November</div>
                    <div class="intro-x relative flex items-center mb-3">
                        <div class="before:block before:absolute before:w-20 before:h-px before:bg-slate-200 before:dark:bg-darkmode-400 before:mt-5 before:ml-5">
                            <div class="w-10 h-10 flex-none image-fit rounded-full overflow-hidden">
                                <img alt="London Churchill College" src="{{ asset('build/assets/images/' . $fakers[7]['photos'][0]) }}">
                            </div>
                        </div>
                        <div class="box px-5 py-3 ml-4 flex-1 zoom-in">
                            <div class="flex items-center">
                                <div class="font-medium">{{ $fakers[7]['users'][0]['name'] }}</div>
                                <div class="text-xs text-slate-500 ml-auto">07:00 PM</div>
                            </div>
                            <div class="text-slate-500 mt-1">Has changed <a class="text-primary" href="">{{ $fakers[7]['products'][0]['name'] }}</a> price and description</div>
                        </div>
                    </div>
                    <div class="intro-x relative flex items-center mb-3">
                        <div class="before:block before:absolute before:w-20 before:h-px before:bg-slate-200 before:dark:bg-darkmode-400 before:mt-5 before:ml-5">
                            <div class="w-10 h-10 flex-none image-fit rounded-full overflow-hidden">
                                <img alt="London Churchill College" src="{{ asset('build/assets/images/' . $fakers[6]['photos'][0]) }}">
                            </div>
                        </div>
                        <div class="box px-5 py-3 ml-4 flex-1 zoom-in">
                            <div class="flex items-center">
                                <div class="font-medium">{{ $fakers[6]['users'][0]['name'] }}</div>
                                <div class="text-xs text-slate-500 ml-auto">07:00 PM</div>
                            </div>
                            <div class="text-slate-500 mt-1">Has changed <a class="text-primary" href="">{{ $fakers[6]['products'][0]['name'] }}</a> description</div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- END: Recent Activities -->
            
            <!-- BEGIN: Schedules -->
            <div class="col-span-12 md:col-span-6 xl:col-span-4 2xl:col-span-12 xl:col-start-1 xl:row-start-2 2xl:col-start-auto 2xl:row-start-auto mt-3 hidden">
                <div class="intro-x flex items-center h-10">
                    <h2 class="text-lg font-medium truncate mr-5">Schedules</h2>
                    <a href="" class="ml-auto text-primary truncate flex items-center">
                        <i data-lucide="plus" class="w-4 h-4 mr-1"></i> Add New Schedules
                    </a>
                </div>
                <div class="mt-5">
                    <div class="intro-x box">
                        <div class="p-5">
                            <div class="flex">
                                <i data-lucide="chevron-left" class="w-5 h-5 text-slate-500"></i>
                                <div class="font-medium text-base mx-auto">April</div>
                                <i data-lucide="chevron-right" class="w-5 h-5 text-slate-500"></i>
                            </div>
                            <div class="grid grid-cols-7 gap-4 mt-5 text-center">
                                <div class="font-medium">Su</div>
                                <div class="font-medium">Mo</div>
                                <div class="font-medium">Tu</div>
                                <div class="font-medium">We</div>
                                <div class="font-medium">Th</div>
                                <div class="font-medium">Fr</div>
                                <div class="font-medium">Sa</div>
                                <div class="py-0.5 rounded relative text-slate-500">29</div>
                                <div class="py-0.5 rounded relative text-slate-500">30</div>
                                <div class="py-0.5 rounded relative text-slate-500">31</div>
                                <div class="py-0.5 rounded relative">1</div>
                                <div class="py-0.5 rounded relative">2</div>
                                <div class="py-0.5 rounded relative">3</div>
                                <div class="py-0.5 rounded relative">4</div>
                                <div class="py-0.5 rounded relative">5</div>
                                <div class="py-0.5 bg-success/20 dark:bg-success/30 rounded relative">6</div>
                                <div class="py-0.5 rounded relative">7</div>
                                <div class="py-0.5 bg-primary text-white rounded relative">8</div>
                                <div class="py-0.5 rounded relative">9</div>
                                <div class="py-0.5 rounded relative">10</div>
                                <div class="py-0.5 rounded relative">11</div>
                                <div class="py-0.5 rounded relative">12</div>
                                <div class="py-0.5 rounded relative">13</div>
                                <div class="py-0.5 rounded relative">14</div>
                                <div class="py-0.5 rounded relative">15</div>
                                <div class="py-0.5 rounded relative">16</div>
                                <div class="py-0.5 rounded relative">17</div>
                                <div class="py-0.5 rounded relative">18</div>
                                <div class="py-0.5 rounded relative">19</div>
                                <div class="py-0.5 rounded relative">20</div>
                                <div class="py-0.5 rounded relative">21</div>
                                <div class="py-0.5 rounded relative">22</div>
                                <div class="py-0.5 bg-pending/20 dark:bg-pending/30 rounded relative">23</div>
                                <div class="py-0.5 rounded relative">24</div>
                                <div class="py-0.5 rounded relative">25</div>
                                <div class="py-0.5 rounded relative">26</div>
                                <div class="py-0.5 bg-primary/10 dark:bg-primary/50 rounded relative">27</div>
                                <div class="py-0.5 rounded relative">28</div>
                                <div class="py-0.5 rounded relative">29</div>
                                <div class="py-0.5 rounded relative">30</div>
                                <div class="py-0.5 rounded relative text-slate-500">1</div>
                                <div class="py-0.5 rounded relative text-slate-500">2</div>
                                <div class="py-0.5 rounded relative text-slate-500">3</div>
                                <div class="py-0.5 rounded relative text-slate-500">4</div>
                                <div class="py-0.5 rounded relative text-slate-500">5</div>
                                <div class="py-0.5 rounded relative text-slate-500">6</div>
                                <div class="py-0.5 rounded relative text-slate-500">7</div>
                                <div class="py-0.5 rounded relative text-slate-500">8</div>
                                <div class="py-0.5 rounded relative text-slate-500">9</div>
                            </div>
                        </div>
                        <div class="border-t border-slate-200/60 p-5">
                            <div class="flex items-center">
                                <div class="w-2 h-2 bg-pending rounded-full mr-3"></div>
                                <span class="truncate">UI/UX Workshop</span>
                                <span class="font-medium xl:ml-auto">23th</span>
                            </div>
                            <div class="flex items-center mt-4">
                                <div class="w-2 h-2 bg-primary rounded-full mr-3"></div>
                                <span class="truncate">VueJs Frontend Development</span>
                                <span class="font-medium xl:ml-auto">10th</span>
                            </div>
                            <div class="flex items-center mt-4">
                                <div class="w-2 h-2 bg-warning rounded-full mr-3"></div>
                                <span class="truncate">Laravel Rest API</span>
                                <span class="font-medium xl:ml-auto">31th</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- END: Schedules -->
        </div>
    </div>
</div>