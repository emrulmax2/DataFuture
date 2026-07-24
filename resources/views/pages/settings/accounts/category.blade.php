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
    @php
        $categoryTrees = [
            ['key' => 'inflow', 'type' => 0, 'title' => 'Inflow', 'blurb' => 'Categories money is received under.', 'icon' => 'arrow-down-left', 'wrap' => 'inflowCategoryWrap', 'rows' => $inflow_parents ?? []],
            ['key' => 'outflow', 'type' => 1, 'title' => 'Outflow', 'blurb' => 'Categories money is paid out under.', 'icon' => 'arrow-up-right', 'wrap' => 'outflowCategoryWrap', 'rows' => $outflow_parents ?? []],
        ];
    @endphp

    <div id="siteSettingsPage" class="ss-page">
        @include('pages.settings.partials.isolated-header')

        <nav class="ss-breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('dashboard') }}">
                <i data-lucide="home"></i>
                Dashboard
            </a>
            <i data-lucide="chevron-right"></i>
            <span>Accounts Settings</span>
            <i data-lucide="chevron-right"></i>
            <span>Categories</span>
        </nav>

        <main class="ss-main">
            <section class="ss-title-card">
                <div class="ss-title-card__content">
                    <button type="button" class="ss-icon-btn ss-sidebar-toggle" data-ss-sidebar-toggle aria-label="Open settings menu">
                        <i data-lucide="panel-left"></i>
                    </button>
                    <span class="ss-title-card__icon">
                        <i data-lucide="folder-tree"></i>
                    </span>
                    <div>
                        <h1>{{ $subtitle }}</h1>
                        <p>Organise the inflow and outflow categories transactions are recorded against.</p>
                    </div>
                </div>
                <a href="{{ route('site.setting') }}" class="ss-back-btn">
                    <i data-lucide="arrow-left"></i>
                    Back to Settings
                </a>
            </section>

            <div class="ss-workspace">
                <button type="button" class="ss-sidebar-backdrop" data-ss-sidebar-close aria-label="Close settings menu"></button>
                <aside class="ss-sidebar">
                    @php($settingsSidebarIcon = 'settings-2')
                    @php($settingsSidebarSubtitle = 'Global configuration')
                    @include('pages.settings.sidebar')
                </aside>

                <section class="ss-content">
                    <div class="ss-cat-toolbar">
                        <div>
                            <h2>Transaction Categories</h2>
                            <p>Select a parent to load the categories nested beneath it.</p>
                        </div>
                        <button data-tw-toggle="modal" data-tw-target="#addCategoryModal" type="button" class="ss-btn ss-btn--primary ss-btn--compact">
                            <i data-lucide="plus"></i>
                            Add Category
                        </button>
                    </div>

                    <div class="ss-cat-grid">
                        @foreach($categoryTrees as $tree)
                            <div class="ss-form-card ss-cat-card ss-cat-card--{{ $tree['key'] }}">
                                <div class="ss-cat-card__header">
                                    <span class="ss-cat-card__icon"><i data-lucide="{{ $tree['icon'] }}"></i></span>
                                    <div>
                                        <h2>{{ $tree['title'] }}</h2>
                                        <p>{{ $tree['blurb'] }}</p>
                                    </div>
                                    <span class="ss-cat-card__count">{{ count($tree['rows']) }}</span>
                                </div>

                                {{-- planTreeWrap / categoryTreeWrap / classPlanTree and the node classes below
                                     are the hooks acc-category.js binds to — keep them when restyling. --}}
                                <div class="planTreeWrap categoryTreeWrap ss-cat-tree" id="{{ $tree['wrap'] }}">
                                    @if(count($tree['rows']) > 0)
                                        <ul class="classPlanTree">
                                            @foreach($tree['rows'] as $cat)
                                                @php($childCount = isset($cat->activechildrens) ? $cat->activechildrens->count() : 0)
                                                <li class="{{ $childCount > 0 ? 'hasChildren' : 'notHasChild' }} relative">
                                                    <a href="javascript:void(0);" data-type="{{ $tree['type'] }}" data-category="{{ $cat->id }}" class="{{ $childCount > 0 ? 'parent_category' : '' }} ss-cat-node">
                                                        <span class="ss-cat-node__label">{{ $cat->category_name }}</span>
                                                        @if($childCount > 0)
                                                            <span class="ss-cat-node__count">{{ $childCount }}</span>
                                                        @endif
                                                        @if(!empty($cat->code))
                                                            <span class="ss-cat-node__code">{{ $cat->code }}</span>
                                                        @endif
                                                        <i data-loading-icon="oval" class="ss-cat-node__spinner"></i>
                                                    </a>
                                                    <div class="settingBtns ss-cat-node__actions">
                                                        <button data-id="{{ $cat->id }}" data-tw-toggle="modal" data-tw-target="#editCategoryModal" type="button" class="edit_btn ss-row-action ss-row-action--edit" aria-label="Edit {{ $cat->category_name }}"><i data-lucide="pencil"></i></button>
                                                        <button data-id="{{ $cat->id }}" type="button" class="delete_btn ss-row-action ss-row-action--delete" aria-label="Delete {{ $cat->category_name }}"><i data-lucide="trash-2"></i></button>
                                                    </div>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <div class="ss-cat-empty">
                                            <span><i data-lucide="folder-tree"></i></span>
                                            <strong>No {{ strtolower($tree['title']) }} categories yet</strong>
                                            <p>Add a category and set its type to {{ $tree['title'] }} to start this tree.</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            </div>
        </main>

        <div id="addCategoryModal" class="modal ss-modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog ss-settings-modal__dialog">
                <form method="POST" action="#" id="addCategoryForm" autocomplete="off">
                    <div class="modal-content ss-settings-modal ss-category-modal">
                        <div class="ss-settings-modal__header">
                            <div>
                                <span></span>
                                <h2>Add Category</h2>
                            </div>
                            <button type="button" data-tw-dismiss="modal" class="ss-modal-close" aria-label="Close modal">
                                <i data-lucide="x"></i>
                            </button>
                        </div>
                        <div class="ss-settings-modal__body">
                            <div class="ss-modal-field">
                                <label for="category_name">Category Name <span>*</span></label>
                                <input id="category_name" name="category_name" type="text" class="ss-modal-input category_name" placeholder="Category name">
                                <div class="acc__input-error error-category_name"></div>
                            </div>

                            <div class="ss-modal-field">
                                <label for="code">Code</label>
                                <input id="code" name="code" type="text" class="ss-modal-input code" placeholder="Code">
                                <div class="acc__input-error error-code"></div>
                            </div>

                            <div class="ss-modal-field">
                                <label>Type <span>*</span></label>
                                <div class="ss-type-options">
                                    <label class="ss-type-option" for="inflow">
                                        <input id="inflow" name="trans_type" type="radio" value="0">
                                        <span><i data-lucide="arrow-down-left"></i></span>
                                        <strong>Inflow</strong>
                                    </label>
                                    <label class="ss-type-option" for="outflow">
                                        <input id="outflow" name="trans_type" type="radio" value="1">
                                        <span><i data-lucide="arrow-up-right"></i></span>
                                        <strong>Outflow</strong>
                                    </label>
                                </div>
                                <div class="acc__input-error error-trans_type"></div>
                            </div>

                            <div class="ss-modal-field">
                                <label for="parent_id">Parent Category</label>
                                <select id="parent_id" name="parent_id" class="w-full tom-selects parent_id">
                                    <option value="">Select Parent Category</option>
                                </select>
                                <div class="acc__input-error error-parent_id"></div>
                            </div>

                            <div class="ss-modal-grid">
                                <div class="ss-modal-field">
                                    <label for="audit_status">Audit Status</label>
                                    <label class="ss-status-toggle" for="audit_status">
                                        <input id="audit_status" name="audit_status" value="1" type="checkbox" autocomplete="off">
                                        <span class="ss-status-toggle__control">
                                            <span class="ss-status-toggle__icon ss-status-toggle__icon--on"><i data-lucide="check"></i></span>
                                            <span class="ss-status-toggle__icon ss-status-toggle__icon--off"><i data-lucide="x"></i></span>
                                        </span>
                                        <span class="ss-status-toggle__copy">
                                            <strong>Not audited</strong>
                                            <small>Excluded from audit reporting</small>
                                        </span>
                                    </label>
                                </div>
                                <div class="ss-modal-field">
                                    <label for="status_add">Status</label>
                                    <label class="ss-status-toggle" for="status_add">
                                        <input id="status_add" checked name="status" value="1" type="checkbox" autocomplete="off">
                                        <span class="ss-status-toggle__control">
                                            <span class="ss-status-toggle__icon ss-status-toggle__icon--on"><i data-lucide="check"></i></span>
                                            <span class="ss-status-toggle__icon ss-status-toggle__icon--off"><i data-lucide="x"></i></span>
                                        </span>
                                        <span class="ss-status-toggle__copy">
                                            <strong>Active</strong>
                                            <small>Shown in the category tree</small>
                                        </span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="ss-settings-modal__footer">
                            <button type="button" data-tw-dismiss="modal" class="ss-btn ss-btn--danger-soft">
                                <i data-lucide="x"></i>
                                Cancel
                            </button>
                            <button type="submit" id="saveCategory" class="ss-btn ss-btn--primary">
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

        <div id="editCategoryModal" class="modal ss-modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog ss-settings-modal__dialog">
                <form method="POST" action="#" id="editCategoryForm" autocomplete="off">
                    <div class="modal-content ss-settings-modal ss-category-modal">
                        <div class="ss-settings-modal__header">
                            <div>
                                <span></span>
                                <h2>Edit Category</h2>
                            </div>
                            <button type="button" data-tw-dismiss="modal" class="ss-modal-close" aria-label="Close modal">
                                <i data-lucide="x"></i>
                            </button>
                        </div>
                        <div class="ss-settings-modal__body">
                            <div class="ss-modal-field">
                                <label for="edit_category_name">Category Name <span>*</span></label>
                                <input id="edit_category_name" name="category_name" type="text" class="ss-modal-input category_name" placeholder="Category name">
                                <div class="acc__input-error error-category_name"></div>
                            </div>

                            <div class="ss-modal-field">
                                <label for="edit_code">Code</label>
                                <input id="edit_code" name="code" type="text" class="ss-modal-input code" placeholder="Code">
                                <div class="acc__input-error error-code"></div>
                            </div>

                            <div class="ss-modal-field">
                                <label>Type <span>*</span></label>
                                <div class="ss-type-options">
                                    <label class="ss-type-option" for="edit_inflow">
                                        <input id="edit_inflow" name="trans_type" type="radio" value="0">
                                        <span><i data-lucide="arrow-down-left"></i></span>
                                        <strong>Inflow</strong>
                                    </label>
                                    <label class="ss-type-option" for="edit_outflow">
                                        <input id="edit_outflow" name="trans_type" type="radio" value="1">
                                        <span><i data-lucide="arrow-up-right"></i></span>
                                        <strong>Outflow</strong>
                                    </label>
                                </div>
                                <div class="acc__input-error error-trans_type"></div>
                            </div>

                            <div class="ss-modal-field">
                                <label for="edit_parent_id">Parent Category</label>
                                <select id="edit_parent_id" name="parent_id" class="w-full tom-selects parent_id">
                                    <option value="">Select Parent Category</option>
                                </select>
                                <div class="acc__input-error error-parent_id"></div>
                            </div>

                            <div class="ss-modal-grid">
                                <div class="ss-modal-field">
                                    <label for="edit_audit_status">Audit Status</label>
                                    <label class="ss-status-toggle" for="edit_audit_status">
                                        <input id="edit_audit_status" name="audit_status" value="1" type="checkbox" autocomplete="off">
                                        <span class="ss-status-toggle__control">
                                            <span class="ss-status-toggle__icon ss-status-toggle__icon--on"><i data-lucide="check"></i></span>
                                            <span class="ss-status-toggle__icon ss-status-toggle__icon--off"><i data-lucide="x"></i></span>
                                        </span>
                                        <span class="ss-status-toggle__copy">
                                            <strong>Not audited</strong>
                                            <small>Excluded from audit reporting</small>
                                        </span>
                                    </label>
                                </div>
                                <div class="ss-modal-field">
                                    <label for="edit_status">Status</label>
                                    <label class="ss-status-toggle" for="edit_status">
                                        <input id="edit_status" checked name="status" value="1" type="checkbox" autocomplete="off">
                                        <span class="ss-status-toggle__control">
                                            <span class="ss-status-toggle__icon ss-status-toggle__icon--on"><i data-lucide="check"></i></span>
                                            <span class="ss-status-toggle__icon ss-status-toggle__icon--off"><i data-lucide="x"></i></span>
                                        </span>
                                        <span class="ss-status-toggle__copy">
                                            <strong>Active</strong>
                                            <small>Shown in the category tree</small>
                                        </span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="ss-settings-modal__footer">
                            <button type="button" data-tw-dismiss="modal" class="ss-btn ss-btn--danger-soft">
                                <i data-lucide="x"></i>
                                Cancel
                            </button>
                            <button type="submit" id="updateCategory" class="ss-btn ss-btn--primary">
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
                                Update
                            </button>
                            <input type="hidden" name="id" value="0"/>
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
                            <button type="button" data-action="none" class="successCloser ss-btn ss-btn--primary">Ok</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

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
    </div>
@endsection

@section('script')
    @vite('resources/js/settings.js')
    @vite('resources/js/acc-category.js')
    @vite('resources/js/site-settings-redesign.js')
@endsection
