# Access Privileges — Live Deployment Runbook

The deploy is **inert**. `.env` is not in git, so live starts on `PRIVILEGE_SOURCE=legacy`
with enforcement off: nobody's access changes until you deliberately flip it in step 4.

Verified before release: 189 users, **0 privilege changes** on deploy day.

---

## 0. Back up the live database — FIRST

```bash
mysqldump -h <host> -u <user> -p --single-transaction --no-tablespaces \
  --routines --triggers <live_db> | gzip > privileges-cutover-$(date +%F).sql.gz

# prove it restores before going further
gzip -t privileges-cutover-$(date +%F).sql.gz && echo "backup OK"
```

## 1. Deploy the code

```bash
git pull                      # or merge your branch
php artisan down              # optional

php artisan migrate --force   # creates department_templates + employee_permissions
npm ci && npm run build       # public/build is gitignored — live MUST build

php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
php artisan queue:restart     # only if queue workers run

php artisan up
```

No new composer/npm packages were added.

**At this point nothing has changed for any user.** The app still reads
`user_privileges`. The only new restriction is `/student` + `/student/list`, now
gated on the `live` privilege — measured: **0 users lose access** (nobody holds
`student_manage` without `live`).

## 2. Populate the new permission system

```bash
php artisan privileges:copy-legacy --all --dry-run   # review, writes nothing
php artisan privileges:copy-legacy --all             # asks to confirm
```

Expect ~3,387 rows for ~189 users. A handful of orphan user ids are skipped
(no such user — they cannot log in).

## 3. Prove parity — this is the gate

```bash
php artisan privileges:verify
```

**Must print `PASS`.** If it prints `FAIL`, do not continue: it means at least one
real user would gain or lose access. Read its output — it distinguishes
"not yet synced" from "a deliberate edit awaiting cutover", which need opposite fixes.

## 4. Cut over

Add to the **live** `.env`:

```env
PRIVILEGE_SOURCE=new
PRIVILEGE_ENFORCE=false
PRIVILEGE_REMOTE_ENFORCE=false
PRIVILEGE_IP_MODE=login
```

```bash
php artisan config:clear      # or: php artisan config:cache
```

Now `priv()` reads `employee_permissions`. The old privilege screen retires itself
automatically (redirects; its save endpoint returns 410) so HR cannot make edits
that silently do nothing.

### Smoke test
- Log in as a normal staff member — menus and pages look unchanged.
- Open an employee's **Privilege** tab — permissions shown match what they had.
- Toggle one permission, save, confirm it takes effect.
- Accounts pages still load for a user with an accounts role.

## 5. ROLLBACK (instant, no deploy)

```env
PRIVILEGE_SOURCE=legacy
```
```bash
php artisan config:clear
```

`user_privileges` is never modified by the cutover, so this is a clean revert.
(Exception: **Revoke All** deliberately clears both tables, so a revoked person
stays revoked through a rollback. That is intentional — a leaver must not regain
access via a config change.)

---

## 6. Later — turn on URL enforcement (evidence first)

Manual URL browsing is **not blocked yet**. The guard is recording what it *would*
block. After a few days of real traffic:

```bash
php artisan privileges:audit-report
```

It lists: unmapped routes staff actually use, who would be denied, and who would
lose the portal entirely. Map the routes in `config/privileges.php` (`routes`),
re-check the blast radius, then:

```env
PRIVILEGE_ENFORCE=true
```

Do **not** enable this before the audit is clean: `deny_unmapped` is `true`, so every
unmapped route would 403.

To lock down one screen at a time instead, add its route name to `enforce_routes`
in `config/privileges.php` — that enforces just that route, leaving the rest log-only.
(`student` and `student.list` already use this.)

### Strict IP checking
`PRIVILEGE_IP_MODE=both` requires the login IP **and** the current request IP to be
college IPs. It can remove the portal entirely from anyone whose IP moved
(mobile, VPN, proxy). Review `privileges:audit-report` first — it reports exactly who.

---

## Commands reference

| Command | What it does |
|---|---|
| `privileges:copy-legacy --all --dry-run` | Preview the copy. Writes nothing. |
| `privileges:copy-legacy --all` | Copy `user_privileges` → `employee_permissions`. |
| `privileges:copy-legacy --user=<id>` | Re-sync one user. |
| `privileges:verify` | Compare both sources. Must PASS before cutover. |
| `privileges:audit-report` | What the guard would block, from real traffic. |

**Danger:** `privileges:copy-legacy --all` overwrites the new system *from* legacy.
After cutover it would destroy edits HR made on the new screen. Use `--user=<id>`.

## Break-glass

Users **1**, **7** and employee **1** bypass the privilege system and the remote-access
check entirely. A bad rule can never lock out the people who must fix it.
Configurable via `PRIVILEGE_BYPASS_IDS` / `PRIVILEGE_BYPASS_EMPLOYEE_IDS`.
