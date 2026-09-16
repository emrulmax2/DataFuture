@extends('../layout/site-settings')

@section('body_class', 'site-settings-isolated')

@section('subhead')
<title>{{ $title }}</title>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Spectral:wght@600;700&display=swap" rel="stylesheet">
@endsection

@section('styles')
@vite('resources/css/site-settings-redesign.css')
@endsection

@section('content')
<div id="siteSettingsPage" class="ss-page ss-permissions-page">
    @include('pages.settings.partials.isolated-header')

    <nav class="ss-breadcrumb" aria-label="Breadcrumb">
        <a href="{{ route('dashboard') }}">
            <i data-lucide="home"></i>
            Dashboard
        </a>
        <i data-lucide="chevron-right"></i>
        <span>User Privilege</span>
        <i data-lucide="chevron-right"></i>
        <span>Permissions</span>
    </nav>

    <main class="ss-main">
        <section class="ss-title-card">
            <div class="ss-title-card__content">
                <button type="button" class="ss-icon-btn ss-sidebar-toggle" data-ss-sidebar-toggle aria-label="Open settings menu">
                    <i data-lucide="panel-left"></i>
                </button>
                <span class="ss-title-card__icon">
                    <i data-lucide="shield-check"></i>
                </span>
                <div>
                    <h1>{{ $subtitle }}</h1>
                    <p>Set the permission template each department's employees inherit.</p>
                </div>
            </div>
            <a href="{{ route('dashboard') }}" class="ss-back-btn">
                <i data-lucide="arrow-left"></i>
                Back to Dashboard
            </a>
        </section>

        <div class="ss-workspace">
            <button type="button" class="ss-sidebar-backdrop" data-ss-sidebar-close aria-label="Close settings menu"></button>
            <aside class="ss-sidebar">
                @php $settingsSidebarIcon = 'settings-2'; $settingsSidebarSubtitle = 'Global configuration'; @endphp
                @include('pages.settings.sidebar')
            </aside>

            <section class="ss-content">
        <form id="permissionUpdateForm">
            <div class="ss-perm-toolbar">
                <div>
                    <h2>Department Permissions</h2>
                    <p>Changes apply to every employee in the department.</p>
                </div>
                <button id="savePermissionsBtn" class="ss-btn ss-btn--primary">
                    <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg" stroke="white" class="ss-spinner">
                        <g fill="none" fill-rule="evenodd">
                            <g transform="translate(1 1)" stroke-width="4">
                                <circle stroke-opacity=".5" cx="18" cy="18" r="18"></circle>
                                <path d="M36 18c0-9.94-8.06-18-18-18">
                                    <animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform>
                                </path>
                            </g>
                        </g>
                    </svg>
                    <i data-lucide="save"></i>
                    Save Permissions
                </button>
            </div>
            <div class="ss-perm-list">
                @if($departments->count() > 0)
                @foreach($departments as $department)
                <div class="accordion">
                    <div class="accordion-item {{ $loop->last ? '' : 'border-b' }}">
                        <div id="department-{{ $department->id }}" class="accordion-header flex justify-between {{ $loop->first ? '' : 'pt-4' }}">
                            <button class="accordion-button collapsed relative w-full text-lg font-semibold"
                                type="button"
                                data-target="#department-collapse-{{ $department->id }}"
                                aria-expanded="false"
                                aria-controls="department-collapse-{{ $department->id }}">
                                <div class="flex items-center font-medium text-base">
                                    <i data-lucide="plus" class="w-6 h-6 mr-2 accordion-icon-plus"></i>
                                    <i data-lucide="minus" class="w-6 h-6 mr-2 accordion-icon-minus hidden"></i>
                                    {{ $department->name }}
                                    @php
                                        /* Sub departments with a saved permission set, read from the
                                           stored templates rather than the boxes on screen: this is
                                           what HR can actually load. */
                                        $deptCategoryTotal = $department->permissionCategories->count();
                                        $deptCategorySaved = $department->permissionCategories
                                            ->filter(fn ($c) => isset($permissions[$department->id][$c->id])
                                                && count($permissions[$department->id][$c->id]) > 0)
                                            ->count();
                                        $deptCountState = $deptCategoryTotal === 0 || $deptCategorySaved === 0
                                            ? 'none'
                                            : ($deptCategorySaved === $deptCategoryTotal ? 'all' : 'some');
                                    @endphp
                                    <span class="ss-perm-bulk__count" data-dept-count data-state="{{ $deptCountState }}">
                                        @if($deptCategoryTotal === 0)
                                            No sub departments
                                        @else
                                            {{ $deptCategorySaved }} of {{ $deptCategoryTotal }} sub {{ $deptCategoryTotal === 1 ? 'department' : 'departments' }} saved
                                        @endif
                                    </span>
                                </div>
                            </button>
                        </div>
                        <div id="department-collapse-{{ $department->id }}" class="accordion-collapse collapse ml-4"
                            aria-labelledby="department-{{ $department->id }}">
                            <div class="accordion-body text-slate-600 dark:text-slate-500 leading-relaxed my-8">
                                {{-- Department -> sub department (permission category) -> permission set.
                                     Each category holds its own template, so the tree renders once per
                                     category rather than once per department. --}}
                                @forelse($department->permissionCategories as $category)
                                    <div class="accordion ss-perm-category">
                                        <div class="accordion-item ss-perm-category__item">
                                            <div id="category-{{ $department->id }}-{{ $category->id }}" class="accordion-header flex justify-between">
                                                <button class="accordion-button collapsed relative w-full font-semibold"
                                                    type="button"
                                                    data-target="#category-collapse-{{ $department->id }}-{{ $category->id }}"
                                                    aria-expanded="false"
                                                    aria-controls="category-collapse-{{ $department->id }}-{{ $category->id }}">
                                                    <div class="flex items-center">
                                                        <i data-lucide="plus" class="w-6 h-6 mr-2 accordion-icon-plus"></i>
                                                        <i data-lucide="minus" class="w-6 h-6 mr-2 accordion-icon-minus hidden"></i>
                                                        <span class="ss-perm-category__label">Sub department</span>
                                                        {{ $category->name }}
                                                    </div>
                                                </button>
                                            </div>
                                            <div id="category-collapse-{{ $department->id }}-{{ $category->id }}" class="accordion-collapse collapse"
                                                aria-labelledby="category-{{ $department->id }}-{{ $category->id }}">
                                                <div class="accordion-body">
                                                    @include('pages.settings.permissions.partials.permission-set', [
                                                        'values' => $permissions[$department->id][$category->id] ?? [],
                                                    ])
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    {{-- Nothing to configure until the department has a sub department:
                                         permissions are now held per category, not per department. --}}
                                    <div class="ss-perm-empty ss-perm-empty--inline" role="status">
                                        <span><i data-lucide="folder-tree"></i></span>
                                        <strong>No sub departments yet</strong>
                                        <p>Add a sub department for {{ $department->name }} before assigning permissions.</p>
                                        <button type="button" class="ss-btn ss-btn--light ss-btn--compact"
                                            data-add-subdepartment
                                            data-department-id="{{ $department->id }}"
                                            data-department-name="{{ $department->name }}">
                                            <i data-lucide="plus"></i>
                                            Add sub department
                                        </button>
                                    </div>
                                @endforelse

                                {{-- Departments that already have sub departments can add another
                                     without leaving the page. Add only: renaming and removing stay
                                     on the Permission Category screen. --}}
                                @if($department->permissionCategories->isNotEmpty())
                                    <div class="ss-perm-add-row">
                                        <button type="button" class="ss-perm-add-row__btn"
                                            data-add-subdepartment
                                            data-department-id="{{ $department->id }}"
                                            data-department-name="{{ $department->name }}">
                                            <i data-lucide="plus"></i>
                                            Add sub department
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
                @else
                <div class="ss-perm-empty">
                    <span><i data-lucide="users"></i></span>
                    <strong>No departments found</strong>
                    <p>Add a department under HR Settings before assigning permissions.</p>
                </div>
                @endif
            </div>
        </form>
            </section>
        </div>
    </main>

<!-- BEGIN: Success Modal Content -->
<div id="addSubDepartmentModal" class="modal ss-modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog ss-settings-modal__dialog">
        <form method="POST" action="#" id="addSubDepartmentForm" autocomplete="off">
            <div class="modal-content ss-settings-modal ss-compact-settings-modal">
                <div class="ss-settings-modal__header">
                    <div>
                        <span></span>
                        <h2>Add Sub Department</h2>
                    </div>
                    <button type="button" data-tw-dismiss="modal" class="ss-modal-close" aria-label="Close modal">
                        <i data-lucide="x"></i>
                    </button>
                </div>
                <div class="ss-settings-modal__body">
                    {{-- Adding reloads the page to draw the new sub department's
                         permission tree, which would discard boxes ticked but not yet
                         saved. Shown only when that is actually the case. --}}
                    <div class="ss-perm-add-warning" id="subDepartmentUnsavedWarning" hidden>
                        <i data-lucide="alert-triangle"></i>
                        <span>You have unsaved permission changes. Save them first — adding a sub department reloads the page.</span>
                    </div>
                    <div class="ss-modal-grid">
                        <div class="ss-modal-field ss-modal-field--full">
                            <label>Department</label>
                            <div class="ss-perm-add-department" id="subDepartmentDepartmentName"></div>
                            <input type="hidden" name="department_id" value="">
                            <div class="acc__input-error error-department_id"></div>
                        </div>
                        <div class="ss-modal-field ss-modal-field--full">
                            <label for="sub_department_name">Sub Department Name <span>*</span></label>
                            <input id="sub_department_name" type="text" name="name" class="ss-modal-input name" placeholder="e.g. Operations">
                            <div class="acc__input-error error-name"></div>
                        </div>
                    </div>
                </div>
                <div class="ss-settings-modal__footer">
                    <button type="button" data-tw-dismiss="modal" class="ss-btn ss-btn--danger-soft">
                        <i data-lucide="x"></i>
                        Cancel
                    </button>
                    <button type="submit" id="saveSubDepartmentBtn" class="ss-btn ss-btn--primary">
                        <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg" stroke="white" class="ss-spinner">
                            <g fill="none" fill-rule="evenodd">
                                <g transform="translate(1 1)" stroke-width="4">
                                    <circle stroke-opacity=".5" cx="18" cy="18" r="18"></circle>
                                    <path d="M36 18c0-9.94-8.06-18-18-18">
                                        <animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform>
                                    </path>
                                </g>
                            </g>
                        </svg>
                        <i data-lucide="check"></i>
                        Save
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<div id="successModal" class="modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content ss-success-modal">
            <div class="modal-body p-0">
                <div class="ss-success-modal__body">
                    <i data-lucide="check-circle" class="ss-success-modal__icon"></i>
                    <div class="successModalTitle"></div>
                    <p class="successModalDesc"></p>
                </div>
                <div class="ss-success-modal__footer">
                    <button type="button" data-tw-dismiss="modal" class="ss-btn ss-btn--primary">Ok</button>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- END: Success Modal Content -->
<!-- BEGIN: Delete Confirm Modal Content -->
<div id="confirmModal" class="modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog ss-confirm-modal__dialog">
        <div class="modal-content ss-confirm-modal">
            <div class="ss-confirm-modal__hero">
                <span><i data-lucide="alert-triangle"></i></span>
                <h2 class="confModTitle">Are you sure?</h2>
            </div>
            <div class="ss-confirm-modal__body">
                <p class="confModDesc"></p>
            </div>
            <div class="ss-confirm-modal__footer">
                <button type="button" data-tw-dismiss="modal" class="ss-btn ss-btn--light">
                    <i data-lucide="x"></i>
                    No, Cancel
                </button>
                <button type="button" data-id="0" data-action="none" class="agreeWith ss-btn ss-btn--danger">
                    <i data-lucide="check"></i>
                    Yes, I agree
                </button>
            </div>
        </div>
    </div>
</div>
<!-- END: Delete Confirm Modal Content -->
</div>
@endsection

@section('script')
@vite('resources/js/settings.js')
@vite('resources/js/department-permissions.js')
@vite('resources/js/site-settings-redesign.js')
<script>
    (function () {
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.accordion-collapse').forEach(collapse => {
                collapse.classList.add('collapse');
            });

            document.querySelectorAll('.accordion-button').forEach(button => {
                button.addEventListener('click', function () {
                    const targetId = button.getAttribute('data-target');
                    const targetContent = document.querySelector(targetId);
                    const plusIcon = button.querySelector('.accordion-icon-plus');
                    const minusIcon = button.querySelector('.accordion-icon-minus');
                    
                    const isExpanded = button.getAttribute('aria-expanded') === 'true';

                    if (isExpanded) {
                        plusIcon.classList.remove('hidden');
                        minusIcon.classList.add('hidden');
                    } else {
                        plusIcon.classList.add('hidden');
                        minusIcon.classList.remove('hidden');
                    }
                    
                    if (!isExpanded) {
                        targetContent.classList.remove('collapse');
                        targetContent.classList.add('show');
                        button.setAttribute('aria-expanded', 'true');
                    } else {
                        targetContent.classList.remove('show');
                        targetContent.classList.add('collapse');
                        button.setAttribute('aria-expanded', 'false');
                    }
                });
            });
        });
    })();
</script>

@endsection