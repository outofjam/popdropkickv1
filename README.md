# PopDropkick API

## Overview

PopDropkick API is a RESTful API designed to manage and serve data related to professional wrestling promotions, wrestlers, championships, and title reign histories. It provides structured endpoints to retrieve detailed information about wrestlers, promotions, championships, and their historical title reigns, including handling vacated titles and multi-title reign scenarios.

Write operations go through a moderation workflow: users without auto-approval privileges have their changes queued as change requests for a moderator/admin to approve or reject, rather than applied immediately.

---

## Features

* Manage Promotions with detailed info
* Manage Wrestlers with unique slugs and identifiers
* Track Championships with metadata such as weight classes and introduction dates
* Track Title Reigns with start and end dates, including vacated and multi-title reigns
* Change-request moderation workflow for create/update actions, with auto-approval for trusted users
* Support for nested resources for easy data retrieval
* Pagination and filtering support on key endpoints

---

## Authentication

Endpoints marked **Auth required** below expect a valid Sanctum token (`Authorization: Bearer <token>`). Endpoints not marked as such are public.

For most create/update endpoints, whether the change applies immediately or goes through moderation depends on the authenticated user's role (`User::canAutoApprove()`) - see [Change Requests](#change-requests) below.

- `GET /api/user` — **Auth required**
  Return the currently authenticated user (whoever the bearer token resolves to). Useful for verifying a token is valid.

---

## API Routes

### Promotions

- `GET /api/promotions`
  Retrieve a paginated list of all promotions.

- `GET /api/promotions/{id_or_slug}`
  Retrieve detailed information about a specific promotion by ID or slug, including active/inactive wrestler counts.

- `GET /api/promotions/{id_or_slug}/championships`
  Retrieve a paginated list of a promotion's championships (active and inactive).

- `GET /api/promotions/{id_or_slug}/wrestlers`
  Retrieve a paginated list of a promotion's wrestlers. Active only by default; pass `?include_inactive=true` for the full roster.

- `POST /api/promotions` — **Auth required**
  Create a new promotion.

---

### Wrestlers

- `GET /api/wrestlers/{id_or_slug}`
  Retrieve detailed information about a wrestler by ID or slug, including title reign history and career stats.

  There is intentionally no unpaginated `GET /api/wrestlers` index - the
  wrestler roster is expected to be far larger than the promotions or
  championships lists, so browsing wrestlers goes through a promotion
  (`GET /api/promotions/{id_or_slug}`) rather than a global listing.

- `POST /api/wrestlers` — **Auth required**
  Create a new wrestler. Applied immediately if the user can auto-approve `wrestler_create`; otherwise queued as a change request (`202 Accepted`).

- `PUT /api/wrestlers/{wrestler}` — **Auth required**
  Update a wrestler. Same auto-approve-vs-change-request branching as create.

- `PATCH /api/wrestlers/{wrestler}/promotions` — **Auth required**
  Add, remove, or (de)activate a wrestler's promotion associations.

- `POST /api/wrestlers/{wrestler}/aliases` — **Auth required**
  Add one or more ring name aliases to a wrestler.

- `DELETE /api/wrestlers/{wrestler}/aliases/{alias}` — **Auth required**
  Remove an alias from a wrestler.

---

### Championships

- `GET /api/championships`
  Retrieve a list of all championships.

- `GET /api/championships/{id_or_slug}`
  Retrieve detailed information about a championship by ID or slug, including current champion info, status, reign statistics, and full title reign history.

- `POST /api/promotions/{promotion}/championships` — **Auth required**
  Create a new championship under a promotion.

- `PUT/PATCH /api/championships/{id_or_slug}` — **Auth required**
  Update a championship's details.

- `PATCH /api/championships/{championship}/toggle-active` — **Auth required**
  Toggle a championship between active and inactive.

---

### Title Reigns

Title reigns don't have standalone list/show endpoints - they're read as part of a wrestler's or championship's detail response (`title_reigns`, `active_title_reigns`). Writes are scoped under a wrestler or by reign ID:

- `POST /api/wrestlers/{wrestler}/title-reigns` — **Auth required**
  Record a new title reign for a wrestler. The wrestler in the URL is always a participant; for a tag team, trios, or stable reign, pass additional co-champions via `participants` (and optionally `team_id` to label the group) - see [Multi-Champion Title Reigns](#multi-champion-title-reigns-tag-teams-trios-stables) below.

- `PATCH /api/title-reigns/{reign}` — **Auth required**
  Update a title reign (e.g. set `lost_on`/`lost_at`, or `vacancy_reason` when a title goes vacant).

- `DELETE /api/title-reigns/{reign}` — **Auth required**
  Delete a title reign. Remaining reigns for that wrestler/championship pair are automatically renumbered.

---

### Change Requests

Moderation queue for create/update actions submitted by users who can't auto-approve. All endpoints require the authenticated user to pass `User::canReview()` (moderator/admin), or return `403`.

- `GET /api/change-requests` — **Auth required**
  List change requests. Supports `status`, `model_type`, and `action` filters, plus `per_page`.

- `GET /api/change-requests/{changeRequest}` — **Auth required**
  Get a single change request, including a diff for `update` actions.

- `POST /api/change-requests/{changeRequest}/approve` — **Auth required**
  Approve a pending change request and apply it. Currently only `model_type: wrestler` is implemented; other model types raise an error.

- `POST /api/change-requests/{changeRequest}/reject` — **Auth required**
  Reject a pending change request. Requires a `comments` string.

- `POST /api/change-requests/bulk-approve` — **Auth required**
  Approve multiple change requests by ID in one call. Returns an approved count plus a per-item error list for any that failed (e.g. already reviewed).

---

## Data Model Highlights

- **Promotion**: Has many Wrestlers and Championships.
- **Wrestler**: Can hold multiple Championships across different Promotions.
- **Championship**: Has a weight class, introduction date, and current status.
- **Title Reign**: Tracks start and end dates, win/loss method, and handles vacant periods. Holds one or more participants (Wrestlers) - a singles reign has one, a tag team/trios/stable reign has N - each with their own alias-at-win, via `title_reign_wrestlers`.
- **Team**: An optional, reusable label (e.g. "The New Day") attached to a multi-champion Title Reign. Not required just because a reign has multiple participants.
- **ChangeRequest**: A pending create/update action awaiting moderator/admin review, tracking the submitting user, the proposed data, and (for updates) the original data for diffing.

---

## Handling Vacated Titles and Multi-Title Reigns

- If a championship has no current champion(s), its status will be `"vacant"` and `current_champions` will be `[]` (always an array - see below).
- Multi-title reigns are supported by allowing a Wrestler to have overlapping reigns of multiple championships.
- A reign is considered vacated when `lost_on` is set but `lost_at` is `null`; the API reports `lost_at` as the literal string `"vacated"` in that case. An optional `vacancy_reason` field can record why the title was vacated (e.g. stripped, injury, retirement).

---

## Multi-Champion Title Reigns (Tag Teams, Trios, Stables)

A title reign can be held by more than one wrestler at once - tag team, trios, and stable championships all have multiple simultaneous champions, each with their own alias-at-win (a wrestler might defend under a different ring name than their current primary one).

**`current_champions` and `title_reigns[].wrestlers` are always arrays** - even for a plain singles title, which is represented as an array of one. This is deliberate: every consumer can iterate champions the same way regardless of whether the title is singles, tag team, trios, or a stable, with no special-casing.

#### Singles title

```json
{
  "current_champions": [
    {
      "id": "7f3dd45a-...",
      "name": "Jon Moxley",
      "slug": "jon-moxley",
      "alias_name": "Jon Moxley",
      "team": null,
      "reign_number": 1,
      "reign_start": "2024-06-30",
      "reign_length": 806,
      "reign_length_human": "2yrs and 2mos"
    }
  ]
}
```

#### Trios title

Bandido, Hangman Adam Page, and Brody King winning the AEW World Trios Championship together as "The Opps":

```json
{
  "current_champions": [
    { "id": "...", "name": "Bandido", "alias_name": "Bandido", "team": { "name": "The Opps" }, "reign_number": 1 },
    { "id": "...", "name": "Hangman Adam Page", "alias_name": "Hangman Adam Page", "team": { "name": "The Opps" }, "reign_number": 1 },
    { "id": "...", "name": "Brody King", "alias_name": "Brody King", "team": { "name": "The Opps" }, "reign_number": 1 }
  ]
}
```

The full `title_reigns` history entry for that same reign nests the co-champions under `wrestlers` instead:

```json
{
  "id": "01a0a03a-2541-...",
  "team": { "id": "...", "name": "The Opps" },
  "wrestlers": [
    { "wrestler": { "id": "...", "name": "Bandido", "slug": "bandido" }, "alias_name": "Bandido", "reign_number": 1 },
    { "wrestler": { "id": "...", "name": "Hangman Adam Page", "slug": "hangman-adam-page" }, "alias_name": "Hangman Adam Page", "reign_number": 1 },
    { "wrestler": { "id": "...", "name": "Brody King", "slug": "brody-king" }, "alias_name": "Brody King", "reign_number": 1 }
  ],
  "won_on": "2024-08-25",
  "won_at": "All In: Texas",
  "lost_on": null
}
```

**Per-wrestler `reign_number`**: each co-champion's `reign_number` is their own count of reigns with that specific championship, independent of their teammates. If Bandido had already held this title solo once before, his entry in a later trios reign would read `reign_number: 2` while Hangman and Brody King (first time holding it) would each read `1`.

**Per-reign alias**: `alias_name` is captured per participant, per reign - not just "their current name." A wrestler who defended under an old ring name keeps that name recorded on that specific reign even after their primary name changes. If no alias is explicitly given for a co-champion, it falls back to their current primary name at read time.

**Recording one**: `POST /api/wrestlers/{wrestler}/title-reigns` with the route wrestler as one participant, plus any others:

```json
{
  "championship_id": "...",
  "team_id": "...",
  "won_on": "2024-08-25",
  "won_at": "All In: Texas",
  "win_type": "pinfall",
  "participants": [
    { "wrestler_id": "..." },
    { "wrestler_id": "...", "wrestler_name_id_at_win": "..." }
  ]
}
```

`team_id` is optional - a multi-champion reign doesn't require a team label, and a team is a lightweight, reusable entity (so a later reign by the same trio can reference it again).

---

## Response Format

All responses are in JSON format with structured resource objects. Examples include:

```json
{
  "id": 1,
  "name": "WWE",
  "slug": "wwe",
  "championships": [
    {
      "id": 5,
      "name": "WWE Championship",
      "weight_class": "Heavyweight",
      "current_champions": [
        {
          "id": 10,
          "name": "John Cena",
          "slug": "john-cena",
          "team": null
        }
      ],
      "status": "active"
    }
  ]
}
```
