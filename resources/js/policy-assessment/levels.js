/*
 * Policy Assessments: levels and badges, for pages that draw them in JS.
 *
 * Imported by the page scripts (not a Vite input of its own):
 *     import { LEVELS, levelLabel, levelPill, badgeSvg } from "./policy-assessment/levels";
 *
 * levelPill() and badgeSvg() return HTML strings with the same markup as the
 * Blade partials pages/hr/policy-assessment/partials/level-pill.blade.php and
 * level-badge.blade.php, so a badge looks the same whether the server or the
 * browser drew it. Styles: resources/css/policy-assessment/levels.css.
 * Keep the colours in step with App\Support\PolicyLevel::COLOURS.
 *
 * Anything passed in is escaped; an unknown level is drawn as Beginner (rows
 * that predate levels are Beginner), while levelLabel() returns "" for it.
 */

/** The level keys, easiest first — same as PolicyLevel::all(). */
export const LEVELS = Object.freeze(["beginner", "intermediate", "expert"]);

/** key => label, same as PolicyLevel::labels(). */
export const LEVEL_LABELS = Object.freeze({
    beginner: "Beginner",
    intermediate: "Intermediate",
    expert: "Expert",
});

/** key => badge name (PolicyLevel::badgeName()). */
export const BADGE_NAMES = Object.freeze({
    beginner: "Bronze",
    intermediate: "Silver",
    expert: "Gold",
});

/** key => { main, soft, ink } — medal colour, pale tint, dark shade for text (PolicyLevel::colours()). */
export const LEVEL_COLOURS = Object.freeze({
    beginner: Object.freeze({ main: "#b0703a", soft: "#f6e6d8", ink: "#7a4a22" }),
    intermediate: Object.freeze({ main: "#7b8794", soft: "#eceff3", ink: "#4a5563" }),
    expert: Object.freeze({ main: "#c49a12", soft: "#fbf2d3", ink: "#7a5d00" }),
});

/* Star polygons in the medal's 48 x 56 viewBox: 1, 2 or 3 stars. Keep in step
   with $paBadgeStars in level-badge.blade.php. */
const STARS = {
    beginner: ["24,15 25.69,19.07 30.09,19.42 26.74,22.29 27.76,26.58 24,24.28 20.24,26.58 21.26,22.29 17.91,19.42 22.31,19.07"],
    intermediate: [
        "19.1,16.9 20.34,19.89 23.57,20.15 21.11,22.25 21.86,25.4 19.1,23.72 16.34,25.4 17.09,22.25 14.63,20.15 17.86,19.89",
        "28.9,16.9 30.14,19.89 33.37,20.15 30.91,22.25 31.66,25.4 28.9,23.72 26.14,25.4 26.89,22.25 24.43,20.15 27.66,19.89",
    ],
    expert: [
        "16.5,18.7 17.58,21.31 20.4,21.53 18.25,23.37 18.91,26.12 16.5,24.65 14.09,26.12 14.75,23.37 12.6,21.53 15.42,21.31",
        "24,15.3 25.08,17.91 27.9,18.13 25.75,19.97 26.41,22.72 24,21.25 21.59,22.72 22.25,19.97 20.1,18.13 22.92,17.91",
        "31.5,18.7 32.58,21.31 35.4,21.53 33.25,23.37 33.91,26.12 31.5,24.65 29.09,26.12 29.75,23.37 27.6,21.53 30.42,21.31",
    ],
};

const ROSETTE =
    "24,0.5 27.16,3.08 31.01,1.74 33.1,5.24 37.18,5.3 37.94,9.3 41.75,10.75 41.1,14.78 44.19,17.44 42.2,21 44.19,24.56 41.1,27.22 41.75,31.25 37.94,32.7 37.18,36.7 33.1,36.76 31.01,40.26 27.16,38.92 24,41.5 20.84,38.92 16.99,40.26 14.9,36.76 10.82,36.7 10.06,32.7 6.25,31.25 6.9,27.22 3.81,24.56 5.8,21 3.81,17.44 6.9,14.78 6.25,10.75 10.06,9.3 10.82,5.3 14.9,5.24 16.99,1.74 20.84,3.08";

/** Pixel width/height per size. */
const SIZES = { sm: [28, 33], md: [48, 56], lg: [88, 103] };

/** Same escaping as Blade's {{ }} (htmlspecialchars with ENT_QUOTES). */
export const escapeHtml = (value) =>
    String(value === null || value === undefined ? "" : value).replace(/[&<>"']/g, (ch) => ({
        "&": "&amp;",
        "<": "&lt;",
        ">": "&gt;",
        '"': "&quot;",
        "'": "&#039;",
    })[ch]);

/** True for "beginner", "intermediate" or "expert". */
export const isLevel = (level) => typeof level === "string" && Object.prototype.hasOwnProperty.call(LEVEL_LABELS, level);

/** The level to draw: an unknown one reads as Beginner. */
const drawLevel = (level) => (isLevel(level) ? level : "beginner");

const clean = (value) => {
    if (value === null || value === undefined) {
        return null;
    }
    const text = String(value).trim();
    return text === "" ? null : text;
};

/** "Beginner" / "Intermediate" / "Expert"; "" for an unknown level. */
export const levelLabel = (level) => (isLevel(level) ? LEVEL_LABELS[level] : "");

/** "Bronze" / "Silver" / "Gold"; "" for an unknown level. */
export const badgeName = (level) => (isLevel(level) ? BADGE_NAMES[level] : "");

/** { main, soft, ink } for the level (Beginner's for an unknown level). */
export const levelColours = (level) => LEVEL_COLOURS[drawLevel(level)];

/**
 * The level pill. `text` (optional) replaces the label, e.g. "B 18/20".
 *   <span class="pa-level-pill pa-level-pill--beginner"><span class="pa-level-pill__dot" aria-hidden="true"></span>Beginner</span>
 */
export const levelPill = (level, text = null) => {
    const key = drawLevel(level);
    const label = clean(text) ?? LEVEL_LABELS[key];

    return `<span class="pa-level-pill pa-level-pill--${key}"><span class="pa-level-pill__dot" aria-hidden="true"></span>${escapeHtml(label)}</span>`;
};

/**
 * The badge medallion. size: "sm" (28px) | "md" (48px) | "lg" (88px).
 * options (all optional):
 *   title   policy title — adds a caption and names the badge "Intermediate badge — <title>"
 *   date    caption line under the title, shown as given (e.g. "12 Oct 2026")
 *   revoked true draws it greyed out
 * With neither title nor date it is the medal alone (span.pa-badge-medal); with
 * either it is wrapped in span.pa-badge-card with a caption.
 */
export const badgeSvg = (level, size = "md", options = {}) => {
    const key = drawLevel(level);
    const sizeKey = Object.prototype.hasOwnProperty.call(SIZES, size) ? size : "md";
    const opts = options && typeof options === "object" ? options : {};
    const title = clean(opts.title);
    const date = clean(opts.date);
    const revoked = opts.revoked ? " is-revoked" : "";
    const label = LEVEL_LABELS[key];
    const aria = escapeHtml(`${label} badge${title !== null ? ` — ${title}` : ""}`);
    const c = LEVEL_COLOURS[key];
    const [width, height] = SIZES[sizeKey];

    const stars = STARS[key].map((points) => `<polygon points="${points}" fill="${c.ink}"/>`).join("");
    const medal =
        `<span class="pa-badge-medal pa-badge-medal--${sizeKey} pa-badge-medal--${key}${revoked}" role="img" aria-label="${aria}" title="${aria}">` +
        `<svg class="pa-badge-medal__svg" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 56" width="${width}" height="${height}" aria-hidden="true" focusable="false">` +
        `<polygon points="15,33 10,53 14.2,50.2 18.5,55.5 23,36" fill="${c.ink}"/>` +
        `<polygon points="33,33 38,53 33.8,50.2 29.5,55.5 25,36" fill="${c.ink}"/>` +
        `<polygon points="${ROSETTE}" fill="${c.main}"/>` +
        `<path d="M8.12 15.22 A16.9 16.9 0 0 1 28.37 4.68" fill="none" stroke="#ffffff" stroke-opacity="0.5" stroke-width="1.6" stroke-linecap="round"/>` +
        `<circle cx="24" cy="21" r="15" fill="${c.soft}" stroke="${c.ink}" stroke-opacity="0.3" stroke-width="1"/>` +
        `<circle cx="24" cy="21" r="12.6" fill="none" stroke="${c.main}" stroke-opacity="0.6" stroke-width="0.8"/>` +
        stars +
        `</svg>` +
        `</span>`;

    if (title === null && date === null) {
        return medal;
    }

    return (
        `<span class="pa-badge-card pa-badge-card--${sizeKey} pa-badge-card--${key}${revoked}">` +
        medal +
        `<span class="pa-badge-card__caption">` +
        `<span class="pa-badge-card__level">${escapeHtml(label)} badge</span>` +
        (title !== null ? `<span class="pa-badge-card__title">${escapeHtml(title)}</span>` : "") +
        (date !== null ? `<span class="pa-badge-card__date">${escapeHtml(date)}</span>` : "") +
        `</span>` +
        `</span>`
    );
};
