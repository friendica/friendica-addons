# QuickPhoto Addon for Friendica

**QuickPhoto** is a Friendica addon designed to streamline the post editor by converting lengthy image BBCode structures into a compact, readable shorthand. It ensures a clutter-free writing experience without compromising data integrity or platform compatibility.

---

## Features

- **Automatic Simplification**: Instantly converts cumbersome "monster BBCodes" like `[url=...][img=...]...[/img][/url]` into the clean format `[img]filename|description[/img]`.
- **Hardened Reconstruction**: Uses a high-priority submit listener and jQuery overrides to ensure shorthand code is converted back to valid Friendica BBCode before submission.
- **Context-Aware Metadata**: Unlike previous versions, image data is now stored directly within the editor's DOM (as `data-` attributes), preventing data loss during device switches, private browsing, or cache clearing.
- **Server-Side Safety Net**: Includes a PHP fallback hook (`post_post`) to resolve shorthand codes server-side if JavaScript fails, ensuring images are never lost.
- **Real-Time Processing**: Responds seamlessly to drag-and-drop, copy-paste, and editor button inserts with zero flicker and stable cursor focus.
- **Internationalization Ready**: Fully compatible with all languages and special characters using secure JSON encoding for translation strings.

---

## How It Works

The addon employs a multi-layered **"Fail-Safe" architecture**:

1. **Frontend (UI)**: A JavaScript watcher simplifies complex image links as you type, making long posts easier to navigate.
2. **Storage**: Metadata (URLs and Resource-IDs) is attached directly to the `textarea` element, ensuring each browser tab maintains its own "source of truth."
3. **The Handshake**: When clicking "Submit" or "Preview," the script interceptor replaces all shorthand codes with the original URLs.
4. **The Safety Anchor**: If frontend reconstruction fails, the `quickphoto_post_hook` in PHP attempts a database lookup to restore the link before saving.

---

## Installation

1. **Download the Addon**: Copy the `quickphoto` folder to the `addon/` directory of your Friendica installation.
2. **Enable the Addon**: Go to the **Addons** section in your Friendica admin panel and enable **QuickPhoto**.
3. The addon works immediately and requires no additional configuration.

---

MIT License

Copyright (c) 2024-2026 Friendica Project & Contributors

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
