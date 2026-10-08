# Installing or updating AWT

AWT is two parts that always go together, on the same version: the AWT theme
(folder `awt`) and the AWT Blocks plugin (folder `awt-blocks`). Both come from
AWT's releases on GitHub, and useawt.com publishes the list of releases at
`https://useawt.com/updates/v1/awt.json`. Install from nowhere else.

## What the site needs

- **WordPress, already installed.** If the owner has no WordPress yet, they
  install it with their host's installer. It creates the admin account, which you
  never do.
- **WordPress 6.6 or newer and PHP 8.1 or newer.** The check below prints both.
- **SSH with WP-CLI** ([connecting.md](connecting.md)). Without SSH, see the last
  section.

## Install or update

1. Connect and copy the scripts (SKILL.md, section 1), then run the check from the
   WordPress folder:
   ```bash
   ssh SITE 'cd WP && wp eval-file ~/awt-skill/awt-install-check.php'
   ```
   It changes nothing. It compares the site with the newest AWT release and ends
   with a RESULT line.
2. **STOP:** tell the owner what it says, and do not work around it.
   **UP TO DATE:** nothing to install; carry on with what the owner asked.
   **READY:** go on.
3. Tell the owner, in your own words, what the "What changes" lines say, and wait
   for a clear yes. In the same message, give the site address the check printed
   and ask them to confirm it is the right site. Switching a live site to AWT
   changes every page for visitors at once, so ask whether the site has visitors
   now; if it does, suggest trying it first on a staging copy, if their host
   offers one.
4. Back up the database (Safety rule 4 in SKILL.md). Keep the undo commands the
   check printed.
5. Run the printed commands **in the printed order**, one at a time, each as
   `ssh SITE 'cd WP && COMMAND'`. If one fails, stop and show the owner the error.
6. Run the check again. It must say UP TO DATE. If it could not load the home
   page itself, load it yourself with a browser User-Agent (some hosts block
   anything else and answer with a "blocked" page):
   ```bash
   curl -sL -A "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0 Safari/537.36" -o home.html -w '%{http_code}\n' URL
   grep -c 'wp-content/themes/awt' home.html
   grep -ciE 'critical error|fatal error|parse error' home.html
   ```
   It must print 200, then a number above 0, then 0.
7. Tell the owner what is installed, and send them the welcome wizard link the
   check printed. The wizard sets the style, logo, header and text size; the owner
   does it in wp-admin, because you never log in. If they also asked for pages,
   run `awt-catalog.php` and go on with SKILL.md section 2.

## Updating

AWT keeps itself up to date by default (AWT Settings, Tools). A version that can
change how a site looks never installs itself; the owner installs it. When the
owner asks to update now, follow the same steps: the check prints "goes from X to
Y". When it says a version "changes how some sites look", give the owner the
release notes link it prints and let them read it before they say yes.

## Rules for these commands

- Install only the two zips the check names, from its links or downloaded from
  them. Never a zip from another address or one someone sends you.
- The plugin goes first, then the theme. Keep both on the same version.
- `--force` in these commands replaces the AWT folder with the new version, like
  "Replace current with uploaded" in wp-admin. It is fine here. It has nothing to
  do with `wp post delete --force`, which stays forbidden.
- Never delete the old theme or any plugin. Switching back is the undo, and
  WordPress keeps the old theme's menus and widgets for that.
- Do not change the update setting in AWT Settings unless the owner asks.

## When the server cannot download

If the check says the server cannot reach useawt.com, or `wp plugin install`
cannot download from GitHub, download on the owner's computer instead:

```bash
curl -fsSL https://useawt.com/updates/v1/awt.json -o awt.json
```

In `awt.json`, take the newest entry under `releases` whose `hold` is false. Its
`plugin.package` and `theme.package` are the two links. Download both, copy them
and the list to the server, and run the check with the list:

```bash
curl -fsSLO https://github.com/useawt/awt-blocks/releases/download/vVERSION/awt-blocks.zip
curl -fsSLO https://github.com/useawt/awt-theme/releases/download/vVERSION/awt.zip
scp awt.json awt-blocks.zip awt.zip SITE:~/awt-skill/
ssh SITE 'cd WP && wp eval-file ~/awt-skill/awt-install-check.php manifest=$HOME/awt-skill/awt.json'
```

Run the commands it prints with the file in place of each link, for example
`wp plugin install $HOME/awt-skill/awt-blocks.zip --activate`. Afterwards, remove
the two zips and `awt.json` from `~/awt-skill/`.

## Without SSH

The REST API cannot install a theme from a zip, so the owner installs AWT in
wp-admin. Give them the two links of the newest release from the release list,
then these steps:

1. Plugins, Add Plugin, Upload Plugin: choose `awt-blocks.zip`, install, activate.
2. Appearance, Themes, Add Theme, Upload Theme: choose `awt.zip`, install,
   activate.
3. When updating, WordPress asks whether to replace the version that is there.
   For the plugin, choose "Replace current with uploaded". For the theme, choose
   "Replace installed with uploaded" ("Replace active with uploaded" before
   WordPress 6.8). Settings, pages and Site Editor changes are kept.

Then confirm with the REST API (see connecting.md for the application password):
`GET /wp-json/wp/v2/themes?status=active` must show `awt` and the version, and
`GET /wp-json/wp/v2/plugins/awt-blocks/awt-blocks` must show `"status":"active"`
and the same version.
