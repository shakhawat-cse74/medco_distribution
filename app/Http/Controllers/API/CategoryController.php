<?php

namespace App\Http\Controllers\API;

use App\Models\Category;
use App\Models\Product;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    use ApiResponse;

    /**
     * Get All Categories
     * Supports: search, featured, parent_only, with_subcategories, all / per_page
     */
    public function index(Request $request)
    {
        try {
            $query = Category::where('is_active', 1);

            // Search filter
            if ($request->filled('search')) {
                $search = trim($request->search);
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")
                      ->orWhere('slug', 'LIKE', "%{$search}%")
                      ->orWhere('page_title', 'LIKE', "%{$search}%");
                });
            }

            // Featured filter
            if ($request->has('featured')) {
                $featured = filter_var($request->featured, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                if ($featured !== null) {
                    $query->where('featured', $featured ? 1 : 0);
                }
            }

            // Parent-only (Top-level) or Subcategories
            $parentOnly = $request->boolean('parent_only', true);
            if ($parentOnly && !$request->filled('search')) {
                $query->whereNull('parent_id')->orWhere('parent_id', 0);
            }

            // Product count
            if ($request->boolean('with_count', true)) {
                $query->withCount(['products' => function ($q) {
                    $q->where('is_active', 1);
                }]);
            }

            // Include nested subcategories
            $withSub = $request->boolean('with_subcategories', true);
            if ($withSub) {
                $query->with(['subcategories' => function ($q) {
                    $q->where('is_active', 1)->withCount(['products' => function ($pq) {
                        $pq->where('is_active', 1);
                    }]);
                }]);
            }

            // Unpaginated if all=1 or all=true
            if ($request->boolean('all', false)) {
                $categories = $query->orderBy('name', 'asc')->get();
                $formatted = $categories->map(fn($c) => $this->formatCategory($c, $withSub));
                return $this->success(['categories' => $formatted], 'Categories retrieved successfully');
            }

            $perPage = (int) $request->input('per_page', 15);
            $categories = $query->orderBy('name', 'asc')->paginate($perPage);

            $formatted = collect($categories->items())->map(fn($c) => $this->formatCategory($c, $withSub));

            return $this->success([
                'categories' => $formatted,
                'pagination' => [
                    'total'        => $categories->total(),
                    'per_page'     => $categories->perPage(),
                    'current_page' => $categories->currentPage(),
                    'last_page'    => $categories->lastPage(),
                ],
            ], 'Categories retrieved successfully');
        } catch (\Throwable $e) {
            return $this->error('Failed to retrieve categories: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get Single Category by ID or Slug
     */
    public function show($idOrSlug)
    {
        try {
            $category = Category::where('is_active', 1)
                ->where(function ($q) use ($idOrSlug) {
                    if (is_numeric($idOrSlug)) {
                        $q->where('id', $idOrSlug)->orWhere('slug', $idOrSlug);
                    } else {
                        $q->where('slug', $idOrSlug);
                    }
                })
                ->with(['parent', 'subcategories' => function ($q) {
                    $q->where('is_active', 1)->withCount(['products' => function ($pq) {
                        $pq->where('is_active', 1);
                    }]);
                }])
                ->withCount(['products' => function ($q) {
                    $q->where('is_active', 1);
                }])
                ->first();

            if (!$category) {
                return $this->error('Category not found', 404);
            }

            return $this->success([
                'category' => $this->formatCategory($category, true, true),
            ], 'Category details retrieved successfully');
        } catch (\Throwable $e) {
            return $this->error('Failed to retrieve category: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get Products of a specific category
     */
    public function products(Request $request, $idOrSlug)
    {
        try {
            $category = Category::where('is_active', 1)
                ->where(function ($q) use ($idOrSlug) {
                    if (is_numeric($idOrSlug)) {
                        $q->where('id', $idOrSlug)->orWhere('slug', $idOrSlug);
                    } else {
                        $q->where('slug', $idOrSlug);
                    }
                })
                ->first();

            if (!$category) {
                return $this->error('Category not found', 404);
            }

            // Include subcategory IDs as well
            $categoryIds = Category::where('parent_id', $category->id)->pluck('id')->push($category->id)->toArray();

            $productApi = app(\App\Http\Controllers\API\ProductController::class);
            $query = Product::where('is_active', 1)
                ->whereIn('category_id', $categoryIds)
                ->with(['brand', 'category', 'unit']);

            // Sorting
            $sort = $request->input('sort', 'latest');
            $query = $productApi->applySorting($query, $sort);

            $perPage = (int) $request->input('per_page', 15);
            $products = $query->paginate($perPage);

            $formatted = collect($products->items())->map(fn($p) => $productApi->formatProduct($p));

            return $this->success([
                'category'   => $this->formatCategory($category, false),
                'products'   => $formatted,
                'pagination' => [
                    'total'        => $products->total(),
                    'per_page'     => $products->perPage(),
                    'current_page' => $products->currentPage(),
                    'last_page'    => $products->lastPage(),
                ],
            ], 'Category products retrieved successfully');
        } catch (\Throwable $e) {
            return $this->error('Failed to retrieve category products: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Format Category Resource
     */
    public function formatCategory($category, $includeSub = true, $includeParent = false)
    {
        $imageUrl = null;
        if ($category->image) {
            if (Str::startsWith($category->image, ['http://', 'https://'])) {
                $imageUrl = $category->image;
            } else {
                $imageUrl = asset('images/category/' . $category->image);
            }
        }

        $productApi = app(\App\Http\Controllers\API\ProductController::class);

        $data = [
            'id'                => $category->id,
            'name'              => $category->name,
            'slug'              => $category->slug,
            'image'             => $category->image,
            'image_url'         => $imageUrl,
            'icon'              => $category->icon,
            'parent_id'         => $category->parent_id,
            'featured'          => (bool) $category->featured,
            'short_description' => $productApi->cleanHtmlText($category->short_description),
            'products_count'    => (int) ($category->products_count ?? 0),
        ];

        if ($includeParent && $category->parent) {
            $data['parent'] = [
                'id'   => $category->parent->id,
                'name' => $category->parent->name,
                'slug' => $category->parent->slug,
            ];
        }

        if ($includeSub && $category->relationLoaded('subcategories')) {
            $data['subcategories'] = $category->subcategories->map(function ($sub) {
                return $this->formatCategory($sub, false);
            });
        }

        return $data;
    }
}
