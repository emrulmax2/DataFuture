<?php

namespace App\Main;

class SideMenu
{
    /**
     * List of side menu items.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public static function menu()
    {
        if(!is_null(\Auth::guard('agent')->user())):
            return [
                'dashboard' => [
                    'icon' => 'home',
                    'title' => 'Dashboard',
                    'params' => [],
                    'route_name' => 'agent.dashboard'
                ],
            ];
        elseif(!is_null(\Auth::guard('applicant')->user())):
            return [
                'dashboard' => [
                    'icon' => 'home',
                    'title' => 'Dashboard',
                    'params' => [],
                    'route_name' => 'applicant.dashboard'
                ],
            ];
        elseif(!is_null(\Auth::guard('student')->user())):
            return [
                'dashboard' => [
                    'icon' => 'home',
                    'title' => 'Dashboard',
                    'params' => [],
                    'route_name' => 'students.dashboard'
                ],
            ];
        elseif(!is_null(\Auth::user())):
            $priv = auth()->user()->priv();
            $menu = [
                'dashboard' => [
                    'icon' => 'home',
                    'title' => 'Dashboard',
                    'route_name' => 'dashboard',
                    'params' => []
                ],
            ];
            if(auth()->user()->remote_access && isset($priv['course_manage']) && $priv['course_manage'] == 1):
                $menu['course.management'] = [
                    'icon' => 'book-open',
                    'title' => 'Courses Management',
                    'route_name' => 'course.management',
                    'params' => []
                ];
            endif;
            if(auth()->user()->remote_access && isset($priv['student_manage']) && $priv['student_manage'] == 1):
                $menu['students'] = [
                    'icon' => 'users',
                    'title' => 'Student Management',
                    'sub_menu' => [
                        'admission' => [
                            'route_name' => 'admission',
                            'params' => [],
                            'title' => 'Admission'
                        ],
                        'student' => [
                            'route_name' => 'student',
                            'params' => [],
                            'title' => 'Live'
                        ],
                        'agent' => [
                            'route_name' => 'agent-user.index',
                            'params' => [],
                            'title' => 'Agent'
                        ]
                    ]
                ];
            endif;
            if(auth()->user()->remote_access && isset($priv['settings']) && $priv['settings'] == 1):
                $menu['site.setting'] = [
                    'icon' => 'settings',
                    'title' => 'Settings',
                    'route_name' => 'site.setting',
                    'params' => []
                ];
            endif;

            return $menu;
        else:
            $menu = [
                'dashboard' => [
                    'icon' => 'home',
                    'title' => 'Dashboard',
                    'route_name' => 'dashboard',
                    'params' => []
                ],
            ];
            return $menu;
        endif;
    }
}
