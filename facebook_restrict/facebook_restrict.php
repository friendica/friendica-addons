<?php

/**
 * Name: Facebook Restrict
 * Description: Install this addon and Facebook users will not be able to link friends. Existing users that are linking friends will not be affected.
 * Version: 1.0
 * Author: Mike Macgirvin <http://macgirvin.com/profile/mike>
 * Status: Unsupported.
 */
function facebook_restrict_install()
{
    set_config('facebook', 'restrict', 1);
}

function facebook_restrict_uninstall()
{
    set_config('facebook', 'restrict', 0);
}
