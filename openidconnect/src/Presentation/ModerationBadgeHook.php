<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect\Presentation;

use Friendica\Database\DBA;
use Friendica\DI;

final class ModerationBadgeHook
{
    public function append(string &$output): void
    {
        $route = DI::args()->getCommand();
        if (strpos($route, 'moderation/users') !== 0) {
            return;
        }

        $oidcUsers = DBA::p(
            "SELECT DISTINCT `uid`
             FROM `pconfig`
             WHERE `cat` = ? AND `k` = ? AND `v` != ''
             UNION
             SELECT `uid`
             FROM `user`
             WHERE `openid` != ''
               AND `openid` NOT LIKE 'http://%'
               AND `openid` NOT LIKE 'https://%'",
            'openidconnect',
            'oidc_sub'
        );

        $uids = [];
        while ($user = DBA::fetch($oidcUsers)) {
            $uids[] = (int)$user['uid'];
        }
        DBA::close($oidcUsers);

        if (empty($uids)) {
            return;
        }

        DI::page()->registerStylesheet(__DIR__ . '/../../static/addon.css');

        try {
            $jsonUids = json_encode($uids, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            DI::logger()->error('openidconnect: failed to encode SSO badge user ids for admin users table', ['error' => $e->getMessage()]);
            return;
        }

        $output .= <<<JS
<script>
document.addEventListener("DOMContentLoaded", function() {
    var uids = {$jsonUids};
    document.querySelectorAll("#users tbody tr").forEach(function(row) {
        var uid = null;
        var checkbox = row.querySelector('input[name="user[]"]');
        if (checkbox) {
            uid = parseInt(checkbox.value);
        } else if (row.id) {
            var m = row.id.match(/^user-(\d+)$/);
            if (m) uid = parseInt(m[1]);
        }
        if (uid && uids.indexOf(uid) !== -1) {
            var cell = row.querySelector('td:nth-child(3), td.name, .name') || row.cells[1] || row.cells[2];
            if (cell) {
                if (cell.querySelector('.openidconnect-sso-badge')) {
                    return;
                }

                var badge = document.createElement("span");
                badge.textContent = "OIDC";
                badge.title = "OpenID Connect SSO";
                badge.className = "badge openidconnect-sso-badge";

                var anchors = cell.querySelectorAll('a');
                var anchor = anchors.length ? anchors[anchors.length - 1] : null;
                if (anchor) {
                    anchor.appendChild(document.createTextNode(' '));
                    anchor.appendChild(badge);
                } else {
                    cell.appendChild(badge);
                }
            }
        }
    });
});
</script>
JS;
    }
}
