# Docs index — question → file

| Question | Read |
|---|---|
| What is this? How do I run it? | `README.MD` (root), `docs/SETUP.md` |
| Agent quickstart / conventions / commands | `AGENTS.md` (root; `CLAUDE.md` for Claude Code) |
| LLM/API index + contract pointers | `llms.txt` (root; also `GET /llms.txt`) |
| How is the app structured? | `docs/ARCHITECTURE.md` |
| What is in scope / not in scope? | `docs/DESIGN.md` |
| Which services exist, what's required vs optional? | `docs/PROVIDERS.md` (services matrix) |
| Register/search the API contract | `GET /v4/docs/openapi` (spec), `GET /v4/docs` (Scalar UI), `GET /v4/ai/manifest.json` (tool schema) |
| How to add an endpoint | `docs/RECIPE_ADD_ENDPOINT.md` |
| Is it production-ready? | `docs/PRODUCTION-READINESS.md` |
| What has been done / roadmap | `docs/PR_CHANGELOG.md` |
| Decisions / RFCs | `docs/RFC.md`, `docs/MIGRATION_PLAN.md` |
| Proposals (open/decided) | `docs/proposals/` |
| Upstream PR plan + checklist | `docs/UPSTREAM_PR.md` |
| Database indexes | `docs/DB_INDEXES.md` |
| Troubleshooting / local setup | `docs/TROUBLESHOOTING.md`, `docs/LOCAL_SETUP.md` |
| Docker / container usage | `docs/DOCKER.md` |
| Typesense search spike/runbook | `docs/TYPESENSE_SPIKE.md` |
| What to do after a full data index | `docs/POST_FULL_INDEX.md` |
| Upgrading 4.2.x → 4.3.0 / what changed for operators vs the Lumen release? | `docs/UPGRADING.md` |
| What went wrong before / traps to avoid | `.backend-suite/known-traps.json` |
| Project conventions as an agent skill | `.ai/skills/jikan-rest/SKILL.md` |
| Latest review pass (what was fixed, evidence) | `docs/PR_CHANGELOG.md` (Audit-fix pass) |
| Quality audits / context layer | `.backend-suite/` (profile, arch-rules, known-traps, findings) |

Think **"docs/INDEX.md first"** — if the answer isn't here, it's almost certainly
in `docs/PR_CHANGELOG.md` or the `.backend-suite` findings.