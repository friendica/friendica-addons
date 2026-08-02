# QuickPhoto for Friendica

QuickPhoto streamlines the Friendica post editor by converting bulky image BBCodes into a clean, readable shorthand while providing a context-sensitive UI for accessibility.

## How it Works

1.  **Shorthand Conversion**: The addon detects standard Friendica image BBCodes (`[url=...][img=...]...[/img][/url]`) and replaces them with a compact format: `[img]filename|Image description[/img]`.
2.  **DOM-Based Metadata**: Original image and link URLs are stored as JSON metadata within a `data-` attribute on the textarea. This ensures that no data is lost during editor interactions or dynamic page reloads.
3.  **Context-Sensitive UI**: When the cursor is placed inside a shorthand tag, a fixed edit bar appears below the editor. This bar displays a preview thumbnail (sourced from metadata) and a dedicated input field for the "Image description" (ALT text).
4.  **Smart Focus Management**: Typing in the edit bar synchronizes the description in the main editor in real-time. Pressing **ENTER** in the description field prevents form submission and automatically jumps the cursor back into the main text area.
5.  **Seamless Reconstruction**: Upon form submission, the shorthand code is transparently converted back into valid, full-length Friendica BBCode to ensure platform compatibility.

## Installation

1. Copy the `quickphoto` folder to your Friendica `addon/` directory.
2. Enable the addon in the Admin Panel.

---

MIT License

Copyright (c) 2024-2026 Friendica Project & Contributors

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
