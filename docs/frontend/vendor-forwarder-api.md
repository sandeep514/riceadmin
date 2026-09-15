# Forwarder vendor API (frontend)

Use this spec to build the forwarder vendor form, dashboard, and buyer catalog.

- Base: `/api`
- Portal: `/api/portal`
- Auth: `Authorization: Bearer <token>` **or** `X-API-TOKEN: <token>`
- Snake_case and camelCase keys are both accepted on create/update.

---

## Auth

| Area | Auth |
|------|------|
| Master dropdowns (`GET /api/get/...`) | None |
| Portal CRUD (`/api/portal/web/forwarder-product/...`) | Required |
| Public catalog (`/api/web/vendor/...`) | Required |

`user_id` on create must match the authenticated user.

---

## How charges work

There are two kinds of charge rows:

| Kind | Source | How frontend sends it | Saved to master? |
|------|--------|------------------------|------------------|
| **Required (admin)** | `GET /api/get/forwarder/charge-types` | `titleId` + amount (default `"0"`) | Already in master |
| **Additional (vendor)** | Vendor types a custom title | `title` + `isOther: true`, **no** `titleId` | **No** — product only |

Admin manages required types in **Service Providers → Masters → Forwarder Charge Titles**.  
If a required row is omitted on save, the API still stores it with amount `"0"`.

---

## 1. Master dropdowns

Load these to fill the create/edit form.

| Method | URL | Use for |
|--------|-----|---------|
| `GET` | `/api/get/port/types` | Port type (ICD / Sea Port) |
| `GET` | `/api/get/icd/locations` | ICD location (when port type is ICD) |
| `GET` | `/api/get/indian/ports` | Indian / origin port |
| `GET` | `/api/get/container/sizes` | Container type (20 FT / 40 FT) |
| `GET` | `/api/get/currencies` | Charge currency (INR, USD, …) |
| `GET` | `/api/get/forwarder/charge-types` | Required charge types (default amount `0`) |
| `GET` | `/api/get/forwarder/charge-titles` | Same payload as charge-types (legacy path) |
| `GET` | `/api/get/destination/regions` | Destination region |
| `GET` | `/api/get/destination/countries/{regionId}` | Countries for a region |
| `GET` | `/api/get/destination/ports` | Sea ports |

### Destination cascade

1. `GET /api/get/destination/regions`
2. `GET /api/get/destination/countries/{regionId}`
3. `GET /api/get/destination/ports?region_id={id}&country_id={id}`

Query aliases: `regionId`, `countryId`.  
Empty lists mean destination masters have not been imported yet. Invalid `regionId` → `404`.

### Charge types

`GET /api/get/forwarder/charge-types`

Prefill every `isRequired: true` row with `charges: "0"`. Vendor can change the amount. Extra vendor rows use `isOther: true` and are not added to this master.

```json
{
  "status": true,
  "message": "Forwarder charge types fetched successfully.",
  "data": [
    {
      "id": 1,
      "name": "Ori THC",
      "title": "Ori THC",
      "description": null,
      "isRequired": true,
      "required": true,
      "defaultCharges": "0",
      "isOther": false
    }
  ],
  "required": [
    {
      "id": 1,
      "name": "Ori THC",
      "title": "Ori THC",
      "description": null,
      "isRequired": true,
      "required": true,
      "defaultCharges": "0",
      "isOther": false
    }
  ],
  "note": "Prefill required rows at 0. Vendor additional charges (isOther=true) are stored only on the product and are not added to this master."
}
```

Use `data` (all active types) or `required` (required only). `isRequired` and `required` are the same flag.

### Currencies

`GET /api/get/currencies`

```json
{
  "status": true,
  "message": "Currencies fetched successfully.",
  "data": [
    { "id": 1, "name": "INR", "code": "INR", "description": null },
    { "id": 2, "name": "USD", "code": "USD", "description": null }
  ]
}
```

Send `currency` as the code (`INR` / `USD`). Only active currencies are returned.

### Container sizes

`GET /api/get/container/sizes`

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

`GET /api/get/destination/ports?region_id=1&country_id=10`

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

---

## 2. Vendor product CRUD (logged-in forwarder)

All under `/api/portal`. Send the portal token.

| Method | URL | Purpose |
|--------|-----|---------|
| `POST` | `/api/portal/web/forwarder-product/create` | Create (`status` starts at `0`) |
| `POST` | `/api/portal/web/forwarder-product/update` | Update (`id` required) |
| `GET` | `/api/portal/web/forwarder-product/list/{userId}` | List that vendor’s products |
| `GET` | `/api/portal/web/forwarder-product/{id}` | Fetch one product |
| `DELETE` | `/api/portal/web/forwarder-product/{id}` | Delete product |

Content-Type: `application/json` (no images / multipart).

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

Update: same body plus `"id": 99`. Missing `id` on update creates a new product.

Charge rules:

- Required master rows: send `titleId`. Amount may be `"0"`.
- Additional vendor rows: send `title` + `isOther: true`, **do not** send `titleId`. These are not written to the master.
- Extra additional rows can also go in `others` / `other_charges` (same shape; API marks them `isOther`).
- If a required `titleId` is omitted, the API still saves that type at `"0"`.
- A custom `title` that does not match a master name is stored as Other.

### Field aliases

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
| `charges` | `particulars`; extras in `others` / `other_charges` |
| `additionalInformation` | `additional_information` |

Charge row aliases: `titleId` / `title_id`, `charges` / `rate` / `amount`, `exchangeRate` / `exc` / `exchange`, `inrAmount` / `inr`, `isOther` / `is_other`.

### Validation

Must send:

- At least one container size (20 FT and/or 40 FT)

Always saved:

- Every required master charge type (amount `0` if omitted)

Optional: port type, ICD, Indian port, region, country, destination port, additional charges, additional information.

### Product response (`data`)

List, show, create, and update all return this shape (list wraps it in an array).

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
      "isRequired": true,
      "sortOrder": 0
    },
    {
      "id": 2,
      "titleId": null,
      "title": "Custom fee",
      "currency": "INR",
      "charges": "500",
      "exchangeRate": "-",
      "exc": "-",
      "inr": "500",
      "inrAmount": "500",
      "remarks": null,
      "isOther": true,
      "isRequired": false,
      "sortOrder": 1
    }
  ],
  "totalUsd": "120",
  "totalInr": "10520",
  "updatedAt": "15 September 2026"
}
```

`particulars` is the same array as `charges`. On edit, keep required rows (`isRequired: true`) and allow add/remove only for `isOther: true` rows.

| `status` | Meaning |
|----------|---------|
| `0` | Pending admin verify (hidden from public catalog) |
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

`{id}`: business id, user id, or (on the forwarder aliases) a product id.

Only **verified** products (`status = 1`) that have charges are returned. `vendorKind` is `"forwarder"`. No product images (`imageBasePath` is `null`).

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
  "data": [{ "id": 99, "userId": 123, "status": 1 }],
  "imageBasePath": null
}
```

`data[]` items use the same product shape as section 2.

---

## Suggested UI flow

1. Load in parallel: port types, Indian ports, container sizes, currencies, charge types, destination regions.
2. If port type is ICD, load ICD locations.
3. Region → countries → destination ports.
4. Prefill required charge types at `0`. Let the vendor add extra `isOther` rows.
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
GET  /api/get/forwarder/charge-types
GET  /api/get/forwarder/charge-titles
GET  /api/get/destination/regions
GET  /api/get/destination/countries/{regionId}
GET  /api/get/destination/ports?region_id=&country_id=

POST   /api/portal/web/forwarder-product/create
POST   /api/portal/web/forwarder-product/update
GET    /api/portal/web/forwarder-product/list/{userId}
GET    /api/portal/web/forwarder-product/{id}
DELETE /api/portal/web/forwarder-product/{id}

GET  /api/web/vendor/products/{id}
GET  /api/web/vendor/forwarder-charges/{id}
GET  /api/web/vendor/forwarder/{id}
```
