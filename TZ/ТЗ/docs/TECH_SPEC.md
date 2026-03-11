# TECH_SPEC.md — Refactor & Completion Plan for LMS 'Dinamika'
Generated: 2025-10-02T19:43:25.176651Z

## Goal
Prepare a complete, developer-ready Technical Specification and implementation artifacts required to refactor and complete the LMS project (Laravel backend, Vue 3 + Vuetify frontend, PostgreSQL). This package contains specification documents, OpenAPI, Vuex design, UI design, CI workflow and test skeletons. It is a *deliverable patch* to be applied to the repository.

## Summary of actions (proposed)
- Audit and fix backend runtime bugs, SQL/N+1, mass assignment vulnerabilities.
- Standardize API responses and implement API versioning (/api/v1).
- Add Spatie permission integration and Policies for resource authorization.
- Improve DB schema: add FKs and indexes; add seeders for demo data.
- Implement background jobs for imports, certificate generation and notifications (Laravel Queues/Redis).
- Frontend: modularize store (Pinia recommended), lazy-load routes, add accessible Vuetify components and table designs.
- Add tests: PHPUnit feature tests and Vue unit tests; add GitHub Actions CI pipeline.
- Produce machine-readable spec files: OpenAPI v3, Vuex module docs, UI design docs.
- Provide DDL patches and migration recommendations and an ASSUMPTIONS file.

> **Note:** This package does not directly change your repository history. It contains the exact files/patches and instructions required to apply the changes as commits, branches and PRs. If you want, I can generate a patch (git format-patch) next.

---

## Files produced in this package
- /docs/TECH_SPEC.md (this file)
- /docs/openapi.yaml (OpenAPI v3 full spec fragment)
- /docs/vuex.md (Vuex/Pinia modular design and example modules)
- /docs/ui-design.md (UI tables, forms, dialogs, states and flows)
- /docs/ASSUMPTIONS.md (explicit assumptions)
- /docs/CHANGELOG.md (proposed changelog & patch list)
- /tests_backend/* (PHPUnit skeletons)
- /tests_frontend/* (Jest/Vue Test Utils skeletons)
- /.github/workflows/ci.yml (CI pipeline definition)
- /docs/README_apply_patch.md (how to apply the proposed patches)
