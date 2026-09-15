# Forwarder vendor API (frontend)

Forwarder vendor flow is ready for the web portal. Use these APIs to load masters, create/edit charge sheets, and show verified products in the public vendor catalog.

- Base: `/api`
- Portal: `/api/portal`
- Auth: `Authorization: Bearer <token>` **or** `X-API-TOKEN: <token>`
- Snake_case and camelCase keys are both accepted on create/update.

---

## Auth

| Area | Auth |
|------|------|
| Master dropdowns (`/api/get/...`) | None |
| Portal CRUD (`/api/portal/web/forwarder-product/...`) | Required |
| Public catalog (`/api/web/vendor/...`) | Required |

`user_id` on create must match the authenticated user.

---

## 1. Master dropdowns

Use these to fill the create/edit form.

| Method | URL | Use for |
|--------|-----|---------|
| `GET` | `/api/get/port/types` | Port type (ICD / Sea Port) |
| `GET` | `/api/get/icd/locations` | ICD location (when port type is ICD) |
| `GET` | `/api/get/indian/ports` | Indian / origin port |
| `GET` | `/api/get/container/sizes` | Container type (20 FT / 40 FT) |
| `GET` | `/api/get/currencies` | Charge currency (INR, USD, …) |
| `GET` | `/api/get/forwarder/charge-titles` | Charge row titles |
| `GET` | `/api/get/destination/regions` | Destination region |
| `GET` | `/api/get/destination/countries/{regionId}` | Countries for a region |
| `GET` | `/api/get/destination/ports` | Sea ports |

### Destination cascade

1. Load regions: `GET /api/get/destination/regions`
2. On region change: `GET /api/get/destination/countries/{regionId}`
3. On country change: `GET /api/get/destination/ports?region_id={id}&country_id={id}`

Query aliases: `regionId`, `countryId`.

If region/country/port lists are empty, admin has not imported destination masters yet.

### Charge titles (seeded)

Ori THC, IHC, BL, Seal + Maintenance Fee, AMS, OWS, O/F, Seaway BL, Surrender BL.

```json
{
  "status": true,
  "message": "Forwarder charge titles fetched successfully.",
  "data": [
    {
      "id": 1,
      "name": "Ori THC",
      "title": "Ori THC",
      "description": null
    }
  ]
}
```

### Container sizes

```json
{
  "status": true,
  "data": [
    { "id": 1, "size": 20, "label": "20 FT", "description": null },
    { "id": 2, "size": 40, "label": "40 FT", "description": null }
  ]
}
```

### Destination ports

```json
{
  "status": true,
  "data": [
    {
      "id": 44,
      "name": "Algeirs",
      "description": null,
      "region_id": 1,
      "region": "Africa",
      "country_id": 10,
      "country": "Algeria"
    }
  ]
}
```

Invalid region id on countries API → `404`.

---

## 2. Vendor product CRUD (logged-in forwarder)

All under `/api/portal`. Send the portal token.

| Method | URL | Purpose |
|--------|-----|---------|
| `POST` | `/api/portal/web/forwarder-product/create` | Create product (`status` starts at `0`) |
| `POST` | `/api/portal/web/forwarder-product/update` | Update product (`id` required) |
| `GET` | `/api/portal/web/forwarder-product/list/{userId}` | List that vendor’s products |
| `GET` | `/api/portal/web/forwarder-product/{id}` | Fetch one product |
| `DELETE` | `/api/portal/web/forwarder-product/{id}` | Delete product |

Content-Type: `application/json` (multipart is not required; no images).

### Create / update body

```json
{
  "user_id": 123,
  "portTypeId": 1,
  "icdLocationId": 2,
  "indianPortId": 5,
  "regionId": 1,
  "countryId": 10,
  "destinationPortId": 44,
  "containerSizeIds": [1, 2],
  "additionalInformation": "Optional notes",
  "charges": [
    {
      "titleId": 1,
      "currency": "USD",
      "charges": "120",
      "exchangeRate": "83.5",
      "inrAmount": "10020",
      "remarks": ""
    },
    {
      "title": "Custom fee",
      "isOther": true,
      "currency": "INR",
      "charges": "500"
    }
  ]
}
```

Update: same body plus `"id": 99`. If `id` is missing, the update endpoint creates a new product.

Accepted aliases:

| Field | Also accepted |
|-------|----------------|
| `user_id` | `userId` |
| `portTypeId` | `port_type_id`, `portType` (name) |
| `icdLocationId` | `icd_location_id` |
| `indianPortId` | `indian_port_id`, `portLocationId`, `port_location_id` |
| `regionId` | `region_id` |
| `countryId` | `country_id` |
| `destinationPortId` | `destination_port_id`, `destinationId`, `destination_id` |
| `containerSizeIds` | `container_size_ids`, `containerSizes`, `container_20_ft`, `container_40_ft` |
| `charges` | `particulars`; extra rows in `others` / `other_charges` |
| `additionalInformation` | `additional_information` |

Charge row aliases: `titleId` / `title_id`, `charges` / `rate` / `amount`, `exchangeRate` / `exc`, `inrAmount` / `inr`, `isOther` / `is_other`.

If `title` does not match a master title, it is stored as **Other**.

### Validation

Required:

- At least one container size (20 FT and/or 40 FT)
- At least one charge with a title and amount

Optional: port type, ICD, Indian port, region, country, destination port, additional information.

### Product response (`data`)

```json
{
  "id": 99,
  "userId": 123,
  "portTypeId": 1,
  "portType": "Sea Port",
  "icdLocationId": null,
  "icdLocation": null,
  "indianPortId": 5,
  "indianPort": "Mundra",
  "portLocation": "Mundra",
  "regionId": 1,
  "region": "Africa",
  "countryId": 10,
  "country": "Algeria",
  "destinationPortId": 44,
  "destination": "Algeirs",
  "destinationPort": "Algeirs",
  "containerSizeIds": [1, 2],
  "containerSizes": [
    { "id": 1, "size": 20, "label": "20 FT" },
    { "id": 2, "size": 40, "label": "40 FT" }
  ],
  "additionalInformation": "Optional notes",
  "status": 0,
  "charges": [
    {
      "id": 1,
      "titleId": 1,
      "title": "Ori THC",
      "currency": "USD",
      "charges": "120",
      "exchangeRate": "83.5",
      "exc": "83.5",
      "inr": "10020",
      "inrAmount": "10020",
      "remarks": null,
      "isOther": false,
      "sortOrder": 0
    }
  ],
  "particulars": [],
  "totalUsd": "120",
  "totalInr": "10520",
  "updatedAt": "15 September 2026"
}
```

`particulars` is the same array as `charges` (alias for clearing-agent-style UI).

| `status` | Meaning |
|----------|---------|
| `0` | Pending admin verify (not shown in public catalog) |
| `1` | Verified / live |

New products are always `status: 0`.

### HTTP codes

| Code | When |
|------|------|
| `200` | Success |
| `401` | Missing/invalid token |
| `403` | `user_id` is not the logged-in user, or product is not owned |
| `404` | Product not found |
| `422` | Validation failed (`errors` object) |

---

## 3. Public catalog (buyers)

Same portal token as other vendor catalog APIs.

| Method | URL | Purpose |
|--------|-----|---------|
| `GET` | `/api/web/vendor/type` | Vendor category list |
| `GET` | `/api/web/vendor/list/{vendorType}` | Vendors of that type |
| `GET` | `/api/web/vendor/products/{id}` | Auto-detect kind; forwarder vendors return forwarder products |
| `GET` | `/api/web/vendor/forwarder-charges/{id}` | Forwarder-only alias |
| `GET` | `/api/web/vendor/forwarder/{id}` | Same as above |

`{id}` on catalog routes: business id, user id, or (on the forwarder aliases) a product id.

Only **verified** products (`status = 1`) that have charges are returned.

```json
{
  "status": true,
  "message": "Vendor products fetched successfully.",
  "vendor": {
    "id": 50,
    "company_name": "ABC Forwarding",
    "product": null,
    "contactPerson": "Amit",
    "contactMobile": "9999999999",
    "address": "Mundra",
    "recommended": 0,
    "has_products": true,
    "vendorKind": "forwarder"
  },
  "data": [ { "id": 99, "userId": 123, "status": 1 } ],
  "imageBasePath": null
}
```

`vendorKind` is `"forwarder"`. There are no product images (`imageBasePath` is `null`).

---

## Suggested UI flow

1. Load masters in parallel: port types, Indian ports, container sizes, charge titles, destination regions.
2. If port type is ICD, also load ICD locations.
3. Region → countries → destination ports.
4. Vendor selects container sizes and fills charge rows (master titles + optional Other).
5. `POST` create. Show as pending until admin verifies.
6. Vendor dashboard: list / edit / delete via portal CRUD.
7. Buyer catalog: `GET /api/web/vendor/products/{businessId}` or the forwarder alias.

---

## Endpoint map

```
GET  /api/get/port/types
GET  /api/get/icd/locations
GET  /api/get/indian/ports
GET  /api/get/container/sizes
GET  /api/get/currencies
GET  /api/get/forwarder/charge-titles
GET  /api/get/destination/regions
GET  /api/get/destination/countries/{regionId}
GET  /api/get/destination/ports?region_id=&country_id=

POST /api/portal/web/forwarder-product/create
POST /api/portal/web/forwarder-product/update
GET  /api/portal/web/forwarder-product/list/{userId}
GET  /api/portal/web/forwarder-product/{id}
DELETE /api/portal/web/forwarder-product/{id}

GET  /api/web/vendor/products/{id}
GET  /api/web/vendor/forwarder-charges/{id}
GET  /api/web/vendor/forwarder/{id}
```
