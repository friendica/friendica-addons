# XMPP Chat Addon for Friendica

Embeds a fully-featured XMPP webchat client (Converse.js) into your Friendica instance.

## Features

- **Full XMPP chat UI** via Converse.js
- **Multi-User Chat (MUC)** support
- **Message Archive Management** (XEP-0313) for chat history
- **Stream Management** (XEP-0198) for reliable connections
- **Optional OMEMO** end-to-end encryption
- **WebSocket & BOSH** transport support
- Overlayed chat widget (minimizable)
- Desktop notifications
- File sharing (when XMPP server supports XEP-0363)

## Requirements

### XMPP Server

You need an XMPP server with:

- **WebSocket** support (recommended) or **BOSH** (HTTP binding)
- Enabled modules:
  - `mod_websocket` or `mod_bosh`
  - `mod_mam` (Message Archive Management)
  - `mod_smacks` (Stream Management)
  - `mod_carbons` (Message Carbons for multi-device sync)
  - `mod_http_upload` (for file sharing)
  - Optional: `mod_omemo` (for OMEMO encryption)

**Example for Prosody** (`/etc/prosody/prosody.cfg.lua`):
```lua
modules_enabled = {
    "websocket";
    "bosh";
    "mam";
    "smacks";
    "carbons";
    "http_upload";
    "csi"; -- Client State Indication
    "vcard4";
    "bookmarks";
}

-- WebSocket configuration
consider_websocket_secure = true
cross_domain_websocket = true

-- HTTP upload limits
http_upload_file_size_limit = 10485760 -- 10 MB
http_upload_expire_after = 60 * 60 * 24 * 7 -- 1 week
```

Ensure your firewall allows:

- Port 5281 (WebSocket/BOSH over HTTPS)
- Port 5222 (XMPP client connections)

### DNS/SSL

For WebSocket over TLS (wss://), you need:

- Valid SSL certificate for your XMPP domain
- DNS records pointing to your XMPP server


## Security Considerations

### Auto-Login

The `auto_login` option is **disabled by default** and requires:

- XMPP usernames matching Friendica usernames
- Separate password management (do NOT reuse Friendica passwords!)
- Consider using:
  - **SASL EXTERNAL** with client certificates
  - **OAuth tokens** via `mod_auth_external`
  - **Anonymous login** for public chat rooms

### User Passwords

**Custom XMPP credentials** are stored per-user in the database:

- Passwords are base64-encoded (basic obfuscation)
- Users should use **separate XMPP passwords**, not their Friendica password

### OMEMO Encryption

To enable end-to-end encryption:
1. Set `enable_omemo => true` in config (or via Admin UI)
2. Ensure Converse.js OMEMO plugin is loaded (CDN version 10.1.6+ includes it)
3. Users must verify device fingerprints manually in the chat UI
4. First connection with OMEMO may take longer due to key generation
5. OMEMO requires:
   - Modern browser with WebCrypto API support
   - Server support for PEP (XEP-0163) and device list storage
   - All chat participants using OMEMO-capable clients

**OMEMO Features in this addon:**

- Automatic device key management
- Multi-device synchronization
- Encrypted message storage (MAM)
- Trust-on-first-use (TOFU) model
- Manual fingerprint verification UI

### Content Security Policy

If you use strict CSP headers, whitelist:

- `cdn.conversejs.org` (for Converse.js assets)
- Your XMPP server domain (for WebSocket/BOSH connections)

## Usage

### For Users

#### Using Your Own XMPP Account

1. Go to **Settings → Addon Settings → XMPP Chat**
2. Enable "Use Custom XMPP Account"
3. Enter your XMPP address (JID), e.g., `user@jabber.org` or `myname@conversations.im`
4. Enter your XMPP password
5. Save settings
6. Reload any page - you'll be automatically logged into the chat

**You can use any XMPP account from any server!** No need to create an account on the Friendica instance's XMPP server.

#### Using the Chat Widget

1. Click the chat icon in bottom-right corner
2. If you haven't configured custom credentials, enter XMPP username@domain and password
3. Chat with contacts or join group rooms (MUCs)
4. Chat state persists across page navigation

### Joining Group Chats

To join a multi-user chat room:
1. Click the "+" icon in Converse.js
2. Select "Join a chat room"
3. Enter room JID: `room@conference.example.org`
4. Choose a nickname

### Managing Bookmarks

Converse.js supports XEP-0048 bookmarks:

- Bookmarked rooms appear in your sidebar
- Auto-join on login (optional)

## Troubleshooting

### Chat widget not appearing

- Check browser console for errors
- Verify `enabled => true` in config
- Ensure `websocket_url` or `bosh_url` is set
- Check Friendica logs: `tail -f friendica.log | grep xmppchat`

### Cannot connect to XMPP server

- Test WebSocket endpoint manually:
  ```bash
  wscat -c wss://xmpp.example.org:5281/xmpp-websocket
  ```
- Verify firewall rules allow port 5281
- Check Prosody logs: `tail -f /var/log/prosody/prosody.log`
- Ensure SSL certificate is valid

### Messages not loading from history

- Verify `mod_mam` is enabled on server
- Check `enable_mam => true` in Friendica config
- MAM requires XMPP server storage backend (SQL recommended)

### OMEMO not working

- Ensure all participants have OMEMO-capable clients
- Verify device trust (fingerprint verification)
- Check for conflicting security plugins

## Performance Tips

- Use **WebSocket** (faster than BOSH)
- Enable **Stream Management** (`mod_smacks`)
- Limit **MAM page size** (default: 50 messages)
- Use **Client State Indication** (`mod_csi`) to reduce traffic when inactive

## Compatibility

- **XMPP Servers**: Prosody, ejabberd, Openfire
- **Browsers**: Modern browsers with WebSocket support

## License

Converse.js is licensed under MPL 2.0.

## Documentation

- Converse.js Documentation: https://conversejs.org/docs/html/
- XMPP Standards: https://xmpp.org/extensions/
