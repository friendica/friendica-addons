# QuickPhoto Addon for Friendica

QuickPhoto is a Friendica addon that simplifies working with images in the editor. It automatically replaces long, cumbersome BBCode structures with a compact shorthand notation, without affecting functionality or compatibility.

---

## Features

- **Automatic Simplification:** Converts "monster BBCodes" like `[url=...][img=...]...[/img][/url]` instantly into the handy format `[img]filename description[/img]`.
- **Intelligent Reconstruction:** Before submitting or previewing, the shorthand code is quickly converted back into the original, valid Friendica BBCode.
- **Real-Time Processing:** Responds immediately to drag & drop, copy & paste, and inserting images via editor buttons.
- **Focus Safety:** Cursor management ensures the focus remains stable during automatic conversion while typing.
- **Maximum Compatibility:** Supports both the standard Jot editor and the Compose module, as well as reply fields.
- **Local Cache:** Image data is securely stored in the browser's localStorage and automatically cleared after 12 hours.

---

## How It Works

The addon operates in a hybrid manner:

- **Frontend:** A JavaScript watcher scans textareas and simplifies complex image links for better readability while writing.
- **Interface:** It integrates deeply with Friendica's jQuery functions to ensure that preview and save functions always receive the correct original data.
- **Events:** By intercepting submit and preview clicks, it guarantees that shorthand codes are never sent to the server in a format it cannot interpret.

---

## Installation

1. Create a folder named `quickphoto` in the `addon/` directory of your Friendica installation.
2. Place the file `quickphoto.php` in this folder.
3. Place the file `quickphoto.js` in the same folder.
4. Enable the addon in the Friendica administration area under **Addons**.
 
 ---

MIT License

Copyright (c) 2024-2026 Friendica Project & Contributors

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
