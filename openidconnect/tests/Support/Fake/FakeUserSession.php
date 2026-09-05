<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect\Tests\Support\Fake;

use Friendica\Core\Session\Capability\IHandleUserSessions;

/**
 * In-memory stand-in for the session, covering both DI::session() and
 * DI::userSession() since production code only needs get/set/remove/clear
 * plus the logged-in user id from this addon's code paths.
 */
final class FakeUserSession implements IHandleUserSessions
{
    private array $values = [];

    /** @var int|false */
    private $localUserId = false;

    public function setLocalUserId($uid): void
    {
        $this->localUserId = $uid;
    }

    public function start(): IHandleUserSessions
    {
        return $this;
    }

    public function regenerateId(): IHandleUserSessions
    {
        return $this;
    }

    public function exists(string $name): bool
    {
        return array_key_exists($name, $this->values);
    }

    public function get(string $name, $defaults = null)
    {
        return $this->values[$name] ?? $defaults;
    }

    public function pop(string $name, $defaults = null)
    {
        $value = $this->values[$name] ?? $defaults;
        unset($this->values[$name]);

        return $value;
    }

    public function set(string $name, $value)
    {
        $this->values[$name] = $value;
    }

    public function setMultiple(array $values)
    {
        foreach ($values as $name => $value) {
            $this->values[$name] = $value;
        }
    }

    public function remove(string $name)
    {
        unset($this->values[$name]);
    }

    public function clear()
    {
        $this->values = [];
    }

    public function getLocalUserId()
    {
        return $this->localUserId;
    }

    public function getLocalUserNickname()
    {
        return false;
    }

    public function getPublicContactId()
    {
        return false;
    }

    public function getRemoteUserId()
    {
        return false;
    }

    public function getRemoteContactID(int $uid): int
    {
        return 0;
    }

    public function getUserIDForVisitorContactID(int $cid): int
    {
        return 0;
    }

    public function getMyUrl(): string
    {
        return '';
    }

    public function isAuthenticated(): bool
    {
        return $this->localUserId !== false;
    }

    public function isSiteAdmin(): bool
    {
        return false;
    }

    public function isModerator(): bool
    {
        return false;
    }

    public function isVisitor(): bool
    {
        return false;
    }

    public function isUnauthenticated(): bool
    {
        return $this->localUserId === false;
    }

    public function getSubManagedUserId()
    {
        return false;
    }

    public function setSubManagedUserId(int $managed_uid): void
    {
    }

    public function setVisitorsContacts(string $my_url)
    {
    }
}
