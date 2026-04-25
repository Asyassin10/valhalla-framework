# Testing

Run PHPUnit from the repo root:

```bash
composer test
```

Covered areas in v1:

- request and response behavior
- routing and middleware ordering
- JWT and API token auth
- CLI route listing
- TCP agent calls

The sample project can add its own feature tests under `tests/` with the same `phpunit.xml` structure.
