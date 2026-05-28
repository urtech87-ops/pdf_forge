# PDFForge — Project Rules

## Project Overview

A free online PDF tools website called **PDFForge**. Same homepage structure as tinywow.com but with our own dark design and branding. Do NOT copy TinyWow's text, logo, colors, or images.

---

## Design Spec

Match this closely. Expect to refine after comparing with approved screenshots.

| Token | Value |
|---|---|
| Background | `#0c0e14` (near-black) |
| Card/surface | `#12151f` |
| Card border | `1px solid #20263a` |
| Purple accent | `#7c5cff` |
| Heading text | `#f2f4fb` (white) |
| Body/muted text | `#9aa3b8` |
| Card border-radius | `12px` |
| Button border-radius | `8px` |

### Header
- Left: purple rounded logo tile + "PDFForge" wordmark
- Desktop: horizontal nav with category dropdowns, dark/light toggle, search box, purple "Sign in" button
- Mobile: logo + hamburger icon only

### Hero (centered)
1. Pill badge — "No sign-up needed" (purple-tinted bg, light purple text)
2. Two-line headline — line 1 white, line 2 in purple accent
3. Gray subtitle
4. Search input
5. Mobile only: full-width purple "Search tools" button below the input

### Category Cards
- Row of colored cards
- Each card: tool-count badge, title, subtitle, featured-tool link
- Desktop: multi-column; mobile: stacked

### Stats
- Large purple numbers, gray label beneath
- Desktop: four across; mobile: two per row

### Popular PDF Tools Section
- White heading
- Category filter tabs
- Tool cards
  - Mobile: horizontal row — small colored icon left, tool name white, one-line gray description beneath; cards stacked vertically
  - Desktop: multi-column grid
- "All tools" button

### Footer
- Left column: brand blurb
- "Navigate" column: page links
- "Tools" column: tool links
- Copyright bar at the bottom

Build desktop **and** mobile layouts deliberately; test both.

---

## Tech Stack

- **WordPress** custom theme — pages, design, blog, SEO
- **Browser-side PDF processing** — pdf-lib, PDF.js (no server cost; files never leave the device)
- Never edit WordPress core files

---

## Single Source of Truth (Data Model)

All dynamic homepage content pulls from WordPress admin so a single edit updates the whole site.

| Element | Source |
|---|---|
| Header dropdowns | "Tool" CPT + "Category" taxonomy |
| Category cards | "Category" taxonomy |
| Popular-tools grid | "Tool" CPT |
| Filter tabs | "Category" taxonomy |
| Footer tool links | "Tool" CPT |
| Stats + hero text | Custom admin options/fields |

### "Tool" Custom Post Type fields
`name`, `slug`, `category`, `description`, `icon`, `tool-count`

### "Category" Taxonomy
Attached to the "Tool" CPT.

---

## Starter Tools

Build these one at a time — **never all at once**.

1. Merge PDF
2. Split PDF
3. Compress PDF

**Order of operations:**
1. Build the homepage layout with sample data
2. Wire each section to admin, one section at a time
3. Build **Merge PDF** fully working before starting any other tool

---

## Workflow Rules

- Work in small steps: one thing, make it work, then stop
- Always work on a **feature branch** — never commit directly to `main`
- Commit after every working step so each step is independently revertible
- Merge into `main` only when a feature fully works **and** I confirm
- Pre-commit hook must block direct commits to `main`
- Use a WordPress-appropriate `.gitignore`
