# AWT blocks: attributes and allowed values

Generated from AWT 2026.10.3. If the site runs a newer version, a block or value may
be missing here: `awt-catalog.php block <name>` shows what the site really has, and
the docs link under each block shows the current allowed values.

Every AWT block is rendered by the server, so its markup is only the block comment
(plus inner blocks for containers). Write `<!-- wp:awt/button {"text":"Go"} /-->`,
never the HTML it outputs. Leave out attributes that keep their default.

Every block also takes the Accessibility panel attributes (`ariaLabel`,
`ariaDescribedby` and similar) where its settings show them.

## awt/accordion: Accordion

A vertical stack of expandable sections. Add Accordion item blocks inside it.
Docs: https://useawt.com/blocks/accordion/

- `align` (string = `"end"`). Allowed: start, end. Which edge the open/close chevron sits on.
- `size` (string = `"md"`). Allowed: sm, md, lg. The control height.
- `singleOpen` (boolean = `false`). Only one section stays open at a time; opening one closes the others.

## awt/accordion-item: Accordion item

One expandable section inside an accordion.

Goes inside: `awt/accordion`.
Docs: https://useawt.com/blocks/accordion/

- `title` (string = `"Section"`). The section heading shown in the always-visible row.
- `defaultExpanded` (boolean = `false`). Starts this item open.
- `disabled` (boolean = `false`). Renders the section unopenable, and announces it as disabled.

## awt/breadcrumb: Breadcrumb

A breadcrumb trail showing where the current page sits in your site. The last item marks the current page.
Docs: https://useawt.com/blocks/breadcrumb/

- `noTrailingSlash` (boolean = `false`). Removes the separator after the last item. Carbon shows it by default.

## awt/breadcrumb-item: Breadcrumb item

One step in a breadcrumb trail. It is plain text if it has no URL or is the current page.

Goes inside: `awt/breadcrumb`.
Docs: https://useawt.com/blocks/breadcrumb/

- `text` (string = `"Item"`). The visible text.
- `href` (string). The URL this points to.
- `isCurrentPage` (boolean = `false`). Renders this step as plain text with aria-current instead of a link.

## awt/button: Button

A button that runs an action or opens a link.
Docs: https://useawt.com/blocks/button/

- `text` (string = `"Button"`). The label people read and screen readers announce. Keep it short and start with a verb, for example "Download".
- `kind` (string = `"primary"`). Allowed: primary, secondary, tertiary, ghost, danger, danger--tertiary, danger--ghost. The visual weight. Carbon’s rule is one primary button per view. Use the danger kinds for destructive actions, like deleting.
- `size` (string = `"lg"`). Allowed: sm, md, lg, xl, 2xl. The button height. Every size meets the 24px minimum target size of WCAG 2.5.8.
- `type` (string = `"button"`). Allowed: button, submit. The button element type. Use submit inside a Form block so the form actually submits.
- `href` (string). Give the button a URL and it renders as a real link that looks like a button. Leave it empty for a real button element.
- `target` (string). Allowed: empty, _blank, _parent, _top. Where the link opens, for example _blank for a new tab. A link that opens a new tab tells screen reader users so.
- `rel` (string). The link’s rel attribute, for example nofollow. Set it only when you need to control it yourself.
- `disabled` (boolean = `false`). Renders the disabled state. Disabled controls are skipped by keyboard focus, so prefer hiding over disabling when you can.
- `iconName` (string). The name of a Carbon icon, for example download. The icon is decorative, so the text must still carry the meaning.
- `iconPosition` (string = `"trailing"`). Allowed: leading, trailing. Which side of the label the icon sits on.
- `isExpressive` (boolean = `false`). Switches to Carbon's expressive type style: a slightly larger label for editorial pages.

## awt/checkbox: Checkbox

A checkbox with a label and optional helper and error text.
Docs: https://useawt.com/blocks/checkbox/

- `label` (string = `"Checkbox label"`). The visible label. Screen readers announce it too, so make it say what the control does.
- `name` (string). The field name submitted with the form.
- `value` (string). The value submitted when this control is selected or filled.
- `checked` (boolean = `false`). Starts the control in its checked state.
- `disabled` (boolean = `false`). Renders the disabled state. Disabled controls are skipped by keyboard focus, so prefer hiding over disabling when you can.
- `indeterminate` (boolean = `false`). The partially-checked state, for a parent checkbox whose children are mixed.
- `required` (boolean = `false`). Marks the field as required, in the markup and for screen readers.
- `helperText` (string). A short hint under the control, linked to it for screen readers.
- `invalid` (boolean = `false`). Renders the error state.
- `invalidText` (string). The error message. It is announced to screen readers, not just colored red.

## awt/code-snippet: Code snippet

A code snippet with styling and a copy button: inline, single-line or multi-line. For plain code, use the Code block.
Docs: https://useawt.com/blocks/code-snippet/

- `variant` (string = `"multi"`). Allowed: single, multi, inline. One line with a copy button, a multi-line block, or a snippet inside running text.
- `code` (string = `"echo 'Hello, world.';"`). The code itself, shown exactly as written.
- `language` (string). The language name, used for the accessible label.
- `copyLabel` (string = `"Copy"`). The copy button’s accessible label.
- `copiedLabel` (string = `"Copied"`). What the copy button announces after copying.
- `hideCopyBtn` (boolean = `false`). Hides the copy button.

## awt/color-scheme-toggle: Color scheme toggle

A switch that lets visitors choose light or dark mode. Remembers each visitor's choice.
Docs: https://useawt.com/blocks/color-scheme-toggle/

- `kind` (string = `"icon-only"`). Allowed: icon-only, with-label, segmented. A single icon button, an icon with a text label, or a three-way light/auto/dark switcher.
- `lightLabel` (string = `"Light mode"`). The label for the light choice.
- `darkLabel` (string = `"Dark mode"`). The label for the dark choice.
- `autoLabel` (string = `"System setting"`). The label for the auto choice, which follows the visitor’s system setting.

## awt/content-switcher: Content switcher

Segments that swap between panels. Add a switcher segment and a panel for each. Best for up to four short options; use Tabs for more.
Docs: https://useawt.com/blocks/content-switcher/

- `size` (string = `"md"`). Allowed: sm, md, lg. The control height.

## awt/content-switcher-item: Switcher segment

One segment in a content switcher. It shows the panel in the same position.

Goes inside: `awt/content-switcher`.
Docs: https://useawt.com/blocks/content-switcher/

- `label` (string = `"Segment"`). The segment’s visible text.
- `value` (string). The identifier that pairs this segment with its panel.
- `disabled` (boolean = `false`). Renders the disabled state. Disabled controls are skipped by keyboard focus, so prefer hiding over disabling when you can.

## awt/content-switcher-panel: Switcher panel

The panel shown by the segment of the content switcher which is in the same position.

Goes inside: `awt/content-switcher`.
Docs: https://useawt.com/blocks/content-switcher/


## awt/data-table: Data table

A data table with sortable columns, a sticky header and striped rows.
Docs: https://useawt.com/blocks/data-table/

- `dataSource` (object)
- `headers` (array = `[{"key": "name", "text": "Name"}, {"key": "type", "text": "Type"}, {"key": "owner", "text": "Owner"}]`). The columns: a key and visible text each, plus an optional cell type such as boolean.
- `rows` (array = `[{"name": "Load balancer", "type": "HTTP", "owner": "Maria S."}, {"name": "Database", "type": "PostgreSQL", "owner": "Aisha K."}, {"name": "Object storage", "type": "S3", "owner": "Diego R."}]`). The data, one object per row keyed by the column keys.
- `size` (string = `"md"`). Allowed: xs, sm, md, lg, xl. The control height.
- `zebra` (boolean = `false`). Alternates row backgrounds.
- `useStaticWidth` (boolean = `false`). Sizes the table to its content instead of the full width.
- `stickyHeader` (boolean = `false`). Keeps the header row visible while the table scrolls.
- `sortable` (boolean = `false`). Makes columns sortable by clicking their headers, with the sort state announced.
- `defaultSortKey` (string). Allowed: A column key. The column key the table is sorted by on load.
- `defaultSortDirection` (string = `"asc"`). Allowed: asc, desc. The starting sort direction.
- `caption` (string). The table’s caption: what this table is about. Screen readers read it first.

## awt/dropdown: Dropdown

A styled dropdown for picking one option. For the device’s own dropdown, use Select.
Docs: https://useawt.com/blocks/dropdown/

- `label` (string = `"Dropdown"`). The visible label. Screen readers announce it too, so make it say what the control does.
- `placeholder` (string = `"Choose…"`). Ghost text inside the empty field. Never use it instead of a label.
- `helperText` (string). A short hint under the control, linked to it for screen readers.
- `invalid` (boolean = `false`). Renders the error state.
- `invalidText` (string). The error message. It is announced to screen readers, not just colored red.
- `disabled` (boolean = `false`). Renders the disabled state. Disabled controls are skipped by keyboard focus, so prefer hiding over disabling when you can.
- `size` (string = `"md"`). Allowed: sm, md, lg. The control height.
- `name` (string). The field name submitted with the form.
- `options` (array = `[{"value": "option-1", "label": "Option 1"}, {"value": "option-2", "label": "Option 2"}, {"value": "option-3", "label": "Option 3"}]`). The list of choices, one per line as label|value pairs.
- `carbonDefault` (boolean = `false`). Uses Carbon’s own field look (a shaded fill with one line under the text) instead of AWT’s default border on all four sides.

## awt/faq-item: FAQ question

A question and answer that opens on click. Works like an accordion item.

Put FAQ items inside an awt/accordion.
Docs: https://useawt.com/blocks/faq-item/

- `question` (string = `"Question?"`). The question, shown in the always-visible row.
- `defaultExpanded` (boolean = `false`). Starts this item open.
- `level` (string = `"3"`). Allowed: 2, 3, 4, 5, 6. The heading level, so the item fits your page outline.

## awt/feature-grid: Feature grid

A grid of feature tiles that adapts to small screens. Each block inside becomes one tile.

Holds: awt/tile, core/group, core/column (one per grid cell).
Also takes `align`: wide, full.
Docs: https://useawt.com/blocks/feature-grid/

- `columns` (number = `3`). Allowed: 2, 3, 4. How many columns on wide screens.
- `gap` (string = `"06"`). Allowed: 04, 05, 06, 07, 08, 09. The space between items.

## awt/footer-link: Footer link

One link in a footer section.

Goes inside: `awt/footer-section`.
Docs: https://useawt.com/blocks/footer-section/

- `text` (string = `"Link"`). The visible text.
- `href` (string = `"#"`). The URL this points to.
- `external` (boolean = `false`). Marks the link as leaving your site.

## awt/footer-section: Footer section

A column of footer links with an optional heading.
Docs: https://useawt.com/blocks/footer-section/

- `title` (string). The column heading above the links.
- `iconName` (string). An optional Carbon icon next to the heading.

## awt/form: Form

A form. Add fields and a button inside it.
Docs: https://useawt.com/blocks/form/

- `action` (string). The URL the form submits to.
- `method` (string = `"post"`). Allowed: get, post. The HTTP method used on submit.
- `enctype` (string). The encoding used on submit, for example multipart/form-data for file uploads.
- `novalidate` (boolean = `false`). Turns off the browser’s built-in validation bubbles so your own error states do the talking.
- `legend` (string). A heading for the group of fields, announced by screen readers.
- `description` (string). Introductory text linked to the form for screen readers.

## awt/header-action: Header action

An icon button for the header that opens a panel or a page.
Docs: https://useawt.com/blocks/header-action/

- `iconName` (string = `"search"`). The name of a Carbon icon, for example download. The icon is decorative, so the text must still carry the meaning.
- `label` (string = `"Search"`). The accessible name of the icon button. Required: an icon alone says nothing to a screen reader.
- `href` (string). The URL this points to.
- `panelId` (string). The ID of a panel this button opens, if it opens one.
- `kind` (string = `"icon-only"`). Allowed: icon-only, with-label. Icon only, or icon with a visible label.

## awt/header-brand: Header brand

Your site name, with an optional logo and prefix, linked to your home page.
Docs: https://useawt.com/blocks/header-brand/

- `kind` (string). Allowed: text-only, text-with-prefix, logo-only, logo-with-text, logo-with-text-and-prefix. What the brand shows: your site name, an optional prefix, a logo, or combinations.
- `prefix` (string). Small text before the site name, the way IBM shows “IBM” before a product name.
- `siteTitle` (string). Overrides the site name. Leave empty to use your WordPress site title.
- `logoUrl` (string). The logo image for the light scheme.
- `logoUrlDark` (string). Allowed: A URL. The logo image for the dark scheme. Leave it empty to use the dark logo from AWT Settings.
- `logoAlt` (string). The logo’s alt text. Required when a logo is set.
- `href` (string = `"/"`). Where the brand links. Defaults to your home page.

## awt/header-global: Header global actions

A group of header buttons, such as search and the color scheme toggle.
Docs: https://useawt.com/blocks/header-global/


## awt/header-menu: Header menu

A header link that opens a submenu. Add Header navigation item blocks inside it.

Goes inside: `awt/header-nav`.
Docs: https://useawt.com/blocks/header-nav/

- `text` (string = `"Menu"`). The menu’s visible label.

## awt/header-nav: Header navigation

A horizontal navigation menu for the header. Add Header navigation item blocks inside it.
Docs: https://useawt.com/blocks/header-nav/


## awt/header-nav-item: Header navigation item

One link in the header navigation menu. Add these inside a Header navigation block.

Goes inside: `awt/header-nav`, `awt/header-menu`.
Docs: https://useawt.com/blocks/header-nav/

- `text` (string = `"Item"`). The visible text.
- `href` (string = `"#"`). The URL this points to.
- `isCurrent` (boolean = `false`). Marks this item as the current page, visually and with aria-current.
- `matchMode` (string = `"exact"`). Allowed: exact, prefix. How the current page is detected from the URL: exact match, or prefix so child pages count too.

## awt/hero: Hero

A large heading, supporting text and a call to action. Use it to open a landing page.

Also takes `align`: wide, full.
Always write `{"version":2}` (the stored default is 1). Inside it, in order: an optional core/paragraph with className `awt-hero__eyebrow`, a core/heading level 1 with className `awt-hero__heading`, a core/paragraph with className `awt-hero__description`, and an awt/inline-set of awt/button blocks. The awt/hero pattern shows the exact markup.
Docs: https://useawt.com/blocks/hero/

- `version` (number = `1`). Allowed: 1, 2. Write 2. Version 1 is the old format that stored the text in attributes.
- `layout` (string = `"text-only"`). Allowed: text-only, text-with-image-right. Text only, or text with an image beside it.
- `imageUrl` (string). The hero image.
- `imageAlt` (string). The image’s alt text. Required when an image is set.
- `imageRatio` (string). Allowed: 16x9, 4x3, 3x2, 1x1, 3x4, 4x5. The image’s aspect ratio.
- `imageWidth` (string = `"equal"`). Allowed: narrow, equal, wide. How much of the hero’s width the image takes.

## awt/icon: Icon

A single icon, on its own or next to text.
Docs: https://useawt.com/blocks/icon/

- `iconName` (string). The name of a Carbon icon, for example download. The icon is decorative, so the text must still carry the meaning.
- `size` (string = `"16"`). Allowed: 16, 20, 24, 32. The control height.
- `label` (string). The icon’s accessible name when it is not decorative.
- `decorative` (boolean = `true`). Hides the icon from screen readers. Turn this off when the icon carries meaning, and give it a label.
- `color` (string = `"inherit"`). Allowed: inherit, text-primary, text-secondary, link-primary, support-error, support-success, support-warning, support-info. A Carbon color token, so the icon adapts to light and dark mode.
- `inline` (boolean = `false`). Sizes and aligns the icon to sit inside a line of text.

## awt/inline-set: Inline set

A row of evenly spaced buttons, tags, icons, links or toggletips.
Docs: https://useawt.com/blocks/inline-set/

- `orientation` (string = `"horizontal"`). Allowed: horizontal, vertical. Lay the items out in a row or a column.
- `gap` (string = `"md"`). Allowed: sm, md, lg, xl. The space between items.
- `align` (string = `"start"`). Allowed: start, center, end, between. How the content aligns.
- `wrap` (boolean = `true`). Lets items wrap to new lines on small screens.

## awt/link: Link

A text link, on its own or inline, with an optional icon.
Docs: https://useawt.com/blocks/link/

- `text` (string = `"Link text"`). The visible text.
- `href` (string). The URL this points to.
- `target` (string). Allowed: empty, _blank, _parent, _top. Where the link opens, for example _blank for a new tab. A link that opens a new tab tells screen reader users so.
- `rel` (string). The link’s rel attribute, for example nofollow. Set it only when you need to control it yourself.
- `size` (string = `"md"`). Allowed: sm, md, lg. The control height.
- `inline` (boolean = `false`). Styles the link to sit inside running text.
- `visited` (boolean = `false`). Shows the link in the visited color once someone has followed it. The browser decides when a link counts as visited.
- `disabled` (boolean = `false`). Renders the link as disabled text.
- `iconName` (string). The name of a Carbon icon, for example download. The icon is decorative, so the text must still carry the meaning.

## awt/list: List

A bulleted or numbered list styled to match your site.
Docs: https://useawt.com/blocks/list/

- `dataSource` (object)
- `type` (string = `"unordered"`). Allowed: unordered, ordered, ordered-native. Bullets, Carbon-styled numbering, or the browser’s native numbering.
- `isExpressive` (boolean = `false`). Slightly larger, editorial type.
- `nested` (boolean = `false`). Indents this list one level, for building nested lists.

## awt/list-item: List item

One item in a list. Press Tab to make it a sub-item of the one above.

Goes inside: `awt/list`.
Docs: https://useawt.com/blocks/list/

- `content` (string). The item’s text.

## awt/menu-button: Menu button

A button that opens a menu of options.
Docs: https://useawt.com/blocks/menu-button/

- `label` (string = `"Menu"`). The visible label. Screen readers announce it too, so make it say what the control does.
- `kind` (string = `"primary"`). Allowed: primary, secondary, tertiary, ghost. The visual style.
- `size` (string = `"lg"`). Allowed: sm, md, lg. The control height.
- `menuAlignment` (string = `"bottom"`). Allowed: bottom, bottom-start, bottom-end, top, top-start, top-end. Where the menu opens relative to the button.
- `disabled` (boolean = `false`). Renders the disabled state. Disabled controls are skipped by keyboard focus, so prefer hiding over disabling when you can.
- `items` (array = `[{"label": "First action", "value": "first"}, {"label": "Second action", "value": "second"}, {"label": "Third action", "value": "third"}]`). The menu entries: a label and a value each, with an optional disabled flag.

## awt/modal: Modal

A pop-up dialog, hidden until a Modal opener opens it.
Docs: https://useawt.com/blocks/modal/

- `id` (string = `"awt-modal"`). The ID a Modal opener block uses to open this dialog.
- `heading` (string = `"Modal heading"`). The dialog’s heading.
- `label` (string). Small text above the heading, announced with it.
- `size` (string = `"md"`). Allowed: xs, sm, md, lg. The control height.
- `primaryAction` (string = `"Continue"`). The main button’s label.
- `secondaryAction` (string = `"Cancel"`). The optional second button’s label.
- `danger` (boolean = `false`). Styles the dialog for a destructive action.
- `primaryHref` (string). A URL the main button goes to. Leave empty to just close the dialog.
- `primaryTarget` (string). Allowed: empty, _blank, _parent, _top. Where that URL opens, for example _blank for a new tab. Leave empty for the same tab.
- `primaryRel` (string). The rel attribute for that URL.

## awt/modal-opener: Modal opener

A button that opens the modal you choose.
Docs: https://useawt.com/blocks/modal-opener/

- `text` (string = `"Open modal"`). The visible text.
- `kind` (string = `"primary"`). Allowed: primary, secondary, tertiary, ghost, danger. The visual style.
- `size` (string = `"md"`). Allowed: sm, md, lg, xl. The control height.
- `modalId` (string = `"awt-modal"`). The ID of the Modal block this button opens.

## awt/notification: Notification

A notification, shown inline or as a toast. Screen readers announce it.
Docs: https://useawt.com/blocks/notification/

- `kind` (string = `"info"`). Allowed: info, success, warning, error. The status this notification communicates.
- `title` (string = `"Notification"`). The bold lead text.
- `subtitle` (string). The detail after the title.
- `caption` (string). Small extra text, shown only in the toast variant.
- `lowContrast` (boolean = `false`). The tinted style instead of the solid dark one.
- `hideCloseButton` (boolean = `false`). Removes the dismiss button.
- `variant` (string = `"inline"`). Allowed: inline, toast. Inline sits in the content flow; toast is styled for corner placement.

## awt/pagination: Pagination

Pagination links with previous and next buttons. Works automatically on blog, category and search pages.
Docs: https://useawt.com/blocks/pagination/

- `totalPages` (number = `0`). How many pages there are.
- `currentPage` (number = `1`). The page you are on, marked with aria-current.
- `baseUrl` (string). The URL pattern the page numbers link to.

## awt/password-input: Password input

A password field with a show/hide button.
Docs: https://useawt.com/blocks/password-input/

- `label` (string = `"Password"`). The visible label. Screen readers announce it too, so make it say what the control does.
- `name` (string = `"password"`). The field name submitted with the form.
- `placeholder` (string). Ghost text inside the empty field. Never use it instead of a label.
- `helperText` (string). A short hint under the control, linked to it for screen readers.
- `invalid` (boolean = `false`). Renders the error state.
- `invalidText` (string). The error message. It is announced to screen readers, not just colored red.
- `warn` (boolean = `false`). Renders the warning state: something needs attention but does not block submission.
- `warnText` (string). The warning message shown and announced in the warning state.
- `disabled` (boolean = `false`). Renders the disabled state. Disabled controls are skipped by keyboard focus, so prefer hiding over disabling when you can.
- `required` (boolean = `false`). Marks the field as required, in the markup and for screen readers.
- `size` (string = `"md"`). Allowed: sm, md, lg. The control height.
- `hideLabel` (boolean = `false`). Visually hides the label while keeping it for screen readers. Use only when the context makes the purpose obvious.
- `showLabel` (string = `"Show password"`). The reveal button’s label while the password is hidden.
- `hidePasswordLabel` (string = `"Hide password"`). The reveal button’s label while the password is shown.
- `autocomplete` (string = `"current-password"`). The HTML autocomplete hint, for example email or current-password. Helps browsers and password managers fill the field correctly.
- `carbonDefault` (boolean = `false`). Uses Carbon’s own field look (a shaded fill with one line under the text) instead of AWT’s default border on all four sides.

## awt/pricing-tile: Pricing tile

A selectable pricing tile with a plan name, price, description and button.
Docs: https://useawt.com/blocks/pricing-tile/

- `tierName` (string = `"Essentials"`). The plan’s name.
- `price` (string). The price, shown large.
- `pricePeriod` (string). Small text after the price, for example per month.
- `description` (string). One line about who the plan is for.
- `ctaText` (string = `"Get started"`). The action button’s label.
- `ctaHref` (string = `"#"`). Where the action button goes.
- `ctaKind` (string = `"primary"`). Allowed: primary, secondary, tertiary, ghost. The action button’s visual style.
- `featured` (boolean = `false`). Highlights this tile as the recommended plan.
- `badge` (string). The small banner text on a featured tile, for example Most popular.
- `selectable` (boolean = `false`). Makes the tile itself selectable instead of showing a button.

## awt/radio-button: Radio button

One radio button in a radio button group.

Goes inside: `awt/radio-button-group`.
Docs: https://useawt.com/blocks/radio-button-group/

- `label` (string = `"Option"`). The visible label. Screen readers announce it too, so make it say what the control does.
- `value` (string). The value submitted when this control is selected or filled.
- `checked` (boolean = `false`). Starts the control in its checked state.
- `disabled` (boolean = `false`). Renders the disabled state. Disabled controls are skipped by keyboard focus, so prefer hiding over disabling when you can.

## awt/radio-button-group: Radio button group

A group of radio buttons with a shared label, so only one option can be chosen.
Docs: https://useawt.com/blocks/radio-button-group/

- `name` (string). The field name submitted with the form.
- `legend` (string = `"Select an option"`). The group’s question or label, announced with every option.
- `orientation` (string = `"horizontal"`). Allowed: horizontal, vertical. Lay the items out in a row or a column.
- `labelPosition` (string = `"right"`). Allowed: left, right. Which side of each radio its label sits on.
- `helperText` (string). A short hint under the control, linked to it for screen readers.
- `invalid` (boolean = `false`). Renders the error state.
- `invalidText` (string). The error message. It is announced to screen readers, not just colored red.
- `required` (boolean = `false`). Marks the field as required, in the markup and for screen readers.

## awt/section: Section

A container that keeps content at a readable width with even spacing.

Also takes `align`: full, wide.
Docs: https://useawt.com/blocks/section/

- `paddingBlock` (string = `"07"`). Allowed: Carbon spacing steps 01 to 13. The vertical padding, as a Carbon spacing step.
- `paddingInline` (string = `"06"`). Allowed: Carbon spacing steps 01 to 13. The horizontal padding, as a Carbon spacing step.
- `noGapBelow` (boolean = `false`). Removes the space below this section so it sits flush against whatever comes next. Overrides the Spacing setting.
- `maxWidth` (string = `"content"`). Allowed: none, narrow, content, wide, custom. How wide the content column is.
- `customMaxWidth` (string). Allowed: A CSS width, for example 60rem. Your own width when maxWidth is custom.
- `backgroundColor` (string). Allowed: empty, background, layer-01, layer-accent-01. A Carbon background token, so it keeps its contrast in light and dark mode.
- `themeScope` (string = `"inherit"`). Allowed: inherit, light, dark, g10, g100. Renders this section in a different Carbon theme than the page, for example a dark band on a light page.
- `tagName` (string = `"section"`). Allowed: section, div, article, aside. The HTML element the section renders as.

## awt/select: Select

A dropdown that uses your device’s own select control. Best on mobile. For a styled dropdown, use the Dropdown block.
Docs: https://useawt.com/blocks/select/

- `label` (string = `"Select"`). The visible label. Screen readers announce it too, so make it say what the control does.
- `name` (string). The field name submitted with the form.
- `helperText` (string). A short hint under the control, linked to it for screen readers.
- `invalid` (boolean = `false`). Renders the error state.
- `invalidText` (string). The error message. It is announced to screen readers, not just colored red.
- `disabled` (boolean = `false`). Renders the disabled state. Disabled controls are skipped by keyboard focus, so prefer hiding over disabling when you can.
- `required` (boolean = `false`). Marks the field as required, in the markup and for screen readers.
- `size` (string = `"md"`). Allowed: sm, md, lg. The control height.
- `hideLabel` (boolean = `false`). Visually hides the label while keeping it for screen readers. Use only when the context makes the purpose obvious.
- `placeholder` (string = `"Choose…"`). Ghost text inside the empty field. Never use it instead of a label.
- `options` (array = `[{"value": "option-1", "label": "Option 1"}, {"value": "option-2", "label": "Option 2"}, {"value": "option-3", "label": "Option 3"}]`). The list of choices, one per line as label|value pairs.
- `carbonDefault` (boolean = `false`). Uses Carbon’s own field look (a shaded fill with one line under the text) instead of AWT’s default border on all four sides.

## awt/side-nav: Side nav

A side nav beside your content on wide screens. On small screens, its links move into the header menu. Add Side nav sections, links and dividers inside.
Docs: https://useawt.com/blocks/side-nav/

- `mode` (string = `"persistent"`). Allowed: persistent, none. Shows the side navigation, or hides it. On small screens its links move into the header menu.
- `id` (string = `"side-nav"`). The ID a toggle control uses to open and close this side nav.

## awt/side-nav-divider: Side nav divider

A line between groups of side navigation links.

Goes inside: `awt/side-nav`, `awt/side-nav-section`.
Docs: https://useawt.com/blocks/side-nav/


## awt/side-nav-link: Side nav link

One link in the side navigation.

Goes inside: `awt/side-nav`, `awt/side-nav-section`.
Docs: https://useawt.com/blocks/side-nav/

- `text` (string = `"Link"`). The visible text.
- `href` (string = `"#"`). The URL this points to.
- `iconName` (string). The name of a Carbon icon, for example download. The icon is decorative, so the text must still carry the meaning.
- `isCurrent` (boolean = `false`). Marks this item as the current page, visually and with aria-current.
- `matchMode` (string = `"exact"`). Allowed: exact, prefix. How the current page is detected from the URL: exact match, or prefix so child pages count too.

## awt/side-nav-section: Side nav section

A group of side navigation links that opens and closes, with an optional heading.

Goes inside: `awt/side-nav`.
Docs: https://useawt.com/blocks/side-nav/

- `title` (string). The section heading.
- `iconName` (string). An optional Carbon icon next to the section title.

## awt/skip-link: Skip link

A "skip to content" link for keyboard users. It's the first thing they reach and appears when focused.
Docs: https://useawt.com/blocks/skip-link/

- `targetId` (string = `"main-content"`). The ID of the element to jump to. Defaults to the main content.
- `text` (string). The link’s text.

## awt/stat: Statistic

A large number with a heading and optional description, for highlighting key figures.
Docs: https://useawt.com/blocks/stat/

- `value` (string = `"90%"`). The big number.
- `heading` (string = `"faster delivery"`). What the number measures.
- `description` (string). A line of context under the heading.
- `level` (string = `"none"`). Allowed: none, 2, 3, 4, 5, 6. By default the label is plain text, not a heading. Set a level (2 to 6) only when the stat should appear in the page outline.
- `align` (string = `"start"`). Allowed: start, center. How the content aligns.
- `sparkline` (string)
- `sparklineLabel` (string)

## awt/tab: Tab

One tab button. It shows the panel in the same position.

Goes inside: `awt/tabs`.
Docs: https://useawt.com/blocks/tabs/

- `label` (string = `"Tab"`). The tab’s visible text.
- `disabled` (boolean = `false`). Renders the disabled state. Disabled controls are skipped by keyboard focus, so prefer hiding over disabling when you can.

## awt/tab-panel: Tab panel

The panel shown by the tab in the same position.

Goes inside: `awt/tabs`.
Docs: https://useawt.com/blocks/tabs/


## awt/tabs: Tabs

Tabs that switch between content panels. Add one Tab and one Tab panel for each.
Docs: https://useawt.com/blocks/tabs/

- `orientation` (string = `"horizontal"`). Allowed: horizontal, vertical. Lay the items out in a row or a column.

## awt/tag: Tag

A small colored label for categories or filters.
Docs: https://useawt.com/blocks/tag/

- `text` (string = `"Tag"`). The visible text.
- `type` (string = `"gray"`). Allowed: red, magenta, purple, blue, cyan, teal, green, gray, cool-gray, warm-gray, high-contrast, outline. The tag’s color, from Carbon’s tag palette. Every pairing meets contrast requirements.
- `size` (string = `"md"`). Allowed: sm, md, lg. The control height.
- `filter` (boolean = `false`). Adds a dismiss button, for removable filter tags.
- `href` (string). Makes the tag a link.
- `target` (string). Allowed: empty, _blank. Where the link opens, for example _blank for a new tab. A link that opens a new tab tells screen reader users so.
- `rel` (string). The link’s rel attribute, for example nofollow. Set it only when you need to control it yourself.

## awt/testimonial: Testimonial

A highlighted quote with attribution.

Also takes `align`: wide, full.
Docs: https://useawt.com/blocks/testimonial/

- `quote` (string). The quotation. Use only words a real person said, and get their permission before you name them.
- `authorName` (string). Who said it.
- `authorRole` (string). Their role.
- `authorOrg` (string). Their organization.
- `authorAvatarUrl` (string). An optional photo.
- `authorAvatarAlt` (string). The photo’s alt text. Required when a photo is set.
- `markStyle` (string = `"double-curved"`). Allowed: none, single-curved, double-curved, double-straight. The quotation mark drawn before the quote.
- `quoteSize` (string = `"lg"`). Allowed: md, lg, xl. The quote’s type size.
- `attributionStyle` (string = `"stacked"`). Allowed: stacked, inline. The attribution stacked under the quote or inline after it.
- `kind` (string = `"plain"`). Allowed: plain, card. Plain, or on a card background.
- `href` (string). The URL this points to.
- `linkText` (string = `"Read the full story"`). The text of the link to where the quote can be read in full. Make it clear on its own.
- `target` (string). Allowed: empty, _blank. Where the link opens, for example _blank for a new tab. A link that opens a new tab tells screen reader users so.
- `rel` (string). The link’s rel attribute, for example nofollow. Set it only when you need to control it yourself.
- `iconName` (string). The name of a Carbon icon, for example download. The icon is decorative, so the text must still carry the meaning.
- `align` (string = `"start"`). Allowed: start, center. How the content aligns.

## awt/text-area: Text area

A multi-line text field with a label, helper text, and error, warning, and read-only states.
Docs: https://useawt.com/blocks/text-area/

- `label` (string = `"Description"`). The visible label. Screen readers announce it too, so make it say what the control does.
- `name` (string). The field name submitted with the form.
- `placeholder` (string). Ghost text inside the empty field. Never use it instead of a label.
- `value` (string). Pre-filled content.
- `rows` (number = `4`). The visible height, in lines.
- `cols` (number = `0`). The visible width, in characters.
- `helperText` (string). A short hint under the control, linked to it for screen readers.
- `invalid` (boolean = `false`). Renders the error state.
- `invalidText` (string). The error message. It is announced to screen readers, not just colored red.
- `warn` (boolean = `false`). Renders the warning state: something needs attention but does not block submission.
- `warnText` (string). The warning message shown and announced in the warning state.
- `disabled` (boolean = `false`). Renders the disabled state. Disabled controls are skipped by keyboard focus, so prefer hiding over disabling when you can.
- `readonly` (boolean = `false`). Makes the field read-only: visible and focusable, but not editable.
- `required` (boolean = `false`). Marks the field as required, in the markup and for screen readers.
- `maxlength` (number = `0`). The maximum number of characters accepted.
- `hideLabel` (boolean = `false`). Visually hides the label while keeping it for screen readers. Use only when the context makes the purpose obvious.
- `carbonDefault` (boolean = `false`). Uses Carbon’s own field look (a shaded fill with one line under the text) instead of AWT’s default border on all four sides.

## awt/text-input: Text input

A single-line text field with a label and optional helper, error and warning text.
Docs: https://useawt.com/blocks/text-input/

- `label` (string = `"Label"`). The visible label. Screen readers announce it too, so make it say what the control does.
- `name` (string). The field name submitted with the form.
- `type` (string = `"text"`). Allowed: text, email, password, number, search, tel, url. The input type, so mobile keyboards and validation match the data.
- `placeholder` (string). Ghost text inside the empty field. Never use it instead of a label.
- `value` (string). A pre-filled value.
- `helperText` (string). A short hint under the control, linked to it for screen readers.
- `invalid` (boolean = `false`). Renders the error state.
- `invalidText` (string). The error message. It is announced to screen readers, not just colored red.
- `warn` (boolean = `false`). Renders the warning state: something needs attention but does not block submission.
- `warnText` (string). The warning message shown and announced in the warning state.
- `disabled` (boolean = `false`). Renders the disabled state. Disabled controls are skipped by keyboard focus, so prefer hiding over disabling when you can.
- `readonly` (boolean = `false`). Makes the field read-only: visible and focusable, but not editable.
- `required` (boolean = `false`). Marks the field as required, in the markup and for screen readers.
- `size` (string = `"md"`). Allowed: sm, md, lg. The control height.
- `hideLabel` (boolean = `false`). Visually hides the label while keeping it for screen readers. Use only when the context makes the purpose obvious.
- `inline` (boolean = `false`). Puts the label beside the field instead of above it.
- `fluid` (boolean = `false`). The fluid style: label inside the field’s border.
- `maxlength` (number = `0`). The maximum number of characters accepted.
- `pattern` (string). Allowed: A regular expression. A regular expression the value must match.
- `autocomplete` (string). The HTML autocomplete hint, for example email or current-password. Helps browsers and password managers fill the field correctly.
- `carbonDefault` (boolean = `false`). Uses Carbon’s own field look (a shaded fill with one line under the text) instead of AWT’s default border on all four sides.

## awt/tile: Tile

A content box with a heading and body. It can be plain, a link, selectable or expandable.
Docs: https://useawt.com/blocks/tile/

- `variant` (string = `"default"`). Allowed: default, clickable, selectable, expandable. A static container, a whole-tile link, a selectable option, or an expandable summary.
- `href` (string). Where a clickable tile goes.
- `groupName` (string). Give every tile in one choice the same group name and people can pick only one of them. Leave it empty for a tile that switches on and off by itself.
- `value` (string). What a tile sends when the form is submitted, such as “large”. Only needed if the tiles are inside a form.
- `summary` (string = `"Expandable tile"`). The always-visible text of an expandable tile.
- `defaultOpen` (boolean = `false`). Starts an expandable tile open.
- `carbonDefault` (boolean = `false`). Uses Carbon’s own field look (a shaded fill with one line under the text) instead of AWT’s default border on all four sides.

## awt/tile-group: Tile group

A set of selectable tiles where visitors pick one, with a heading. Give the tiles the same group name.
Docs: https://useawt.com/blocks/tile-group/

- `label` (string = `"Choose an option"`). The heading for the choice, shown above the tiles.

## awt/toggle: Toggle

An on/off switch for a single setting.
Docs: https://useawt.com/blocks/toggle/

- `label` (string = `"Toggle label"`). The visible label. Screen readers announce it too, so make it say what the control does.
- `name` (string). The field name submitted with the form.
- `size` (string = `"md"`). Allowed: sm, md. The control height.
- `toggled` (boolean = `false`). Starts the switch on.
- `disabled` (boolean = `false`). Renders the disabled state. Disabled controls are skipped by keyboard focus, so prefer hiding over disabling when you can.
- `readonly` (boolean = `false`). Makes the field read-only: visible and focusable, but not editable.
- `labelA` (string = `"Off"`). The state text shown while off.
- `labelB` (string = `"On"`). The state text shown while on.
- `hideLabel` (boolean = `false`). Visually hides the label while keeping it for screen readers. Use only when the context makes the purpose obvious.

## awt/toggletip: Toggletip

An info button that opens a small pop-up on click. Use it instead of Tooltip for links or longer text.
Docs: https://useawt.com/blocks/toggletip/

- `label` (string). The visible text before the info button.
- `description` (string = `"Additional context shown on click."`). The content of the popover.
- `align` (string = `"bottom"`). Allowed: top, top-start, top-end, bottom, bottom-start, bottom-end, left, right. Where the popover opens relative to the button.

## awt/tooltip: Tooltip

A short hint that appears when visitors hover over or focus text, an icon or a button.
Docs: https://useawt.com/blocks/tooltip/

- `description` (string = `"Tooltip text"`). The tooltip text.
- `align` (string = `"top"`). Allowed: top, top-start, top-end, bottom, bottom-start, bottom-end, left, right. Where the tooltip opens relative to the trigger.
- `defaultOpen` (boolean = `false`). Starts with the tooltip showing.
- `enterDelayMs` (number = `100`). Milliseconds before the tooltip opens on hover.
- `leaveDelayMs` (number = `300`). Milliseconds before it closes after the pointer leaves.
- `triggerText` (string = `"Hover me"`). The underlined text that carries the tooltip.
