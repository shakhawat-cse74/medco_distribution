<?php

namespace App\Http\Controllers\API;

use App\Models\Brand;
use App\Models\Product;
use App\Models\Category;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    use ApiResponse;

    /**
     * Get Products List
     * Supports: search, category_id/slug, brand_id/slug, featured, promotion,
     * price range (min_price, max_price), in_stock, sort, per_page
     */
    public function index(Request $request)
    {
        try {
            $query = Product::where('is_active', 1)
                ->with(['brand', 'category', 'unit']);

            // 1. Search Query
            if ($request->filled('search')) {
                $search = trim($request->search);
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")
                      ->orWhere('code', 'LIKE', "%{$search}%")
                      ->orWhere('slug', 'LIKE', "%{$search}%")
                      ->orWhere('tags', 'LIKE', "%{$search}%")
                      ->orWhere('short_description', 'LIKE', "%{$search}%");
                });
            }

            // 2. Category Filter
            if ($request->filled('category_id')) {
                $catId = $request->category_id;
                $categoryIds = Category::where('parent_id', $catId)->pluck('id')->push($catId)->toArray();
                $query->whereIn('category_id', $categoryIds);
            } elseif ($request->filled('category_slug')) {
                $cat = Category::where('slug', $request->category_slug)->first();
                if ($cat) {
                    $categoryIds = Category::where('parent_id', $cat->id)->pluck('id')->push($cat->id)->toArray();
                    $query->whereIn('category_id', $categoryIds);
                }
            }

            // 3. Brand Filter
            if ($request->filled('brand_id')) {
                $query->where('brand_id', $request->brand_id);
            } elseif ($request->filled('brand_slug')) {
                $brand = Brand::where('slug', $request->brand_slug)->first();
                if ($brand) {
                    $query->where('brand_id', $brand->id);
                }
            }

            // 4. Featured Filter
            if ($request->has('featured')) {
                $featured = filter_var($request->featured, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                if ($featured !== null) {
                    $query->where('featured', $featured ? 1 : 0);
                }
            }

            // 5. Promotional / On Sale Filter
            if ($request->has('promotion') || $request->has('on_sale')) {
                $promotion = filter_var($request->input('promotion', $request->input('on_sale')), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                if ($promotion !== null) {
                    $query->where('promotion', $promotion ? 1 : 0);
                }
            }

            // 6. Price Range Filter
            if ($request->filled('min_price')) {
                $query->where('price', '>=', (float) $request->min_price);
            }
            if ($request->filled('max_price')) {
                $query->where('price', '<=', (float) $request->max_price);
            }

            // 7. In Stock Filter
            if ($request->has('in_stock')) {
                $inStock = filter_var($request->in_stock, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                if ($inStock === true) {
                    $query->where(function ($q) {
                        $q->where('in_stock', 1)->orWhere('qty', '>', 0);
                    });
                }
            }

            // 8. Sorting
            $sort = $request->input('sort', 'latest');
            $query = $this->applySorting($query, $sort);

            // 9. Pagination
            $perPage = (int) $request->input('per_page', 15);
            $products = $query->paginate($perPage);

            $formatted = collect($products->items())->map(fn($p) => $this->formatProduct($p));

            return $this->success([
                'products'   => $formatted,
                'pagination' => [
                    'total'        => $products->total(),
                    'per_page'     => $products->perPage(),
                    'current_page' => $products->currentPage(),
                    'last_page'    => $products->lastPage(),
                ],
            ], 'Products retrieved successfully');
        } catch (\Throwable $e) {
            return $this->error('Failed to retrieve products: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get Featured Products
     */
    public function featured(Request $request)
    {
        $request->merge(['featured' => true]);
        return $this->index($request);
    }

    /**
     * Get Promotional / Flash Sale Products
     */
    public function promotions(Request $request)
    {
        $request->merge(['promotion' => true]);
        return $this->index($request);
    }

    public function promotional(Request $request)
    {
        return $this->promotions($request);
    }

    /**
     * Get Single Product Details by ID or Slug
     */
    public function show($idOrSlug)
    {
        try {
            $product = Product::where('is_active', 1)
                ->where(function ($q) use ($idOrSlug) {
                    if (is_numeric($idOrSlug)) {
                        $q->where('id', $idOrSlug)->orWhere('slug', $idOrSlug);
                    } else {
                        $q->where('slug', $idOrSlug);
                    }
                })
                ->with(['brand', 'category', 'unit', 'variant'])
                ->first();

            if (!$product) {
                return $this->error('Product not found', 404);
            }

            // Related products
            $relatedProducts = Product::where('is_active', 1)
                ->where('id', '!=', $product->id)
                ->where(function ($q) use ($product) {
                    if ($product->category_id) {
                        $q->where('category_id', $product->category_id);
                    }
                    if ($product->brand_id) {
                        $q->orWhere('brand_id', $product->brand_id);
                    }
                })
                ->with(['brand', 'category', 'unit'])
                ->limit(6)
                ->get()
                ->map(fn($p) => $this->formatProduct($p));

            $formatted = $this->formatProductDetails($product, $relatedProducts);

            return $this->success([
                'product' => $formatted,
            ], 'Product details retrieved successfully');
        } catch (\Throwable $e) {
            return $this->error('Failed to retrieve product details: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get Related Products for a product
     */
    public function related($idOrSlug)
    {
        try {
            $product = Product::where('is_active', 1)
                ->where(function ($q) use ($idOrSlug) {
                    if (is_numeric($idOrSlug)) {
                        $q->where('id', $idOrSlug)->orWhere('slug', $idOrSlug);
                    } else {
                        $q->where('slug', $idOrSlug);
                    }
                })
                ->first();

            if (!$product) {
                return $this->error('Product not found', 404);
            }

            $relatedProducts = Product::where('is_active', 1)
                ->where('id', '!=', $product->id)
                ->where(function ($q) use ($product) {
                    if ($product->category_id) {
                        $q->where('category_id', $product->category_id);
                    }
                    if ($product->brand_id) {
                        $q->orWhere('brand_id', $product->brand_id);
                    }
                })
                ->with(['brand', 'category', 'unit'])
                ->limit(10)
                ->get()
                ->map(fn($p) => $this->formatProduct($p));

            return $this->success([
                'related_products' => $relatedProducts,
            ], 'Related products retrieved successfully');
        } catch (\Throwable $e) {
            return $this->error('Failed to retrieve related products: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Apply sorting to query
     */
    public function applySorting($query, $sort)
    {
        switch ($sort) {
            case 'price_low_high':
            case 'price_asc':
                return $query->orderBy('price', 'asc');
            case 'price_high_low':
            case 'price_desc':
                return $query->orderBy('price', 'desc');
            case 'name_asc':
                return $query->orderBy('name', 'asc');
            case 'name_desc':
                return $query->orderBy('name', 'desc');
            case 'featured':
                return $query->orderBy('featured', 'desc')->orderBy('id', 'desc');
            case 'popular':
            case 'latest':
            default:
                return $query->orderBy('id', 'desc');
        }
    }

    /**
     * Format Basic Product Item (for listings)
     */
    public function formatProduct($product)
    {
        $images = $this->parseProductImages($product->image);

        $price = (float) $product->price;
        $isPromo = (bool) $product->promotion;
        $promoPrice = $isPromo && $product->promotion_price ? (float) $product->promotion_price : null;
        $currentPrice = $isPromo && $promoPrice ? $promoPrice : $price;

        $discountPercent = 0;
        if ($isPromo && $promoPrice && $price > 0 && $price > $promoPrice) {
            $discountPercent = round((($price - $promoPrice) / $price) * 100);
        }

        return [
            'id'                => $product->id,
            'name'              => $product->name,
            'slug'              => $product->slug,
            'code'              => $product->code,
            'type'              => $product->type,
            'price'             => $price,
            'promotion_price'   => $promoPrice,
            'current_price'     => $currentPrice,
            'is_promotion'      => $isPromo,
            'discount_percent'  => $discountPercent,
            'qty'               => (float) $product->qty,
            'in_stock'          => (bool) ($product->in_stock ?? ($product->qty > 0)),
            'featured'          => (bool) $product->featured,
            'image'             => $product->image,
            'image_url'         => $images[0] ?? null,
            'images'            => $images,
            'unit'              => $product->unit ? $product->unit->unit_name : null,
            'short_description' => $this->cleanHtmlText($product->short_description),
            'category'          => $product->category ? [
                'id'   => $product->category->id,
                'name' => $product->category->name,
                'slug' => $product->category->slug,
            ] : null,
            'brand'             => $product->brand ? [
                'id'    => $product->brand->id,
                'title' => $product->brand->title,
                'slug'  => $product->brand->slug,
            ] : null,
        ];
    }

    /**
     * Format Comprehensive Product Details
     */
    public function formatProductDetails($product, $relatedProducts = [])
    {
        $base = $this->formatProduct($product);

        $variants = [];
        if ($product->is_variant && $product->relationLoaded('variant')) {
            $variants = $product->variant->map(function ($v) {
                return [
                    'id'               => $v->pivot->id,
                    'variant_id'       => $v->id,
                    'name'             => $v->name,
                    'item_code'        => $v->pivot->item_code,
                    'additional_cost'  => (float) $v->pivot->additional_cost,
                    'additional_price' => (float) $v->pivot->additional_price,
                    'qty'              => (float) $v->pivot->qty,
                ];
            });
        }

        return array_merge($base, [
            'barcode_symbology'    => $product->barcode_symbology,
            'short_description'    => $this->cleanHtmlText($product->short_description),
            'product_details'      => $this->cleanHtmlText($product->product_details),
            'specification'        => $this->cleanHtmlText($product->specification),
            'product_details_html' => $product->product_details,
            'specification_html'   => $product->specification,
            'alert_quantity'       => (float) $product->alert_quantity,
            'is_variant'           => (bool) $product->is_variant,
            'is_batch'             => (bool) $product->is_batch,
            'is_imei'              => (bool) $product->is_imei,
            'tags'                 => $product->tags,
            'warranty'             => $product->warranty,
            'guarantee'            => $product->guarantee,
            'warranty_type'        => $product->warranty_type,
            'guarantee_type'       => $product->guarantee_type,
            'variants'             => $variants,
            'related_products'     => $relatedProducts,
        ]);
    }

    /**
     * Clean HTML tags, decode HTML entities and format text cleanly
     */
    public function cleanHtmlText(?string $html): ?string
    {
        if ($html === null || trim($html) === '') {
            return null;
        }

        // 1. Convert line-break and block tags to newlines
        $text = preg_replace('/<(br|hr)\s*\/?>/i', "\n", $html);
        $text = preg_replace('/<\/(p|div|li|h[1-6]|tr)>/i', "\n", $text);

        // 2. Strip all remaining HTML tags
        $text = strip_tags($text);

        // 3. Decode all HTML entities (e.g., &nbsp;, &amp;, &quot;, &#39;, &lt;, &gt;)
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // 4. Clean non-breaking spaces
        $text = str_replace(["\xc2\xa0", "\u{00a0}", "&nbsp;"], ' ', $text);

        // 5. Trim lines and remove excessive blank lines
        $lines = explode("\n", $text);
        $cleanedLines = [];
        foreach ($lines as $line) {
            $trimmed = trim($line);
            if ($trimmed !== '') {
                $cleanedLines[] = $trimmed;
            }
        }

        $result = implode("\n", $cleanedLines);

        return $result !== '' ? $result : null;
    }

    /**
     * Helper to parse comma separated product images to full URLs
     */
    protected function parseProductImages($rawImages)
    {
        if (empty($rawImages)) {
            return [];
        }

        $list = explode(',', $rawImages);
        $result = [];

        foreach ($list as $img) {
            $img = trim($img);
            if (empty($img)) continue;

            if (Str::startsWith($img, ['http://', 'https://'])) {
                $result[] = $img;
            } elseif (file_exists(public_path('images/product/' . $img))) {
                $result[] = asset('images/product/' . $img);
            } elseif (file_exists(public_path('frontend/images/product/' . $img))) {
                $result[] = asset('frontend/images/product/' . $img);
            } else {
                $result[] = asset('images/product/' . $img);
            }
        }

        return $result;
    }
}
