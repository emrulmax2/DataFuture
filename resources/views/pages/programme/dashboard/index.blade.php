@extends('../layout/' . $layout)

@section('subhead')
    <title>Dashboard - Enigma - Tailwind HTML Admin Template</title>
@endsection

@section('subcontent')
    <div class="grid grid-cols-12 gap-6">
        <div class="col-span-12 2xl:col-span-9 pt-5">
            <div class="intro-y block sm:flex items-center h-10">
                <h2 class="text-lg font-medium truncate mr-5">Daily Class Information</h2>
                <div class="flex items-center sm:ml-auto mt-3 sm:mt-0">
                    <div class="btn box flex items-center text-slate-600 dark:text-slate-300 p-0 pl-2">
                        <i data-lucide="sliders-horizontal" class="hidden sm:block w-4 h-4 mr-2"></i>
                        <select class="form-control w-full border-0">
                            <option value="">All Course</option>
                            <option value="1">HND in Business</option>
                            <option value="1">HND in Hospitality Management</option>
                        </select>
                    </div>
                    <button type="button" class="ml-3 btn box flex items-center text-slate-600 dark:text-slate-300">
                        <i data-lucide="calendar-days" class="hidden sm:block w-4 h-4 mr-2"></i> {{ date('jS M, Y')}}
                    </button>
                    <button class="ml-3 btn box flex items-center text-slate-600 dark:text-slate-300">
                        <i data-lucide="calendar-clock" class="hidden sm:block w-4 h-4 mr-2"></i> {{ date('H:i:s') }}
                    </button>
                </div>
            </div>
            <div class="intro-y overflow-auto lg:overflow-visible mt-8 sm:mt-0">
                <table class="table table-report sm:mt-2">
                    <thead>
                        <tr>
                            <th class="whitespace-nowrap uppercase">Schedule</th>
                            <th class="whitespace-nowrap uppercase">Module</th>
                            <th class="text-left whitespace-nowrap uppercase">Tutor</th>
                            <th class="text-left whitespace-nowrap uppercase">Room</th>
                            <th class="text-left whitespace-nowrap uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="intro-x">
                            <td>
                                <span class="font-fedium">10:00</span>
                            </td>
                            <td>
                                <div class="flex items-center">
                                    <div class="rounded-full text-lg bg-success text-white cursor-pointer font-medium w-10 h-10 inline-flex justify-center items-center">B</div>
                                    <div class="ml-4">
                                        <a href="" class="font-medium whitespace-nowrap">Business Environment</a>
                                        <div class="text-slate-500 text-xs whitespace-nowrap mt-0.5">HND in Business</div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-left text-success">DR. K Razi</td>
                            <td class="text-left">BH. 101</td>
                            <td class="text-left">
                                <span class="font-medium text-success">Started 10:10</span>
                            </td>
                        </tr>
                        <tr class="intro-x">
                            <td>
                                <span class="font-fedium">10:00</span>
                            </td>
                            <td>
                                <div class="flex items-center">
                                    <div class="rounded-full text-lg bg-success text-white cursor-pointer font-medium w-10 h-10 inline-flex justify-center items-center">B</div>
                                    <div class="ml-4">
                                        <a href="" class="font-medium whitespace-nowrap">Business Environment</a>
                                        <div class="text-slate-500 text-xs whitespace-nowrap mt-0.5">HND in Business</div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-left text-danger">DR. Sabrina</td>
                            <td class="text-left">BH. 101</td>
                            <td class="text-left">
                                <span class="font-medium text-danger">Starting Shortly</span>
                            </td>
                        </tr>
                        <tr class="intro-x">
                            <td>
                                <span class="font-fedium">10:00</span>
                            </td>
                            <td>
                                <div class="flex items-center">
                                    <div class="rounded-full text-lg bg-success text-white cursor-pointer font-medium w-10 h-10 inline-flex justify-center items-center">B</div>
                                    <div class="ml-4">
                                        <a href="" class="font-medium whitespace-nowrap">Business Environment</a>
                                        <div class="text-slate-500 text-xs whitespace-nowrap mt-0.5">HND in Business</div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-left text-success">DR. Gorge</td>
                            <td class="text-left">BH. 101</td>
                            <td class="text-left">
                                <span class="font-medium text-warning">Starting Shortly</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            
            <div class="intro-y block sm:flex items-center h-10 pt-5 mt-5">
                <h2 class="text-lg font-medium truncate mr-5">Class Started</h2>
                <div class="flex items-center sm:ml-auto mt-3 sm:mt-0">
                    <div class="btn box flex items-center text-slate-600 dark:text-slate-300 p-0 pl-2">
                        <i data-lucide="sliders-horizontal" class="hidden sm:block w-4 h-4 mr-2"></i>
                        <select class="form-control w-full border-0">
                            <option value="">All Course</option>
                            <option value="1">HND in Business</option>
                            <option value="1">HND in Hospitality Management</option>
                        </select>
                    </div>
                    <button type="button" class="ml-3 btn box flex items-center text-slate-600 dark:text-slate-300">
                        <i data-lucide="calendar-days" class="hidden sm:block w-4 h-4 mr-2"></i> {{ date('jS M, Y')}}
                    </button>
                    <button class="ml-3 btn box flex items-center text-slate-600 dark:text-slate-300">
                        <i data-lucide="calendar-clock" class="hidden sm:block w-4 h-4 mr-2"></i> {{ date('H:i:s') }}
                    </button>
                </div>
            </div>
            <div class="intro-y overflow-auto lg:overflow-visible mt-8 sm:mt-0">
                <table class="table table-report sm:mt-2">
                    <thead>
                        <tr>
                            <th class="whitespace-nowrap uppercase">Schedule</th>
                            <th class="whitespace-nowrap uppercase">Module</th>
                            <th class="text-left whitespace-nowrap uppercase">Tutor</th>
                            <th class="text-left whitespace-nowrap uppercase">Room</th>
                            <th class="text-left whitespace-nowrap uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="intro-x">
                            <td>
                                <span class="font-fedium text-danger">10:00 - Started 10:10</span>
                            </td>
                            <td>
                                <div class="flex items-center">
                                    <div class="rounded-full text-lg bg-success text-white cursor-pointer font-medium w-10 h-10 inline-flex justify-center items-center">B</div>
                                    <div class="ml-4">
                                        <a href="" class="font-medium whitespace-nowrap">Business Environment</a>
                                        <div class="text-slate-500 text-xs whitespace-nowrap mt-0.5">HND in Business</div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-left text-success">DR. K Razi</td>
                            <td class="text-left">BH. 101</td>
                            <td class="text-left">
                                <div class="rounded-full text-lg bg-pending text-white cursor-pointer font-medium w-10 h-10 inline-flex justify-center items-center">AA</div>
                            </td>
                        </tr>
                        <tr class="intro-x">
                            <td>
                                <span class="font-fedium text-danger">10:00 - Started 10:08</span>
                            </td>
                            <td>
                                <div class="flex items-center">
                                    <div class="rounded-full text-lg bg-success text-white cursor-pointer font-medium w-10 h-10 inline-flex justify-center items-center">B</div>
                                    <div class="ml-4">
                                        <a href="" class="font-medium whitespace-nowrap">Business Environment</a>
                                        <div class="text-slate-500 text-xs whitespace-nowrap mt-0.5">HND in Business</div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-left text-danger">DR. Sabrina</td>
                            <td class="text-left">BH. 101</td>
                            <td class="text-left">
                                <div class="rounded-full text-lg bg-success text-white cursor-pointer font-medium w-10 h-10 inline-flex justify-center items-center">AC</div>
                            </td>
                        </tr>
                        <tr class="intro-x">
                            <td>
                                <span class="font-fedium text-success">10:00 - Started 10:05</span>
                            </td>
                            <td>
                                <div class="flex items-center">
                                    <div class="rounded-full text-lg bg-success text-white cursor-pointer font-medium w-10 h-10 inline-flex justify-center items-center">B</div>
                                    <div class="ml-4">
                                        <a href="" class="font-medium whitespace-nowrap">Business Environment</a>
                                        <div class="text-slate-500 text-xs whitespace-nowrap mt-0.5">HND in Business</div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-left text-success">DR. Gorge</td>
                            <td class="text-left">BH. 101</td>
                            <td class="text-left">
                                <div class="rounded-full text-lg bg-success text-white cursor-pointer font-medium w-10 h-10 inline-flex justify-center items-center">AC</div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>


            <div class="intro-y block sm:flex items-center h-10 pt-5 mt-5">
                <h2 class="text-lg font-medium truncate mr-5">Class Finished</h2>
                <div class="flex items-center sm:ml-auto mt-3 sm:mt-0">
                    <div class="btn box flex items-center text-slate-600 dark:text-slate-300 p-0 pl-2">
                        <i data-lucide="sliders-horizontal" class="hidden sm:block w-4 h-4 mr-2"></i>
                        <select class="form-control w-full border-0">
                            <option value="">All Course</option>
                            <option value="1">HND in Business</option>
                            <option value="1">HND in Hospitality Management</option>
                        </select>
                    </div>
                    <button type="button" class="ml-3 btn box flex items-center text-slate-600 dark:text-slate-300">
                        <i data-lucide="calendar-days" class="hidden sm:block w-4 h-4 mr-2"></i> {{ date('jS M, Y')}}
                    </button>
                    <button class="ml-3 btn box flex items-center text-slate-600 dark:text-slate-300">
                        <i data-lucide="calendar-clock" class="hidden sm:block w-4 h-4 mr-2"></i> {{ date('H:i:s') }}
                    </button>
                </div>
            </div>
            <div class="intro-y overflow-auto lg:overflow-visible mt-8 sm:mt-0">
                <table class="table table-report sm:mt-2">
                    <thead>
                        <tr>
                            <th class="whitespace-nowrap uppercase">Schedule</th>
                            <th class="whitespace-nowrap uppercase">Module</th>
                            <th class="text-left whitespace-nowrap uppercase">Tutor</th>
                            <th class="text-left whitespace-nowrap uppercase">Room</th>
                            <th class="text-left whitespace-nowrap uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="intro-x">
                            <td>
                                <span class="font-fedium text-danger">
                                    Scheduled 10:00 <br/>
                                    10:10 - 12:47
                                </span>
                            </td>
                            <td>
                                <div class="flex items-center">
                                    <div class="rounded-full text-lg bg-success text-white cursor-pointer font-medium w-10 h-10 inline-flex justify-center items-center">B</div>
                                    <div class="ml-4">
                                        <a href="" class="font-medium whitespace-nowrap">Business Environment</a>
                                        <div class="text-slate-500 text-xs whitespace-nowrap mt-0.5">HND in Business</div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-left text-success">DR. K Razi</td>
                            <td class="text-left">BH. 101</td>
                            <td class="text-left">
                                <div class="rounded-full text-lg bg-pending text-white cursor-pointer font-medium w-10 h-10 inline-flex justify-center items-center">AA</div>
                            </td>
                        </tr>
                        <tr class="intro-x">
                            <td>
                                <span class="font-fedium text-danger">
                                    Scheduled 10:00 <br/> 
                                    10:08 - 12:00
                                </span>
                            </td>
                            <td>
                                <div class="flex items-center">
                                    <div class="rounded-full text-lg bg-success text-white cursor-pointer font-medium w-10 h-10 inline-flex justify-center items-center">B</div>
                                    <div class="ml-4">
                                        <a href="" class="font-medium whitespace-nowrap">Business Environment</a>
                                        <div class="text-slate-500 text-xs whitespace-nowrap mt-0.5">HND in Business</div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-left text-danger">DR. Sabrina</td>
                            <td class="text-left">BH. 101</td>
                            <td class="text-left">
                                <div class="rounded-full text-lg bg-success text-white cursor-pointer font-medium w-10 h-10 inline-flex justify-center items-center">AC</div>
                            </td>
                        </tr>
                        <tr class="intro-x">
                            <td>
                                <span class="font-fedium text-success">
                                    Scheduled 10:00 <br/>
                                    10:05 - 12:00
                                </span>
                            </td>
                            <td>
                                <div class="flex items-center">
                                    <div class="rounded-full text-lg bg-success text-white cursor-pointer font-medium w-10 h-10 inline-flex justify-center items-center">B</div>
                                    <div class="ml-4">
                                        <a href="" class="font-medium whitespace-nowrap">Business Environment</a>
                                        <div class="text-slate-500 text-xs whitespace-nowrap mt-0.5">HND in Business</div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-left text-success">DR. Gorge</td>
                            <td class="text-left">BH. 101</td>
                            <td class="text-left">
                                <div class="rounded-full text-lg bg-success text-white cursor-pointer font-medium w-10 h-10 inline-flex justify-center items-center">AC</div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="col-span-12 2xl:col-span-3">
            <div class="2xl:border-l -mb-10 pb-10">
                <div class="2xl:pl-6 grid grid-cols-12 gap-x-6 2xl:gap-x-0 gap-y-6">
                    <div class="col-span-12 md:col-span-6 xl:col-span-12 mt-3 2xl:mt-5">
                        <div class="intro-x flex items-center h-10">
                            <h2 class="text-lg font-medium truncate mr-5">Student Attendance</h2>
                            <a href="#" class="ml-auto text-primary truncate">Show More</a>
                        </div>
                        <div class="mt-5 intro-x">
                            <div class="report-box-2 before:hidden xl:before:block intro-y mt-5">
                                <div class="box p-5">
                                    <div class="mt-3">
                                        <div class="h-[196px]">
                                            <canvas id="report-donut-chart"></canvas>
                                        </div>
                                    </div>
                                    <div class="w-52 sm:w-auto mx-auto mt-8">
                                        <div class="flex items-center">
                                            <div class="w-2 h-2 bg-primary rounded-full mr-3"></div>
                                            <span class="truncate">May 2023 HND</span>
                                            <span class="font-medium ml-auto">62%</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-span-12 md:col-span-6 xl:col-span-4 2xl:col-span-12 mt-3">
                        <div class="intro-x flex items-center h-10">
                            <h2 class="text-lg font-medium truncate mr-5">Staff Absence Today</h2>
                            <a href="#" class="ml-auto text-primary truncate">Show More</a>
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
                </div>
            </div>
        </div>
    </div>
@endsection
