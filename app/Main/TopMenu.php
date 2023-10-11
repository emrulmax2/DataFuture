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
        if(!is_null(\Auth::guard('applicant')->user())):
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
        else:
            return [
                'dashboard' => [
                    'icon' => 'home',
                    'title' => 'Dashboard',
                    'route_name' => 'dashboard',
                    'params' => []
                ],
                'coursemanagement' => [
                    'icon' => 'book-open',
                    'title' => 'Courses Management',
                    'sub_menu' => [
                        'courses' => [
                            'icon' => '',
                            'title' => 'Courses & Semesters',
                            'sub_menu' => [
                                'semester' => [
                                    'icon' => '',
                                    'route_name' => 'semester',
                                    'params' => [],
                                    'title' => 'Semesters'
                                ],
                                'courses' => [
                                    'icon' => '',
                                    'route_name' => 'courses',
                                    'params' => [],
                                    'title' => 'Courses'
                                ],
                                'course.creation' => [
                                    'icon' => '',
                                    'route_name' => 'course.creation',
                                    'params' => [],
                                    'title' => 'Course Creations'
                                ],
                            ]
                        ],
                        'terms' => [
                            'icon' => '',
                            'title' => 'Terms & Modules',
                            'sub_menu' => [
                                'term.module.creation' => [
                                    'icon' => '',
                                    'route_name' => 'term.module.creation',
                                    'params' => [],
                                    'title' =>'Term Module Creations'
                                ],
                                'modulelevels' => [
                                    'icon' => '',
                                    'route_name' => 'modulelevels',
                                    'params' => [],
                                    'title' =>'Module Levels'
                                ],
                                'groups' => [
                                    'icon' => '',
                                    'route_name' => 'groups',
                                    'params' => [],
                                    'title' =>'Groups'
                                ],
                            ]
                        ],
                        'plans' => [
                            'icon' => '',
                            'title' => 'Plans',
                            'sub_menu' => [
                                'venues' => [
                                    'icon' => '',
                                    'route_name' => 'class.plan',
                                    'params' => [],
                                    'title' =>'Class Plans'
                                ],
                            ]
                        ],
                    ]
                ],
                'students' => [
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
                        ]
                    ]
                ],
                'management' => [
                    'icon' => 'layers',
                    'title' => 'Management',
                    'sub_menu' => [
                        'usersettings' => [
                            'icon' => '',
                            'title' => 'User Managment',
                            'sub_menu' => [
                                'users' => [
                                    'icon' => '',
                                    'route_name' => 'users',
                                    'params' => [],
                                    'title' => 'Users'
                                ],
                                'roles' => [
                                    'icon' => '',
                                    'route_name' => 'roles',
                                    'params' => [],
                                    'title' => 'Role'
                                ],
                                'permissioncategory' => [
                                    'icon' => '',
                                    'route_name' => 'permissioncategory',
                                    'params' => [],
                                    'title' => 'Permission Category'
                                ],
                                'department' => [
                                    'icon' => '',
                                    'route_name' => 'department',
                                    'params' => [],
                                    'title' => 'Department'
                                ],
                            ]
                        ],
                    ]
                ],
                'site.setting' => [
                    'icon' => 'settings',
                    'title' => 'Settings',
                    'route_name' => 'site.setting',
                    'params' => []
                ],
                /*'apps' => [ 
                    'icon' => 'activity',
                    'title' => 'Apps',
                    'sub_menu' => [
                        'users' => [
                            'icon' => 'users',
                            'title' => 'Users',
                            'sub_menu' => [
                                'users-layout-1' => [
                                    'icon' => '',
                                    'route_name' => 'users-layout-1',
                                    'params' => [
                                        'layout' => 'top-menu'
                                    ],
                                    'title' => 'Layout 1'
                                ],
                                'users-layout-2' => [
                                    'icon' => '',
                                    'route_name' => 'users-layout-2',
                                    'params' => [
                                        'layout' => 'top-menu'
                                    ],
                                    'title' => 'Layout 2'
                                ],
                                'users-layout-3' => [
                                    'icon' => '',
                                    'route_name' => 'users-layout-3',
                                    'params' => [
                                        'layout' => 'top-menu'
                                    ],
                                    'title' => 'Layout 3'
                                ]
                            ]
                        ],
                        'profile' => [
                            'icon' => 'trello',
                            'title' => 'Profile',
                            'sub_menu' => [
                                'profile-overview-1' => [
                                    'icon' => '',
                                    'route_name' => 'profile-overview-1',
                                    'params' => [
                                        'layout' => 'top-menu'
                                    ],
                                    'title' => 'Overview 1'
                                ],
                                'profile-overview-2' => [
                                    'icon' => '',
                                    'route_name' => 'profile-overview-2',
                                    'params' => [
                                        'layout' => 'top-menu'
                                    ],
                                    'title' => 'Overview 2'
                                ],
                                'profile-overview-3' => [
                                    'icon' => '',
                                    'route_name' => 'profile-overview-3',
                                    'params' => [
                                        'layout' => 'top-menu'
                                    ],
                                    'title' => 'Overview 3'
                                ]
                            ]
                        ],
                        'e-commerce' => [
                            'icon' => 'shopping-bag',
                            'title' => 'E-Commerce',
                            'sub_menu' => [
                                'categories' => [
                                    'icon' => '',
                                    'route_name' => 'categories',
                                    'params' => [
                                        'layout' => 'top-menu'
                                    ],
                                    'title' => 'Categories'
                                ],
                                'add-product' => [
                                    'icon' => '',
                                    'route_name' => 'add-product',
                                    'params' => [
                                        'layout' => 'top-menu'
                                    ],
                                    'title' => 'Add Product',
                                ],
                                'product-list' => [
                                    'icon' => '',
                                    'route_name' => 'product-list',
                                    'params' => [
                                        'layout' => 'top-menu'
                                    ],
                                    'title' => 'Product List'
                                ],
                                'product-grid' => [
                                    'icon' => '',
                                    'route_name' => 'product-grid',
                                    'params' => [
                                        'layout' => 'top-menu'
                                    ],
                                    'title' => 'Product Grid'
                                ],
                                'transaction-list' => [
                                    'icon' => '',
                                    'route_name' => 'transaction-list',
                                    'params' => [
                                        'layout' => 'top-menu'
                                    ],
                                    'title' => 'Transaction List'
                                ],
                                'transaction-detail' => [
                                    'icon' => '',
                                    'route_name' => 'transaction-detail',
                                    'params' => [
                                        'layout' => 'top-menu'
                                    ],
                                    'title' => 'Transaction Detail'
                                ],
                                'seller-list' => [
                                    'icon' => '',
                                    'route_name' => 'seller-list',
                                    'params' => [
                                        'layout' => 'top-menu'
                                    ],
                                    'title' => 'Seller List'
                                ],
                                'seller-detail' => [
                                    'icon' => '',
                                    'route_name' => 'seller-detail',
                                    'params' => [
                                        'layout' => 'top-menu'
                                    ],
                                    'title' => 'Seller Detail'
                                ],
                                'reviews' => [
                                    'icon' => '',
                                    'route_name' => 'reviews',
                                    'params' => [
                                        'layout' => 'top-menu'
                                    ],
                                    'title' => 'Reviews'
                                ],
                            ]
                        ],
                        'inbox' => [
                            'icon' => 'inbox',
                            'route_name' => 'inbox',
                            'params' => [
                                'layout' => 'top-menu'
                            ],
                            'title' => 'Inbox'
                        ],
                        'file-manager' => [
                            'icon' => 'folder',
                            'route_name' => 'file-manager',
                            'params' => [
                                'layout' => 'top-menu'
                            ],
                            'title' => 'File Manager'
                        ],
                        'point-of-sale' => [
                            'icon' => 'credit-card',
                            'route_name' => 'point-of-sale',
                            'params' => [
                                'layout' => 'top-menu'
                            ],
                            'title' => 'Point of Sale'
                        ],
                        'chat' => [
                            'icon' => 'message-square',
                            'route_name' => 'chat',
                            'params' => [
                                'layout' => 'top-menu'
                            ],
                            'title' => 'Chat'
                        ],
                        'post' => [
                            'icon' => 'file-text',
                            'route_name' => 'post',
                            'params' => [
                                'layout' => 'top-menu'
                            ],
                            'title' => 'Post'
                        ],
                        'calendar' => [
                            'icon' => 'calendar',
                            'route_name' => 'calendar',
                            'params' => [
                                'layout' => 'top-menu'
                            ],
                            'title' => 'Calendar'
                        ],
                        'crud' => [
                            'icon' => 'edit',
                            'title' => 'Crud',
                            'sub_menu' => [
                                'crud-data-list' => [
                                    'icon' => '',
                                    'route_name' => 'crud-data-list',
                                    'params' => [
                                        'layout' => 'top-menu'
                                    ],
                                    'title' => 'Data List'
                                ],
                                'crud-form' => [
                                    'icon' => '',
                                    'route_name' => 'crud-form',
                                    'params' => [
                                        'layout' => 'top-menu'
                                    ],
                                    'title' => 'Form'
                                ]
                            ]
                        ]
                    ]
                ],
                'pages' => [
                    'icon' => 'layout',
                    'title' => 'Pages',
                    'sub_menu' => [
                        'wizards' => [
                            'icon' => '',
                            'title' => 'Wizards',
                            'sub_menu' => [
                                'wizard-layout-1' => [
                                    'icon' => '',
                                    'route_name' => 'wizard-layout-1',
                                    'params' => [
                                        'layout' => 'top-menu'
                                    ],
                                    'title' => 'Layout 1'
                                ],
                                'wizard-layout-2' => [
                                    'icon' => '',
                                    'route_name' => 'wizard-layout-2',
                                    'params' => [
                                        'layout' => 'top-menu'
                                    ],
                                    'title' => 'Layout 2'
                                ],
                                'wizard-layout-3' => [
                                    'icon' => '',
                                    'route_name' => 'wizard-layout-3',
                                    'params' => [
                                        'layout' => 'top-menu'
                                    ],
                                    'title' => 'Layout 3'
                                ]
                            ]
                        ],
                        'blog' => [
                            'icon' => '',
                            'title' => 'Blog',
                            'sub_menu' => [
                                'blog-layout-1' => [
                                    'icon' => '',
                                    'route_name' => 'blog-layout-1',
                                    'params' => [
                                        'layout' => 'top-menu'
                                    ],
                                    'title' => 'Layout 1'
                                ],
                                'blog-layout-2' => [
                                    'icon' => '',
                                    'route_name' => 'blog-layout-2',
                                    'params' => [
                                        'layout' => 'top-menu'
                                    ],
                                    'title' => 'Layout 2'
                                ],
                                'blog-layout-3' => [
                                    'icon' => '',
                                    'route_name' => 'blog-layout-3',
                                    'params' => [
                                        'layout' => 'top-menu'
                                    ],
                                    'title' => 'Layout 3'
                                ]
                            ]
                        ],
                        'pricing' => [
                            'icon' => '',
                            'title' => 'Pricing',
                            'sub_menu' => [
                                'pricing-layout-1' => [
                                    'icon' => '',
                                    'route_name' => 'pricing-layout-1',
                                    'params' => [
                                        'layout' => 'top-menu'
                                    ],
                                    'title' => 'Layout 1'
                                ],
                                'pricing-layout-2' => [
                                    'icon' => '',
                                    'route_name' => 'pricing-layout-2',
                                    'params' => [
                                        'layout' => 'top-menu'
                                    ],
                                    'title' => 'Layout 2'
                                ]
                            ]
                        ],
                        'invoice' => [
                            'icon' => '',
                            'title' => 'Invoice',
                            'sub_menu' => [
                                'invoice-layout-1' => [
                                    'icon' => '',
                                    'route_name' => 'invoice-layout-1',
                                    'params' => [
                                        'layout' => 'top-menu'
                                    ],
                                    'title' => 'Layout 1'
                                ],
                                'invoice-layout-2' => [
                                    'icon' => '',
                                    'route_name' => 'invoice-layout-2',
                                    'params' => [
                                        'layout' => 'top-menu'
                                    ],
                                    'title' => 'Layout 2'
                                ]
                            ]
                        ],
                        'faq' => [
                            'icon' => '',
                            'title' => 'FAQ',
                            'sub_menu' => [
                                'faq-layout-1' => [
                                    'icon' => '',
                                    'route_name' => 'faq-layout-1',
                                    'params' => [
                                        'layout' => 'top-menu'
                                    ],
                                    'title' => 'Layout 1'
                                ],
                                'faq-layout-2' => [
                                    'icon' => '',
                                    'route_name' => 'faq-layout-2',
                                    'params' => [
                                        'layout' => 'top-menu'
                                    ],
                                    'title' => 'Layout 2'
                                ],
                                'faq-layout-3' => [
                                    'icon' => '',
                                    'route_name' => 'faq-layout-3',
                                    'params' => [
                                        'layout' => 'top-menu'
                                    ],
                                    'title' => 'Layout 3'
                                ]
                            ]
                        ],
                        'login' => [
                            'icon' => '',
                            'route_name' => 'login',
                            'params' => [
                                'layout' => 'top-menu'
                            ],
                            'title' => 'Login'
                        ],
                        'register' => [
                            'icon' => '',
                            'route_name' => 'register',
                            'params' => [
                                'layout' => 'top-menu'
                            ],
                            'title' => 'Register'
                        ],
                        'error-page' => [
                            'icon' => '',
                            'route_name' => 'error-page',
                            'params' => [
                                'layout' => 'top-menu'
                            ],
                            'title' => 'Error Page'
                        ],
                        'update-profile' => [
                            'icon' => '',
                            'route_name' => 'update-profile',
                            'params' => [
                                'layout' => 'top-menu'
                            ],
                            'title' => 'Update profile'
                        ],
                        'change-password' => [
                            'icon' => '',
                            'route_name' => 'change-password',
                            'params' => [
                                'layout' => 'top-menu'
                            ],
                            'title' => 'Change Password'
                        ]
                    ]
                ],*/
                'components' => [
                    'icon' => 'inbox',
                    'title' => 'Components',
                    'sub_menu' => [
                        'grid' => [
                            'icon' => '',
                            'title' => 'Grid',
                            'sub_menu' => [
                                'regular-table' => [
                                    'icon' => '',
                                    'route_name' => 'regular-table',
                                    'params' => [
                                        'layout' => 'top-menu'
                                    ],
                                    'title' => 'Regular Table'
                                ],
                                'tabulator' => [
                                    'icon' => '',
                                    'route_name' => 'tabulator',
                                    'params' => [
                                        'layout' => 'top-menu'
                                    ],
                                    'title' => 'Tabulator'
                                ]
                            ]
                        ],
                        'overlay' => [
                            'icon' => '',
                            'title' => 'Overlay',
                            'sub_menu' => [
                                'modal' => [
                                    'icon' => '',
                                    'route_name' => 'modal',
                                    'params' => [
                                        'layout' => 'top-menu'
                                    ],
                                    'title' => 'Modal'
                                ],
                                'slide-over' => [
                                    'icon' => '',
                                    'route_name' => 'slide-over',
                                    'params' => [
                                        'layout' => 'top-menu'
                                    ],
                                    'title' => 'Slide Over'
                                ],
                                'notification' => [
                                    'icon' => '',
                                    'route_name' => 'notification',
                                    'params' => [
                                        'layout' => 'top-menu'
                                    ],
                                    'title' => 'Notification'
                                ],
                            ]
                        ],
                        'tab' => [
                            'icon' => '',
                            'route_name' => 'tab',
                            'params' => [
                                'layout' => 'top-menu'
                            ],
                            'title' => 'Tab'
                        ],
                        'accordion' => [
                            'icon' => '',
                            'route_name' => 'accordion',
                            'params' => [
                                'layout' => 'top-menu'
                            ],
                            'title' => 'Accordion'
                        ],
                        'button' => [
                            'icon' => '',
                            'route_name' => 'button',
                            'params' => [
                                'layout' => 'top-menu'
                            ],
                            'title' => 'Button'
                        ],
                        'alert' => [
                            'icon' => '',
                            'route_name' => 'alert',
                            'params' => [
                                'layout' => 'top-menu'
                            ],
                            'title' => 'Alert'
                        ],
                        'progress-bar' => [
                            'icon' => '',
                            'route_name' => 'progress-bar',
                            'params' => [
                                'layout' => 'top-menu'
                            ],
                            'title' => 'Progress Bar'
                        ],
                        'tooltip' => [
                            'icon' => '',
                            'route_name' => 'tooltip',
                            'params' => [
                                'layout' => 'top-menu'
                            ],
                            'title' => 'Tooltip'
                        ],
                        'dropdown' => [
                            'icon' => '',
                            'route_name' => 'dropdown',
                            'params' => [
                                'layout' => 'top-menu'
                            ],
                            'title' => 'Dropdown'
                        ],
                        'typography' => [
                            'icon' => '',
                            'route_name' => 'typography',
                            'params' => [
                                'layout' => 'top-menu'
                            ],
                            'title' => 'Typography'
                        ],
                        'icon' => [
                            'icon' => '',
                            'route_name' => 'icon',
                            'params' => [
                                'layout' => 'top-menu'
                            ],
                            'title' => 'Icon'
                        ],
                        'loading-icon' => [
                            'icon' => '',
                            'route_name' => 'loading-icon',
                            'params' => [
                                'layout' => 'top-menu'
                            ],
                            'title' => 'Loading Icon'
                        ]
                    ]
                ],
                'forms' => [
                    'icon' => 'sidebar',
                    'title' => 'Forms',
                    'sub_menu' => [
                        'regular-form' => [
                            'icon' => '',
                            'route_name' => 'regular-form',
                            'params' => [
                                'layout' => 'top-menu'
                            ],
                            'title' => 'Regular Form'
                        ],
                        'datepicker' => [
                            'icon' => '',
                            'route_name' => 'datepicker',
                            'params' => [
                                'layout' => 'top-menu'
                            ],
                            'title' => 'Datepicker'
                        ],
                        'tom-select' => [
                            'icon' => '',
                            'route_name' => 'tom-select',
                            'params' => [
                                'layout' => 'top-menu'
                            ],
                            'title' => 'Tom Select'
                        ],
                        'file-upload' => [
                            'icon' => '',
                            'route_name' => 'file-upload',
                            'params' => [
                                'layout' => 'top-menu'
                            ],
                            'title' => 'File Upload'
                        ],
                        'wysiwyg-editor' => [
                            'icon' => '',
                            'title' => 'Wysiwyg Editor',
                            'sub_menu' => [
                                'wysiwyg-editor-classic' => [
                                    'icon' => '',
                                    'route_name' => 'wysiwyg-editor-classic',
                                    'params' => [
                                        'layout' => 'top-menu'
                                    ],
                                    'title' => 'Classic'
                                ],
                                'wysiwyg-editor-inline' => [
                                    'icon' => '',
                                    'route_name' => 'wysiwyg-editor-inline',
                                    'params' => [
                                        'layout' => 'top-menu'
                                    ],
                                    'title' => 'Inline'
                                ],
                                'wysiwyg-editor-balloon' => [
                                    'icon' => '',
                                    'route_name' => 'wysiwyg-editor-balloon',
                                    'params' => [
                                        'layout' => 'top-menu'
                                    ],
                                    'title' => 'Balloon'
                                ],
                                'wysiwyg-editor-balloon-block' => [
                                    'icon' => '',
                                    'route_name' => 'wysiwyg-editor-balloon-block',
                                    'params' => [
                                        'layout' => 'top-menu'
                                    ],
                                    'title' => 'Balloon Block'
                                ],
                                'wysiwyg-editor-document' => [
                                    'icon' => '',
                                    'route_name' => 'wysiwyg-editor-document',
                                    'params' => [
                                        'layout' => 'top-menu'
                                    ],
                                    'title' => 'Document'
                                ],
                            ]
                        ],
                        'validation' => [
                            'icon' => '',
                            'route_name' => 'validation',
                            'params' => [
                                'layout' => 'top-menu'
                            ],
                            'title' => 'Validation'
                        ]
                    ]
                ],
                'widgets' => [
                    'icon' => 'hard-drive',
                    'title' => 'Widgets',
                    'sub_menu' => [
                        'chart' => [
                            'icon' => '',
                            'route_name' => 'chart',
                            'params' => [
                                'layout' => 'top-menu'
                            ],
                            'title' => 'Chart'
                        ],
                        'slider' => [
                            'icon' => '',
                            'route_name' => 'slider',
                            'params' => [
                                'layout' => 'top-menu'
                            ],
                            'title' => 'Slider'
                        ],
                        'image-zoom' => [
                            'icon' => '',
                            'route_name' => 'image-zoom',
                            'params' => [
                                'layout' => 'top-menu'
                            ],
                            'title' => 'Image Zoom'
                        ]
                    ]
                ]
            ];
        endif;
    }
}
