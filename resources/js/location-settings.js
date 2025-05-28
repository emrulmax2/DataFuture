import xlsx from 'xlsx';
import { createIcons, icons } from 'lucide';
import Tabulator from 'tabulator-tables';
import TomSelect from 'tom-select';

('use strict');
var locationListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let querystr = $('#query').val() != '' ? $('#query').val() : '';
        let status = $('#status').val() != '' ? $('#status').val() : '';
        let tableContent = new Tabulator('#locationListTable', {
            ajaxURL: route('library-locations.list'),
            ajaxParams: { querystr: querystr, status: status },
            ajaxFiltering: true,
            ajaxSorting: true,
            printAsHtml: true,
            printStyled: true,
            pagination: 'remote',
            paginationSize: 10,
            paginationSizeSelector: [true, 5, 10, 20, 30, 40],
            layout: 'fitColumns',
            responsiveLayout: 'collapse',
            placeholder: 'No matching records found',
            columns: [
                {
                    title: '#ID',
                    field: 'id',
                    width: '180',
                },
                {
                    title: 'Name',
                    field: 'name',
                    headerHozAlign: 'left',
                    formatter(cell, formatterParams) {
                        var html = '';
                        html +=
                            '<div class="flex lg:justify-start items-center">';
                        html +=
                            '<div class="intro-x w-10 h-10 image-fit mr-3">';
                        html +=
                            '<div class="font-medium whitespace-nowrap">' +
                            cell.getData().name +
                            '</div>';
                        html += '</div>';

                        return html;
                    },
                },
                {
                    title: 'Venue',
                    field: 'venue',
                    headerSort: false,
                    headerHozAlign: 'left',
                    formatter(cell, formatterParams) {
                        return (
                            '<div style="white-space: normal;">' +
                            cell.getData().venue +
                            '</div>'
                        );
                    },
                },
                {
                    title: 'Description',
                    field: 'description',
                    headerHozAlign: 'left',
                },
                {
                    title: 'Actions',
                    field: 'id',
                    headerSort: false,
                    hozAlign: 'right',
                    headerHozAlign: 'right',
                    width: '180',
                    download: false,
                    formatter(cell, formatterParams) {
                        var btns = '';
                        if (cell.getData().deleted_at == null) {
                            btns +=
                                '<button data-id="' +
                                cell.getData().id +
                                '" data-tw-toggle="modal" data-tw-target="#editLocationsModal" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="Pencil" class="w-4 h-4"></i></a>';
                            if (cell.getData().id > 1) {
                                btns +=
                                    '<button data-id="' +
                                    cell.getData().id +
                                    '"  class="delete_btn btn btn-danger text-white btn-rounded ml-1 p-0 w-9 h-9"><i data-lucide="Trash2" class="w-4 h-4"></i></button>';
                            }
                        } else if (cell.getData().deleted_at != null) {
                            btns +=
                                '<button data-id="' +
                                cell.getData().id +
                                '"  class="restore_btn btn btn-linkedin text-white btn-rounded ml-1 p-0 w-9 h-9"><i data-lucide="rotate-cw" class="w-4 h-4"></i></button>';
                        }

                        return btns;
                    },
                },
            ],
            renderComplete() {
                createIcons({
                    icons,
                    'stroke-width': 1.5,
                    nameAttr: 'data-lucide',
                });
                const columnLists = this.getColumns();
                if (columnLists.length > 0) {
                    const lastColumn = columnLists[columnLists.length - 1];
                    const currentWidth = lastColumn.getWidth();
                    lastColumn.setWidth(currentWidth - 1);
                }   
            },
        });

        // Redraw table onresize
        window.addEventListener('resize', () => {
            tableContent.redraw();
            createIcons({
                icons,
                'stroke-width': 1.5,
                nameAttr: 'data-lucide',
            });
        });

        // Export
        $('#tabulator-export-csv').on('click', function (event) {
            tableContent.download('csv', 'data.csv');
        });

        $('#tabulator-export-json').on('click', function (event) {
            tableContent.download('json', 'data.json');
        });

        $('#tabulator-export-xlsx').on('click', function (event) {
            window.XLSX = xlsx;
            tableContent.download('xlsx', 'data.xlsx', {
                sheetName: 'Locations Details',
            });
        });

        $('#tabulator-export-html').on('click', function (event) {
            tableContent.download('html', 'data.html', {
                style: true,
            });
        });

        // Print
        $('#tabulator-print').on('click', function (event) {
            tableContent.print();
        });
    };
    return {
        init: function () {
            _tableGen();
        },
    };
})();

(function () {
    // Tabulator
    if ($('#locationListTable').length) {
        // Init Table
        locationListTable.init();

        // Filter function
        function filterHTMLForm() {
            locationListTable.init();
        }

        // On submit filter form
        $('#tabulatorFilterForm')[0].addEventListener(
            'keypress',
            function (event) {
                let keycode = event.keyCode ? event.keyCode : event.which;
                if (keycode == '13') {
                    event.preventDefault();
                    filterHTMLForm();
                }
            }
        );

        // On click go button
        $('#tabulator-html-filter-go').on('click', function (event) {
            filterHTMLForm();
        });

        // On reset filter form
        $('#tabulator-html-filter-reset').on('click', function (event) {
            $('#query').val('');
            $('#status').val('1');
            filterHTMLForm();
        });

        let tomOptions = {
            plugins: {
                dropdown_input: {},
                remove_button: {
                    title: 'Remove this item',
                },
            },
            placeholder: 'Search Here...',
            persist: false,
            create: true,
            allowEmptyOption: true,
            onDelete: function (values) {
                return confirm(
                    values.length > 1
                        ? 'Are you sure you want to remove these ' +
                              values.length +
                              ' items?'
                        : 'Are you sure you want to remove "' + values[0] + '"?'
                );
            },
        };

        var venue_id = new TomSelect('#venue_id', tomOptions);
        var edit_venue_id = new TomSelect('#edit_venue_id', tomOptions);

        const addLocationsModal = tailwind.Modal.getOrCreateInstance(
            document.querySelector('#addLocationsModal')
        );
        const editLocationsModal = tailwind.Modal.getOrCreateInstance(
            document.querySelector('#editLocationsModal')
        );
        const successModal = tailwind.Modal.getOrCreateInstance(
            document.querySelector('#successModal')
        );
        const confirmModal = tailwind.Modal.getOrCreateInstance(
            document.querySelector('#confirmModal')
        );
        let confModalDelTitle = 'Are you sure?';

        const addLocationsModalEl =
            document.getElementById('addLocationsModal');
        addLocationsModalEl.addEventListener('hide.tw.modal', function (event) {
            $('#addLocationsModal .acc__input-error').html('');
            $(
                '#addLocationsModal .modal-body input:not([type="checkbox"])'
            ).val('');
            $('#addLocationsModal .modal-body select').val('');
            var placeholder = $('#addLocationsModal .locationImageAdd').attr(
                'data-placeholder'
            );
            $('#addLocationsModal .locationImageAdd').attr('src', placeholder);
            $('#addLocationsModal input[type="checkbox"]').prop(
                'checked',
                false
            );
            venue_id.clear(true);
        });

        const editLocationsModalEl =
            document.getElementById('editLocationsModal');
        editLocationsModalEl.addEventListener(
            'hide.tw.modal',
            function (event) {
                $('#editLocationsModal .acc__input-error').html('');
                $(
                    '#editLocationsModal .modal-body input:not([type="checkbox"])'
                ).val('');
                $('#editLocationsModal .modal-body select').val('');
                var placeholder = $(
                    '#addLocationsModal .locationImageEdit'
                ).attr('data-placeholder');
                $('#editLocationsModal .locationImageEdit').attr(
                    'src',
                    placeholder
                );
                $('#editLocationsModal input[type="checkbox"]').prop(
                    'checked',
                    false
                );
                $('#editLocationsModal input[name="id"]').val('0');
                edit_venue_id.clear(true);
            }
        );

        $('#addLocationsForm').on('change', '#locationPhotoAdd', function () {
            showPreview('locationPhotoAdd', 'locationImageAdd');
        });
        $('#editLocationsForm').on('change', '#locationPhotoEdit', function () {
            showPreview('locationPhotoEdit', 'locationImageEdit');
        });
      
        $('#addLocationsForm').on('submit', function (e) {
            e.preventDefault();
            const form = document.getElementById('addLocationsForm');

            document
                .querySelector('#saveLocation')
                .setAttribute('disabled', 'disabled');
            document.querySelector('#saveLocation svg').style.cssText =
                'display: inline-block;';

            let form_data = new FormData(form);

            axios({
                method: 'post',
                url: route('library-locations.store'),
                data: form_data,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                        'content'
                    ),
                },
            })
                .then((response) => {
                    document
                        .querySelector('#saveLocation')
                        .removeAttribute('disabled');
                    document.querySelector('#saveLocation svg').style.cssText =
                        'display: none;';
                    console.log(response.status);
                    //return false;
                    
                    if (response.status == 200) {
                        addLocationsModal.hide();

                        successModal.show();
                        document
                            .getElementById('successModal')
                            .addEventListener(
                                'shown.tw.modal',
                                function (event) {
                                    $('#successModal .successModalTitle').html(
                                        'Congratulations!'
                                    );
                                    $('#successModal .successModalDesc').html(
                                        'New User Successfully created.'
                                    );
                                }
                            );
                    }
                    locationListTable.init();
                })
                .catch((error) => {
                    document
                        .querySelector('#saveLocation')
                        .removeAttribute('disabled');
                    document.querySelector('#saveLocation svg').style.cssText =
                        'display: none;';
                    if (error.response) {
                        if (error.response.status == 422) {
                            for (const [key, val] of Object.entries(
                                error.response.data.errors
                            )) {
                                $(`#addLocationsForm .${key}`).addClass(
                                    'border-danger'
                                );
                                $(`#addLocationsForm  .error-${key}`).html(val);
                            }
                        } else {
                            console.log('error');
                        }
                    }
                });
        });

        $('#locationListTable').on('click', '.edit_btn', function () {
            let $editBtn = $(this);
            let editId = $editBtn.attr('data-id');

            axios({
                method: 'get',
                url: route('library-locations.edit', editId),
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                        'content'
                    ),
                },
            })
                .then((response) => {
                    if (response.status == 200) {
                        let dataset = response.data;
                        let venue = dataset.venue;

                        $('#editLocationsModal input[name="name"]').val(
                            dataset.name ? dataset.name : ''
                        );
                        $('#editLocationsModal input[name="description"]').val(
                            dataset.description ? dataset.description : ''
                        );
                        $('#editLocationsModal select[name="gender"]').val(
                            dataset.gender ? dataset.gender : ''
                        );

                        $('#editLocationsModal input[name="id"]').val(editId);

                        edit_venue_id.addItem(venue.id, true);
                    }
                })
                .catch((error) => {
                    console.log(error);
                });
        });

        // Update Course Data
        $('#editLocationsForm').on('submit', function (e) {
            e.preventDefault();
            let editId = $('#editLocationsForm input[name="id"]').val();
            const form = document.getElementById('editLocationsForm');

            document
                .querySelector('#updateLocation')
                .setAttribute('disabled', 'disabled');
            document.querySelector('#updateLocation svg').style.cssText =
                'display: inline-block;';

            let form_data = new FormData(form);

            axios({
                method: 'post',
                url: route('library-locations.update', editId),
                data: form_data,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                        'content'
                    ),
                },
            })
                .then((response) => {
                    if (response.status == 200) {
                        document
                            .querySelector('#updateLocation')
                            .removeAttribute('disabled');
                        document.querySelector(
                            '#updateLocation svg'
                        ).style.cssText = 'display: none;';
                        editLocationsModal.hide();

                        successModal.show();
                        document
                            .getElementById('successModal')
                            .addEventListener(
                                'shown.tw.modal',
                                function (event) {
                                    $('#successModal .successModalTitle').html(
                                        'Congratulations!'
                                    );
                                    $('#successModal .successModalDesc').html(
                                        'User data successfully updated.'
                                    );
                                }
                            );
                    }
                    locationListTable.init();
                })
                .catch((error) => {
                    document
                        .querySelector('#updateLocation')
                        .removeAttribute('disabled');
                    document.querySelector(
                        '#updateLocation svg'
                    ).style.cssText = 'display: none;';
                    if (error.response) {
                        if (error.response.status == 422) {
                            for (const [key, val] of Object.entries(
                                error.response.data.errors
                            )) {
                                $(`#editLocationsForm .${key}`).addClass(
                                    'border-danger'
                                );
                                $(`#editLocationsForm  .error-${key}`).html(
                                    val
                                );
                            }
                        } else if (error.response.status == 304) {
                            editLocationsModal.hide();

                            successModal.show();
                            document
                                .getElementById('successModal')
                                .addEventListener(
                                    'shown.tw.modal',
                                    function (event) {
                                        $('#successModal .successModal').html(
                                            'Oops!'
                                        );
                                        $('#successModal .successModal').html(
                                            'No data change found!'
                                        );
                                    }
                                );
                        } else {
                            console.log('error');
                        }
                    }
                });
        });

        // Confirm Modal Action
        $('#confirmModal .agreeWith').on('click', function () {
            let $agreeBTN = $(this);
            let recordID = $agreeBTN.attr('data-id');
            let action = $agreeBTN.attr('data-action');

            $('#confirmModal button').attr('disabled', 'disabled');
            if (action == 'DELETE') {
                axios({
                    method: 'delete',
                    url: route('library-locations.destory', recordID),
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                            'content'
                        ),
                    },
                })
                    .then((response) => {
                        if (response.status == 200) {
                            $('#confirmModal button').removeAttr('disabled');
                            confirmModal.hide();

                            successModal.show();
                            document
                                .getElementById('successModal')
                                .addEventListener(
                                    'shown.tw.modal',
                                    function (event) {
                                        $(
                                            '#successModal .successModalTitle'
                                        ).html('WOW!');
                                        $(
                                            '#successModal .successModalDesc'
                                        ).html(
                                            'Record successfully deleted from DB row.'
                                        );
                                    }
                                );
                        }
                        locationListTable.init();
                    })
                    .catch((error) => {
                        console.log(error);
                    });
            } else if (action == 'RESTORE') {
                axios({
                    method: 'post',
                    url: route('library-locations.restore', recordID),
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                            'content'
                        ),
                    },
                })
                    .then((response) => {
                        if (response.status == 200) {
                            $('#confirmModal button').removeAttr('disabled');
                            confirmModal.hide();

                            successModal.show();
                            document
                                .getElementById('successModal')
                                .addEventListener(
                                    'shown.tw.modal',
                                    function (event) {
                                        $(
                                            '#successModal .successModalTitle'
                                        ).html('WOW!');
                                        $(
                                            '#successModal .successModalDesc'
                                        ).html('Record Successfully Restored!');
                                    }
                                );
                        }
                        locationListTable.init();
                    })
                    .catch((error) => {
                        console.log(error);
                    });
            }
        });

        // Delete Course
        $('#locationListTable').on('click', '.delete_btn', function () {
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');

            confirmModal.show();
            document
                .getElementById('confirmModal')
                .addEventListener('shown.tw.modal', function (event) {
                    $('#confirmModal .confModTitle').html(confModalDelTitle);
                    $('#confirmModal .confModDesc').html(
                        'Do you really want to delete these record? If yes then please click on the agree btn.'
                    );
                    $('#confirmModal .agreeWith').attr('data-id', rowID);
                    $('#confirmModal .agreeWith').attr('data-action', 'DELETE');
                });
        });

        // Restore Course
        $('#locationListTable').on('click', '.restore_btn', function () {
            let $statusBTN = $(this);
            let courseID = $statusBTN.attr('data-id');

            confirmModal.show();
            document
                .getElementById('confirmModal')
                .addEventListener('shown.tw.modal', function (event) {
                    $('#confirmModal .confModTitle').html(confModalDelTitle);
                    $('#confirmModal .confModDesc').html(
                        'Do you really want to restore these record? Click on agree to continue.'
                    );
                    $('#confirmModal .agreeWith').attr('data-id', courseID);
                    $('#confirmModal .agreeWith').attr(
                        'data-action',
                        'RESTORE'
                    );
                });
        });
    }

    function showPreview(inputId, targetImageId) {
        var src = document.getElementById(inputId);
        var target = document.getElementById(targetImageId);
        var title = document.getElementById('selected_image_title');
        var fr = new FileReader();
        fr.onload = function () {
            target.src = fr.result;
        };
        fr.readAsDataURL(src.files[0]);
    }
})();
