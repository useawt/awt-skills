# AWT skills for AI agents

`awt-site-builder` teaches an AI agent to build and edit pages on a WordPress site
that runs [AWT](https://useawt.com). It connects over SSH with WP-CLI, starts from
AWT's own patterns, checks every page for broken blocks and accessibility problems,
and saves drafts for you to review. Once you approve a page, it publishes or
schedules exactly that version and confirms the live page shows it. It never
publishes or overwrites your own edits without asking.

It can also install AWT on a WordPress site, or update it. It checks the site
first, tells you what will change, and installs only after you say yes.

It uses the open [Agent Skills](https://agentskills.io) format, so the same folder
works in Claude Code and in other agents that read `SKILL.md` files.

## Install

**Claude Code:** add this repository as a plugin marketplace, then install the plugin.

```
/plugin marketplace add useawt/awt-skills
/plugin install awt@awt
```

**Other agents** (Codex, GitHub Copilot, Cursor, Gemini CLI and others): copy
`skills/awt-site-builder` into that agent's skills folder. Its documentation says
where.

```bash
git clone https://github.com/useawt/awt-skills.git
```

## Use

Ask your agent something like:

> Connect to my site over SSH (host `mysite`) and make a pricing page with three
> plans and an FAQ. Save it as a draft.

> Install AWT on my site over SSH (host `mysite`).

The first time, the agent walks you through giving it SSH access. You turn SSH on
in your host's control panel and add the key it creates; it never needs your
passwords. Hosts without SSH can use the WordPress REST API instead, with fewer
checks. See [connecting.md](skills/awt-site-builder/references/connecting.md).

## What is inside

| File | What it does |
|---|---|
| `SKILL.md` | The workflow and the safety rules |
| `references/page-building.md` | Page structure, block markup, accessibility and plain-language rules |
| `references/blocks.md` | Every AWT block's settings and allowed values |
| `references/connecting.md` | SSH setup, page caches, and the REST API route |
| `references/installing.md` | Installing and updating AWT, with or without SSH |
| `scripts/awt-catalog.php` | Lists the site's blocks, patterns, icons, presets and settings |
| `scripts/awt-check.php` | Checks markup or a saved page before anyone sees it |
| `scripts/awt-save-page.php` | Saves a draft, or replaces a page only if nobody edited it since |
| `scripts/awt-publish.php` | Publishes or schedules the exact version you approved, then confirms the live page shows it |
| `scripts/awt-install-check.php` | Checks whether AWT can be installed or updated, and prints the commands, what they change and how to undo them |

## License

GPL-3.0-or-later, like AWT.
