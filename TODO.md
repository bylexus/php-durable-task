# Durable Tasks TODO

## Phase 1: Project Skeleton

- [x] Add Composer package metadata and autoloading.
- [x] Create the base source layout.
- [x] Add enum and exception scaffolding.
- [x] Add test infrastructure.
- [x] Verify the package installs and autoloads.
- [x] Verify the test runner executes.

## Phase 2: Core Domain Types

- [x] Implement task and step status enums.
- [x] Implement the retry mode enum.
- [x] Implement `StepResult` and `ErrorInfo`.
- [x] Add tests for result and enum behavior.
- [x] Verify the core value objects are stable and tested.

## Phase 3: Attributes And Metadata

- [x] Implement `CleanupAfter`, `RetryMode`, `Retries`, and `MaxRuntime` attributes.
- [x] Implement metadata DTOs.
- [x] Implement metadata resolver and cache.
- [x] Add validation tests.
- [x] Verify invalid metadata fails early.
- [x] Verify precedence rules are covered by tests.
- [x] Verify task cleanup retention can be resolved from task metadata.

## Phase 4: Queue Schema And Records

- [x] Implement queue record representation.
- [x] Implement schema manager.
- [x] Implement queue configuration.
- [x] Add schema bootstrap integration tests.
- [x] Add a support path to dump the required schema DDL.
- [x] Verify an empty PostgreSQL database can be bootstrapped idempotently.
- [x] Verify the queue schema includes the cleanup deadline column used for terminal row retention.
- [x] Verify schema bootstrap runs only once at startup or explicitly by the user, not before every database call.

## Phase 5: Queue Operations

- [ ] Implement enqueue.
- [ ] Implement claim.
- [ ] Implement transition persistence methods.
- [ ] Implement notifications.
- [ ] Implement expired task cleanup.
- [ ] Add concurrency and cleanup integration tests.
- [ ] Verify multiple runner processes cannot claim the same task at the same time.
- [ ] Verify expired succeeded and failed task rows can be deleted safely without affecting active work.

## Phase 6: Task And Step Contracts

- [ ] Implement base `Task` and `Step` classes.
- [ ] Implement direct task and step instantiation from persisted class names.
- [ ] Define how queue records hydrate domain instances.
- [ ] Add unit tests with example task and step classes.
- [ ] Verify a queued record can be reconstructed into executable objects.

## Phase 7: Runner

- [ ] Implement runner configuration.
- [ ] Implement signal handling.
- [ ] Implement single mode.
- [ ] Implement loop mode.
- [ ] Implement optional one-time schema bootstrap on runner startup, disabled by default.
- [ ] Implement runtime, cancellation, and cleanup checks.
- [ ] Add integration tests for normal execution, retries, shutdown behavior, and retention cleanup.
- [ ] Verify the runner can process tasks end-to-end and stop predictably.

## Phase 8: Examples And Documentation

- [ ] Add one example workflow.
- [ ] Document installation and runner usage.
- [ ] Document attribute behavior.
- [ ] Document schema bootstrap options and DDL export usage.
- [ ] Document operational constraints and non-goals.
- [ ] Verify a new user can understand the mental model and run the example locally.
- [ ] Verify a new user can understand how to create the schema through explicit bootstrap or exported DDL.

## V1 Acceptance

- [ ] Verify a task can be enqueued with a payload.
- [ ] Verify the queue schema can be created on an empty PostgreSQL database through explicit bootstrap.
- [ ] Verify the runner can optionally perform a one-time startup bootstrap when configured.
- [ ] Verify one or more runner processes can safely claim and execute tasks.
- [ ] Verify task state remains durable across runner restarts.
- [ ] Verify a failed step is retried according to metadata.
- [ ] Verify exhausted retries result in terminal task failure.
- [ ] Verify cancellation is persisted and stops further workflow progression.
- [ ] Verify succeeded and failed task rows are deleted after their configured cleanup timeout.
- [ ] Verify end-to-end tests cover the main lifecycle paths.
