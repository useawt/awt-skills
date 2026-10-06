# Building an AWT page

## Page structure

A page is a column of **sections**. Each top-level block is an `awt/section`, and
everything else sits inside one.

```html
<!-- wp:awt/section {"ariaLabel":"Features"} -->
<!-- wp:heading -->
<h2 class="wp-block-heading">What you get</h2>
<!-- /wp:heading -->

<!-- wp:awt/feature-grid {"columns":3} -->
<!-- wp:awt/tile -->
<!-- wp:awt/icon {"iconName":"checkmark","size":"24"} /-->
<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">Accessible</h3>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>Every block meets WCAG 2.2 AA.</p>
<!-- /wp:paragraph -->
<!-- /wp:awt/tile -->
<!-- /wp:awt/feature-grid -->
<!-- /wp:awt/section -->
```

Layout habits that make a page look like AWT:

- **Rhythm.** Alternate plain sections with `"backgroundColor":"layer-01"` ones.
  When two sections with backgrounds touch, add `"noGapBelow":true` to the first.
- **One dark band at most**, for the main call to action:
  `{"themeScope":"dark","align":"full","noGapBelow":true}`. Every color inside
  switches to the dark palette by itself; never set colors by hand.
- **Width.** `maxWidth` `content` (the default) for most sections, `narrow` for
  long text, `wide` for big grids and tables.
- **Ordinary pages** (default template, no hero) open with a short section: one
  or two sentences in a `body-02` paragraph, no heading, since the page title is
  right above it.
- **Landing pages** use the "Page without title" template (`template=page-no-title`)
  and open with `awt/hero` holding the heading 1. Other pages keep the default
  template, which prints the page title as heading 1, so their first heading is a
  heading 2. A hero is always `{"version":2}` with this inside:

  ```html
  <!-- wp:awt/hero {"version":2} -->
  <!-- wp:paragraph {"className":"awt-hero__eyebrow"} -->
  <p class="awt-hero__eyebrow">Short label above the heading (optional)</p>
  <!-- /wp:paragraph -->

  <!-- wp:heading {"level":1,"className":"awt-hero__heading"} -->
  <h1 class="wp-block-heading awt-hero__heading">The page's main promise</h1>
  <!-- /wp:heading -->

  <!-- wp:paragraph {"className":"awt-hero__description"} -->
  <p class="awt-hero__description">One or two sentences that support it.</p>
  <!-- /wp:paragraph -->

  <!-- wp:awt/inline-set -->
  <!-- wp:awt/button {"text":"Book a call","href":"/contact/","size":"lg"} /-->
  <!-- /wp:awt/inline-set -->
  <!-- /wp:awt/hero -->
  ```
- **Lists of features or links** go in `awt/feature-grid` with `awt/tile`s. A tile
  that links somewhere is `{"variant":"clickable","href":"/path/"}` and must not
  contain other links or buttons.
- **Buttons** are `awt/button`. Group them in `awt/inline-set`. One `primary` per
  screen; the rest `secondary`, `tertiary` or `ghost`.
- **Numbers** go in `awt/stat`: four in a row is the `awt/stats-bar` pattern; for
  two or three, put one `awt/stat` in each column of a `core/columns`.
  **Comparisons** go in `awt/data-table`, **questions** in an `awt/accordion` of
  `awt/faq-item`s, **alternatives** in `awt/tabs`.
- **Short labels** (categories, statuses) are `awt/tag`s in an `awt/inline-set`.
  Tags cut off long text; use a list for anything longer than three or four words.
- **Text beside an image:** `core/columns` with widths that add up to 100%, and
  `"verticalAlignment":"center"` on the text column when the image is taller.
- **Spacing and type sizes** come from the presets. Never set pixel sizes or colors.

Start from a pattern whenever one fits (`awt-catalog.php` lists them):
`awt/page-home`, `awt/page-about`, `awt/page-pricing`, `awt/page-contact`,
`awt/page-faq`, `awt/hero`, `awt/feature-grid`, `awt/cta-section`, `awt/stats-bar`,
`awt/testimonial` and more. Copy its markup, then change the words. Patterns are
starting points, so finish them: give every button an `href` (or `"type":"submit"`
inside a form), and make an icon decorative (leave out `decorative` and `label`)
when the heading next to it already says the same thing.

## Writing block markup

**AWT blocks** have no HTML of their own in the post. Write the comment only, with
the attributes that differ from the default:

```html
<!-- wp:awt/button {"text":"Book a demo","href":"/demo/","kind":"primary"} /-->
<!-- wp:awt/tag {"text":"New","type":"green"} /-->
```

**Attribute JSON** must be escaped the way WordPress writes it, or the editor and
the page disagree:

| Character | Write |
|---|---|
| `"` inside a value | `\u0022` |
| `<` and `>` | `\u003c` and `\u003e` |
| `&` | `\u0026` |
| `--` | `\u002d\u002d` |

A raw `--` usually still works, but the sequence `-->` inside a value ends the
block comment and breaks the block, and the editor rewrites every `--` the next
time it saves. Escape them all. Icon names often contain `--`: `"iconName":"arrow\u002d\u002dright"`.

In Python, `json.dumps(attrs, ensure_ascii=False, separators=(',', ':'))` followed by
those replacements gives exactly WordPress's output.

**Core blocks** must have exactly this HTML, or the editor reports them as invalid:

```html
<!-- wp:heading -->
<h2 class="wp-block-heading">Level 2 heading</h2>
<!-- /wp:heading -->

<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">Level 3 heading</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Text with a <a href="/pricing/">link</a> and <strong>bold</strong>.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"fontSize":"body-02"} -->
<p class="has-body-02-font-size">A larger lead paragraph, for an intro.</p>
<!-- /wp:paragraph -->

<!-- wp:image {"id":123,"sizeSlug":"large","linkDestination":"none"} -->
<figure class="wp-block-image size-large"><img src="https://example.com/wp-content/uploads/2026/10/photo.jpg" alt="Two testers reviewing a report" class="wp-image-123"/></figure>
<!-- /wp:image -->

<!-- wp:columns -->
<div class="wp-block-columns"><!-- wp:column {"width":"33.33%"} -->
<div class="wp-block-column" style="flex-basis:33.33%">...</div>
<!-- /wp:column -->

<!-- wp:column {"verticalAlignment":"center","width":"66.66%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:66.66%">...</div>
<!-- /wp:column --></div>
<!-- /wp:columns -->
```

For lists, prefer the AWT list: each item is
`<!-- wp:awt/list-item {"content":"Item text"} /-->` inside
`<!-- wp:awt/list --> ... <!-- /wp:awt/list -->`.

Keep the file on many short lines. One enormous line can make `wp eval-file`
silently do nothing.

## Images

Upload an image, then use the ID it gets and the URL of its `large` size (the
size the image block below asks for):

```bash
scp photo.jpg SITE:~/awt-skill/
ssh SITE 'cd WP && wp media import ~/awt-skill/photo.jpg --title="Testers reviewing a report" --alt="Two testers reviewing a report" --porcelain'
ssh SITE 'cd WP && wp eval "echo wp_get_attachment_image_url( 123, \"large\" );"'
```

Reuse what is already in the media library before uploading:
`wp post list --post_type=attachment --post_mime_type=image --fields=ID,post_title`
and `wp post meta get ID _wp_attachment_image_alt` for its alt text. Do not trust a
title or file name to say what an image shows: download it and look at it before
you choose it or write its alt text. Never use an image that does not fit just
to fill a space; leave the space out and tell the owner.

## Accessibility (AWT is an accessibility product; this is not optional)

- **Headings in order.** One heading 1 per page. Never skip a level going down
  (2 then 4). Headings describe the section; never use one just for big text, use
  a `fontSize` preset on a paragraph instead.
- **Alt text** on every informative image, saying what it shows that matters.
  `alt=""` only for decoration. Never "image of".
- **Links and buttons say where they go or what they do.** No "click here" or bare
  "Read more"; if a card has "Read more", the card's heading must make it unique.
- **Icons**: decorative unless they carry meaning nothing else carries; then give
  them `"decorative":false` and a `label`.
- **Color never carries meaning alone.** A status tag also says the status.
- **Video** has controls, and captions when it has speech. Nothing autoplays with
  sound.
- **Tables** have a caption and header cells (`awt/data-table` does this for you).
- **Forms**: every field has a visible label. Use AWT's form blocks.
- Run `awt-check.php` and read every WARN; it catches most of the above.

## Plain language

Write for the site's visitors, not for developers.

- **Never invent facts.** Quotes, names, prices, numbers, dates, client logos and
  claims come from the owner or the current site. When you have none, write a
  clearly marked placeholder such as `[Customer quote]` or `[Price]`, and list
  every placeholder for the owner when you hand over the draft.
- Write prices, dates and numbers the way the site already does (currency,
  language, format).
- Lead with what the reader gets. Short sentences, active voice, "you".
- Cut filler: "simply", "in order to", "please note that", "leverage", "empower".
- Keep real terms the audience uses (product names, standards like WCAG), and
  explain an unfamiliar one in a few words the first time.
- One idea per paragraph. Turn lists hidden in sentences into real lists.
- On a redesign, show the owner every wording change, old next to new, before
  publishing. Never add a claim the old page did not make.
