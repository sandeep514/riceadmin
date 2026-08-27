/**
 * Cartoon & Cylinder vendor API — frontend export
 * Base: /api  |  Portal: /api/portal
 * Auth: Authorization: Bearer <token>  OR  X-API-TOKEN: <token>
 */

export const API_BASE = '/api';
export const PORTAL_BASE = '/api/portal';

export type VendorKind = 'cartoon' | 'cylinder' | 'rice_bag';

export interface ApiResponse<T> {
  status: boolean;
  message?: string;
  data?: T;
  errors?: Record<string, string[]>;
  imageBasePath?: string | null;
  received_keys?: string[];
}

export interface VendorTypeItem {
  id: number;
  category: string;
  image?: string | null;
}

export interface VendorListItem {
  id: number;
  company_name: string;
  product: string;
  contactPerson: string;
  contactMobile: string;
  address: string;
  recommended: number;
  has_products: boolean;
}

export interface VendorInfo {
  id: number;
  company_name: string;
  product: string;
  contactPerson: string;
  contactMobile: string;
  address: string;
  recommended: number;
  has_products: boolean;
  vendorKind?: VendorKind | null;
}

export interface ProductVariant {
  id?: number;
  packingSizeId: number;
  packingSize?: string | null;
  rate: number | string;
  gst?: number | string | null;
  totalPrice?: number | string | null;
  bagSize?: string | null;
  bagWeight?: string | null;
  image?: string | File | null;
  imageUrl?: string | null;
  existingImage?: string | null;
  sortOrder?: number;
}

export interface CartoonType {
  id: number;
  type: string;
  description?: string | null;
}

export interface CylinderType {
  id: number;
  type: string;
  description?: string | null;
}

export interface PackingSize {
  id: number;
  size: string;
  packing?: string | null;
  order?: number;
  status?: number;
}

// —— Cartoon ——

export interface CartoonProductPayload {
  user_id: number;
  cartoon_type_id: number;
  specification?: string | null;
  description?: string | null;
  additional_information?: string | null;
  variants: ProductVariant[];
  id?: number;
}

export interface CartoonProduct {
  id: number;
  userId: number;
  cartoonTypeId: number | null;
  specification: string | null;
  description: string | null;
  additionalInformation: string | null;
  status: 0 | 1;
  variants: Required<Pick<ProductVariant, 'id' | 'packingSizeId'>> & ProductVariant[];
}

export interface CartoonCatalogProduct {
  id: number;
  cartoonTypeId: number | null;
  cartoonTypeName: string | null;
  specification: string | null;
  description: string | null;
  additionalInformation: string | null;
  variants: ProductVariant[];
}

// —— Cylinder ——

export interface CylinderProductPayload {
  user_id: number;
  cylinder_type_id: number;
  specification?: string | null;
  description?: string | null;
  additional_information?: string | null;
  variants: ProductVariant[];
  id?: number;
}

export interface CylinderProduct {
  id: number;
  userId: number;
  cylinderTypeId: number | null;
  specification: string | null;
  description: string | null;
  additionalInformation: string | null;
  status: 0 | 1;
  variants: Required<Pick<ProductVariant, 'id' | 'packingSizeId'>> & ProductVariant[];
}

export interface CylinderCatalogProduct {
  id: number;
  cylinderTypeId: number | null;
  cylinderTypeName: string | null;
  specification: string | null;
  description: string | null;
  additionalInformation: string | null;
  variants: ProductVariant[];
}

// —— Endpoints ——

export const CartoonCylinderEndpoints = {
  // Shared masters
  packingSizes: () => `${API_BASE}/get/packing/size`,
  cartoonTypes: () => `${API_BASE}/get/cartoon/types`,
  cylinderTypes: () => `${API_BASE}/get/cylinder/types`,

  // Public vendor catalog
  vendorTypes: () => `${API_BASE}/web/vendor/type`,
  vendorList: (vendorType: number | string) => `${API_BASE}/web/vendor/list/${vendorType}`,
  vendorProducts: (businessId: number | string) => `${API_BASE}/web/vendor/products/${businessId}`,

  // Cartoon portal CRUD
  cartoon: {
    create: () => `${PORTAL_BASE}/web/cartoon-product/create`,
    update: () => `${PORTAL_BASE}/web/cartoon-product/update`,
    list: (userId: number | string) => `${PORTAL_BASE}/web/cartoon-product/list/${userId}`,
    show: (id: number | string) => `${PORTAL_BASE}/web/cartoon-product/${id}`,
    delete: (id: number | string) => `${PORTAL_BASE}/web/cartoon-product/${id}`,
    deleteImage: (variantId: number | string) => `${PORTAL_BASE}/web/cartoon-product/image/${variantId}`,
  },

  // Cylinder portal CRUD
  cylinder: {
    create: () => `${PORTAL_BASE}/web/cylinder-product/create`,
    update: () => `${PORTAL_BASE}/web/cylinder-product/update`,
    list: (userId: number | string) => `${PORTAL_BASE}/web/cylinder-product/list/${userId}`,
    show: (id: number | string) => `${PORTAL_BASE}/web/cylinder-product/${id}`,
    delete: (id: number | string) => `${PORTAL_BASE}/web/cylinder-product/${id}`,
    deleteImage: (variantId: number | string) => `${PORTAL_BASE}/web/cylinder-product/image/${variantId}`,
  },
} as const;

/** Build multipart FormData for create/update with variant images */
export function buildVendorProductFormData(
  payload: CartoonProductPayload | CylinderProductPayload,
  typeKey: 'cartoon_type_id' | 'cylinder_type_id'
): FormData {
  const form = new FormData();
  if (payload.id != null) form.append('id', String(payload.id));
  form.append('user_id', String(payload.user_id));
  form.append(typeKey, String((payload as Record<string, unknown>)[typeKey]));

  if (payload.specification) form.append('specification', payload.specification);
  if (payload.description) form.append('description', payload.description);
  if (payload.additional_information) form.append('additional_information', payload.additional_information);

  payload.variants.forEach((variant, index) => {
    if (variant.id != null) form.append(`variants[${index}][id]`, String(variant.id));
    form.append(`variants[${index}][packingSizeId]`, String(variant.packingSizeId));
    if (variant.packingSize) form.append(`variants[${index}][packingSize]`, variant.packingSize);
    form.append(`variants[${index}][rate]`, String(variant.rate));
    if (variant.gst != null) form.append(`variants[${index}][gst]`, String(variant.gst));
    if (variant.totalPrice != null) form.append(`variants[${index}][totalPrice]`, String(variant.totalPrice));
    if (variant.bagSize) form.append(`variants[${index}][bagSize]`, variant.bagSize);
    if (variant.bagWeight) form.append(`variants[${index}][bagWeight]`, variant.bagWeight);
    if (variant.existingImage) form.append(`variants[${index}][existingImage]`, variant.existingImage);
    if (variant.image instanceof File) form.append(`variants[${index}][image]`, variant.image);
  });

  return form;
}

export function buildCartoonFormData(payload: CartoonProductPayload): FormData {
  return buildVendorProductFormData(payload, 'cartoon_type_id');
}

export function buildCylinderFormData(payload: CylinderProductPayload): FormData {
  return buildVendorProductFormData(payload, 'cylinder_type_id');
}

/** Example fetch helper */
export async function apiGet<T>(url: string, token: string): Promise<ApiResponse<T>> {
  const res = await fetch(url, {
    headers: {
      Authorization: `Bearer ${token}`,
      Accept: 'application/json',
    },
  });
  return res.json();
}

export async function apiPostJson<T>(url: string, token: string, body: unknown): Promise<ApiResponse<T>> {
  const res = await fetch(url, {
    method: 'POST',
    headers: {
      Authorization: `Bearer ${token}`,
      Accept: 'application/json',
      'Content-Type': 'application/json',
    },
    body: JSON.stringify(body),
  });
  return res.json();
}

export async function apiPostForm<T>(url: string, token: string, form: FormData): Promise<ApiResponse<T>> {
  const res = await fetch(url, {
    method: 'POST',
    headers: {
      Authorization: `Bearer ${token}`,
      Accept: 'application/json',
    },
    body: form,
  });
  return res.json();
}

export async function apiDelete<T>(url: string, token: string): Promise<ApiResponse<T>> {
  const res = await fetch(url, {
    method: 'DELETE',
    headers: {
      Authorization: `Bearer ${token}`,
      Accept: 'application/json',
    },
  });
  return res.json();
}
