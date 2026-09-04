/**
 * Lab Equipment & Machinery Equipment vendor API — frontend export
 * Base: /api  |  Portal: /api/portal
 * Auth: Authorization: Bearer <token>  OR  X-API-TOKEN: <token>
 *
 * Preferred: variants[] rows (equipmentId + rate + description + catalogue).
 * Also accepted (single product UI): flat fields user_id, equipmentId|machineryName|name,
 * rate, description, catalogue|image — API wraps them into one variants[0] row.
 */

export const API_BASE = '/api';
export const PORTAL_BASE = '/api/portal';

export type EquipmentVendorKind = 'lab_equipment' | 'machinery_equipment';

export interface ApiResponse<T> {
  status: boolean;
  message?: string;
  data?: T;
  errors?: Record<string, string[]>;
  imageBasePath?: string | null;
}

export interface EquipmentMasterItem {
  id: number;
  name: string;
  description?: string | null;
}

/** One row of the "New Product" form */
export interface EquipmentVariantInput {
  id?: number;
  equipmentId: number;
  rate: number | string;
  description?: string | null;
  catalogue?: File | null;
  existingCatalogue?: string | null;
}

export interface EquipmentVariant {
  id: number;
  equipmentId: number | null;
  equipmentName: string | null;
  rate: string | null;
  description: string | null;
  catalogue: string | null;
  catalogueUrl: string | null;
  sortOrder: number;
}

export interface EquipmentProduct {
  id: number;
  userId: number;
  status: 0 | 1;
  variants: EquipmentVariant[];
}

export interface EquipmentProductPayload {
  id?: number;
  user_id: number;
  variants: EquipmentVariantInput[];
}

export const EquipmentEndpoints = {
  labEquipments: () => `${API_BASE}/get/lab/equipments`,
  machineryEquipments: () => `${API_BASE}/get/machinery/equipments`,

  vendorList: (vendorType: number | string) => `${API_BASE}/web/vendor/list/${vendorType}`,
  vendorProducts: (businessId: number | string) => `${API_BASE}/web/vendor/products/${businessId}`,

  lab: {
    create: () => `${PORTAL_BASE}/web/lab-equipment-product/create`,
    update: () => `${PORTAL_BASE}/web/lab-equipment-product/update`,
    list: (userId: number | string) => `${PORTAL_BASE}/web/lab-equipment-product/list/${userId}`,
    show: (id: number | string) => `${PORTAL_BASE}/web/lab-equipment-product/${id}`,
    delete: (id: number | string) => `${PORTAL_BASE}/web/lab-equipment-product/${id}`,
    deleteCatalogue: (variantId: number | string) =>
      `${PORTAL_BASE}/web/lab-equipment-product/catalogue/${variantId}`,
  },

  machinery: {
    create: () => `${PORTAL_BASE}/web/machinery-equipment-product/create`,
    update: () => `${PORTAL_BASE}/web/machinery-equipment-product/update`,
    list: (userId: number | string) => `${PORTAL_BASE}/web/machinery-equipment-product/list/${userId}`,
    show: (id: number | string) => `${PORTAL_BASE}/web/machinery-equipment-product/${id}`,
    delete: (id: number | string) => `${PORTAL_BASE}/web/machinery-equipment-product/${id}`,
    deleteCatalogue: (variantId: number | string) =>
      `${PORTAL_BASE}/web/machinery-equipment-product/catalogue/${variantId}`,
  },
} as const;

/** Multipart body for create/update with catalogue uploads */
export function buildEquipmentFormData(payload: EquipmentProductPayload): FormData {
  const form = new FormData();
  if (payload.id != null) form.append('id', String(payload.id));
  form.append('user_id', String(payload.user_id));

  payload.variants.forEach((variant, index) => {
    if (variant.id != null) form.append(`variants[${index}][id]`, String(variant.id));
    form.append(`variants[${index}][equipmentId]`, String(variant.equipmentId));
    form.append(`variants[${index}][rate]`, String(variant.rate));
    if (variant.description) form.append(`variants[${index}][description]`, variant.description);
    if (variant.existingCatalogue) {
      form.append(`variants[${index}][existingCatalogue]`, variant.existingCatalogue);
    }
    if (variant.catalogue instanceof File) {
      form.append(`variants[${index}][catalogue]`, variant.catalogue);
    }
  });

  return form;
}
