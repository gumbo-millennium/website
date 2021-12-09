# `assets/icons` folder

This folder contains all SVG files which will be merged into the icon map.
You can use the `<x-icon />` Blade directive to render them (an `<svg>` object will be returned).

## Naming

You can name the file however you'd like, but it's recommended to stick with `a-z`, `0-9` and dashes (`-`). The filename
will be prefixed with `icon-`, which means a file named `fa-eye.svg` will be placed in the iconmap as `icon-fa-eye`.

## Usage

The best way to use the spritemap is by using the `<x-icon />` blade directive. The directive takes up to two arguments: the icon
name without `icon-` and a CSS class name (or multiple, they're used in `class="…"`).

```blade
<div class="message">
    <x-icon icon="solid/fa-info-circle" class="message__icon" />
    <span class="message_text">…</span>
</div>
```
