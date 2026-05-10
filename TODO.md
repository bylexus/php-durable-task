# PHP TR TODO

## Multi-Database Queue Support

### 1. Queue Platform Abstractions

- [x] Add a database platform resolver based on the PDO driver and server family.
- [x] Add concrete platform helpers for PostgreSQL, MySQL 8+, MariaDB 10.6+, and SQLite.
- [x] Centralize identifier quoting, qualified table naming, schema or catalog handling, and feature flags in the platform layer.
- [x] Map `QueueConfiguration::schemaName` to the current database or catalog concept on MySQL and MariaDB.
- [x] Reject `QueueConfiguration::schemaName` on SQLite with a clear configuration exception.

### 2. Queue Storage Refactor

- [x] Replace the PostgreSQL-specific queue class with a database-agnostic queue store implementation.
- [x] Rename the queue abstraction and usages to remove PostgreSQL-specific type coupling.
- [x] Keep `DatabaseQueue` as the concrete queue storage boundary while avoiding queue SQL or backend-specific behavior outside it.
- [x] Move all queue SQL generation behind the queue store and platform helpers.
- [x] Keep `QueueRecord` and the existing high-level queue workflow semantics stable.

### 3. Portable DML Changes

- [x] Remove PostgreSQL-only `RETURNING` usage from queue inserts, queue updates, and blob inserts.
- [x] Rework enqueue, update, claim, and blob insert flows to use `lastInsertId()` plus follow-up selects where needed.
- [x] Keep JSON payload persistence portable by storing JSON in standard JSON columns where supported and text otherwise.
- [x] Keep blob storage portable across PostgreSQL, MySQL or MariaDB, and SQLite.

### 4. Claiming And Locking

- [x] Preserve `FOR UPDATE SKIP LOCKED` claiming for PostgreSQL, MySQL 8+, and MariaDB 10.6+.
- [x] Move runner-side cleanup claim queries into the queue store so `Runner` no longer embeds SQL.
- [x] Implement a SQLite-specific claim path with transactional row claiming that preserves correctness without `SKIP LOCKED`.
- [x] Verify the claim implementation still prevents the same queued task from being processed twice.

### 5. Notifications And Polling

- [x] Introduce a notification or wakeup strategy abstraction used by the runner loop.
- [x] Keep PostgreSQL `LISTEN` or `NOTIFY` support behind the PostgreSQL notification strategy.
- [x] Add poll-only strategies for MySQL, MariaDB, and SQLite.
- [x] Keep the existing poll timeout behavior as the common fallback path.

### 6. Schema Management

- [x] Refactor `SchemaManager` to delegate DDL and schema inspection to the database platform helpers.
- [x] Replace static DDL export with a live-PDO `SchemaManager` helper so export uses the resolved backend at runtime.
- [x] Remove the standalone `bin/dump-schema.php` script and document that users must wire schema dumping through their own tooling.
- [x] Generate queue table DDL per backend while keeping the logical column model unchanged.
- [x] Generate blob table DDL per backend while keeping foreign-key semantics where supported.
- [x] Replace PostgreSQL-only information schema assumptions with backend-aware table and column existence checks.
- [x] Keep exported DDL available for all supported backends.

### 7. Blob Store And Attachment Flow

- [x] Refactor `AttachmentBlobStore` to use the shared database platform helpers.
- [x] Remove duplicated identifier and schema handling from the blob store.
- [x] Verify file attachment round-tripping still works unchanged across supported backends.

### 8. Public API Integration

- [x] Update `Task`, `Runner`, and `QueueContext` to use the renamed queue abstraction.
- [x] Keep `QueueContext` as the main public entry point for enqueueing, runner creation, and schema helpers.
- [x] Update README and examples to describe PostgreSQL notifications versus poll-only backends.
- [x] Update package requirements and docs to list PostgreSQL, MySQL 8+, MariaDB 10.6+, and SQLite support.

### 9. Testing

- [x] Keep PostgreSQL integration coverage as the reference implementation.
- [x] Add focused tests for platform resolution and backend feature flags.
- [ ] Add integration coverage for MySQL or MariaDB queue claiming and schema bootstrap.
- [ ] Add integration coverage for SQLite queue bootstrap, enqueue, claim, and runner polling.
- [x] Update tests that currently depend on PostgreSQL-specific class names or notification APIs.

### 10. Acceptance Criteria

- [ ] Verify the queue schema can be created on empty PostgreSQL, MySQL, MariaDB, and SQLite databases through explicit bootstrap.
- [ ] Verify one or more runner processes can safely claim and execute tasks on PostgreSQL, MySQL, and MariaDB.
- [ ] Verify SQLite can run correctly with the poll loop despite lacking a database notification mechanism.
- [ ] Verify task state remains across runner restarts on all supported backends.
- [ ] Verify end-to-end tests cover the main lifecycle paths on at least one notification backend and one poll-only backend.
