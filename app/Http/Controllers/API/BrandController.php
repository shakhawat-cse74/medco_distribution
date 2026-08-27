<?php

namespace App\Http\Controllers\API;

use App\Models\Brand;
use App\Models\Product;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;

class BrandController extends Controller
{
    use ApiResponse;

    /**
     * Get All Brands
     * Supports: search, with_count, all / per_page
     */
    public function index(Request $request)
    {
        try {
            $query = Brand::where('is_active', 1);

            // Search
            if ($request->filled('search')) {
                $search = trim($request->search);
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'LIKE', "%{$search}%")
                      ->orWhere('slug', 'LIKE', "%{$search}%")
                      ->orWhere('page_title', 'LIKE', "%{$search}%");
                });
            }

            // Products count
            if ($request->boolean('with_count', true)) {
                $query->withCount(['products' => function ($q) {
                    $q->where('is_active', 1);
                }]);
            }

            // All unpaginated
            if ($request->boolean('all', false)) {
                $brands = $query->orderBy('title', 'asc')->get();
                $formatted = $brands->map(fn($b) => $this->formatBrand($b));
                return $this->success(['brands' => $formatted], 'Brands retrieved successfully');
            }

            $perPage = (int) $request->input('per_page', 15);
            $brands = $query->orderBy('title', 'asc')->paginate($perPage);

            $formatted = collect($brands->items())->map(fn($b) => $this->formatBrand($b));

            return $this->success([
                'brands'     => $formatted,
                'pagination' => [
                    'total'        => $brands->total(),
                    'per_page'     => $brands->perPage(),
                    'current_page' => $brands->currentPage(),
                    'last_page'    => $brands->lastPage(),
                ],
            ], 'Brands retrieved successfully');
        } catch (\Throwable $e) {
            return $this->error('Failed to retrieve brands: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get Single Brand by ID or Slug
     */
    public function show($idOrSlug)
    {
        try {
            $brand = Brand::where('is_active', 1)
                ->where(function ($q) use ($idOrSlug) {
                    if (is_numeric($idOrSlug)) {
                        $q->where('id', $idOrSlug)->orWhere('slug', $idOrSlug);
                    } else {
                        $q->where('slug', $idOrSlug);
                    }
                })
                ->withCount(['products' => function ($q) {
                    $q->where('is_active', 1);
                }])
                ->first();

            if (!$brand) {
                return $this->error('Brand not found', 404);
            }

            return $this->success([
                'brand' => $this->formatBrand($brand),
            ], 'Brand details retrieved successfully');
        } catch (\Throwable $e) {
            return $this->error('Failed to retrieve brand: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get Products of a specific brand
     */
    public function products(Request $request, $idOrSlug)
    {
        try {
            $brand = Brand::where('is_active', 1)
                ->where(function ($q) use ($idOrSlug) {
                    if (is_numeric($idOrSlug)) {
                        $q->where('id', $idOrSlug)->orWhere('slug', $idOrSlug);
                    } else {
                        $q->where('slug', $idOrSlug);
                    }
                })
                ->first();

            if (!$brand) {
                return $this->error('Brand not found', 404);
            }

            $productApi = app(\App\Http\Controllers\API\ProductController::class);
            $query = Product::where('is_active', 1)
                ->where('brand_id', $brand->id)
                ->with(['brand', 'category', 'unit']);

            // Sorting
            $sort = $request->input('sort', 'latest');
            $query = $productApi->applySorting($query, $sort);

            $perPage = (int) $request->input('per_page', 15);
            $products = $query->paginate($perPage);

            $formatted = collect($products->items())->map(fn($p) => $productApi->formatProduct($p));

            return $this->success([
                'brand'      => $this->formatBrand($brand),
                'products'   => $formatted,
                'pagination' => [
                    'total'        => $products->total(),
                    'per_page'     => $products->perPage(),
                    'current_page' => $products->currentPage(),
                    'last_page'    => $products->lastPage(),
                ],
            ], 'Brand products retrieved successfully');
        } catch (\Throwable $e) {
            return $this->error('Failed to retrieve brand products: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Format Brand Resource
     */
    public function formatBrand($brand)
    {
        $imageUrl = null;
        if ($brand->image) {
            if (Str::startsWith($brand->image, ['http://', 'https://'])) {
                $imageUrl = $brand->image;
            } else {
                $imageUrl = asset('images/brand/' . $brand->image);
            }
        }

        $productApi = app(\App\Http\Controllers\API\ProductController::class);

        return [
            'id'                => $brand->id,
            'title'             => $brand->title,
            'slug'              => $brand->slug,
            'image'             => $brand->image,
            'image_url'         => $imageUrl,
            'short_description' => $productApi->cleanHtmlText($brand->short_description),
            'products_count'    => (int) ($brand->products_count ?? 0),
        ];
    }
}
