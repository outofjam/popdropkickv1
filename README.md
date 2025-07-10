# PopDropkick API

## Overview

PopDropkick API is a RESTful API designed to manage and serve data related to professional wrestling promotions, wrestlers, championships, and title reign histories. It provides structured endpoints to retrieve detailed information about wrestlers, promotions, championships, and their historical title reigns, including handling vacated titles and multi-title reign scenarios.

---

## Features

* Manage Promotions with detailed info
* Manage Wrestlers with unique slugs and identifiers
* Track Championships with metadata such as weight classes and introduction dates
* Track Title Reigns with start and end dates, including vacated and multi-title reigns
* Support for nested resources for easy data retrieval
* Pagination and filtering support on key endpoints

---

## Base URL

---

## API Routes

### Promotions

- `GET /api/promotions`  
  Retrieve a list of all promotions.

- `GET /api/promotions/{id_or_slug}`  
  Retrieve detailed information about a specific promotion by ID or slug.

- `GET /api/promotions/{id_or_slug}/championships`  
  Retrieve all championships associated with a promotion.

- `GET /api/promotions/{id_or_slug}/wrestlers`  
  Retrieve all wrestlers associated with a promotion.

---

### Wrestlers

- `GET /api/wrestlers`  
  Retrieve a list of all wrestlers.

- `GET /api/wrestlers/{id_or_slug}`  
  Retrieve detailed information about a wrestler by ID or slug.

---

### Championships

- `GET /api/championships`  
  Retrieve a list of all championships.

- `GET /api/championships/{id_or_slug}`  
  Retrieve detailed information about a championship by ID or slug, including current champion info and status.

---

### Title Reigns

- `GET /api/title-reigns`  
  Retrieve all title reign records.

- `GET /api/title-reigns/{id}`  
  Retrieve details of a specific title reign by its ID.

- `GET /api/championships/{id_or_slug}/title-reigns`  
  Retrieve all title reigns for a specific championship.

---

## Data Model Highlights

- **Promotion**: Has many Wrestlers and Championships.
- **Wrestler**: Can hold multiple Championships across different Promotions.
- **Championship**: Has a weight class, introduction date, and current status.
- **Title Reign**: Tracks start and end dates, champion (Wrestler), and handles vacant periods.

---

## Handling Vacated Titles and Multi-Title Reigns

- If a championship has no current champion, its status will be `"vacant"` and `current_champion` will be `null`.
- Multi-title reigns are supported by allowing a Wrestler to have overlapping reigns of multiple championships.
- Vacant periods are represented as title reigns without an assigned wrestler but with start and end dates.

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
