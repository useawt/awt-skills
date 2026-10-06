# Connecting to the site

## What you need

- **An agent with a shell**, such as Claude Code (terminal, desktop app or IDE).
  Chat apps without a shell cannot use SSH; see "Without SSH" below.
- **SSH access to the site's server, with WP-CLI.** Most managed WordPress hosts
  offer both. Some cheap shared plans offer neither; then use "Without SSH". If SSH
  works but `wp` is missing, ask the owner whether you may install WP-CLI in their
  home folder, using the official download from wp-cli.org.

## First-time setup over SSH

The owner does steps 1 and 3 in their host's control panel. Never log in to the
host's panel yourself, and never ask for the panel password.

1. **Owner:** turn on SSH access for the site in the host's control panel, and note
   the host name, port and user name it shows.
2. **Agent:** make a key just for this, on the owner's computer:
   ```bash
   ssh-keygen -t ed25519 -f ~/.ssh/awt-site -C "AI agent for example.com"
   cat ~/.ssh/awt-site.pub
   ```
3. **Owner:** add that public key (the `.pub` line) to the host's SSH keys page.
4. **Agent:** add a short name to `~/.ssh/config`:
   ```
   Host mysite
     HostName ssh.example-host.com
     Port 22
     User example
     IdentityFile ~/.ssh/awt-site
     IdentitiesOnly yes
   ```
5. **Agent:** test it and find the WordPress folder:
   ```bash
   ssh mysite 'wp --info | head -3'
   ssh mysite 'find ~ -maxdepth 4 -name wp-config.php 2>/dev/null'
   ssh mysite 'cd FOLDER && wp option get siteurl'
   ```
   The folder holding `wp-config.php` (often `public_html`, `public`, `www` or
   `htdocs`) is the WordPress folder (`FOLDER` above, `WP` in SKILL.md).
   `wp option get siteurl` must print the site's address. If the account holds
   several sites, ask the owner which one.

To revoke access later, the owner deletes the key in the host's panel.

## Clearing the page cache

After publishing, clear the page cache so visitors see the change. Look for a
command from the host or the cache plugin:

```bash
ssh mysite 'cd WP && wp plugin list --status=active --field=name'
ssh mysite 'cd WP && wp help | grep -i cache'
```

Common ones: `wp cache flush` (object cache), `wp litespeed-purge all` (LiteSpeed),
`wp w3-total-cache flush all` (W3 Total Cache), `wp sg purge` (SiteGround),
`wp kinsta cache purge --all` (Kinsta). If there is no command, ask the owner to
clear it from the host's panel or the cache plugin's menu.

## Without SSH: the WordPress REST API

Every WordPress site has a REST API. It can read the catalog and save drafts, but
the check scripts need WP-CLI, so you lose `awt-check.php`.

1. **Owner:** in WordPress, go to Users, then Profile, then Application Passwords.
   Create one named for the agent. Save it in a file only the owner can read, so
   the agent uses it without ever seeing it. Write the password **without its
   spaces**; WordPress accepts it either way, and this file format splits on spaces:
   ```
   # ~/.awt-site.netrc   (chmod 600)
   machine example.com login OWNER_USERNAME password xxxxxxxxxxxxxxxxxxxxxxxx
   ```
2. **Agent:** use it with `curl --netrc-file ~/.awt-site.netrc`. Never print the file.
   To stop access, the owner revokes the application password in the same place.

| Task | Request |
|---|---|
| AWT blocks and their attributes | `GET /wp-json/wp/v2/block-types?namespace=awt` |
| Pattern markup | `GET /wp-json/wp/v2/block-patterns/patterns` |
| Read a page (raw markup, last edit) | `GET /wp-json/wp/v2/pages/ID?context=edit` |
| Save a new draft | `POST /wp-json/wp/v2/pages` with JSON `{"title":"...","content":"<markup>","status":"draft"}` |
| Update a page | `POST /wp-json/wp/v2/pages/ID` with JSON `{"content":"<markup>"}` |
| Publish or schedule an approved page | `POST /wp-json/wp/v2/pages/ID` with `{"status":"publish"}`, or `{"status":"future","date":"2026-10-20T09:00:00"}` (site time zone) |
| Upload an image | `POST /wp-json/wp/v2/media` with the file, then `POST /wp-json/wp/v2/media/ID` with `{"alt_text":"..."}` |

The same safety rules apply. Before updating a page, read it again and compare its
`modified_gmt` with what you saw before; if it changed, the owner edited it, so
stop and ask. WordPress keeps the old version as a revision. After publishing,
load the public URL with `curl -sL` and check the new words are there; if not,
clear the page cache.

Build the JSON body with a real JSON encoder (`jq`, Python), never by hand: the
markup is full of quotes and backslashes.

Without the check script, check the rules in page-building.md yourself, and if you
have a browser where the owner is logged in, open the draft in the editor: an
invalid block shows a warning there.
