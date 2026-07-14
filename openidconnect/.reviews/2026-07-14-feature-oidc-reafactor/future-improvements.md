# Future Improvements

**Date**: 2026-07-14

Sizes: **S** = < 1 session (quick win) | **M** = 1–3 sessions (task-batch) | **L** = needs own spec

---

## Small (S) — Quick Wins

### [FI-001]: Add property-based testing for nickname normalization

**Description**: Use a property-based testing library (e.g., PHPUnit's generators or a dedicated library) to fuzz-test `UserProvisioner::normaliseNickname()` with thousands of random inputs and verify invariants (e.g., output is always lowercase alphanumeric, no special chars).

**Rationale**: Nickname normalization handles complex Unicode input. Property-based testing would catch edge cases that hand-written examples miss (emoji, RTL text, combining characters, etc.).

**Effort**: S

**Category**: DX / Test Quality

---

### [FI-002]: Add mutation testing to measure test quality

**Description**: Integrate infection/infection or similar mutation testing tool to verify that tests actually fail when production code is mutated (e.g., flip a boolean, change `===` to `==`).

**Rationale**: High code coverage doesn't guarantee high test quality. Mutation testing finds weak assertions and uncovered branches.

**Effort**: S

**Category**: DX / Test Quality

---

## Medium (M) — Task Batches

### [FI-003]: Create integration test harness with real HTTP server

**Description**: Spin up a minimal OIDC provider (e.g., oauth2-mock-server) in Docker for integration tests. Test full auth flows end-to-end instead of mocking HTTP client.

**Rationale**: Current tests mock HTTP responses at the client layer. Integration tests would catch issues with header encoding, URL escaping, multipart forms, etc. that stub-based tests can't detect.

**Effort**: M

**Category**: Test Quality / Architecture

---

### [FI-004]: Implement test data builders pattern

**Description**: Replace inline array construction (e.g., `DBA::seedUser(['uid' => 5, ...])`) with fluent builder pattern:
```php
UserBuilder::new()
    ->withUid(5)
    ->withOpenId('subject-1')
    ->withEmail('user@example.test')
    ->build();
```

**Rationale**: Improves test readability, enforces required fields, enables reusable test data templates.

**Effort**: M

**Category**: DX / Test Quality

---

## Large (L) — Needs Own Spec

None identified in this review scope.
