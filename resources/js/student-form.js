/*
 * Progress behaviour for the in-portal "Do it online" forms.
 *
 * The markup carries everything this needs: each step card declares the
 * section it belongs to and every field that counts towards it, so the same
 * script serves any form built on the `.sfm` shell without being told about
 * its fields.
 */
import Litepicker from 'litepicker';
import { createIcons, icons } from 'lucide';

(function () {
    const root = document.querySelector('[data-sfm-form]');

    if (!root) return;

    /* Markup added after the page loaded carries data-lucide attributes that
       nothing has drawn yet. Wrapped because this is decoration: a failure
       here must never stop the form working. */
    const drawIcons = () => {
        try {
            createIcons({ icons });
        } catch (error) {
            /* Nothing to do - the icon simply does not appear. */
        }
    };

    /* Date fields use the app-wide Litepicker rather than the browser's own
       date control, so the panel matches every other date field here. Applied
       to a scope, because repeating rows arrive after the first pass. */
    const attachPickers = (scope) => {
        scope.querySelectorAll('[data-sfm-datepicker]').forEach((field) => {
            if (field.dataset.sfmPickerReady) return;

            field.dataset.sfmPickerReady = '1';

            /* An absence already happened; a deadline or a start date has
               not. Which way a field runs is declared in the markup. */
            const past = field.dataset.sfmDatepicker === 'past';

            new Litepicker({
                element: field,
                autoApply: true,
                singleMode: true,
                format: 'DD-MM-YYYY',
                minDate: past ? null : new Date(),
                maxDate: past ? new Date() : null,
                dropdowns: { minYear: 2000, maxYear: 2050, months: true, years: true },
                setup: (picker) => {
                    /* Litepicker has no className option, so the page's own
                       class is put on the panel as it renders - the month/year
                       header needs laying out and the global skin leaves it
                       stacked. */
                    picker.on('render', (ui) => ui.classList.add('sfm-picker'));
                    picker.on('selected', () => {
                        field.dispatchEvent(new Event('change', { bubbles: true }));
                    });
                },
            });
        });
    };

    attachPickers(root);

    const counter = document.querySelector('[data-sfm-counter]');
    const sections = Array.from(document.querySelectorAll('[data-sfm-section]'));

    const answered = (field) => {
        if (field.type === 'checkbox') return field.checked;
        if (field.type === 'file') return field.files && field.files.length > 0;
        /* A radio is answered when any button in its group is picked, not when
           this particular one is. */
        if (field.type === 'radio') {
            return !!root.querySelector(`input[name="${field.name}"]:checked`);
        }

        return String(field.value || '').trim() !== '';
    };

    const refresh = () => {
        let doneSections = 0;

        sections.forEach((section) => {
            const key = section.dataset.sfmSection;
            /* One radio group counts once, however many buttons it has. */
            const seen = new Set();
            const fields = Array.from(
                root.querySelectorAll(`[data-sfm-field="${key}"]`)
            ).filter((field) => {
                if (field.type !== 'radio') return true;
                if (seen.has(field.name)) return false;
                seen.add(field.name);

                return true;
            });
            const filled = fields.filter(answered).length;
            const state = document.querySelector(`[data-sfm-state="${key}"]`);
            const step = document.querySelector(`[data-sfm-step="${key}"]`);
            // A section with no fields (the read-only record) is already done.
            const complete = fields.length === 0 || filled === fields.length;

            if (complete) doneSections += 1;

            /* A section of repeating rows counts rows, not fields: "2
               assignments added" says more than "4 of 4 answered". */
            if (state && section.dataset.sfmCount === 'rows') {
                const rows = section.querySelectorAll('.sfm-row').length;
                const noun = section.dataset.sfmRownoun || 'assignment';

                state.textContent = complete && rows
                    ? `${rows} ${rows === 1 ? noun : noun + 's'} added`
                    : section.dataset.sfmPending || 'Not yet added';
            }

            /* A section can spell out its own wording (the declaration says
               "Confirmed", not "1 of 1 answered"); otherwise it counts. */
            if (state && !['false', 'rows'].includes(section.dataset.sfmCount)) {
                if (complete) {
                    state.textContent = section.dataset.sfmDone || 'Completed';
                } else {
                    state.textContent =
                        section.dataset.sfmPending ||
                        `${filled} of ${fields.length} answered`;
                }
            }

            if (step) {
                step.classList.toggle('sfm-prog--done', complete);
                step.classList.toggle(
                    'sfm-prog--active',
                    !complete && filled > 0
                );
            }
        });

        if (counter) {
            counter.textContent = `${doneSections} of ${sections.length} answered`;
        }
    };

    root.addEventListener('input', refresh);
    root.addEventListener('change', refresh);


    /* Bank fields take digits only; the sort code reads back as 12-34-56 while
       it is typed. The server strips the formatting again either way. */
    const digitsOnly = (value) => String(value || '').replace(/\D/g, '');

    root.querySelectorAll('[data-sfm-digits]').forEach((field) => {
        const max = Number(field.dataset.sfmDigits) || 8;

        field.addEventListener('input', () => {
            field.value = digitsOnly(field.value).slice(0, max);
        });
    });

    root.querySelectorAll('[data-sfm-sortcode]').forEach((field) => {
        field.addEventListener('input', () => {
            const pairs = digitsOnly(field.value).slice(0, 6).match(/\d{1,2}/g);
            field.value = pairs ? pairs.join('-') : '';
        });
    });

    /* An amount typed as "£1,250.50" is posted as 1250.50. */
    root.querySelectorAll('[data-sfm-money]').forEach((field) => {
        const clean = () => String(field.value || '').replace(/[^\d.]/g, '');

        field.addEventListener('blur', () => {
            const amount = clean();
            field.value = amount && !isNaN(amount)
                ? Number(amount).toFixed(2)
                : field.value;
        });

        root.addEventListener('submit', () => {
            field.value = clean();
        });
    });


    /* Repeating rows: one claim can cover several assignments. The rows are
       numbered on every change so they always post as a clean, gapless array,
       whichever one was removed. */
    const rowList = root.querySelector('[data-sfm-rows]');
    const rowTemplate = root.querySelector('[data-sfm-rowtemplate]');

    if (rowList && rowTemplate) {
        const field = rowList.dataset.sfmRows || 'assignments';

        const number = () => {
            const rows = Array.from(rowList.querySelectorAll('.sfm-row'));

            rows.forEach((row, index) => {
                row.querySelectorAll('[data-sfm-rowkey]').forEach((input) => {
                    const key = input.dataset.sfmRowkey;

                    input.id = `${field}_${index}_${key}`;
                    input.name = `${field}[${index}][${key}]`;

                    const label = row.querySelector(`[data-sfm-rowlabel="${key}"]`);
                    if (label) label.htmlFor = input.id;
                });

                /* The first row cannot be removed - a claim with no assignment
                   on it is not a claim. */
                const remove = row.querySelector('[data-sfm-removerow]');
                if (remove) remove.hidden = rows.length === 1;
            });

            refresh();
        };

        const addRow = (focus) => {
            const row = rowTemplate.content.firstElementChild.cloneNode(true);

            row.querySelector('[data-sfm-removerow]')?.addEventListener('click', () => {
                row.remove();
                number();
            });

            rowList.appendChild(row);
            number();
            attachPickers(row);

            drawIcons();
            if (focus) row.querySelector('input')?.focus();
        };

        root.querySelector('[data-sfm-addrow]')?.addEventListener('click', () => addRow(true));

        addRow(false);
    }

    /* A question can open a panel of its own, declared on the panel as
       data-sfm-when="field:value". The panel's fields stay disabled while it
       is hidden, so an answer the student changed their mind about is never
       posted - and a file input that is not in play posts nothing. */
    const panels = Array.from(root.querySelectorAll('[data-sfm-when]'));

    if (panels.length) {
        const syncPanels = () => {
            panels.forEach((panel) => {
                const [name, wanted] = String(panel.dataset.sfmWhen).split(':');
                /* Radios have one answer; tick boxes have as many as were
                   ticked, so the panel asks whether its own value is among
                   them. */
                /* Quoted attribute values need no CSS escaping - and escaping
                   them breaks a name like reasons[], which is how a tick-box
                   group posts. */
                const show = Array.from(root.querySelectorAll(`input[name="${name}"]`))
                    .some((input) => input.checked && input.value === wanted);

                panel.hidden = !show;
                panel.querySelectorAll('input, textarea, select').forEach((field) => {
                    field.disabled = !show;
                });
            });
        };

        root.addEventListener('change', syncPanels);
        syncPanels();
    }

    /* Some answers make the form unsubmittable - an appeal before the results
       were notified, for instance. A panel saying so carries data-sfm-blocks,
       and while it is on screen the submit button is off. The server refuses
       the same answer, because a disabled button is a courtesy, not a rule. */
    const blockers = Array.from(root.querySelectorAll('[data-sfm-blocks]'));
    const submit = root.querySelector('button[type="submit"]');

    if (blockers.length && submit) {
        const syncBlockers = () => {
            const blocking = blockers.find((panel) => !panel.hidden);

            submit.disabled = !!blocking;
            submit.title = blocking ? blocking.dataset.sfmBlocks : '';
        };

        /* After the panels themselves, so their hidden state is settled. */
        root.addEventListener('change', syncBlockers);
        syncBlockers();
    }

    /* Device details, as the form says are recorded. A convenience only - the
       sender can edit them, so the server keeps the IP and User-Agent too. */
    const deviceFields = Array.from(root.querySelectorAll('[data-sfm-device]'));

    if (deviceFields.length) {
        const ua = navigator.userAgent;
        // iPadOS calls itself a Mac, so touch points are what give it away.
        const touchMac = /Macintosh/.test(ua) && navigator.maxTouchPoints > 1;
        const tablet = /iPad|Tablet/.test(ua) || touchMac || (/Android/.test(ua) && !/Mobile/.test(ua));

        const device = {
            type: tablet ? 'Tablet' : /Mobi|iPhone|iPod|Android/.test(ua) ? 'Mobile' : 'Desktop',
            os: /Windows/.test(ua) ? 'Windows'
                : /iPad/.test(ua) || touchMac ? 'iPadOS'
                : /iPhone|iPod/.test(ua) ? 'iOS'
                : /Android/.test(ua) ? 'Android'
                : /Mac OS X/.test(ua) ? 'macOS'
                : /CrOS/.test(ua) ? 'ChromeOS'
                : /Linux/.test(ua) ? 'Linux' : 'Unknown OS',
            browser: /Edg(e|A|iOS)?\//.test(ua) ? 'Edge'
                : /OPR\//.test(ua) ? 'Opera'
                : /SamsungBrowser/.test(ua) ? 'Samsung Internet'
                : /Firefox\/|FxiOS/.test(ua) ? 'Firefox'
                : /Chrome\/|CriOS/.test(ua) ? 'Chrome'
                : /Safari\//.test(ua) ? 'Safari' : 'Unknown browser',
            screen: `${window.screen.width} x ${window.screen.height}`,
        };

        deviceFields.forEach((field) => {
            field.value = device[field.dataset.sfmDevice] || '';
        });
    }

    /* Supporting documents: the picker is hidden behind the drop zone, so the
       chosen files are listed back or nothing on screen changes. */
    const upload = root.querySelector('[data-sfm-upload]');
    const fileList = root.querySelector('[data-sfm-filelist]');
    const dropZone = root.querySelector('[data-sfm-drop]');

    if (upload && fileList) {
        const readableSize = (bytes) =>
            bytes >= 1048576
                ? `${(bytes / 1048576).toFixed(1)} MB`
                : `${Math.max(1, Math.round(bytes / 1024))} KB`;

        const listFiles = () => {
            fileList.innerHTML = '';

            Array.from(upload.files || []).forEach((file) => {
                const row = document.createElement('div');
                row.className = 'sfm-file';
                row.innerHTML =
                    '<i data-lucide="paperclip" class="w-4 h-4"></i>' +
                    `<span class="sfm-file__name"></span>` +
                    `<span class="sfm-file__size">${readableSize(file.size)}</span>`;
                row.querySelector('.sfm-file__name').textContent = file.name;
                fileList.appendChild(row);
            });

            drawIcons();
        };

        upload.addEventListener('change', listFiles);

        if (dropZone) {
            ['dragenter', 'dragover'].forEach((type) =>
                dropZone.addEventListener(type, (e) => {
                    e.preventDefault();
                    dropZone.classList.add('sfm-drop--over');
                })
            );

            ['dragleave', 'drop'].forEach((type) =>
                dropZone.addEventListener(type, (e) => {
                    e.preventDefault();
                    dropZone.classList.remove('sfm-drop--over');
                })
            );

            dropZone.addEventListener('drop', (e) => {
                if (!e.dataTransfer || !e.dataTransfer.files.length) return;

                upload.files = e.dataTransfer.files;
                listFiles();
                refresh();
            });
        }
    }

    /* Character count under the free-text answers. */
    root.querySelectorAll('[data-sfm-counted]').forEach((field) => {
        const readout = root.querySelector(
            `[data-sfm-countfor="${field.dataset.sfmCounted}"]`
        );

        if (!readout) return;

        const show = () => {
            readout.textContent = `${String(field.value || '').trim().length} characters`;
        };

        field.addEventListener('input', show);
        show();
    });

    refresh();
})();
