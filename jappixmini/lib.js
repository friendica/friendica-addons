function jappixmini_addon_xor(str1, str2) {
    if (str1.length != str2.length) throw "not same length";

    encoded = "";

    for (i=0; i<str1.length;i++) {
        var a = str1.charCodeAt(i);
        var b = str2.charCodeAt(i);
        var c = a ^ b;

        encoded += String.fromCharCode(c);
    }

    return encoded;
}

function jappixmini_addon_set_client_secret(password) {
	client_secret = str_sha1("client_secret:"+password);
	setDB('jappix-mini', 'client_secret', client_secret);
}

function jappixmini_addon_get_client_secret() {
	client_secret = getDB('jappix-mini', 'client_secret');
	if (client_secret===null) {
		div = $('<div style="position:fixed;padding:1em;background-color:#F00;color:#fff;top:50px;left:50px;" id="x123">Reintroduce your Friendica password for chatting:</div>');
		div.append($("<br>"));
		input = $('<input type="password">')
		div.append(input);
		button = $('<input type="button" value="OK">');
		button.click(function(){
			password = input.val();
			jappixmini_addon_set_client_secret(password);
			div.remove();
		});
		div.append(button);
		$("body").append(div);
	}

	return client_secret;
}

function jappixmini_addon_encrypt_password(password) {
	client_secret = jappixmini_addon_get_client_secret();

	// add \0 to password until it has the same length as secret
	if (client_secret.length<password.length) throw "password too long";
	while (password.length<client_secret.length) {
		password += "\0";
	}

	// xor password with secret
	encrypted_password = jappixmini_addon_xor(client_secret, password);

	encrypted_password = encodeURI(encrypted_password)
	return encrypted_password;
}

function jappixmini_addon_decrypt_password(encrypted_password) {
	encrypted_password = decodeURI(encrypted_password);

	client_secret = jappixmini_addon_get_client_secret();

	// xor encrypted password with secret
	password = jappixmini_addon_xor(client_secret, encrypted_password);

        // remove \0
	first_null = password.indexOf("\0")
	password = password.substr(0, first_null);

	return password;
}

function jappixmini_manage_roster(contacts, autoapprove, autosubscribe) {
	// listen for subscriptions
	con.registerHandler('presence',function(presence){
		var type = presence.getType();
		if (type != "subscribe") return;

		var from = fullXID(getStanzaFrom(presence));
		var xid = bareXID(from);

		approve = true;
		if ((!autoapprove) || ($.inArray(xid, contacts) == -1))
			approve = confirm("Accept "+xid+" for chat?");

		if (approve) {
			acceptSubscribe(xid);
			//alert("Accepted "+xid+" for chat.");
		}
	});

	// autosubscribe
	if (autosubscribe) {
		for (i=0; i<contacts.length; i++) {
			xid = contacts[i];
			sendSubscribe(xid, "subscribe");
		}
	}
}

function jappixmini_addon_start(server, username, bosh, encrypted_password) {
    // check if settings have changed, reinitialize jappix mini if this is the case
    settings_identifier = str_sha1(server);
    settings_identifier += str_sha1(username);
    settings_identifier += str_sha1(bosh);
    settings_identifier += str_sha1(encrypted_password);

    saved_identifier = getDB("jappix-mini", "settings_identifier");
    if (saved_identifier != settings_identifier) removeDB('jappix-mini', 'dom');
    setDB("jappix-mini", "settings_identifier", settings_identifier);

    // set bosh host
    HOST_BOSH = HOST_BOSH+"?host_bosh="+encodeURI(bosh);

    // decrypt password
    password = jappixmini_addon_decrypt_password(encrypted_password);

    // start jappix mini
    launchMini(true, false, server, username, password);
}
