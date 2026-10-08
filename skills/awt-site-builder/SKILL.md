---
name: awt-site-builder
description: Build and edit pages on a WordPress site that runs AWT, the accessibility-first block theme and blocks plugin built on IBM's Carbon Design System, and install or update AWT itself. Works over SSH with WP-CLI. Use when the user asks to install or update AWT on a WordPress site, to create, redesign, restyle or update pages or sections on their AWT site, use AWT blocks or patterns, add images, or change AWT Settings. Every page stays accessible, nothing is published or installed without the owner's yes (then it publishes or schedules, and confirms the live page), and the owner's own edits are never overwritten.
license: GPL-3.0-or-later
compatibility: Needs a shell that can reach the site over SSH (for example Claude Code), and WP-CLI on the server. The site needs WordPress; the skill can install the AWT theme and the AWT Blocks plugin.
metadata:
  author: useawt
  homepage: https://useawt.com
  version: "1.2.0"
---

# Building pages on an AWT site

AWT is a WordPress block theme (`awt`) and blocks plugin (`awt-blocks`) built on
Carbon, IBM's design system. Pages are made of AWT blocks (sections, heroes, tiles,
buttons, tabs, tables and more) mixed with core blocks (headings, paragraphs,
lists, images). You work on the owner's real site, so the rules in "Safety" come
before everything else.

## 1. Connect and look around

Ask the owner for the SSH host alias (or host and user) and the WordPress folder,
if you do not know them. [references/connecting.md](references/connecting.md) has the
setup steps for a site that has never been connected, and what to do without SSH.

Copy the scripts to the server at the start of every session, outside the public
web folder, so an older copy left there by an earlier session is replaced. Run
this from the skill's own folder. The folder is private to the SSH user, because
backups go there too:

```bash
ssh SITE 'mkdir -p ~/awt-skill && chmod 700 ~/awt-skill'
scp scripts/* SITE:~/awt-skill/
```

Every command after that runs from the WordPress folder (`WP` below):

```bash
ssh SITE 'cd WP && wp eval-file ~/awt-skill/awt-catalog.php'
```

The summary prints the site URL, the AWT version, every AWT block and pattern,
and the presets (font sizes, spacing, colors). **Check the URL is the site the
owner means before you change anything.** If AWT is not the active theme or the
plugin is missing, tell the owner and offer to install it (section 7).

Other catalog commands:

| Command | Gives you |
|---|---|
| `awt-catalog.php block awt/tile` | One block: attributes, defaults, where it may sit, a docs link |
| `awt-catalog.php pattern awt/page-home` | A pattern's complete, valid markup to start from |
| `awt-catalog.php icons chart` | Icon names for `awt/icon` and any `iconName` attribute |
| `awt-catalog.php settings` | The site's AWT Settings |
| `awt-catalog.php page 123` | A page's status, URL, content hash and last edit |

[references/blocks.md](references/blocks.md) lists every block's attributes and
**allowed values**. Read the entries for the blocks you use; do not guess values.

## 2. Plan the page with the owner

Before writing markup, agree in a few lines: the page's job, its sections in
order, and which existing page (if any) it replaces. Start from a pattern when one
fits; patterns are tested, accessible markup. For a redesign, read the current page
first (`wp post get ID --field=post_content`) and keep every claim, link and
image unless the owner says otherwise.

## 3. Write the markup

Write the page as a local `.html` file of block markup. Follow
[references/page-building.md](references/page-building.md): it has the page
structure AWT expects, the exact HTML shape of core blocks, how to add images, and
the accessibility and plain-language rules.

The short version:

- AWT blocks are rendered by the server. Write only their comment:
  `<!-- wp:awt/button {"text":"Get started","href":"/start/"} /-->`. Containers wrap
  inner blocks: `<!-- wp:awt/section --> ... <!-- /wp:awt/section -->`.
- Core blocks need their exact HTML, as shown in page-building.md.
- Attribute JSON is escaped the way WordPress writes it: `"` inside a value as
  `\u0022`, `<` `>` `&` as `\u003c` `\u003e` `\u0026`, and `--` as `\u002d\u002d`.
  Icon names often contain `--` (`arrow--right`), so write `"iconName":"arrow\u002d\u002dright"`.
- Headings go in order, every image has alt text, every link and button says
  where it goes or what it does.

## 4. Check, save as a draft, show the owner

```bash
scp pricing.html SITE:~/awt-skill/pricing.html
ssh SITE 'md5sum ~/awt-skill/pricing.html'       # compare with your local md5
ssh SITE 'cd WP && wp eval-file ~/awt-skill/awt-check.php file=$HOME/awt-skill/pricing.html template=page-no-title'
ssh SITE 'cd WP && wp eval-file ~/awt-skill/awt-save-page.php file=$HOME/awt-skill/pricing.html title="Pricing" template=page-no-title'
```

- Name the file after the page, so two jobs never share one file.
- Pass the same `template=` to the check and the save. Leave it out for pages with
  the default template (title shown as heading 1).
- `awt-check.php` finds unclosed blocks, unknown blocks and attributes, wrong
  nesting, broken escapes, skipped headings, missing alt text, unnamed links and
  buttons, and broken HTML. **Fix every ERROR. Read every WARN** and fix it unless
  it is deliberate.
- `awt-save-page.php` runs the same check, then saves a **draft** and prints its
  preview and edit links. It cannot publish a new page.
- Send the owner the preview link. A draft is only visible to someone logged in,
  and you must not log in yourself, so the owner does the visual review. If you
  have a browser where the owner is already logged in, look at the page in light
  and dark mode and at phone width first, and open it in the editor: a block the
  editor cannot read shows a warning there.

## 5. Publish only when the owner says so

The owner approves one specific version: they look at the draft through its
preview link and say to publish it. Words you showed them in chat are not the
page. When you hand over a draft, note its hash (the save prints it; later,
`awt-catalog.php page ID`; `wp post get` adds a newline, so its md5 differs). Publishing with that hash means exactly what they reviewed goes live:
if they or anyone else changed the page since, the scripts refuse. Then read it
again, tell the owner what changed, and ask again.

- **A new page:** publish the approved draft, or schedule it in the site's own
  time zone:
  ```bash
  ssh SITE 'cd WP && wp eval-file ~/awt-skill/awt-publish.php id=ID expect=HASH'
  ssh SITE 'cd WP && wp eval-file ~/awt-skill/awt-publish.php id=ID expect=HASH at="2026-10-20 09:00"'
  ```
  It checks the page again and refuses on any error, on a page with no real title,
  and on placeholders like `[Price]` still in it. It changes only the status and
  date, never the content. Before scheduling, compare the site's time zone
  (`awt-catalog.php` prints it) with the owner's, and confirm the time with them
  when they differ.
- **Replacing a live page:** `awt-save-page.php file=... id=ID expect=HASH`. It
  refuses if anyone edited the page since you read it, or has unsaved changes open
  in the editor, and keeps the old content as a revision.
- Never publish with `wp post update --post_status=publish`: it skips every check.
- Both scripts then load the live page the way a visitor does and say whether the
  new content is there. If they report an old copy from the page cache, clear the
  cache (see connecting.md) and check again. If the server cannot load its own
  page, check it yourself: `curl -sL URL | grep -c "the words it names"` must be
at least 1.

## 6. Site-wide changes

AWT Settings (header, footer, color scheme, identity and more) live in one option.
Read them with `awt-catalog.php settings`. To change one value, back the option up
and use AWT's own setter, which validates the value:

```bash
ssh SITE 'cd WP && wp option get awt_theme_settings > ~/awt-skill/settings-$(date +%Y%m%d-%H%M).json'
ssh SITE 'cd WP && wp eval "var_dump( AWT\Theme\Settings\set( \"header.colorScheme\", \"dark\" ) );"'
```

Ask the owner before any site-wide change; it affects every page. For the header,
footer and templates, prefer telling the owner where to change it in the Site
Editor or AWT Settings screen over editing them yourself.

## 7. Install or update AWT

When the owner asks to install or update AWT, or section 1 finds it missing, run
the install check. It changes nothing; it says what to run and what that changes:

```bash
ssh SITE 'cd WP && wp eval-file ~/awt-skill/awt-install-check.php'
```

- **STOP:** tell the owner what it says. Do not work around it.
- **UP TO DATE:** nothing to do.
- **READY:** tell the owner what changes and wait for a clear yes. Then back up
  (Safety rule 4), run the printed commands in order, and run the check again:
  it must say UP TO DATE.

[references/installing.md](references/installing.md) has the full steps, updates,
servers that cannot download, and sites without SSH.

## Safety

These rules protect the owner's site. Follow them even when asked to hurry.

1. **Never publish, overwrite or change site-wide settings without the owner's
   clear yes** for that specific change. Drafts are always fine.
2. **The owner edits pages by hand too.** Always read the current content right
   before you change a page. Never write an old local copy over it. Use `expect=`.
3. **Never delete.** Move pages to the trash (`wp post delete ID`, never with
   `--force`). Media cannot go to the trash, so never remove media; tell the owner
   what could go. Never run `wp db reset`, `wp site empty`, or `DROP`.
4. **Back up before bulk changes** (more than one page, search-replace, settings,
   installing or updating AWT):
   ```bash
   ssh SITE 'cd WP && wp db export ~/awt-skill/backup-$(date +%Y%m%d-%H%M).sql --tables="$(wp db tables --format=csv)"'
   ```
   That saves WordPress's own tables (pages, media records, settings), which is
   everything this skill changes, and nothing from other sites sharing the
   database. Keep the newest three of each kind of backup and remove older ones:
   ```bash
   ssh SITE 'cd ~/awt-skill && ls -1r backup-*.sql | tail -n +4 | while read -r f; do rm -- "$f"; done'
   ```
   (The same with `settings-*.json`.) Run `wp search-replace` with `--dry-run`
   first and show the owner the count.
5. **Write content only through `awt-save-page.php`** or `wp_update_post( wp_slash( ... ) )`.
   Never raw SQL: it silently strips the backslashes in attribute escapes.
6. **Do not touch code on the server.** No editing theme or plugin files, no
   installing, updating or removing plugins or themes, no changes to `wp-config.php`,
   unless the owner asks for that exact thing. Installing or updating AWT itself
   goes through section 7, and only with the commands its check prints.
7. **Credentials stay private.** Never print, copy or store passwords, keys or salts
   from `wp-config.php` or elsewhere. Never create users or log-in sessions.
8. **Clean up.** Remove your temporary files from `~/awt-skill/` when you finish,
   except the scripts and the backups.

## When something goes wrong

- **The editor says "This block contains unexpected or invalid content":** a core
  block's HTML does not match what WordPress expects. Compare it with the shapes in
  page-building.md, or ask the owner to click "Attempt recovery" and save.
- **Text like `u003c` or `u002d` shows on the page:** an escape lost its backslash.
  Run `awt-check.php post=ID`, then save the page again from a correct file.
- **`wp eval-file` does nothing and exits 0:** the file probably has one very long
  line. Keep markup on many lines, send it as a file, and check its md5.
- **The page looks unchanged:** clear the page cache, then reload.
- **To undo:** the editor's Revisions panel restores any earlier version.
