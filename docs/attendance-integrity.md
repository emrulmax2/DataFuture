# Attendance Integrity

Monitoring of how attendance is entered and changed, with anomaly detection, risk scores, investigations and reports. Built 3 Nov 2026.

- **Monitoring:** `/attendance-integrity`.
- **Settings:** Attendance Integrity > Settings (`/attendance-integrity/settings`). Moved from Site Settings on 3 Nov 2026; the old address `/site-settings/attendance-settings` redirects there.
- **Times:** UK time throughout.

Findings are patterns that need an explanation. They are not proof of wrongdoing, and nothing here acts on staff or students automatically.

## Deploying

1. **Deploy the files** listed at the end of this document. `routes/web.php`, `vite.config.js`, `app/Console/Kernel.php`, `app/Providers/AppServiceProvider.php`, `config/privileges.php`, the settings sidebar and `ProcessController` also carry other people's changes: copy only the Attendance Integrity lines, which are marked with comments.
2. **Run `php artisan migrate`.** It adds two migrations, which create new tables only: `attendance_audit_logs`, `attendance_audit_seals` and the `aim_*` tables. No existing table is altered.
   - If the code goes live before the migration, attendance keeps saving: the history is skipped and a warning is written to the log.
3. **Run `npm run build`.** New: `resources/js/attendance-integrity.js`, `resources/css/attendance-integrity.css`. Changed: `tutor-attendance-feed.js`, `attendance-feed.js`, `attendance-studentstaff.js`, plus the new `attendance-correction-reason.js` they import.
4. **Check the scheduler runs:** cron must run `php artisan schedule:run` every minute. It drives three jobs:
   - `attendance-integrity:scan --pending`: every 5 minutes, runs scans queued from the screens.
   - `attendance-integrity:scan --daily`: nightly at the time set in Settings > Scans (default 02:30).
   - `attendance-integrity:seal`: nightly at 04:10, adds the next hash seal to the history.

   No queue worker is needed.
5. **Grant access:** give the "Attendance Integrity Privileges" parent switch plus the areas to the named people. Use the employee's Privilege tab or a department template. Super admins have everything already.
6. **Scan past terms:** use Attendance Integrity > Settings > Scan terms, or run `php artisan attendance-integrity:scan --term=53 --term=54 --term=55`. A term takes about 3–5 seconds.
7. **Review Settings** (`/attendance-integrity/settings`). The defaults are listed below.

**To undo:** `php artisan migrate:rollback --step=2` drops only the new tables. Settings live in `options` under `ATTENDANCE_SETTINGS`.

## What changed for staff (security fixes)

| Screen | Before | Now |
|---|---|---|
| Class register (tutor screen) | Anyone signed in could save any register; the "locked after the class ended" rule was only a hidden button | Only the class's tutor, personal tutor or cover tutor (or the theory-class tutor, or whoever started the class) can save. Others need Module Contents > Edit Attendance. The ended-and-fed lock is checked on the server. On/off: Settings > Who can record attendance |
| Class register (office screen) | Anyone signed in | Same ownership rule as the tutor screen |
| Student profile > attendance edit | The page checked Attendance Edit; the save did not | The save checks it too; the rows must belong to one student; rows with a note (visits) cannot be changed |
| Delete attendance | No check (the Attendance Delete privilege existed but was unused) | Needs Live Student > Attendance Delete; the button is hidden without it |
| Restore attendance | Broken (no id in the route, no response) | `POST attendance/restore/{id}`, needs Attendance Delete |
| Student visits | Routes outside the login group; add, edit and delete checked only in the page | Login required; Visit Add / Edit / Delete checked on the server |
| Late corrections | Nothing | Changing a status, or deleting, more than N hours after the class ended (default 48) asks for a reason (default at least 10 characters). The reason is kept in the history and shown in Attendance Integrity |

**Who was affected on 3 Nov 2026:** these staff saved registers for classes they do not teach, without Edit Attendance, in September–October 2026: Sohel Rana, Irteeza Haider Chowdhury and Sumaiya Islam. Grant them Edit Attendance if that is their job, or switch the rule off.

## How it works

### Attendance history (`attendance_audit_logs`)

- **What is recorded:** every create, update, delete and restore of an `attendances` row. Each entry has old and new values, who, when (UK time), the class end, the minutes after the class, the screen (`source`), the late-correction flag and reason, IP address, user agent and route.
- **How it is captured:**
  - Model saves go through `App\Observers\AttendanceObserver`.
  - Single-SQL updates go through `AttendanceAudit::updateRows()`, used by the class registers, excuse review, group re-assign and personal tutor notes.
  - Each row's change and its history entry share one transaction.
- **Append-only:**
  - The model refuses updates and deletes.
  - `attendance-integrity:seal` chains every row into a SHA-256 hash each night.
  - `php artisan attendance-integrity:seal --verify` reports any row changed or removed afterwards.
- **No backfill:** history starts on deployment and nothing is invented. Older changes show only the last editor.
- **The absence-notice trail:** the class register writes `sms_notification` and `notofication_date` only when it saves a student Absent, and clears them on any other register save. The single-change screen leaves them alone. So a row that is Present now and still carries the notice was saved Absent first. That is how older Absent > Present changes are shown, labelled as known from the absence notice.

### Detection rules

- **Code:** `app/Services/AttendanceIntegrity/Detection`. The scanner runs each rule over one term.
- **Storage:**
  - Results go to `aim_findings`, one row per fingerprint, so re-scans update rows rather than duplicate them.
  - Findings that are no longer produced are marked `cleared_at`, never deleted.
  - Dismissed findings keep their reason.
- **Basis of each finding:** fact, anomaly, or evidence not available.

| Rule | What it finds | Default points |
|---|---|---|
| A Late entry | First entry later than 24/48/72h/7/14 days after the scheduled class end (from 2025, when `created_at` is reliable) | 5/8/12/20/25 per class date and person |
| B Changes | From the history: Absent > Present (25), late corrections (10), repeated changes (10), Present > Absent (0). Before the history: Absent > Present from the absence notice (20). By the class's own tutor: 5. Excuse-review changes are not scored | per record |
| C Bulk entry | Many separate entries (a register saved at once counts as one) across 2+ class dates: 10 in 30s, 20 in 60s, 30 in 120s | 15 per burst |
| D High attendance | Class at least 95% and 20+ points above similar classes; a student's attendance up 30+ points on the previous term | 5 |
| E Patterns | Changes in classes the person does not teach and without Edit Attendance (25 once per term; 0 with Edit Attendance); the same student's absence changed to Present 3+ times (15); lone Presents between two absences, 10+ and 15%+ (15) | |
| F Outside hours | Night (22:00–06:00); a non-working day (not a teaching day and no hours in the HR working pattern); approved leave; 14+ days after term end | 10 per person per day |
| G Timetable | Present at a cancelled class (15); attendance on a deleted class date (10); two campus classes at once, online classes left out (15); not assigned to the class (10) | |
| H Evidence | Changed to Present after the student's own excuse was rejected (30); register Present while the rejected excuse says absent (10); Present despite an approved excuse (10); Present during a mitigating-circumstances absence (30); no class start record (10) | |
| J Deletions | Deleted records per class date. Who deleted them only from the history or the visit record | 5 |

- **Rule I (notifications):** this is the absence-notice trail described above, used inside rule B. It is not scored separately.
- **Independent evidence:** there is no card swipe, QR or learning-platform data. Missing data is never treated as absence.

### Scores

- **Event keys:** findings about the same event share a key. That is one record, one class date for one person, or one pattern per person, pair or class per term.
- **A subject's score** is the sum over its events of the highest points among that event's findings. Dismissed and cleared findings do not count.
- **Bands:** staff and students score on different scales, so each has its own bands. Defaults come from the 2026 terms (about the top 10% High, the next 15% Medium):
  - Staff: Medium from 600, High from 1,500.
  - Students: Medium from 100, High from 200.

### Screens and access

Each area needs the parent switch "Attendance Integrity" plus its own key. The keys are in `App\Support\LegacyPrivilegeMap`, the routes in `config/privileges.php` (enforced from day one), and each action checks again through `IntegrityAccess::authorize()`.

| Area | Key |
|---|---|
| Dashboard, records, Retrospective, Bulk, Timetable, all findings | View dashboard |
| Staff Analysis, Staff–Student Patterns | View staff |
| Student Analysis | View students |
| Attendance Amendments (the history) | View attendance history |
| Investigations (read) | View investigations |
| Open cases, change status, notes, evidence, outcome, dismiss findings | Manage investigations |
| Reports and every export | Export |
| Settings (rules, scans, history seal) | Manage settings |

- **Access log:** every page, record, export and evidence download is written to `aim_access_logs` (UK GDPR).
- **Case evidence:** files are stored on the private local disk under `aim-evidence/{case}` with a SHA-256 hash, and cannot be removed.

### Reports

The ten reports from the specification are listed below. Each exports to Excel, CSV and PDF (the PDF holds up to 1,500 rows). Every file starts with the filters, the time generated (UK), who generated it, notes, and a Basis column.

1. Term summary
2. Staff anomalies
3. Student anomalies
4. Retrospective
5. Amendment history
6. Bulk entry
7. Staff–student patterns
8. Timetable conflicts
9. Deleted attendance
10. Case register

## Data limitations

- **Last editor only:** `attendances.updated_by` keeps only the last editor. Before the history, earlier editors and previous values are lost.
- **Capture date:** `attendance_captured_at` is a date only, and every register save overwrites it.
- **Late status:** "Late" is chosen by the tutor. Nothing compares it with the class start time.
- **Notification fields:**
  - `email_notification` is never set.
  - `sms_notification` is set whenever the status is Absent, whether or not an SMS was sent.
  - `notofied_by` holds the class tutor, not the sender.
- **Times on dev:** `updated_at` and `deleted_at` hold UK clock digits written as UTC. Code reads them with the session time zone at +00:00. New tables use DATETIME in UK time.
- **HR working patterns** describe contracted hours. Teaching days always count as working days.

## Tests

`php vendor/bin/phpunit tests/Feature/AttendanceIntegrity/` runs 26 tests. They use the dev database inside a rolled-back transaction, and fake outgoing HTTP so no SMS can leave.

## Files

- **New:**
  - `app/Services/AttendanceIntegrity/*`, including `Detection/*`
  - `app/Http/Controllers/AttendanceIntegrity/*` (settings: `DashboardController::settings()` / `saveSettings()`)
  - `app/Models/AttendanceAuditLog.php` and `app/Models/AttendanceIntegrity/*`
  - `app/Observers/AttendanceObserver.php`
  - `app/Console/Commands/AttendanceIntegrityScan.php` and `AttendanceAuditSeal.php`
  - two migrations dated `2026_11_03_*`
  - `resources/views/pages/attendance-integrity/*` (layout `resources/views/layout/attendance-integrity.blade.php`)
  - `resources/js/attendance-integrity.js`, `resources/js/attendance-correction-reason.js` and `resources/css/attendance-integrity.css`
  - `tests/Feature/AttendanceIntegrity/*`
  - `docs/attendance-integrity.md`
- **Changed (Attendance Integrity lines only):**
  - `routes/web.php`, `vite.config.js`, `app/Console/Kernel.php`, `app/Providers/AppServiceProvider.php`
  - `config/privileges.php`, `app/Support/LegacyPrivilegeMap.php`
  - `app/Http/Controllers/Attendance/AttendanceController.php`, `StudentVisitController.php`
  - `Student/ProcessController.php`, `Student/NoteController.php`
  - `CourseManagement/AssignController.php`, `CourseManagement/PlanController.php`
  - `resources/js/tutor-attendance-feed.js`, `attendance-feed.js`, `attendance-studentstaff.js`
  - `resources/views/pages/students/live/attendance/form.blade.php`, `resources/views/pages/settings/sidebar.blade.php`
  - the three privilege screens: `employee/profile/permission-template.blade.php`, `employee/profile/privilege.blade.php` and `settings/permissions/partials/permission-set.blade.php`
