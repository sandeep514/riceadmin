<?php

namespace App\Http\Controllers;

use App\SitePolicy;
use Illuminate\Http\Request;

/**
 * Public Site Policies API (terms, privacy, disclaimer, return policy, etc.).
 */
class SitePolicyApiController extends Controller
{
    /**
     * List active site policies.
     *
     * Query:
     * - slug (optional) — filter to one policy, e.g. terms_and_conditions
     *
     * Default: all active policies ordered by order_no.
     */
    public function index(Request $request)
    {
        $slug = $request->filled('slug')
            ? strtolower(trim((string) $request->input('slug')))
            : null;

        if ($slug !== null && $slug !== '') {
            $slug = preg_replace('/[^a-z0-9_\-]+/', '', str_replace(' ', '_', $slug));
            if ($slug === '') {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid slug.',
                    'data' => [],
                ], 422);
            }
        } else {
            $slug = null;
        }

        $policies = SitePolicy::query()
            ->where('status', SitePolicy::STATUS_ACTIVE)
            ->when($slug !== null, fn ($q) => $q->where('slug', $slug))
            ->orderByRaw('order_no IS NULL, order_no ASC')
            ->orderBy('id')
            ->get(['id', 'slug', 'title', 'content', 'pdf_path', 'status', 'order_no', 'updated_at']);

        $data = $policies->map(function (SitePolicy $policy) {
            return [
                'id' => $policy->id,
                'slug' => $policy->slug,
                'title' => $policy->title,
                'content' => $policy->content,
                'pdf_path' => $policy->pdf_path,
                'pdf_url' => $policy->pdf_url,
                'status' => (int) $policy->status,
                'order_no' => $policy->order_no,
                'updated_at' => $policy->updated_at
                    ? $policy->updated_at->timezone(config('app.timezone', 'Asia/Kolkata'))->format('Y-m-d H:i:s')
                    : null,
            ];
        })->values();

        return response()->json([
            'status' => true,
            'message' => $data->isEmpty() ? 'No policies found.' : 'Policies fetched successfully.',
            'count' => $data->count(),
            'data' => $data,
        ]);
    }
}
