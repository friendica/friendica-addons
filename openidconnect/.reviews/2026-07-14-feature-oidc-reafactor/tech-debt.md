# Technical Debt

**Date**: 2026-07-14

## Current Debt Items

### [TD-001]: Transaction stub lacks rollback behavior (PRIORITY: HIGH)

- **Type**: Missing Abstraction
- **Location**: `tests/stubs/DBA.php` — `transaction()` method (lines 98-101)
- **Impact**: Tests pass when production code relies on rollback-on-exception. Data consistency bugs can reach production.
- **Effort**: S (< 1 hour to add snapshot/restore logic)
- **Priority**: P0 (critical) — This masks serious bugs
- **Status**: OPEN

### [TD-002]: Test stubs don't validate field types or required fields (PRIORITY: MEDIUM)

- **Type**: Missing Abstraction
- **Location**: `tests/stubs/Model.php` — `User::create()` and `Contact::updateAvatar()`
- **Impact**: Tests don't exercise validation that production code performs. Field validation bugs could reach production.
- **Effort**: M (1-2 hours to add validation logic matching production behavior)
- **Priority**: P1 (high)
- **Status**: OPEN

### [TD-003]: No runtime verification of stub reset completeness (PRIORITY: LOW)

- **Type**: Missing Abstraction
- **Location**: `tests/Support/AddonTestCase.php` — `setUp()` method
- **Impact**: If new stub is added but not reset, state leaks between tests causing flaky failures
- **Effort**: M (requires stub registry pattern)
- **Priority**: P2 (medium)
- **Status**: OPEN
