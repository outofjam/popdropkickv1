# PopDropkick API TODO List

## Features to Implement

- [x] **ChampionshipController::show** — Add more meta data:
    - Longest reign
    - Most reigns
    - Shortest reign
- [x] **WrestlerController::show** — Add more meta data:
    - Total Championships Held
    - Days as Champion
- [ ] **PromotionController::show** — Add more meta data:
    - Total Championships Held
    - Days as Champion
## Ideas / Future Enhancements

- [x] Handle vacated titles more explicitly, including detailed reasons (`vacancy_reason`)
- [x] Support multi-title reigns where one wrestler holds multiple championships simultaneously
- [ ] Add pagination and filtering for title reigns — moot until there's a standalone title-reigns list endpoint; currently reigns are only readable nested under a wrestler's or championship's `show` response
- [ ] Add endpoints for match histories or event data

## Bugs / Fixes

- [x] Fix date formatting inconsistencies across resources — all resources now go through `BaseResource::formatDate()`/`formatTimestamp()`
- [x] Refactor repeated code into shared resources (e.g., TitleReignResource) — completed via `BaseResource`

---

*Last updated: 2026-09-11*
