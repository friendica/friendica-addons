function jappixmini_addon_xor(str1, str2) {
    if (str1.length != str2.length) throw "not same length";

    var encoded = "";

    for (var i=0; i<str1.length;i++) {
        var a = str1.charCodeAt(i);
        var b = str2.charCodeAt(i);
        var c = a ^ b;

        encoded += String.fromCharCode(c);
    }

    return encoded;
}

function jappixmini_addon_set_client_secret(password) {
	if (!password) return;

	var salt1 = "h8doCRekWto0njyQohKpdx6BN0UTyC6N";
	var salt2 = "jdX8OwFC1kWAq3s9uOyAcE8g3UNNO5t3";

	var client_secret1 = str_sha1(salt1+password);
	var client_secret2 = str_sha1(salt2+password);
	var client_secret = client_secret1 + client_secret2;

	setPersistent('jappix-mini', 'client-secret', client_secret);
	console.log("client secret set");
}

function jappixmini_addon_get_client_secret(callback) {
	var client_secret = getPersistent('jappix-mini', 'client-secret');
	if (client_secret===null) {
		var div = document.getElementById("#jappixmini-password-query-div");

		if (!div) {
			div = $('<div id="jappixmini-password-query-div" style="position:fixed;padding:1em;background-color:#F00;color:#fff;top:50px;left:50px;">Retype your Friendica password for chatting:<br></div>');

			var input = $('<input type="password" id="jappixmini-password-query-input">')
			div.append(input);

			var button = $('<input type="button" value="OK" id="jappixmini-password-query-button">');
			div.append(button);

			$("body").append(div);
		}

		button.click(function(){
			var password = $("#jappixmini-password-query-input").val();
			jappixmini_addon_set_client_secret(password);
			div.remove();

			var client_secret = getPersistent('jappix-mini', 'client-secret');
			callback(client_secret);
		});
	}
	else {
		callback(client_secret);
	}
}

function jappixmini_addon_encrypt_password(password, callback) {
	jappixmini_addon_get_client_secret(function(client_secret){
		// add \0 to password until it has the same length as secret
		if (password.length>client_secret.length-1) throw "password too long";
		while (password.length<client_secret.length) {
			password += "\0";
		}

		// xor password with secret
		var encrypted_password = jappixmini_addon_xor(client_secret, password);

		encrypted_password = encodeURI(encrypted_password)
		callback(encrypted_password);
	});
}

function jappixmini_addon_decrypt_password(encrypted_password, callback) {
	encrypted_password = decodeURI(encrypted_password);

	jappixmini_addon_get_client_secret(function(client_secret){
		// xor password with secret
		var password = jappixmini_addon_xor(client_secret, encrypted_password);

		// remove \0
		var first_null = password.indexOf("\0")
		if (first_null==-1) throw "Decrypted password does not contain \\0";
		password = password.substr(0, first_null);

		callback(password);
	});
}

function jappixmini_manage_roster(contacts, contacts_hash, autoapprove, autosubscribe) {
	// listen for subscriptions
	con.registerHandler('presence',function(presence){
		var type = presence.getType();
		if (type != "subscribe") return;

		var from = fullXID(getStanzaFrom(presence));
		var xid = bareXID(from);
		var pstatus = presence.getStatus();

		var approve;

		if (autoapprove && contacts[xid]!==undefined) {
			// approve known address
			approve = true;
			console.log("Approve known Friendica contact "+xid+".");
		}
		else if (autoapprove && pstatus && pstatus.indexOf("Friendica")!=-1) {
			// Unknown address claims to be a Friendica contact.
			// This is probably because the other side knows our
			// address, but we do not know the other side yet.
			// But it's only a matter of time, so wait - do not
			// approve yet and do not annoy the user by asking.
			approve = false;
			console.log("Do not approve unknown Friendica contact "+xid+" - wait instead.");
		}
		else {
			// In all other cases, ask the user.
			var message = "Accept "+xid+" for chat?";
			if (pstatus) message += "\n\nStatus:\n"+pstatus;
			approve = confirm(message);

			// do not ask any more
			if (!approve) sendSubscribe(xid, "unsubscribed");
		}

		if (approve) {
			var name = contacts[xid];
			if (!name) name = xid;

			acceptSubscribe(xid, name);
			console.log("Accepted "+xid+" ("+name+") for chat.");
		}
	});

	// autosubscribe
	if (!autosubscribe) return;

	var stored_hash = getPersistent("jappix-mini", "contacts-hash");
	var contacts_changed = (stored_hash != contacts_hash); // stored_hash gets updated later if everything was successful
	if (!contacts_changed) return;

	console.log("Start autosubscribe.");

	var get_roster = new JSJaCIQ();
	get_roster.setType('get');
	get_roster.setQuery(NS_ROSTER);

	con.send(get_roster, function(iq){
		var handleXML = iq.getQuery();

		// filter out contacts that are already in the roster
		$(handleXML).find('item').each(function() {
                        var node = $(this);
			var xid = node.attr("jid");
			var name = node.attr("name");
			var subscription = node.attr("subscription");

			// ignore accounts that are not in the list
			if (contacts[xid]===undefined) return;

			// add to Friendica group or change name if necessary
			var groups = [];
			node.find('group').each(function() {
				var group_text = $(this).text();
				if (group_text) groups.push(group_text);
			});

			if ($.inArray("Friendica", groups)==-1 || name!=contacts[xid]) {
				groups.push("Friendica");
				sendRoster(xid, null, contacts[xid], groups);
				console.log("Added "+xid+" to Friendica group and set name to "+contacts[xid]+".");
			}

			// authorize if necessary
			if (subscription=="to") {
				sendSubscribe(xid, 'subscribed');
				console.log("Authorized "+xid+" automatically.");
			}

			// remove from list
			delete contacts[xid];
		});

		// go through remaining contacts
		for (var xid in contacts) {if(!contacts.hasOwnProperty(xid)) continue;
			// subscribe
			var presence = new JSJaCPresence();
			presence.setTo(xid);
			presence.setType("subscribe");

			// must contain the word "~Friendica" so the other side knows
			// how to handle this
			presence.setStatus("I'm "+MINI_NICKNAME+" from ~Friendica.\n[machine-generated message]");

			con.send(presence);
			console.log("Subscribed to "+xid+" automatically.");

			// add to roster
			var iq = new JSJaCIQ();
			iq.setType('set');
			var iqQuery = iq.setQuery(NS_ROSTER);
			var item = iqQuery.appendChild(iq.buildNode('item', {'xmlns': NS_ROSTER, 'jid': xid}));
			item.setAttribute('name', contacts[xid]);
			item.appendChild(iq.buildNode('group', {'xmlns': NS_ROSTER}, "Friendica"));
			con.send(iq);
			console.log("Added "+xid+" ("+contacts[xid]+") to roster.");
		}

		setPersistent("jappix-mini", "contacts-hash", contacts_hash);
		console.log("Autosubscribe done.");
	});

}

function jappixmini_addon_subscribe() {
        if (!window.con) {
		alert("Not connected.");
		return;
        }

	var xid = prompt("Jabber address");
	sendSubscribe(xid, "subscribe");
}

function jappixmini_addon_start(server, username, proxy, bosh, encrypted, password, nickname, contacts, contacts_hash, autoapprove, autosubscribe) {
    var handler = function(password){
        // check if settings have changed, reinitialize jappix mini if this is the case
        var settings_identifier = str_sha1(server);
        settings_identifier += str_sha1(username);
        settings_identifier += str_sha1(proxy);
        settings_identifier += str_sha1(bosh);
        settings_identifier += str_sha1(password);
        settings_identifier += str_sha1(nickname);

        var saved_identifier = getDB("jappix-mini", "settings-identifier");
        if (saved_identifier != settings_identifier) {
            disconnectMini();
            removeDB('jappix-mini', 'dom');
            removePersistent("jappix-mini", "contacts-hash");
        }
        setDB("jappix-mini", "settings-identifier", settings_identifier);

        // set HOST_BOSH
        if (proxy)
            HOST_BOSH = proxy+"?host_bosh="+encodeURI(bosh);
        else
            HOST_BOSH = bosh;

        // start jappix mini
        MINI_NICKNAME = nickname;
        LOCK_HOST = "off";
        launchMini(true, false, server, username, password);

        // increase priority over other Jabber clients - does not seem to work?
        var priority = 101;
        presenceMini(null,null,priority);

        jappixmini_manage_roster(contacts, contacts_hash, autoapprove, autosubscribe)
    }

    // decrypt password if necessary
    if (encrypted)
        jappixmini_addon_decrypt_password(password, handler);
    else
        handler(password);
}
