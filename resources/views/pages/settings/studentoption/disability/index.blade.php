    <!-- BEGIN: HTML Table Data -->
    <div class="relative">
        <div class="flex flex-col sm:flex-row sm:items-end xl:items-start">
            <form id="tabulatorFilterForm-DISABILITY" class="xl:flex sm:mr-auto" >
                <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                    <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Query</label>
                    <input id="query-DISABILITY" name="query" type="text" class="form-control sm:w-40 2xl:w-full mt-2 sm:mt-0"  placeholder="Search...">
                </div>
                <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                    <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Status</label>
                    <select id="status-DISABILITY" name="status" class="form-select w-full mt-2 sm:mt-0 sm:w-auto" >
                        <option value="1">Active</option>
                        <option value="0">In Active</option>
                        <option value="2">Archived</option>
                    </select>
                </div>
                <div class="mt-2 xl:mt-0">
                    <button id="tabulator-html-filter-go-DISABILITY" type="button" class="btn btn-primary w-full sm:w-16" >Go</button>
                    <button id="tabulator-html-filter-reset-DISABILITY" type="button" class="btn btn-secondary w-full sm:w-16 mt-2 sm:mt-0 sm:ml-1" >Reset</button>
                </div>
            </form>
            <div class="flex mt-5 sm:mt-0">
                <button id="tabulator-print-DISABILITY" class="btn btn-outline-secondary w-1/2 sm:w-auto mr-2">
                    <i data-lucide="printer" class="w-4 h-4 mr-2"></i> Print
                </button>
                <div class="dropdown w-1/2 sm:w-auto">
                    <button class="dropdown-toggle btn btn-outline-secondary w-full sm:w-auto" aria-expanded="false" data-tw-toggle="dropdown">
                        <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export <i data-lucide="chevron-down" class="w-4 h-4 ml-auto sm:ml-2"></i>
                    </button>
                    <div class="dropdown-menu w-40">
                        <ul class="dropdown-content">
                            <li>
                                <a id="tabulator-export-csv-DISABILITY" href="javascript:;" class="dropdown-item">
                                    <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export CSV
                                </a>
                            </li>
                            <li>
                                <a id="tabulator-export-json-DISABILITY" href="javascript:;" class="dropdown-item">
                                    <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export JSON
                                </a>
                            </li>
                            <li>
                                <a id="tabulator-export-xlsx-DISABILITY" href="javascript:;" class="dropdown-item">
                                    <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export XLSX
                                </a>
                            </li>
                            <li>
                                <a id="tabulator-export-html-DISABILITY" href="javascript:;" class="dropdown-item">
                                    <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export HTML
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="overflow-x-auto scrollbar-hidden">
            <div id="disabilityListTable" class="mt-5 table-report table-report--tabulator"></div>
        </div>
    </div>
    <!-- END: HTML Table Data -->
    <!-- BEGIN: Add Modal -->
    <div id="addDisabilityModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="#" id="addDisabilityForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Add Disability</h2>
                        <a data-tw-dismiss="modal" href="javascript:;">
                            <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                        </a>
                    </div>
                    <div class="modal-body">
                        <div>
                            <div>
                                <label for="name" class="form-label">Title <span class="text-danger">*</span></label>
                                <input id="name" type="text" name="name" class="form-control w-full">
                                <div class="acc__input-error error-name text-danger mt-2"></div>
                            </div>
                        </div>
                        <div class="mt-3">
                            <div class="form-check form-switch">
                                <label class="form-check-label mr-3 ml-0" for="is_hesa">Is Hesa Code?</label>
                                <input id="is_hesa" class="form-check-input" name="is_hesa" value="1" type="checkbox">
                            </div>
                        </div>
                        <div class="mt-3 hesa_code_area" style="display: none;">
                            <label for="hesa_code" class="form-label">Hesa Code <span class="text-danger">*</span></label>
                            <input id="hesa_code" type="text" name="hesa_code" class="form-control w-full">
                            <div class="acc__input-error error-hesa_code text-danger mt-2"></div>
                        </div>
                        <div class="mt-3">
                            <div class="form-check form-switch">
                                <label class="form-check-label mr-3 ml-0" for="is_df">Is DF Code?</label>
                                <input id="is_df" class="form-check-input" name="is_df" value="1" type="checkbox">
                            </div>
                        </div>
                        <div class="mt-3 df_code_area" style="display: none;">
                            <label for="df_code" class="form-label">DF Code <span class="text-danger">*</span></label>
                            <input id="df_code" type="text" name="df_code" class="form-control w-full">
                            <div class="acc__input-error error-df_code text-danger mt-2"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="form-check form-switch" style="float: left; margin: 7px 0 0;">
                            <label class="form-check-label mr-3 ml-0" for="active">Active</label>
                            <input id="active" class="form-check-input m-0" name="active" checked value="1" type="checkbox">
                        </div>
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" id="saveDisability" class="btn btn-primary w-auto">     
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
    <!-- END: Add Modal -->
    <!-- BEGIN: Edit Modal -->
    <div id="editDisabilityModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="#" id="editDisabilityForm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Edit Disability</h2>
                        <a data-tw-dismiss="modal" href="javascript:;">
                            <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                        </a>
                    </div>
                    <div class="modal-body">
                        <div>
                            <div>
                                <label for="edit_name" class="form-label">Title <span class="text-danger">*</span></label>
                                <input id="edit_name" type="text" name="name" class="form-control w-full">
                                <div class="acc__input-error error-name text-danger mt-2"></div>
                            </div>
                        </div>
                        <div class="mt-3">
                            <div class="form-check form-switch">
                                <label class="form-check-label mr-3 ml-0" for="edit_is_hesa">Is Hesa Code?</label>
                                <input id="edit_is_hesa" class="form-check-input" name="is_hesa" value="1" type="checkbox">
                            </div>
                        </div>
                        <div class="mt-3 hesa_code_area" style="display: none;">
                            <label for="edit_hesa_code" class="form-label">Hesa Code <span class="text-danger">*</span></label>
                            <input id="edit_hesa_code" type="text" name="hesa_code" class="form-control w-full">
                            <div class="acc__input-error error-hesa_code text-danger mt-2"></div>
                        </div>
                        <div class="mt-3">
                            <div class="form-check form-switch">
                                <label class="form-check-label mr-3 ml-0" for="edit_is_df">Is DF Code?</label>
                                <input id="edit_is_df" class="form-check-input" name="is_df" value="1" type="checkbox">
                            </div>
                        </div>
                        <div class="mt-3 df_code_area" style="display: none;">
                            <label for="edit_df_code" class="form-label">DF Code <span class="text-danger">*</span></label>
                            <input id="edit_df_code" type="text" name="df_code" class="form-control w-full">
                            <div class="acc__input-error error-df_code text-danger mt-2"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="form-check form-switch" style="float: left; margin: 7px 0 0;">
                            <label class="form-check-label mr-3 ml-0" for="edit_active">Active</label>
                            <input id="edit_active" class="form-check-input m-0" name="active" value="1" type="checkbox">
                        </div>
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" id="updateDisability" class="btn btn-primary w-auto">
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
                        <input type="hidden" name="id" value="0" />
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Edit Modal -->