<template>
    <div class="mx-back">
        <div class="mx-box cm-box" role="dialog" aria-modal="true" aria-labelledby="cm-h">
            <div class="mx-h cm-head">
                <div>
                    <h2 id="cm-h">{{ isEmail ? "Send email" : "Send SMS" }}</h2>
                    <div class="muted small">{{ target.name }}<template v-if="opts && opts.student.regNo"> · {{ opts.student.regNo }}</template></div>
                </div>
                <button ref="xBtn" type="button" class="cm-x" aria-label="Close" @click="close"><Ico name="x" /></button>
            </div>

            <div v-if="sent" class="mx-b cm-sent" role="status">
                <span class="cm-tick"><Ico name="check" /></span>
                <p>{{ sent }}</p>
                <a v-if="opts" class="btn-link small" :href="opts.student.communicationUrl" target="_blank" rel="noopener">Open their Communication tab ↗</a>
            </div>

            <form v-else id="cm-form" class="mx-b" novalidate @submit.prevent="submit">
                <div class="cm-to">
                    <span class="lbl">To</span>
                    <span v-if="!opts" class="muted">Loading…</span>
                    <template v-else-if="isEmail"><span v-if="opts.student.emails.length" class="mono small">{{ opts.student.emails.join(", ") }}</span><span v-else class="bad-text">No email address on record</span></template>
                    <template v-else><span v-if="opts.student.mobile" class="mono small">{{ opts.student.mobile }}</span><span v-else class="bad-text">No mobile number on record</span></template>
                    <a v-if="opts" class="btn-link small cm-commlink" :href="opts.student.communicationUrl" target="_blank" rel="noopener">Communication tab ↗</a>
                </div>
                <div v-if="opts && isEmail && opts.mailDivertedTo" class="notice warn"><Ico name="info" /><span>Test site: this email goes to <b>{{ opts.mailDivertedTo }}</b>, not to the student.</span></div>
                <div v-if="opts && !isEmail && opts.smsToLog" class="notice warn"><Ico name="info" /><span>Test site: the SMS is written to the application log, not sent to the student.</span></div>
                <div v-if="opts && !isEmail && !opts.student.mobile" class="notice bad"><Ico name="info" /><span>{{ opts.student.hasContact ? "The SMS will be saved on the student’s Communication tab but not sent, because they have no mobile number." : "The student has no contact details on record, so an SMS can’t be saved or sent." }}</span></div>
                <div v-if="failed" class="notice bad" role="alert"><Ico name="info" /><span>{{ failed }}</span></div>

                <template v-if="isEmail">
                    <div class="cm-f">
                        <label class="lbl" for="cm-smtp">SMTP <span class="req">*</span></label>
                        <select id="cm-smtp" v-model="smtp" class="input" :aria-invalid="errors.comon_smtp_id ? 'true' : undefined" :disabled="!opts">
                            <option value="">Please select</option>
                            <option v-for="s in (opts ? opts.smtps : [])" :key="s.id" :value="String(s.id)">{{ s.name }}</option>
                        </select>
                        <div v-if="errors.comon_smtp_id" class="cm-err">{{ errors.comon_smtp_id }}</div>
                    </div>
                    <div class="cm-f">
                        <label class="lbl" for="cm-subject">Subject <span class="req">*</span></label>
                        <input id="cm-subject" ref="first" v-model="subject" class="input" type="text" :aria-invalid="errors.subject ? 'true' : undefined">
                        <div v-if="errors.subject" class="cm-err">{{ errors.subject }}</div>
                    </div>
                    <div class="cm-f">
                        <label class="lbl" for="cm-bcc">BCC</label>
                        <input id="cm-bcc" v-model="bcc" class="input" type="text" placeholder="name@domain.com, name@domain.com" autocomplete="off" aria-describedby="cm-bcc-h" :aria-invalid="errors.bcc_emails ? 'true' : undefined">
                        <div id="cm-bcc-h" class="muted small">Separate more than one email with a comma.</div>
                        <div v-if="errors.bcc_emails" class="cm-err">{{ errors.bcc_emails }}</div>
                    </div>
                    <div class="cm-f">
                        <label class="lbl" for="cm-tpl">Template</label>
                        <select id="cm-tpl" v-model="template" class="input" :disabled="!opts" @change="useTemplate">
                            <option value="">Please select</option>
                            <option v-for="t in (opts ? opts.emailTemplates : [])" :key="t.id" :value="String(t.id)">{{ t.name }}</option>
                        </select>
                    </div>
                    <div class="cm-f">
                        <span id="cm-body-l" class="lbl">Message <span class="req">*</span></span>
                        <div v-if="!editorFailed" :class="'cm-editor' + (errors.body ? ' is-bad' : '')">
                            <div ref="toolbarEl" class="cm-toolbar"></div>
                            <div ref="editorEl" class="cm-editable" aria-labelledby="cm-body-l"></div>
                            <div v-if="!editorReady" class="cm-editor-wait muted small">Loading the editor…</div>
                        </div>
                        <textarea v-else v-model="bodyText" class="input cm-textarea" rows="10" aria-labelledby="cm-body-l"></textarea>
                        <div v-if="errors.body" class="cm-err">{{ errors.body }}</div>
                    </div>
                    <div class="cm-f">
                        <div class="row">
                            <label class="btn btn-sm" for="cm-files"><Ico name="clip" />Upload attachments</label>
                            <input id="cm-files" class="cm-file" type="file" multiple accept=".jpeg,.jpg,.png,.gif,.txt,.pdf,.xl,.xls,.xlsx,.doc,.docx,.ppt,.pptx" @change="addFiles">
                            <span v-if="!files.length" class="muted small">No attachments</span>
                        </div>
                        <ul v-if="files.length" class="cm-files">
                            <li v-for="(f, k) in files" :key="k + f.name"><Ico name="doc" /><span>{{ f.name }}</span><span class="muted small">{{ size(f.size) }}</span><button type="button" class="btn-link small" :aria-label="'Remove ' + f.name" @click="files.splice(k, 1)">Remove</button></li>
                        </ul>
                        <div v-if="errors.documents" class="cm-err">{{ errors.documents }}</div>
                    </div>
                </template>

                <template v-else>
                    <div class="cm-f">
                        <label class="lbl" for="cm-stpl">Template</label>
                        <select id="cm-stpl" v-model="template" class="input" :disabled="!opts" @change="useTemplate">
                            <option value="">Please select</option>
                            <option v-for="t in (opts ? opts.smsTemplates : [])" :key="t.id" :value="String(t.id)">{{ t.name }}</option>
                        </select>
                    </div>
                    <div class="cm-f">
                        <label class="lbl" for="cm-ssubject">Subject <span class="req">*</span></label>
                        <input id="cm-ssubject" ref="first" v-model="subject" class="input" type="text" :aria-invalid="errors.subject ? 'true' : undefined">
                        <div v-if="errors.subject" class="cm-err">{{ errors.subject }}</div>
                    </div>
                    <div class="cm-f">
                        <div class="cm-lblrow"><label class="lbl" for="cm-sms">SMS <span class="req">*</span></label><span class="cm-count" aria-live="polite">{{ smsCount.left }} characters left · {{ smsCount.parts }} SMS</span></div>
                        <textarea id="cm-sms" v-model="sms" class="input cm-textarea" rows="7" :aria-invalid="errors.sms ? 'true' : undefined"></textarea>
                        <div v-if="errors.sms" class="cm-err">{{ errors.sms }}</div>
                    </div>
                </template>

                <label v-if="canMark" class="check-line cm-mark"><input v-model="markContacted" type="checkbox"><span>Also mark <b>{{ target.contact.label }}</b> as contacted on the Contact student list</span></label>
            </form>

            <div class="mx-f">
                <button v-if="sent" ref="doneBtn" type="button" class="btn btn-primary" @click="close">Done</button>
                <template v-else>
                    <label v-if="!isEmail" class="check-line cm-news"><input v-model="showAsNews" type="checkbox"><span>Show as news</span></label>
                    <button type="button" class="btn" @click="close">Cancel</button>
                    <button type="submit" form="cm-form" class="btn btn-primary" :disabled="!opts || store.busy || (!isEmail && !opts.student.hasContact)">{{ store.busy ? "Sending…" : isEmail ? "Send email" : "Send SMS" }}</button>
                </template>
            </div>
        </div>
    </div>
</template>

<script>
import { computed, inject, nextTick, onBeforeUnmount, onMounted, reactive, ref } from "vue";
import Ico from "./Ico.vue";

/*
 * Actions › Email / SMS (user, 6 Nov 2026): the student Communication tab's Send Email and Send SMS
 * modals (/student/communication/{id}) in the page's own look — SMTP, subject, BCC, template, the
 * same document editor and attachments for email; template, subject, the 160-character counter and
 * Show as news for SMS. The server sends through that tab's own senders, so the message lands on the
 * student's Communication tab. Opened from a Contact student line it starts with that line's message
 * and can mark the line contacted. Like the original (a static modal), only Cancel or × closes it,
 * so a click outside or Escape in the editor doesn't lose a half-written message.
 */
const SMTP_KEY = "slr.comms.smtp"; // the SMTP last sent from, per viewer

const esc = (t) => String(t).replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");

export default {
    components: { Ico },
    props: {
        // { kind: 'email' | 'sms', studentId, name, preset: { subject, text } | null, contact: { rowId, key, label, state } | null }
        target: { type: Object, required: true },
    },
    emits: ["close"],
    setup(props, { emit }) {
        const { store, get, send } = inject("slr");
        const isEmail = props.target.kind === "email";
        const opts = ref(null);
        const failed = ref("");
        const sent = ref("");
        const errors = reactive({});
        const preset = props.target.preset || {};

        const smtp = ref("");
        const subject = ref(preset.subject || "");
        const bcc = ref("");
        const template = ref("");
        const files = ref([]);
        const sms = ref(isEmail ? "" : preset.text || "");
        const showAsNews = ref(false);
        const markContacted = ref(true);
        const canMark = computed(() => !!(props.target.contact && !props.target.contact.state));

        // the email body: the Communication tab's document editor (CKEditor, loaded when first needed)
        const editorEl = ref(null);
        const toolbarEl = ref(null);
        const editorReady = ref(false);
        const editorFailed = ref(false);
        const bodyText = ref(""); // only if the editor can't load
        const startBody = isEmail && preset.text ? "<p>" + esc(preset.text) + "</p>" : "";
        let editor = null;
        let closed = false;
        const setBody = (html) => { if (editor) editor.setData(html); else bodyText.value = html; };
        const getBody = () => (editor ? editor.getData() : bodyText.value);

        const first = ref(null);
        const xBtn = ref(null);
        const doneBtn = ref(null);
        const opener = document.activeElement;
        const overflow = document.body.style.overflow;

        onMounted(async () => {
            document.body.style.overflow = "hidden";
            if (first.value) first.value.focus();
            const d = await get("slc.registration.comms", { student_id: props.target.studentId }).catch(() => null);
            if (closed) return;
            if (!d) { failed.value = "The student’s contact details could not be loaded. Close this and try again."; return; }
            opts.value = d;
            try {
                const last = window.localStorage.getItem(SMTP_KEY);
                if (last && d.smtps.some((s) => String(s.id) === last)) smtp.value = last;
            } catch (e) { /* no storage: start on Please select */ }
            if (isEmail) {
                try {
                    const { default: DecoupledEditor } = await import("@ckeditor/ckeditor5-build-decoupled-document");
                    if (closed || !editorEl.value) return;
                    editor = await DecoupledEditor.create(editorEl.value);
                    if (closed) { editor.destroy(); editor = null; return; }
                    toolbarEl.value.appendChild(editor.ui.view.toolbar.element);
                    if (startBody) editor.setData(startBody);
                    editor.model.document.on("change:data", () => { delete errors.body; });
                    editorReady.value = true;
                } catch (e) {
                    editorFailed.value = true;
                    bodyText.value = startBody;
                }
            }
        });
        onBeforeUnmount(() => {
            closed = true;
            document.body.style.overflow = overflow;
            if (editor) { editor.destroy().catch(() => {}); editor = null; }
            if (opener && typeof opener.focus === "function" && document.contains(opener)) opener.focus();
        });

        const close = () => emit("close");

        const useTemplate = async () => {
            if (!template.value) {
                if (isEmail) setBody(""); else sms.value = "";
                return;
            }
            const d = await get("slc.registration.comms.template", { type: isEmail ? "email" : "sms", id: template.value }).catch(() => null);
            if (!d) return;
            if (isEmail) {
                subject.value = d.subject || "";
                setBody(d.body || "");
            } else {
                sms.value = d.body || "";
            }
        };

        // SMS: characters left in the current message / how many messages, as the Communication tab counts them
        const smsCount = computed(() => {
            const chars = sms.value.length;
            if (!chars) return { left: 160, parts: 1 };
            const parts = Math.ceil(chars / 160);
            return { left: parts * 160 - (chars % (parts * 160) || parts * 160), parts };
        });

        const addFiles = (e) => {
            files.value = [...files.value, ...Array.from(e.target.files || [])];
            e.target.value = "";
            delete errors.documents;
        };
        const size = (b) => (b >= 1048576 ? (b / 1048576).toFixed(1) + " MB" : Math.max(1, Math.round(b / 1024)) + " KB");

        const submit = async () => {
            if (!opts.value || store.busy) return;
            failed.value = "";
            Object.keys(errors).forEach((k) => delete errors[k]);
            const fd = new FormData();
            fd.append("student_id", String(props.target.studentId));
            fd.append("subject", subject.value);
            if (isEmail) {
                fd.append("comon_smtp_id", smtp.value);
                fd.append("bcc_emails", bcc.value);
                fd.append("email_template_id", template.value);
                fd.append("body", getBody());
                files.value.forEach((f) => fd.append("documents[]", f));
            } else {
                fd.append("sms_template_id", template.value);
                fd.append("sms", sms.value);
                if (showAsNews.value) fd.append("show_as_news", "1");
            }
            if (props.target.contact) {
                fd.append("row_id", String(props.target.contact.rowId));
                fd.append("key", props.target.contact.key);
                fd.append("mark_contacted", canMark.value && markContacted.value ? "1" : "0");
            }
            const r = await send(isEmail ? "slc.registration.comms.email" : "slc.registration.comms.sms", fd);
            if (r.errors) {
                Object.entries(r.errors).forEach(([k, v]) => { errors[k.split(".")[0]] = Array.isArray(v) ? v[0] : v; });
                if (errors.student_id) failed.value = errors.student_id;
                return;
            }
            if (!r.ok) {
                failed.value = (r.data && ((r.data.flash && r.data.flash.text) || r.data.message)) || "The message could not be sent. Please try again.";
                return;
            }
            if (isEmail) { try { window.localStorage.setItem(SMTP_KEY, smtp.value); } catch (e) { /* not kept */ } }
            sent.value = (r.data && r.data.flash && r.data.flash.text) || (isEmail ? "Email sent." : "SMS sent.");
            nextTick(() => { if (doneBtn.value) doneBtn.value.focus(); });
        };

        return { store, isEmail, opts, failed, sent, errors, smtp, subject, bcc, template, files, sms, showAsNews, markContacted, canMark, editorEl, toolbarEl, editorReady, editorFailed, bodyText, first, xBtn, doneBtn, close, useTemplate, smsCount, addFiles, size, submit };
    },
};
</script>
