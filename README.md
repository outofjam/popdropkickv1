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

---

## API Routes

### Promotions

- `GET /api/promotions`
  Retrieve a paginated list of all promotions.

- `GET /api/promotions/{id_or_slug}`
  Retrieve detailed information about a specific promotion by ID or slug, including active/inactive wrestler counts.

- `GET /api/promotions/{id_or_slug}/championships`
  Retrieve all championships associated with a promotion.

- `GET /api/promotions/{id_or_slug}/wrestlers`
  Retrieve all wrestlers associated with a promotion.

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
  Record a new title reign for a wrestler.

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
- **Title Reign**: Tracks start and end dates, champion (Wrestler), win/loss method, and handles vacant periods.
- **ChangeRequest**: A pending create/update action awaiting moderator/admin review, tracking the submitting user, the proposed data, and (for updates) the original data for diffing.

---

## Handling Vacated Titles and Multi-Title Reigns

- If a championship has no current champion, its status will be `"vacant"` and `current_champion` will be `null`.
- Multi-title reigns are supported by allowing a Wrestler to have overlapping reigns of multiple championships.
- A reign is considered vacated when `lost_on` is set but `lost_at` is `null`; the API reports `lost_at` as the literal string `"vacated"` in that case. An optional `vacancy_reason` field can record why the title was vacated (e.g. stripped, injury, retirement).

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
      "current_champion": {
        "id": 10,
        "name": "John Cena",
        "slug": "john-cena"
      },
      "status": "active"
    }
  ]
}
```
