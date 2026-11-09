# Admission Task Analysis
### Baseline study for automating the admission process

Prepared 19 October 2026. Every figure below is measured from the live database
(3,554 applicants, 25,002 applicant task rows, 24,462 task-document links).
No code was changed to produce this.

---

## 1. How the process works today

Four process lists exist. Admission is **process 1, "New Application Task"**
(phase `Applicant`), holding **10 possible tasks**. The other three
(`New Student`, `SLC NEW`, `General`) are phase `Live` and belong to the student
side, after conversion.

The pipeline is driven by applicant status. Two transitions are already
automatic:

| Status | How it is reached |
| --- | --- |
| 1 Submitting -> 2 New | applicant submits the application |
| 3 In Progress | AUTOMATIC - the moment any task is assigned (AdmissionController.php:1050) |
| 4 Awaiting Decision | AUTOMATIC - when the last pending task is completed (AdmissionController.php:1202) |
| 5 Accepted -> 6 Offer Placed -> 7 Offer Accepted | manual decision |
| 8 Rejected / 9 Offer Rejected / 46 Canceled | manual |

Current population: 2,231 Offer Accepted, 977 Rejected, 171 In Progress,
122 Submitting, 23 Offer Rejected, 10 Canceled, 9 New, 3 Awaiting Decision.

### The finding that matters most

Task assignment is **100% manual**. `admissionStoreProcessTask`
(AdmissionController.php:1016) does nothing more than diff the
`task_list_ids` array posted from a checklist against what already exists,
inserting the additions and soft-deleting the removals.

There is no rule engine, no default set, no template. Every applicant's task
list is an individual human decision, repeated roughly 3,400 times.

---

## 2. How many tasks are assigned to each applicant

3,409 of 3,554 applicants have at least one task.

| Tasks assigned | Applicants |
| --- | --- |
| 9 | 12 |
| 8 | 1,045 |
| 7 | 1,731 |
| 6 | 389 |
| 5 | 56 |
| 4 | 19 |
| 3 | 22 |
| 2 | 11 |
| 1 | 119 |

Seventy distinct task-set combinations exist, but that number overstates the
variety. Five combinations cover 73% of all applicants, and each is the same
spine with one or two slots swapped:

| Applicants | Task set |
| --- | --- |
| 1,162 | 1, 2, 3, 4, 6, 7, 8 |
| 620 | 1, 2, 3, 4, 6, 7, 8, 25 |
| 358 | 1, 2, 3, 4, 5, 7, 8 |
| 288 | 1, 2, 3, 4, 6, 7, 24, 25 |
| 140 | 1, 2, 3, 4, 6, 7, 25 |

The low-count sets (1 task, 2 tasks) are applications abandoned or rejected
before the set was finished, not a different kind of applicant.

**The current standard set, as used in 2026-Q3, is 8 tasks:**

1. Signed Application form
2. Proof of ID
3. Proof of Address
4. Proof of Residency
5. or 6. Course Entry Documentation (Academic *or* Mature Entry)
7. Academic Interview
24. English Proficiency Certificate
25. Signed Application form Audit

---

## 3. Which tasks require documents

**All 10 admission tasks are flagged `upload = Yes`.** In practice that flag is
honoured almost perfectly:

| ID | Task | Assigned | With documents |
| --- | --- | --- | --- |
| 1 | Signed Application form | 3,335 | 100% |
| 2 | Proof of ID | 3,246 | 100% |
| 3 | Proof of Address | 3,228 | 100% |
| 4 | Proof of Residency | 3,082 | 100% |
| 6 | Course Entry Documentation (Mature Entry) | 2,482 | 100% |
| 25 | Signed Application form Audit | 1,353 | 100% |
| 7 | Academic Interview | 3,291 | 99% |
| 8 | English Language Test | 2,548 | 96% |
| 5 | Course Entry Documentation (Academic Entry) | 576 | 94% |
| 24 | English Proficiency Certificate | 341 | 66% (new, still in flight) |

### Completion enforces nothing

`admissionCompletedTask` (AdmissionController.php:1186) sets the status to
`Completed` and writes a log entry. It does **not** check that a document is
attached. The 100% figures above are staff discipline, not a system
constraint. Any automation that assigns tasks should add this check, or the
inconsistency will scale with the volume.

### Tasks carrying a Pass / Fail outcome

Two tasks have an outcome in addition to a status, via `task_list_statuses`:

- **7 Academic Interview** - Pass 2,807 / Fail 444 / not set 40
- **8 English Language Test** - Pass 1,993 / Fail 213 / Not Required 241 / not set 101

The third option is *"Not Required (Kindly include a note explaining the
reason.)"* - this is how exemptions are recorded.

**Gap:** task 24, which has replaced task 8, has **no** Pass/Fail outcome
configured. Worth fixing before building rules on top of it.

---

## 4. Why task sets differ

There are only three reasons a task set varies, and only one of them is a
property of the applicant.

### (a) Task 5 vs 6 - the one genuine applicant-driven rule

The Academic / Mature Entry choice tracks whether the applicant has
qualifications recorded in `applicant_qualifications`:

| Group | Applicants | Got task 5 (Academic) | Got task 6 (Mature) | Rule accuracy |
| --- | --- | --- | --- | --- |
| Has qualification | 617 | 571 | 47 | **92.5%** |
| No qualification | 2,439 | 5 | 2,435 | **99.8%** |

Age correlates strongly (Mature entrants average 36.9 years, 99.2% are 21+;
Academic entrants average 28.4) but age is a *symptom*. The qualification
record is the actual signal, and it is far cleaner.

This rule is automatable, but needs a staff override: roughly 1 in 13
applicants holding a qualification is still routed to Mature Entry by a human.

### (b) Task 8 vs 24 - not applicant-driven, a changeover

Task 24 (English Proficiency Certificate) has replaced task 8 (English
Language Test). This is a change in college practice over time, not a
difference between applicants:

| Quarter | Applicants | Task 8 | Task 24 | Task 25 |
| --- | --- | --- | --- | --- |
| 2024-Q2 | 115 | 87 | 0 | 0 |
| 2024-Q3 | 307 | 260 | 0 | 0 |
| 2024-Q4 | 338 | 328 | 0 | 1 |
| 2025-Q1 | 283 | 245 | 0 | 0 |
| 2025-Q2 | 300 | 272 | 0 | 0 |
| 2025-Q3 | 247 | 196 | 0 | 0 |
| 2025-Q4 | 403 | 365 | 0 | 0 |
| 2026-Q1 | 649 | 477 | 4 | 602 |
| 2026-Q2 | 434 | 316 | 36 | 426 |
| 2026-Q3 | 328 | **2** | **301** | 324 |

The migration is nearly complete but not clean: 14 applicants currently hold
both tasks.

### (c) Task 25 - also a changeover

Signed Application form Audit was introduced in 2026-Q1 and is now effectively
universal (324 of 328 applicants in 2026-Q3).

### What does NOT drive variation

Two plausible-sounding theories are both contradicted by the data:

- **Nationality.** The English task is assigned at 71-96% across every
  nationality, including United Kingdom applicants (71%) and Romania (86%,
  n=2,193). It is near-universal, and exemptions are recorded through the
  "Not Required" outcome rather than by omitting the task.
- **Fee eligibility.** Proof of Residency is assigned to 94% of applicants
  whether they are eligible for home fees or not.

**In short:** "tasks change depending on the application" amounts to one real
rule (qualifications) plus two historical migrations.

---

## 5. How often staff change the task set after assigning it

**1,088 applicants (32%) had at least one task removed after it was assigned.**

Tasks removed (soft-deleted):

| ID | Task | Removed |
| --- | --- | --- |
| 24 | English Proficiency Certificate | 428 |
| 8 | English Language Test | 391 |
| 5 | Course Entry Documentation (Academic Entry) | 266 |
| 4 | Proof of Residency | 151 |
| 6 | Course Entry Documentation (Mature Entry) | 89 |
| 3 | Proof of Address | 67 |
| 7 | Academic Interview | 55 |
| 2 | Proof of ID | 52 |
| 1 | Signed Application form | 16 |
| 25 | Signed Application form Audit | 5 |

Tasks added later (more than 5 minutes after the applicant's first task):

| ID | Task | Late additions |
| --- | --- | --- |
| 8 | English Language Test | 335 |
| 24 | English Proficiency Certificate | 55 |
| 6 | Course Entry Documentation (Mature Entry) | 51 |
| 25 | Signed Application form Audit | 39 |
| 7 | Academic Interview | 37 |
| 5 | Course Entry Documentation (Academic Entry) | 28 |

The churn sits almost entirely on the two swap pairs, 8 <-> 24 and 5 <-> 6.
This is staff correcting the route once they have seen the file. A rules
engine applied at submission would remove most of it.

---

## 6. The decision gate

The Academic Interview outcome is, in practice, the admission decision:

| Task 7 outcome | Final applicant status | Count |
| --- | --- | --- |
| Fail | Rejected | 413 |
| Fail | In Progress | 24 |
| Fail | Offer Accepted | 6 |
| Pass | Offer Accepted | 2,218 |
| Pass | Rejected | 430 |
| Pass | In Progress | 133 |

A Fail leads to rejection in 93% of cases. This is where the real judgement
lives and it should stay with a human.

---

## 7. What already happens on conversion to student

`app/Jobs/ProcessStudentTask.php` runs when an applicant becomes a student:

1. Every applicant task is copied into `student_tasks`, preserving its status
   and its Pass/Fail outcome.
2. All 9 "New Student" tasks (process 2) are then created as Pending,
   **unconditionally**, driven by the `auto_feed = Yes` flag on the process.

So the student side is already blanket-automated. Admission is the only stage
still done entirely by hand.

**Caveat for reuse:** that job looks up the auto-feed process with
`where('auto_feed','Yes')->where('phase','Live')` and takes only the first
match (ProcessStudentTask.php:70). The existing mechanism therefore cannot be
reused for the Applicant phase without modification, and it supports only one
auto-fed process at a time.

---

## 8. The biggest automation lever: documents already exist

`applicant_task_documents` is only a join table. It links a task to an
`applicant_document` row that **already exists** - the document is not uploaded
against the task, it is attached to it afterwards.

And the document carries a label in `display_file_name` that is, in most cases,
literally the task name:

| Document label | Linked to task | Count |
| --- | --- | --- |
| Signed Application form | Signed Application form | 3,405 |
| Proof of Address | Proof of Address | 2,977 |
| Proof of ID | Proof of ID | 2,970 |
| Proof of Residency | Proof of Residency | 2,771 |
| Course Entry Documentation (Mature Entry) | Course Entry Documentation (Mature Entry) | 2,287 |
| Signed Application form Audit | Signed Application form Audit | 1,366 |
| Proof of address - Proof of Address | Proof of Address | 352 |
| College Interview Outcome | Academic Interview | 326 |

Across all 24,300 task-document links:

- **68.2%** have a label that is *exactly* the task name
- **87.1%** have a label that *contains* the task name
- 0 have no label at all

That is a strong enough signal to automate the matching.

---

## 9. Recommended automation, in order of risk

**1. Auto-assign the universal spine on submission.**
Tasks 1, 2, 3, 4, 7 and 25. No judgement is involved - every applicant
receives these today. Lowest risk, largest single saving.

**2. Auto-select task 5 or 6 from the qualification record.**
92.5% / 99.8% accurate against current staff behaviour. Must be overridable.

**3. Auto-assign task 24 as the current English task.**
And finish retiring task 8 - 14 applicants presently hold both. Add the
Pass/Fail/Not Required outcome set to task 24 first.

**4. Auto-link documents to tasks by label, and move the task to
*In Progress* - not *Completed*.**
Roughly 87% of the clerical linking disappears while a human still confirms.

**5. Keep with humans:** the interview Pass/Fail, the "Not Required"
exemptions, and the final accept/reject decision.

### What the saving looks like

3,175 applicants have completed their full task set, taking on average
**15.8 days from submission**, handled by **80 different staff** across
49,291 task-log entries.

Average days from task creation to completion:

| ID | Task | Completed | Avg days | Max |
| --- | --- | --- | --- | --- |
| 25 | Signed Application form Audit | 1,311 | 1.3 | 126 |
| 7 | Academic Interview | 3,257 | 1.5 | 548 |
| 4 | Proof of Residency | 3,034 | 1.7 | 555 |
| 2 | Proof of ID | 3,198 | 1.8 | 555 |
| 6 | Course Entry Documentation (Mature Entry) | 2,437 | 1.9 | 352 |
| 3 | Proof of Address | 3,178 | 2.2 | 555 |
| 1 | Signed Application form | 3,285 | 4.4 | 555 |
| 8 | English Language Test | 2,446 | 10.3 | 549 |
| 24 | English Proficiency Certificate | 224 | 15.8 | 64 |
| 5 | Course Entry Documentation (Academic Entry) | 542 | 24.6 | 194 |

The slow tasks are the ones that need a document chased from the applicant
(5, 24, 8). Automation will not speed those up. The clerical tasks already
turn around in 1.3-2.2 days - steps 1 to 4 remove that work altogether and
stop the 32% churn.

---

## 10. Risks to design around

1. **No document validation on completion.** A task can be marked Completed
   with nothing attached. Automating assignment without adding this check
   will scale the inconsistency.
2. **Task 24 has no Pass/Fail outcome** although the task it replaced did.
   Fix before building rules on it.
3. **The 5/6 rule is 92.5% accurate, not 100%.** Staff override is mandatory,
   and the overrides should be logged so the rule can be refined.
4. **The 8 -> 24 migration is unfinished** - 14 applicants hold both tasks.
5. **`auto_feed` cannot be reused as-is** for the Applicant phase; it is
   hardcoded to `phase = 'Live'` and to a single process.
6. **Two applicants hold both task 5 and task 6**, which should be mutually
   exclusive. Worth a constraint.

---

## Appendix: the 10 admission tasks

| ID | Name | Upload | Interview | Outcome |
| --- | --- | --- | --- | --- |
| 1 | Signed Application form | Yes | No | No |
| 2 | Proof of ID | Yes | No | No |
| 3 | Proof of Address | Yes | No | No |
| 4 | Proof of Residency | Yes | No | No |
| 5 | Course Entry Documentation (Academic Entry) | Yes | No | No |
| 6 | Course Entry Documentation (Mature Entry) | Yes | No | No |
| 7 | Academic Interview | Yes | **Yes** | **Yes** |
| 8 | English Language Test | Yes | No | **Yes** |
| 24 | English Proficiency Certificate | Yes | No | No |
| 25 | Signed Application form Audit | Yes | No | No |

### Key source files

- `app/Http/Controllers/AdmissionController.php` - assignment (:1016),
  completion (:1186), process screen (:967)
- `app/Jobs/ProcessStudentTask.php` - conversion to student, auto_feed
- Tables: `process_lists`, `task_lists`, `applicant_tasks`,
  `applicant_task_documents`, `applicant_documents`, `applicant_task_logs`,
  `task_statuses`, `task_list_statuses`
