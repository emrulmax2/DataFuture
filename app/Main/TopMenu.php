<?php

namespace App\Main;

class TopMenu
{
    /**
     * List of top menu items.
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
            $remoteAccess = auth()->user()->remote_access;
            $menu = [
                'dashboard' => [
                    'icon' => 'home',
                    'title' => 'Dashboard',
                    'route_name' => 'dashboard',
                    'params' => []
                ],
            ];
            if($remoteAccess && isset($priv['course_manage']) && $priv['course_manage'] == 1):
                $menu['course.management'] = [
                    'icon' => 'book-open',
                    'title' => 'Courses Management',
                    'route_name' => 'course.management',
                    'params' => []
                ];
            endif;
            if($remoteAccess && isset($priv['student_manage']) && $priv['student_manage'] == 1):
                $studentSubMenu = [
                    'admission' => [
                        'route_name' => 'admission',
                        'params' => [],
                        'title' => 'Admission'
                    ],
                ];

                // The Live Student privilege used to hide only the dashboard tile,
                // leaving this menu entry (and the URL behind it) open to anyone
                // with student_manage. It now gates the entry too.
                if(isset($priv['live']) && $priv['live'] == 1):
                    $studentSubMenu['student'] = [
                        'route_name' => 'student',
                        'params' => [],
                        'title' => 'Live'
                    ];
                endif;

                $studentSubMenu['agent_management'] = [
                    'route_name' => 'agent.management',
                    'params' => [],
                    'title' => 'Agent Management'
                ];

                $menu['students'] = [
                    'icon' => 'users',
                    'title' => 'Student Management',
                    'sub_menu' => $studentSubMenu
                ];
            endif;
            if($remoteAccess && isset($priv['settings']) && $priv['settings'] == 1):
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
