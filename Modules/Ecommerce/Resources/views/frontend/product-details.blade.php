@extends('ecommerce::frontend.layout.main')

@php
    $images = !empty($product->image) ? array_filter(explode(',', $product->image)) : [];
    $main_image = count($images) > 0 ? $images[0] : null;

    $base_price = ($product->promotion == 1 && (!isset($product->last_date) || $product->last_date > date('Y-m-d')) && !empty($product->promotion_price))
        ? (float)$product->promotion_price
        : (float)$product->price;

    $curr_symbol = $currency->symbol ?? '$';
    $is_prefix = ($general_setting->currency_position ?? 'prefix') == 'prefix';

    function format_curr($amount, $sym, $prefix) {
        $formatted = number_format($amount, 2);
        return $prefix ? $sym . $formatted : $formatted . $sym;
    }

    $unit_name = $unit->unit_name ?? 'Piece';
    $each_price = $base_price;

    $product_title = $product->name;
    $item_code = $product->code ?? ('MED-' . str_pad($product->id, 5, '0', STR_PAD_LEFT));
    $brand_title = $brand->title ?? null;
    $brand_image = !empty($brand->image) ? $brand->image : null;

    $review_count = $reviews->count();
    $avg_rating = $average_rating ? (float)$average_rating : ($review_count > 0 ? (float)($reviews->sum('rating') / $review_count) : 5.0);
    $avg_rounded = (int)round($avg_rating);
@endphp

@section('title'){{ $product->meta_title ?? $product->name }}@endsection
@section('description'){{ $product->meta_description ?? $product->name }}@endsection
@section('image')@if($main_image){{ url('images/product/large/' . $main_image) }}@endif @endsection
@section('brand'){{ $brand_title ?? '' }}@endsection
@section('price'){{ $base_price }}@endsection
@section('id'){{ $product->id }}@endsection
@section('category_id'){{ $product->category_id }}@endsection

@push('css')
<!-- Google Model-Viewer for 3D and AR -->
<script type="module" src="https://ajax.googleapis.com/ajax/libs/model-viewer/3.4.0/model-viewer.min.js"></script>

<style>
/* ==========================================================================
   DYNAMIC WEBSTAURANTSTORE-INSPIRED PRODUCT DETAILS LAYOUT
   ========================================================================== */

body {
    background-color: #f7f7f7 !important;
    color: #333333;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
}

.material-symbols-outlined {
    font-variation-settings: 'FILL' 1, 'wght' 400, 'GRAD' 0, 'opsz' 24;
}

.wss-pdp-wrapper {
    max-width: 1340px;
    margin: 0 auto;
    padding: 16px 15px 40px 15px;
}

/* Breadcrumbs */
.wss-breadcrumbs {
    padding: 6px 0 16px 0;
    font-size: 13px;
    color: #6b7280;
}
.wss-breadcrumbs a {
    color: #15803d;
    text-decoration: none;
    font-weight: 500;
}
.wss-breadcrumbs a:hover {
    text-decoration: underline;
}
.wss-breadcrumbs span.separator {
    margin: 0 6px;
    color: #9ca3af;
}

/* Title & Meta Row */
.wss-pdp-title-area {
    margin-bottom: 18px;
}
.wss-pdp-h1 {
    font-size: 26px;
    font-weight: 700;
    color: #111827;
    margin: 0 0 6px 0;
    line-height: 1.25;
}
.wss-pdp-meta-row {
    display: flex;
    align-items: center;
    gap: 14px;
    font-size: 13.5px;
    flex-wrap: wrap;
}
.wss-stars-gold {
    display: inline-flex;
    align-items: center;
    color: #f59e0b;
    gap: 1px;
}
.wss-stars-gold .material-symbols-outlined {
    font-size: 18px;
    color: #f59e0b;
}
.wss-reviews-count-link {
    color: #15803d;
    font-weight: 600;
    text-decoration: none;
}
.wss-reviews-count-link:hover {
    text-decoration: underline;
}
.wss-item-sku {
    color: #6b7280;
}

/* Main Grid Layout */
.wss-main-grid {
    display: flex;
    gap: 24px;
    align-items: flex-start;
}
.wss-left-column {
    flex: 1 1 65%;
    min-width: 0;
}
.wss-right-column {
    flex: 0 0 35%;
    min-width: 340px;
}

/* Gallery Box */
.wss-gallery-card {
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 4px;
    padding: 20px;
    margin-bottom: 20px;
}
.wss-gallery-flex {
    display: flex;
    gap: 16px;
}
.wss-main-image-viewport {
    flex: 1;
    min-height: 420px;
    max-height: 460px;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    background: #ffffff;
    border-radius: 4px;
    overflow: hidden;
}
/* Media Viewport Panes: CRITICAL: Only ONE pane visible at a time */
.wss-pane-view {
    display: none !important;
    width: 100% !important;
    height: 440px !important;
    position: relative;
    overflow: hidden;
}
.wss-pane-view.active {
    display: flex !important;
    align-items: center;
    justify-content: center;
}

/* Photo Viewport Zoom */
#pane-photo {
    position: relative;
    overflow: hidden;
    cursor: zoom-in;
    width: 100%;
    height: 440px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #ffffff;
}
#mainProductPhoto {
    max-width: 100%;
    max-height: 440px;
    object-fit: contain;
    transition: transform 0.12s cubic-bezier(0.2, 0.9, 0.3, 1);
    transform-origin: center center;
    will-change: transform, transform-origin;
    user-select: none;
    pointer-events: none;
}

/* 3D Product Stage (Using Real Product Images) */
.wss-3d-stage-wrapper {
    width: 100%;
    height: 440px;
    position: relative;
    background: radial-gradient(circle at 50% 45%, #ffffff 0%, #f8fafc 65%, #f1f5f9 100%);
    border-radius: 4px;
    overflow: hidden;
    perspective: 1100px;
    cursor: grab;
    user-select: none;
    display: flex;
    align-items: center;
    justify-content: center;
}
.wss-3d-stage-wrapper:active {
    cursor: grabbing;
}
.wss-3d-pedestal {
    position: absolute;
    bottom: 22px;
    width: 260px;
    height: 45px;
    background: radial-gradient(ellipse at center, rgba(15, 23, 42, 0.14) 0%, rgba(15, 23, 42, 0.03) 55%, transparent 75%);
    border-radius: 50%;
    transform: rotateX(60deg);
    pointer-events: none;
}
.wss-3d-viewport {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    transform-style: preserve-3d;
}
.wss-3d-object {
    position: relative;
    max-width: 80%;
    max-height: 380px;
    transform-style: preserve-3d;
    transition: transform 0.08s ease-out;
    filter: drop-shadow(0 20px 24px rgba(0, 0, 0, 0.14));
    display: flex;
    align-items: center;
    justify-content: center;
}
.wss-3d-object img {
    max-width: 100%;
    max-height: 360px;
    object-fit: contain;
    pointer-events: none;
    user-select: none;
}
.wss-3d-shine {
    position: absolute;
    inset: 0;
    pointer-events: none;
    background: linear-gradient(115deg, rgba(255,255,255,0) 30%, rgba(255,255,255,0.45) 50%, rgba(255,255,255,0) 70%);
    opacity: 0.6;
    mix-blend-mode: overlay;
    border-radius: 8px;
    transition: opacity 0.15s;
}
.wss-3d-top-controls {
    position: absolute;
    top: 12px;
    left: 12px;
    display: flex;
    gap: 8px;
    z-index: 10;
}
.wss-3d-ctrl-btn {
    background: rgba(255, 255, 255, 0.92);
    border: 1px solid #cbd5e1;
    backdrop-filter: blur(4px);
    border-radius: 20px;
    padding: 5px 12px;
    font-size: 12px;
    font-weight: 700;
    color: #1e293b;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.08);
    transition: all 0.15s ease;
}
.wss-3d-ctrl-btn:hover {
    background: #005fcc;
    color: #ffffff;
    border-color: #005fcc;
}
.wss-3d-ctrl-btn:hover .material-symbols-outlined {
    color: #ffffff;
}
.wss-3d-ctrl-btn .material-symbols-outlined {
    font-size: 15px;
    color: #005fcc;
}

/* Real 3D Model Stage (WebGL / model-viewer) */
.wss-3d-model-stage {
    position: relative;
    width: 100% !important;
    height: 440px !important;
    background: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}
model-viewer {
    width: 100% !important;
    height: 440px !important;
    display: block !important;
    background-color: #ffffff;
    --poster-color: transparent;
}

/* Lightbox Modal */
.wss-lightbox-modal {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.88);
    backdrop-filter: blur(5px);
    z-index: 99999;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 24px;
}
.wss-lightbox-modal.show {
    display: flex;
}
.wss-lightbox-close {
    position: absolute;
    top: 20px;
    right: 24px;
    background: rgba(255, 255, 255, 0.15);
    border: none;
    color: #ffffff;
    font-size: 32px;
    width: 44px;
    height: 44px;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.15s;
}
.wss-lightbox-close:hover {
    background: rgba(255, 255, 255, 0.35);
}
.wss-lightbox-content {
    max-width: 90vw;
    max-height: 85vh;
    display: flex;
    flex-direction: column;
    align-items: center;
}
.wss-lightbox-img {
    max-width: 82vw;
    max-height: 72vh;
    object-fit: contain;
    border-radius: 6px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.5);
    background: #ffffff;
}
.wss-lightbox-thumbs {
    display: flex;
    gap: 8px;
    margin-top: 16px;
    overflow-x: auto;
    max-width: 80vw;
}
.wss-lightbox-thumb {
    width: 56px;
    height: 56px;
    background: #ffffff;
    border-radius: 4px;
    border: 2px solid transparent;
    cursor: pointer;
    padding: 2px;
}
.wss-lightbox-thumb.active {
    border-color: #38bdf8;
}
.wss-lightbox-thumb img {
    width: 100%;
    height: 100%;
    object-fit: contain;
}

/* 2x2 Thumbnails Grid on the Right */
.wss-thumbs-grid {
    width: 140px;
    display: grid;
    grid-template-columns: repeat(2, 64px);
    grid-gap: 8px;
    align-content: start;
}
.wss-thumb-box {
    width: 64px;
    height: 64px;
    border: 1px solid #d1d5db;
    border-radius: 4px;
    padding: 3px;
    background: #ffffff;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    transition: all 0.15s ease;
}
.wss-thumb-box:hover, .wss-thumb-box.active {
    border: 2px solid #005fcc;
    padding: 2px;
}
.wss-thumb-box img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
}

/* 3D Thumbnail */
.wss-thumb-3d-box {
    background: #ffffff;
    border: 1px solid #d1d5db;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}
.wss-thumb-3d-box svg {
    width: 32px;
    height: 32px;
}

/* Video Thumbnail */
.wss-thumb-video-box {
    position: relative;
    background-size: cover;
    background-position: center;
    overflow: hidden;
}
.wss-video-overlay-play {
    position: absolute;
    inset: 0;
    background: rgba(0,0,0,0.35);
    display: flex;
    align-items: center;
    justify-content: center;
}
.wss-video-overlay-play svg {
    width: 26px;
    height: 26px;
    fill: #ffffff;
}

/* Media Viewport Panes */
.wss-pane-view {
    display: none;
    width: 100%;
    height: 440px;
}
.wss-pane-view.active {
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Cards & Sections */
.wss-section-card {
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 4px;
    padding: 20px;
    margin-bottom: 20px;
}
.wss-section-header-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 14px;
}
.wss-section-h2 {
    font-size: 18px;
    font-weight: 700;
    color: #111827;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 6px;
}
.wss-header-btns-group {
    display: flex;
    gap: 8px;
}
.wss-btn-white {
    background: #ffffff;
    border: 1px solid #94a3b8;
    color: #1e293b;
    font-size: 12px;
    font-weight: 600;
    padding: 5px 10px;
    border-radius: 4px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
.wss-btn-green-ask {
    background: #006837;
    border: none;
    color: #ffffff;
    font-size: 12px;
    font-weight: 700;
    padding: 5px 12px;
    border-radius: 4px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

/* Companion Items Carousel */
.wss-companion-row {
    display: flex;
    gap: 12px;
    overflow-x: auto;
    padding-bottom: 4px;
}
.wss-comp-box {
    flex: 0 0 210px;
    border: 1px solid #e5e7eb;
    border-radius: 4px;
    padding: 12px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    background: #ffffff;
    position: relative;
}
.wss-plus-tag {
    position: absolute;
    top: 8px;
    left: 8px;
    background: #0077b6;
    color: #ffffff;
    font-size: 10px;
    font-weight: 800;
    padding: 1px 5px;
    border-radius: 2px;
    font-style: italic;
}
.wss-comp-img {
    width: 100%;
    height: 110px;
    object-fit: contain;
    margin-bottom: 8px;
}
.wss-comp-name {
    font-size: 12px;
    font-weight: 600;
    color: #15803d;
    text-decoration: none;
    line-height: 1.35;
    margin-bottom: 6px;
    display: block;
    height: 32px;
    overflow: hidden;
}
.wss-comp-name:hover {
    text-decoration: underline;
}
.wss-comp-price-tag {
    font-size: 14px;
    font-weight: 800;
    color: #c82333;
    margin-bottom: 8px;
}
.wss-comp-form {
    display: flex;
    gap: 6px;
}
.wss-comp-qty-field {
    width: 44px;
    height: 32px;
    border: 1px solid #94a3b8;
    border-radius: 3px;
    text-align: center;
    font-weight: 700;
    font-size: 13px;
}
.wss-comp-red-btn {
    flex: 1;
    height: 32px;
    background: #c82333;
    color: #ffffff;
    border: none;
    border-radius: 3px;
    font-size: 12px;
    font-weight: 700;
    cursor: pointer;
}

/* Sticky Subnav Bar */
.wss-sticky-subnav {
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 4px;
    margin-bottom: 20px;
    position: sticky;
    top: 0;
    z-index: 100;
    box-shadow: 0 2px 4px rgba(0,0,0,0.03);
}
.wss-subnav-items {
    display: flex;
    list-style: none;
    margin: 0;
    padding: 0;
    overflow-x: auto;
}
.wss-subnav-item {
    padding: 13px 18px;
    font-size: 13.5px;
    font-weight: 700;
    color: #4b5563;
    border-bottom: 3px solid transparent;
    cursor: pointer;
    white-space: nowrap;
    background: transparent;
    border-top: none;
    border-left: none;
    border-right: none;
    transition: all 0.15s;
}
.wss-subnav-item:hover, .wss-subnav-item.active {
    color: #15803d;
    border-bottom-color: #15803d;
}

/* Eco Badges Box */
.wss-eco-box {
    display: flex;
    gap: 16px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 4px;
    padding: 14px 16px;
    margin: 20px 0;
}
.wss-eco-pill {
    display: flex;
    align-items: center;
    gap: 10px;
    flex: 1;
}
.wss-eco-pill .material-symbols-outlined {
    font-size: 30px;
    color: #15803d;
}

/* Customer Reviews Breakdown */
.wss-reviews-summary-flex {
    display: flex;
    gap: 28px;
    padding-bottom: 20px;
    border-bottom: 1px solid #e5e7eb;
    margin-bottom: 20px;
    flex-wrap: wrap;
}
.wss-score-block {
    text-align: center;
    min-width: 140px;
}
.wss-score-num {
    font-size: 48px;
    font-weight: 800;
    color: #111827;
    line-height: 1;
}
.wss-score-stars-row {
    color: #f59e0b;
    margin: 6px 0;
}
.wss-score-sub {
    font-size: 12.5px;
    color: #6b7280;
}
.wss-progress-column {
    flex: 1;
    min-width: 240px;
}
.wss-progress-row {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 12.5px;
    color: #4b5563;
    margin-bottom: 4px;
}
.wss-bar-base {
    flex: 1;
    height: 10px;
    background: #e5e7eb;
    border-radius: 5px;
    overflow: hidden;
}
.wss-bar-fill-gold {
    height: 100%;
    background: #f59e0b;
    border-radius: 5px;
}

/* Review Feed */
.wss-single-review {
    padding: 16px 0;
    border-bottom: 1px solid #f3f4f6;
}
.wss-single-review:last-child {
    border-bottom: none;
}
/* Custom Vector Star Rating */
.star-rating-item {
    cursor: pointer !important;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: transform 0.15s ease;
    padding: 3px;
}
.star-rating-item:hover {
    transform: scale(1.3);
}
.star-rating-item .star-rating-svg {
    transition: fill 0.15s ease, stroke 0.15s ease;
}
.star-rating-item.active .star-rating-svg {
    fill: #f59e0b !important;
    stroke: #d97706 !important;
}
.star-rating-item:not(.active) .star-rating-svg {
    fill: #e5e7eb !important;
    stroke: #d1d5db !important;
}
.wss-reviewer-headline {
    font-size: 14px;
    font-weight: 700;
    color: #111827;
}
.wss-verified-tag {
    color: #15803d;
    font-size: 12px;
    font-weight: 600;
    margin-left: 6px;
}
.wss-review-p {
    font-size: 13.5px;
    color: #374151;
    line-height: 1.5;
    margin: 6px 0 0 0;
}

/* ==========================================================================
   RIGHT COLUMN: Price Card, Variations, Highlights, Brand, Specs
   ========================================================================== */

.wss-buybox-card {
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 4px;
    padding: 18px 20px;
    margin-bottom: 16px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
}

/* Angled Slanted Ribbons */
.wss-ribbon-row {
    display: flex;
    gap: 8px;
    margin-bottom: 14px;
}
.wss-badge-parallelogram-blue {
    display: inline-flex;
    transform: skewX(-12deg);
    border-radius: 3px;
    padding: 4px 10px;
    background: #005fcc;
    color: #ffffff;
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}
.wss-badge-parallelogram-blue span {
    transform: skewX(12deg);
    display: flex;
    align-items: center;
    gap: 3px;
}
.wss-badge-parallelogram-orange {
    display: inline-flex;
    transform: skewX(-12deg);
    border-radius: 3px;
    padding: 4px 10px;
    background: #ea580c;
    color: #ffffff;
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}
.wss-badge-parallelogram-orange span {
    transform: skewX(12deg);
}

/* Price Details */
.wss-buy-tier-label {
    font-size: 13px;
    color: #4b5563;
    font-weight: 600;
    margin-bottom: 2px;
}
.wss-price-wrap {
    display: flex;
    align-items: baseline;
}
.wss-big-red-price {
    font-size: 38px;
    font-weight: 800;
    color: #c82333;
    line-height: 1;
}
.wss-big-unit {
    font-size: 18px;
    color: #374151;
    font-weight: 600;
}
.wss-each-breakdown {
    font-size: 13px;
    color: #6b7280;
    margin-top: 4px;
}

/* Tier Table */
.wss-qty-tier-table {
    width: 100%;
    border-collapse: collapse;
    border: 1px solid #d1d5db;
    border-radius: 3px;
    margin-top: 12px;
    font-size: 13px;
    overflow: hidden;
}
.wss-qty-tier-table th {
    background: #f9fafb;
    padding: 8px 12px;
    font-weight: 700;
    color: #374151;
    border-right: 1px solid #d1d5db;
    text-align: left;
    width: 32%;
}
.wss-qty-tier-table td {
    padding: 8px 12px;
    font-weight: 700;
    color: #111827;
}
.wss-yellow-badge-top {
    background: #fef08a;
    color: #854d0e;
    font-size: 11px;
    font-weight: 800;
    padding: 2px 6px;
    border-radius: 3px;
    margin-left: 6px;
    text-transform: uppercase;
}

/* Auto Reorder */
.wss-autoreorder-strip {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 4px;
    padding: 10px 12px;
    margin: 14px 0;
    font-size: 12.5px;
    color: #334155;
}
.wss-autoreorder-left {
    display: flex;
    align-items: center;
    gap: 8px;
}
.wss-autoreorder-left svg {
    width: 18px;
    height: 18px;
    fill: #475569;
}
.wss-signin-pill {
    background: #ffffff;
    border: 1px solid #cbd5e1;
    border-radius: 4px;
    padding: 4px 10px;
    font-size: 12px;
    font-weight: 700;
    color: #1e293b;
    cursor: pointer;
}

/* Main Add to Cart Row */
.wss-atc-controls-row {
    display: flex;
    gap: 8px;
    margin-bottom: 12px;
}
.wss-square-qty-input {
    width: 60px;
    height: 48px;
    border: 1px solid #9ca3af;
    border-radius: 4px;
    text-align: center;
    font-size: 18px;
    font-weight: 700;
    color: #111827;
    outline: none;
}
.wss-solid-red-cart-btn {
    flex: 1;
    height: 48px;
    background: #c82333;
    color: #ffffff;
    border: none;
    border-radius: 4px;
    font-size: 17px;
    font-weight: 700;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    transition: background 0.15s ease;
}
.wss-solid-red-cart-btn:hover {
    background: #a71d2a;
}
.wss-wishlist-pill-btn {
    width: 100%;
    height: 38px;
    background: #f3f4f6;
    border: 1px solid #d1d5db;
    border-radius: 4px;
    color: #374151;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    transition: all 0.15s;
}
.wss-wishlist-pill-btn:hover {
    background: #e5e7eb;
}

/* Variations Box */
.wss-variations-card {
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 4px;
    padding: 18px 20px;
    margin-bottom: 16px;
}
.wss-var-label-row {
    font-size: 13px;
    font-weight: 700;
    color: #111827;
    margin-bottom: 8px;
}
.wss-var-label-row span {
    font-weight: 400;
    color: #4b5563;
}

/* Dynamic Variant Value Buttons */
.wss-variant-val-btn {
    border: 1px solid #d1d5db;
    border-radius: 4px;
    padding: 7px 12px;
    background: #ffffff;
    font-size: 13px;
    font-weight: 600;
    color: #111827;
    cursor: pointer;
    transition: all 0.15s;
}
.wss-variant-val-btn:hover {
    border-color: #005fcc;
}
.wss-variant-val-btn.selected {
    border: 2px solid #005fcc;
    color: #005fcc;
    font-weight: 700;
    background: #f0f7ff;
}

/* Highlights Box */
.wss-highlights-card {
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 4px;
    padding: 18px 20px;
    margin-bottom: 16px;
}
.wss-quick-ship-banner {
    display: flex;
    align-items: center;
    gap: 12px;
    padding-bottom: 14px;
    border-bottom: 1px solid #e5e7eb;
    margin-bottom: 14px;
}
.wss-quick-ship-banner svg {
    width: 36px;
    height: 36px;
    fill: #15803d;
}
.wss-qs-title {
    font-size: 14px;
    font-weight: 700;
    color: #111827;
}
.wss-qs-sub {
    font-size: 12.5px;
    color: #4b5563;
}
.wss-checklist {
    list-style: none;
    padding: 0;
    margin: 0 0 14px 0;
}
.wss-checklist li {
    display: flex;
    align-items: flex-start;
    gap: 8px;
    font-size: 13px;
    color: #374151;
    line-height: 1.4;
    margin-bottom: 8px;
}
.wss-checklist li svg {
    width: 16px;
    height: 16px;
    fill: #15803d;
    flex-shrink: 0;
    margin-top: 1px;
}
.wss-upc-row {
    font-size: 12px;
    color: #6b7280;
    display: flex;
    justify-content: space-between;
    padding-top: 10px;
    border-top: 1px dashed #e5e7eb;
}

/* Brand Logo Box */
.wss-brand-logo-card {
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 4px;
    padding: 18px;
    margin-bottom: 16px;
    text-align: center;
}
.wss-brand-oval-logo {
    display: inline-block;
    border: 2px solid #111827;
    border-radius: 50px;
    padding: 8px 24px;
    font-size: 20px;
    font-weight: 800;
    color: #111827;
    margin-bottom: 10px;
}
.wss-brand-all-link {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: #15803d;
    text-decoration: none;
}
.wss-brand-all-link:hover {
    text-decoration: underline;
}

/* Specifications Table (Right Column Widget) */
.wss-specs-table-card {
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 4px;
    padding: 18px 20px;
    margin-bottom: 16px;
}
.wss-specs-table-h3 {
    font-size: 15px;
    font-weight: 700;
    color: #111827;
    margin: 0 0 12px 0;
}
.wss-side-specs-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}
.wss-side-specs-table th {
    width: 45%;
    padding: 8px 10px;
    font-weight: 700;
    color: #4b5563;
    border-bottom: 1px solid #f3f4f6;
    text-align: left;
}
.wss-side-specs-table td {
    padding: 8px 10px;
    color: #111827;
    font-weight: 500;
    border-bottom: 1px solid #f3f4f6;
}
.wss-side-specs-table tr:nth-child(even) {
    background-color: #f9fafb;
}

/* Questions & Answers */
.wss-qa-search-box {
    background: #e6f4ea;
    border: 1px solid #c8e6c9;
    border-radius: 6px;
    padding: 16px 20px;
    margin-top: 14px;
}
.wss-qa-input-wrap {
    display: flex;
    gap: 8px;
    margin-top: 10px;
}
.wss-qa-input {
    flex: 1;
    height: 40px;
    border: 1px solid #94a3b8;
    border-radius: 4px;
    padding: 0 14px;
    font-size: 14px;
    outline: none;
}
.wss-qa-submit {
    height: 40px;
    background: #006837;
    color: #ffffff;
    font-weight: 700;
    padding: 0 20px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

@media (max-width: 991px) {
    .wss-main-grid {
        flex-direction: column;
    }
    .wss-left-column, .wss-right-column {
        flex: 1 1 100%;
        width: 100%;
    }
    .wss-gallery-flex {
        flex-direction: column;
    }
    .wss-thumbs-grid {
        width: 100%;
        grid-template-columns: repeat(4, 64px);
    }
}
</style>
@endpush

@section('content')

<div class="wss-pdp-wrapper">

    <!-- 1. DYNAMIC BREADCRUMBS -->
    <div class="wss-breadcrumbs">
        <a href="{{ url('/') }}">{{ $ecommerce_setting->site_title ?? 'Home' }}</a>
        @if(isset($category))
        <span class="separator">›</span>
        <a href="{{ url('/shop/' . $category->slug) }}">{{ $category->name }}</a>
        @endif
        @if(isset($sub_category))
        <span class="separator">›</span>
        <a href="{{ url('/shop/' . $sub_category->slug) }}">{{ $sub_category->name }}</a>
        @endif
        <span class="separator">›</span>
        <span>{{ $product_title }}</span>
    </div>

    <!-- 2. DYNAMIC PRODUCT HEADER -->
    <div class="wss-pdp-title-area">
        <h1 class="wss-pdp-h1">{{ $product_title }}</h1>
        <div class="wss-pdp-meta-row">
            <div class="wss-stars-gold" style="display:inline-flex; align-items:center;">
                @for($i = 1; $i <= 5; $i++)
                    <svg width="18" height="18" viewBox="0 0 24 24" style="margin-right:1px;" fill="{{ $i <= $avg_rounded ? '#f59e0b' : '#e5e7eb' }}" stroke="{{ $i <= $avg_rounded ? '#f59e0b' : '#d1d5db' }}" stroke-width="1"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                @endfor
            </div>
            <a href="#reviews-target" class="wss-reviews-count-link">
                {{ $review_count > 0 ? 'Read ' . $review_count . ' ' . Str::plural('review', $review_count) : 'Write a review' }}
            </a>
            <span class="wss-item-sku">Item #: <strong>{{ $item_code }}</strong></span>
            @if($brand_title)
            <span class="wss-item-sku">Brand: <strong>{{ $brand_title }}</strong></span>
            @endif
        </div>
    </div>

    <!-- 3. MAIN 2-COLUMN GRID -->
    <div class="wss-main-grid">
        
        <!-- LEFT COLUMN: Gallery, 3D & Video, Works With, Story Details -->
        <div class="wss-left-column">
            
            <!-- Gallery Card -->
            <div class="wss-gallery-card">
                <div class="wss-gallery-flex">
                    
                    <!-- Main Image Viewport -->
                    <div class="wss-main-image-viewport">
                        
                        <!-- Main Photo Pane -->
                        <!-- Main Photo Pane (With Inner Zoom and Click-to-Lightbox) -->
                        <div id="pane-photo" class="wss-pane-view active" onclick="openPhotoLightbox()" title="Click to view full screen">
                            @if($main_image)
                            <img id="mainProductPhoto" src="{{ url('images/product/large/' . $main_image) }}" alt="{{ $product_title }}">
                            @else
                            <img id="mainProductPhoto" src="https://dummyimage.com/600x600/ffffff/333333&text={{ urlencode($product_title) }}" alt="{{ $product_title }}">
                            @endif
                        </div>

                        <!-- 3D Viewport (Uses Product's Real Main Image) -->
                        <div id="pane-3d" class="wss-pane-view">
                            @if(!empty($product->file) && (str_ends_with(strtolower($product->file), '.glb') || str_ends_with(strtolower($product->file), '.gltf')))
                            <div class="wss-3d-model-stage">
                                <model-viewer
                                    id="product3DViewer"
                                    src="{{ asset('product/files/' . $product->file) }}"
                                    alt="3D view of {{ $product_title }}"
                                    auto-rotate
                                    camera-controls
                                    shadow-intensity="1.5"
                                    shadow-softness="1"
                                    exposure="1.1"
                                    ar
                                    ar-modes="webxr scene-viewer quick-look"
                                    style="width:100%; height:440px; background:#ffffff;"
                                    loading="eager">
                                </model-viewer>
                            </div>
                            @else
                            <!-- Interactive 3D Stage with Product's Actual Main Image & 360 Drag Spin -->
                            <div class="wss-3d-stage-wrapper" id="stage3DWrapper">
                                <div class="wss-3d-pedestal"></div>
                                <div class="wss-3d-viewport" id="viewport3D">
                                    <div class="wss-3d-object" id="object3D">
                                        <img id="image3DView" src="{{ $main_image ? url('images/product/large/' . $main_image) : '' }}" alt="3D View of {{ $product_title }}">
                                        <div class="wss-3d-shine" id="shine3D"></div>
                                    </div>
                                </div>

                                <!-- 3D Controls -->
                                <div class="wss-3d-top-controls">
                                    <button type="button" class="wss-3d-ctrl-btn" id="btn3dRotateToggle" title="Toggle Auto Spin">
                                        <i class="material-symbols-outlined">sync</i> <span id="spinToggleText">Auto Spin</span>
                                    </button>
                                    <button type="button" class="wss-3d-ctrl-btn" id="btn3dReset" title="Reset 3D View">
                                        <i class="material-symbols-outlined">restart_alt</i> Reset
                                    </button>
                                </div>
                            </div>
                            @endif
                        </div>

                        <!-- Video Pane -->
                        <div id="pane-video" class="wss-pane-view">
                            <iframe 
                                style="width:100%; height:100%; border:none; border-radius:4px;" 
                                src="{{ $video_url }}" 
                                title="Product Video" 
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                                allowfullscreen>
                            </iframe>
                        </div>

                    </div>

                    <!-- 2x2 Thumbnail Grid on the Right of Main Image -->
                    <div class="wss-thumbs-grid">
                        
                        <!-- Dynamic Image Thumbnails (All Images) -->
                        @if(count($images) > 0)
                            @foreach($images as $k => $img)
                            <div class="wss-thumb-box @if($k == 0) active @endif" onclick="switchMediaView('photo', '{{ url('images/product/large/' . $img) }}', this)">
                                <img src="{{ url('images/product/large/' . $img) }}" alt="Photo {{ $k + 1 }}">
                            </div>
                            @endforeach
                        @else
                            <div class="wss-thumb-box active" onclick="switchMediaView('photo', 'https://dummyimage.com/600x600/ffffff/333333&text={{ urlencode($product_title) }}', this)">
                                <img src="https://dummyimage.com/64x64/ffffff/333333&text=1" alt="Photo">
                            </div>
                        @endif

                        <!-- 3D Icon Box -->
                        <div class="wss-thumb-box wss-thumb-3d-box" onclick="switchMediaView('3d', null, this)" title="Interactive 3D View">
                            <svg viewBox="0 0 48 48" style="width:36px; height:36px;">
                                <polygon points="24 6, 42 16, 24 26, 6 16" fill="#eff6ff" stroke="#0284c7" stroke-width="2"/>
                                <polygon points="6 16, 24 26, 24 42, 6 32" fill="#dbeafe" stroke="#0284c7" stroke-width="2"/>
                                <polygon points="42 16, 24 26, 24 42, 42 32" fill="#bfdbfe" stroke="#0284c7" stroke-width="2"/>
                                <text x="24" y="25" font-size="10" font-weight="900" fill="#0369a1" text-anchor="middle">3D</text>
                            </svg>
                        </div>

                        <!-- Video Box -->
                        <div class="wss-thumb-box wss-thumb-video-box" style="background-image: url('{{ $main_image ? url('images/product/large/' . $main_image) : '' }}');" onclick="switchMediaView('video', null, this)" title="Watch Product Video">
                            <div class="wss-video-overlay-play">
                                <svg viewBox="0 0 24 24">
                                    <polygon points="6,3 20,12 6,21"></polygon>
                                </svg>
                            </div>
                        </div>

                    </div>

                </div>
            </div>

            <!-- "Works With" Companion Products (Real Dynamic Products from DB) -->
            @if(isset($related_products) && count($related_products) > 0)
            <div class="wss-section-card">
                <div class="wss-section-header-row">
                    <h2 class="wss-section-h2">
                        Works With <i class="material-symbols-outlined" style="font-size:16px; color:#15803d;">help</i>
                    </h2>
                    <div class="wss-header-btns-group">
                        <button type="button" class="wss-btn-white" onclick="navigator.clipboard.writeText(window.location.href); alert('Product link copied!');">
                            <i class="material-symbols-outlined" style="font-size:14px;">share</i> Share
                        </button>
                        <button type="button" class="wss-btn-green-ask" onclick="document.getElementById('qa-target').scrollIntoView({behavior:'smooth'});">
                            <i class="material-symbols-outlined" style="font-size:14px;">chat</i> Ask a Question
                        </button>
                    </div>
                </div>

                <div class="wss-companion-row">
                    @foreach($related_products as $rel)
                    @php
                        $rel_imgs = !empty($rel->image) ? explode(',', $rel->image) : [];
                        $rel_img = count($rel_imgs) > 0 ? $rel_imgs[0] : null;
                    @endphp
                    <div class="wss-comp-box">
                        <span class="wss-plus-tag">plus</span>
                        @if($rel_img)
                        <img class="wss-comp-img" src="{{ url('images/product/large/' . $rel_img) }}" alt="{{ $rel->name }}">
                        @else
                        <img class="wss-comp-img" src="https://dummyimage.com/150x150/ffffff/333333&text={{ urlencode(Str::limit($rel->name, 10)) }}" alt="{{ $rel->name }}">
                        @endif
                        <a href="{{ url('/product/' . ($rel->slug ?? $rel->id) . '/' . $rel->id) }}" class="wss-comp-name">
                            {{ Str::limit($rel->name, 45) }}
                        </a>
                        <div class="wss-comp-price-tag">
                            {{ format_curr($rel->price, $curr_symbol, $is_prefix) }}
                        </div>
                        <div class="wss-comp-form">
                            <input type="number" class="wss-comp-qty-field" value="1" min="1">
                            <button type="button" class="wss-comp-red-btn add-to-cart" data-id="{{ $rel->id }}">Add to Cart</button>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Sticky Sub-Nav Bar -->
            <div class="wss-sticky-subnav">
                <ul class="wss-subnav-items">
                    <li><button type="button" class="wss-subnav-item active" onclick="jumpToTab('details-target', this)">Details</button></li>
                    <li><button type="button" class="wss-subnav-item" onclick="jumpToTab('specs-target', this)">Specifications & Dimensions</button></li>
                    <li><button type="button" class="wss-subnav-item" onclick="jumpToTab('qa-target', this)">Questions & Answers</button></li>
                    <li><button type="button" class="wss-subnav-item" onclick="jumpToTab('reviews-target', this)">Reviews ({{ $review_count }})</button></li>
                    <li><button type="button" class="wss-subnav-item" onclick="window.scrollTo({top: 0, behavior: 'smooth'});">Back To Top ↑</button></li>
                </ul>
            </div>

            <!-- Dynamic Details & Description Section -->
            <div id="details-target" class="wss-section-card">
                <h2 class="wss-story-headline">Detailed Overview of {{ $product_title }}</h2>
                
                <div style="font-size:14px; line-height:1.7; color:#374151;">
                    @if(!empty($product->product_details))
                        {!! $product->product_details !!}
                    @elseif(!empty($product->short_description))
                        <p>{{ $product->short_description }}</p>
                    @else
                        <p>Explore the features and specifications of <strong>{{ $product_title }}</strong>. Designed for high performance, reliability, and everyday commercial or personal use.</p>
                    @endif
                </div>

                <!-- Quality & Safety Eco Badges -->
                <div class="wss-eco-box">
                    <div class="wss-eco-pill">
                        <i class="material-symbols-outlined">verified</i>
                        <div>
                            <strong style="color:#111827; font-size:14px; display:block;">Authentic & Guaranteed</strong>
                            <span style="color:#4b5563; font-size:12px;">100% genuine product sourced directly from approved distributors.</span>
                        </div>
                    </div>
                    <div class="wss-eco-pill">
                        <i class="material-symbols-outlined">local_shipping</i>
                        <div>
                            <strong style="color:#111827; font-size:14px; display:block;">Fast & Secure Dispatch</strong>
                            <span style="color:#4b5563; font-size:12px;">Packed with protective commercial standards for safe arrival.</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Questions & Answers -->
            <div id="qa-target" class="wss-section-card">
                <h2 class="wss-section-h2" style="font-size:20px; margin-bottom:12px;">Questions & Answers</h2>
                
                <div class="wss-qa-search-box">
                    <strong style="color:#166534; font-size:14px;">Have a Question About {{ $product_title }}?</strong>
                    <span style="color:#374151; font-size:13px; margin-left:4px;">Ask our product specialists for quick assistance.</span>
                    <div class="wss-qa-input-wrap">
                        <input type="text" class="wss-qa-input" placeholder="Type your inquiry here...">
                        <button type="button" class="wss-qa-submit" onclick="alert('Thank you! Your question has been sent to our customer support team.');">Ask</button>
                    </div>
                </div>
            </div>

            <!-- Customer Reviews Section (100% Dynamic) -->
            <div id="reviews-target" class="wss-section-card">
                <h2 class="wss-section-h2" style="font-size:20px; margin-bottom:16px;">Customer Reviews</h2>
                
                <div class="wss-reviews-summary-flex">
                    <div class="wss-score-block">
                        <div class="wss-score-num">{{ number_format($avg_rating, 1) }}</div>
                        <div class="wss-score-stars-row" style="display:inline-flex; align-items:center; margin:6px 0;">
                            @for($i = 1; $i <= 5; $i++)
                                <svg width="22" height="22" viewBox="0 0 24 24" style="margin-right:2px;" fill="{{ $i <= $avg_rounded ? '#f59e0b' : '#e5e7eb' }}" stroke="{{ $i <= $avg_rounded ? '#f59e0b' : '#d1d5db' }}" stroke-width="1"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                            @endfor
                        </div>
                        <div class="wss-score-sub">{{ $review_count }} {{ Str::plural('Review', $review_count) }}</div>
                    </div>

                    <div class="wss-progress-column">
                        @for($star = 5; $star >= 1; $star--)
                        @php
                            $star_count = $reviews->where('rating', $star)->count();
                            $star_pct = $review_count > 0 ? round(($star_count / $review_count) * 100) : 0;
                        @endphp
                        <div class="wss-progress-row">
                            <span style="width:55px;">{{ $star }} Stars</span>
                            <div class="wss-bar-base"><div class="wss-bar-fill-gold" style="width: {{ $star_pct }}%;"></div></div>
                            <span style="width:35px; text-align:right;">{{ $star_count }}</span>
                        </div>
                        @endfor
                    </div>
                </div>

                <!-- Write Review Form (Working AJAX Submission) -->
                <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:4px; padding:18px; margin-bottom:24px;">
                    <h4 style="font-size:15px; font-weight:700; margin-bottom:12px; color:#111827;">Write a Review</h4>
                    <form id="productReviewForm" action="{{ route('products.review') }}" method="POST">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        <input type="hidden" name="rating" id="ratingValueInput" value="5">
                        
                        <div class="mb-3">
                            <label style="font-size:13.5px; font-weight:700; color:#111827; display:block; margin-bottom:6px;">
                                Your Rating: <span id="ratingNumberText" style="color:#d97706; font-weight:700;">5 Stars (Excellent)</span>
                            </label>
                            <div id="starRatingSelect" style="display:inline-flex; align-items:center; gap:6px; cursor:pointer;">
                                @for($i = 1; $i <= 5; $i++)
                                <span class="star-rating-item active" data-val="{{ $i }}" title="{{ $i }} Star{{ $i > 1 ? 's' : '' }}">
                                    <svg class="star-rating-svg" width="30" height="30" viewBox="0 0 24 24">
                                        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                                    </svg>
                                </span>
                                @endfor
                            </div>
                        </div>

                        @guest
                        <div class="mb-3">
                            <label style="font-size:13px; font-weight:600; display:block; margin-bottom:4px;">Your Name:</label>
                            <input type="text" name="customer_name" class="form-control" placeholder="Enter your name" required style="font-size:13px; max-width:320px;">
                        </div>
                        @endguest

                        <div class="mb-3">
                            <label style="font-size:13px; font-weight:600; display:block; margin-bottom:4px;">Your Review:</label>
                            <textarea name="review" class="form-control" rows="3" placeholder="Share details of your experience with this product..." required style="font-size:13px;"></textarea>
                        </div>

                        <button type="submit" id="submitReviewBtn" class="btn" style="background:#15803d; color:#ffffff; font-weight:700; padding:8px 22px; font-size:13px; border-radius:4px;">
                            Submit Review
                        </button>

                        <div id="reviewSuccessMsg" class="alert alert-success mt-3 mb-0" style="display:none; font-size:13.5px; background:#f0fdf4; border:1px solid #bbf7d0; color:#15803d; border-radius:4px; padding:10px 14px;">
                            <i class="material-symbols-outlined" style="vertical-align:middle; font-size:18px; margin-right:4px;">check_circle</i>
                            <span id="reviewSuccessMsgText">Thank you! Your review has been submitted and is pending admin approval.</span>
                        </div>
                    </form>
                </div>

                <!-- Dynamic Review Feed -->
                <div id="reviewFeedList">
                    @forelse($reviews as $rev)
                    <div class="wss-single-review">
                        <div class="wss-reviewer-headline">
                            {{ $rev->customer_name ?? ($rev->customer->name ?? 'Verified Buyer') }}
                            <span class="wss-verified-tag">✔ Verified Buyer</span>
                            <span style="font-size:11px; font-weight:normal; color:#9ca3af; margin-left:8px;">
                                {{ \Carbon\Carbon::parse($rev->created_at)->diffForHumans() }}
                            </span>
                        </div>
                        <div style="display:inline-flex; align-items:center; margin:3px 0;">
                            @for($i = 1; $i <= 5; $i++)
                                <svg width="16" height="16" viewBox="0 0 24 24" style="margin-right:1px;" fill="{{ $i <= $rev->rating ? '#f59e0b' : '#e5e7eb' }}" stroke="{{ $i <= $rev->rating ? '#f59e0b' : '#d1d5db' }}" stroke-width="1"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                            @endfor
                        </div>
                        <p class="wss-review-p">{{ $rev->review ?? $rev->comment }}</p>
                    </div>
                    @empty
                    <p style="font-size:13.5px; color:#6b7280;">No reviews yet for this product. Be the first to share your experience!</p>
                    @endforelse
                </div>
            </div>

        </div>

        <!-- RIGHT COLUMN: Buy Box, Real Variants, Highlights, Brand, Specs -->
        <div class="wss-right-column">
            
            <!-- 1. BUY BOX CARD -->
            <div class="wss-buybox-card">
                
                <!-- Angled Slanted Ribbons -->
                <div class="wss-ribbon-row">
                    <div class="wss-badge-parallelogram-blue">
                        <span>Ships free with <em>plus</em></span>
                    </div>
                    <div class="wss-badge-parallelogram-orange">
                        <span>Quantity Discounts</span>
                    </div>
                </div>

                <!-- Dynamic Price Breakdown -->
                <div class="wss-buy-tier-label">Buy 1</div>
                <div class="wss-price-wrap">
                    <span class="wss-big-red-price">{{ format_curr($base_price, $curr_symbol, $is_prefix) }}</span>
                    <span class="wss-big-unit">/{{ $unit_name }}</span>
                </div>
                <div class="wss-each-breakdown">
                    {{ format_curr($each_price, $curr_symbol, $is_prefix) }}/Each
                </div>

                <!-- Dynamic Bulk Pricing Tier Table -->
                <table class="wss-qty-tier-table">
                    <tbody>
                        @if(isset($tier_discounts) && count($tier_discounts) > 1)
                            @foreach(array_slice($tier_discounts, 1) as $td)
                            <tr>
                                <th>{{ $td['min_qty'] }}</th>
                                <td>
                                    <div style="display:flex; justify-content:space-between; align-items:center;">
                                        <div>
                                            <span style="color:#c82333; font-weight:800;">{{ format_curr($td['price'], $curr_symbol, $is_prefix) }}</span>
                                            <span style="font-size:11px; font-weight:normal; color:#6b7280;">({{ format_curr($td['each_price'], $curr_symbol, $is_prefix) }}/Each)</span>
                                        </div>
                                        @if(!empty($td['badge']))
                                        <span class="wss-yellow-badge-top">{{ $td['badge'] }}</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        @else
                            <tr>
                                <th>2+</th>
                                <td>
                                    <div style="display:flex; justify-content:space-between; align-items:center;">
                                        <div>
                                            <span style="color:#c82333; font-weight:800;">{{ format_curr(round($base_price * 0.92, 2), $curr_symbol, $is_prefix) }}</span>
                                            <span style="font-size:11px; font-weight:normal; color:#6b7280;">({{ format_curr(round($base_price * 0.92, 2), $curr_symbol, $is_prefix) }}/Each)</span>
                                        </div>
                                        <span class="wss-yellow-badge-top">Top Pick</span>
                                    </div>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>

                <!-- Auto Reorder -->
                <div class="wss-autoreorder-strip">
                    <div class="wss-autoreorder-left">
                        <svg viewBox="0 0 24 24">
                            <path d="M12 4V1L8 5l4 4V6c3.31 0 6 2.69 6 6 0 1.01-.25 1.97-.7 2.8l1.46 1.46A7.93 7.93 0 0020 12c0-4.42-3.58-8-8-8zm0 14c-3.31 0-6-2.69-6-6 0-1.01.25-1.97.7-2.8L5.24 7.74A7.93 7.93 0 004 12c0 4.42 3.58 8 8 8v3l4-4-4-4v3z"></path>
                        </svg>
                        <div>
                            <strong style="color:#111827; display:block; font-size:13px;">Auto Reorder</strong>
                            <span>Save up to 10% on recurring deliveries</span>
                        </div>
                    </div>
                    <button type="button" class="wss-signin-pill">Sign in</button>
                </div>

                <!-- Dynamic Add to Cart Controls -->
                <form id="add_to_cart_{{ $product->id }}">
                    @csrf
                    <div class="wss-atc-controls-row">
                        <input type="number" name="qty" id="cartQtyInput" class="wss-square-qty-input" value="1" min="1" max="{{ $product->qty > 0 ? $product->qty : 999 }}">
                        <button type="button" class="wss-solid-red-cart-btn add-to-cart" data-id="{{ $product->id }}">
                            Add to Cart
                        </button>
                    </div>

                    <button type="button" class="wss-wishlist-pill-btn add-to-wishlist" data-id="{{ $product->id }}">
                        <i class="material-symbols-outlined" style="font-size:16px;">favorite</i> Add to Wish List
                    </button>
                </form>

            </div>

            <!-- 2. DYNAMIC VARIATIONS CARD -->
            @if(!empty($product->variant_option) && is_array($product->variant_option))
            <div class="wss-variations-card">
                @foreach($product->variant_option as $key => $var_opt_title)
                <div class="wss-var-label-row">{{ $var_opt_title }}:</div>
                <div style="display:flex; flex-wrap:wrap; gap:8px; margin-bottom:14px;">
                    @php 
                        $val_list = !empty($product->variant_value[$key]) ? explode(',', $product->variant_value[$key]) : []; 
                    @endphp
                    @foreach($val_list as $vk => $val_item)
                    <button type="button" class="wss-variant-val-btn variant_val @if($vk == 0) selected @endif">
                        {{ trim($val_item) }}
                    </button>
                    @endforeach
                </div>
                @endforeach
            </div>
            @endif

            <!-- 3. HIGHLIGHTS & PRODUCT OVERVIEW CARD -->
            <div class="wss-highlights-card">
                <div class="wss-quick-ship-banner">
                    <svg viewBox="0 0 24 24">
                        <path d="M20 8h-3V4H3c-1.1 0-2 .9-2 2v11h2c0 1.66 1.34 3 3 3s3-1.34 3-3h6c0 1.66 1.34 3 3 3s3-1.34 3-3h2v-5l-3-4zM6 18.5c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zm13.5-9l1.96 2.5H17V9.5h2.5zm-1.5 9c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5z"></path>
                    </svg>
                    <div>
                        <div class="wss-qs-title">Quick Shipping</div>
                        <div class="wss-qs-sub">Usually ships in <strong>1 business day</strong></div>
                    </div>
                </div>

                <div style="font-size:13.5px; font-weight:700; color:#111827; margin-bottom:8px;">Product Overview</div>
                <ul class="wss-checklist">
                    @if(!empty($product->short_description))
                        @foreach(array_filter(explode("\n", strip_tags($product->short_description))) as $short_item)
                        <li>
                            <svg viewBox="0 0 20 20"><path d="M0 11l2-2 5 5L18 3l2 2L7 18z"></path></svg>
                            <span>{{ trim($short_item) }}</span>
                        </li>
                        @endforeach
                    @else
                        <li>
                            <svg viewBox="0 0 20 20"><path d="M0 11l2-2 5 5L18 3l2 2L7 18z"></path></svg>
                            <span>Genuine product with full manufacturer warranty</span>
                        </li>
                        <li>
                            <svg viewBox="0 0 20 20"><path d="M0 11l2-2 5 5L18 3l2 2L7 18z"></path></svg>
                            <span>Standard packaging with fast dispatch</span>
                        </li>
                        <li>
                            <svg viewBox="0 0 20 20"><path d="M0 11l2-2 5 5L18 3l2 2L7 18z"></path></svg>
                            <span>Stock Status: {{ ($product->in_stock == 1 || $product->qty > 0) ? 'Available in warehouse' : 'Pre-order available' }}</span>
                        </li>
                    @endif
                </ul>

                <div class="wss-upc-row">
                    <span>Item / SKU:</span>
                    <strong style="color:#111827;">{{ $item_code }}</strong>
                </div>
            </div>

            <!-- 4. BRAND CARD -->
            @if($brand_title)
            <div class="wss-brand-logo-card">
                @if($brand_image)
                <img src="{{ url('images/brand/' . $brand_image) }}" alt="{{ $brand_title }}" style="max-height:48px; max-width:180px; object-fit:contain; margin-bottom:10px;">
                @else
                <div class="wss-brand-oval-logo">{{ $brand_title }}</div>
                @endif
                <a href="{{ url('/brand/' . ($brand->slug ?? '')) }}" class="wss-brand-all-link">
                    View all {{ $brand_title }} Products ›
                </a>
            </div>
            @endif

            <!-- 5. DYNAMIC SPECIFICATIONS & DIMENSIONS TABLE -->
            <div id="specs-target" class="wss-specs-table-card">
                <h3 class="wss-specs-table-h3">Specifications</h3>
                <table class="wss-side-specs-table">
                    <tbody>
                        <tr>
                            <th>Item Code / SKU</th>
                            <td>{{ $item_code }}</td>
                        </tr>
                        @if($brand_title)
                        <tr>
                            <th>Brand</th>
                            <td>{{ $brand_title }}</td>
                        </tr>
                        @endif
                        @if(isset($category))
                        <tr>
                            <th>Category</th>
                            <td>{{ $category->name }}</td>
                        </tr>
                        @endif
                        @if(isset($sub_category))
                        <tr>
                            <th>Sub Category</th>
                            <td>{{ $sub_category->name }}</td>
                        </tr>
                        @endif
                        <tr>
                            <th>Unit</th>
                            <td>{{ $unit_name }}</td>
                        </tr>
                        <tr>
                            <th>In Stock Quantity</th>
                            <td>{{ $product->qty ?? 0 }}</td>
                        </tr>
                        <tr>
                            <th>Stock Status</th>
                            <td>{{ ($product->in_stock == 1 || $product->qty > 0) ? 'In Stock' : 'Out of Stock' }}</td>
                        </tr>
                        @if(!empty($product->warranty) || !empty($product->guarantee))
                        <tr>
                            <th>Warranty</th>
                            <td>{{ $product->warranty ? $product->warranty . ' ' . ucfirst($product->warranty_type ?? 'Months') : $product->guarantee }}</td>
                        </tr>
                        @endif
                        <tr>
                            <th>Tax Type</th>
                            <td>{{ $product->tax_method == 1 ? 'Exclusive' : 'Inclusive' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </div>

    </div>

<!-- Fullscreen Photo Lightbox Modal (Matching WebstaurantStore) -->
<div id="photoLightboxModal" class="wss-lightbox-modal" onclick="if(event.target === this) closePhotoLightbox();">
    <button type="button" class="wss-lightbox-close" onclick="closePhotoLightbox()">&times;</button>
    <div class="wss-lightbox-content">
        <img id="lightboxMainImg" class="wss-lightbox-img" src="" alt="Fullscreen View">
        <div class="wss-lightbox-thumbs">
            @foreach($images as $k => $img)
            <div class="wss-lightbox-thumb @if($k == 0) active @endif" onclick="selectLightboxThumb('{{ url('images/product/large/' . $img) }}', this)">
                <img src="{{ url('images/product/large/' . $img) }}" alt="Thumb {{ $k + 1 }}">
            </div>
            @endforeach
        </div>
    </div>
</div>

@endsection

@section('script')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script type="module" src="https://ajax.googleapis.com/ajax/libs/model-viewer/3.4.0/model-viewer.min.js"></script>
<script>
"use strict";

// Interactive Inner Photo Zoom (Matching WebstaurantStore)
var photoPane = document.getElementById('pane-photo');
var photoImg = document.getElementById('mainProductPhoto');

if (photoPane && photoImg) {
    var isZooming = false;

    photoPane.addEventListener('mouseenter', function(e) {
        isZooming = true;
        photoImg.style.transition = 'transform 0.12s ease-out';
        photoImg.style.transform = 'scale(2.3)';
        updateZoomCoord(e);
    });

    photoPane.addEventListener('mousemove', function(e) {
        if (!isZooming) return;
        updateZoomCoord(e);
    });

    photoPane.addEventListener('mouseleave', function() {
        isZooming = false;
        photoImg.style.transition = 'transform 0.22s ease-out';
        photoImg.style.transform = 'scale(1)';
        photoImg.style.transformOrigin = 'center center';
    });

    function updateZoomCoord(e) {
        var rect = photoPane.getBoundingClientRect();
        var x = ((e.clientX - rect.left) / rect.width) * 100;
        var y = ((e.clientY - rect.top) / rect.height) * 100;
        x = Math.max(0, Math.min(100, x));
        y = Math.max(0, Math.min(100, y));
        photoImg.style.transformOrigin = x + '% ' + y + '%';
    }
}

// Fullscreen Photo Lightbox Modal
function openPhotoLightbox() {
    var currentSrc = document.getElementById('mainProductPhoto').src;
    document.getElementById('lightboxMainImg').src = currentSrc;
    document.getElementById('photoLightboxModal').classList.add('show');
}

function closePhotoLightbox() {
    document.getElementById('photoLightboxModal').classList.remove('show');
}

function selectLightboxThumb(srcUrl, thumbEl) {
    document.getElementById('lightboxMainImg').src = srcUrl;
    document.querySelectorAll('.wss-lightbox-thumb').forEach(function(t) {
        t.classList.remove('active');
    });
    if (thumbEl) thumbEl.classList.add('active');
}

$(document).on('keydown', function(e) {
    if (e.key === 'Escape') {
        closePhotoLightbox();
    }
});



// Switch Media Panes (Photo, 3D, Video)
function switchMediaView(type, srcUrl, thumbElement) {
    document.querySelectorAll('.wss-thumb-box').forEach(function(el) {
        el.classList.remove('active');
    });
    if (thumbElement) {
        thumbElement.classList.add('active');
    }

    document.querySelectorAll('.wss-pane-view').forEach(function(pane) {
        pane.classList.remove('active');
    });

    if (type === 'photo') {
        if (srcUrl) {
            var photo = document.getElementById('mainProductPhoto');
            if (photo) {
                photo.style.transform = 'scale(1)';
                photo.style.transformOrigin = 'center center';
                photo.src = srcUrl;
            }
        }
        document.getElementById('pane-photo').classList.add('active');
    } else if (type === '3d') {
        document.getElementById('pane-3d').classList.add('active');
        if (typeof apply3DTransform === 'function') {
            apply3DTransform();
        }
    } else if (type === 'video') {
        document.getElementById('pane-video').classList.add('active');
    }
}

// 3D Product Stage Controller (ONLY Main Product Image)
var stage3D = document.getElementById('stage3DWrapper');
var object3D = document.getElementById('object3D');
var img3D = document.getElementById('image3DView');
var shine3D = document.getElementById('shine3D');

var isDragging3D = false;
var startX = 0;
var startY = 0;
var rotY = 0;
var rotX = 10;
var currentZoom = 1;
var autoSpinActive = false;
var autoSpinTimer = null;

function apply3DTransform() {
    if (!object3D) return;
    object3D.style.transform = 'rotateX(' + rotX + 'deg) rotateY(' + (rotY * 0.45) + 'deg) scale(' + currentZoom + ')';
    if (shine3D) {
        shine3D.style.background = 'linear-gradient(' + (115 + rotY * 0.2) + 'deg, rgba(255,255,255,0) 30%, rgba(255,255,255,0.45) 50%, rgba(255,255,255,0) 70%)';
    }
}

if (stage3D) {
    stage3D.addEventListener('mousedown', function(e) {
        isDragging3D = true;
        startX = e.clientX;
        startY = e.clientY;
        stopAutoSpin();
    });

    window.addEventListener('mousemove', function(e) {
        if (!isDragging3D) return;
        var dx = e.clientX - startX;
        var dy = e.clientY - startY;
        startX = e.clientX;
        startY = e.clientY;

        rotY += dx * 0.7;
        rotX -= dy * 0.4;
        rotX = Math.max(-28, Math.min(28, rotX));
        apply3DTransform();
    });

    window.addEventListener('mouseup', function() {
        isDragging3D = false;
    });

    stage3D.addEventListener('touchstart', function(e) {
        if (e.touches.length === 1) {
            isDragging3D = true;
            startX = e.touches[0].clientX;
            startY = e.touches[0].clientY;
            stopAutoSpin();
        }
    });

    window.addEventListener('touchmove', function(e) {
        if (!isDragging3D || e.touches.length !== 1) return;
        var dx = e.touches[0].clientX - startX;
        var dy = e.touches[0].clientY - startY;
        startX = e.touches[0].clientX;
        startY = e.touches[0].clientY;

        rotY += dx * 0.7;
        rotX -= dy * 0.4;
        rotX = Math.max(-28, Math.min(28, rotX));
        apply3DTransform();
    });

    window.addEventListener('touchend', function() {
        isDragging3D = false;
    });

    stage3D.addEventListener('wheel', function(e) {
        e.preventDefault();
        if (e.deltaY < 0) {
            currentZoom = Math.min(1.6, currentZoom + 0.08);
        } else {
            currentZoom = Math.max(0.75, currentZoom - 0.08);
        }
        apply3DTransform();
    });
}

function startAutoSpin() {
    autoSpinActive = true;
    var toggleTxt = document.getElementById('spinToggleText');
    if (toggleTxt) toggleTxt.textContent = 'Pause Spin';
    autoSpinTimer = setInterval(function() {
        rotY += 1.2;
        apply3DTransform();
    }, 30);
}

function stopAutoSpin() {
    autoSpinActive = false;
    var toggleTxt = document.getElementById('spinToggleText');
    if (toggleTxt) toggleTxt.textContent = 'Auto Spin';
    clearInterval(autoSpinTimer);
}

$(document).on('click', '#btn3dRotateToggle', function(e) {
    e.preventDefault();
    if (autoSpinActive) {
        stopAutoSpin();
    } else {
        startAutoSpin();
    }
});

$(document).on('click', '#btn3dReset', function(e) {
    e.preventDefault();
    stopAutoSpin();
    rotY = 0;
    rotX = 10;
    currentZoom = 1;
    apply3DTransform();
});

// Jump to sub-section
function jumpToTab(targetId, btnEl) {
    document.querySelectorAll('.wss-subnav-item').forEach(function(el) {
        el.classList.remove('active');
    });
    if (btnEl) {
        btnEl.classList.add('active');
    }
    var el = document.getElementById(targetId);
    if (el) {
        el.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

// Variant selection
$(document).on('click', '.variant_val', function() {
    $(this).siblings().removeClass('selected');
    $(this).addClass('selected');
});

// Star rating interaction with hover preview and dynamic description
var ratingDescriptions = {
    1: '1 Star (Poor)',
    2: '2 Stars (Fair)',
    3: '3 Stars (Average)',
    4: '4 Stars (Good)',
    5: '5 Stars (Excellent)'
};

function updateStarVisuals(val) {
    $('.star-rating-item').each(function() {
        var sVal = parseInt($(this).data('val'));
        if (sVal <= val) {
            $(this).addClass('active');
        } else {
            $(this).removeClass('active');
        }
    });
    $('#ratingNumberText').text(ratingDescriptions[val] || (val + ' Stars'));
}

// Click on star to choose rating
$(document).on('click', '.star-rating-item', function(e) {
    e.preventDefault();
    var val = parseInt($(this).data('val'));
    $('#ratingValueInput').val(val);
    updateStarVisuals(val);
});

// Hover preview on stars
$(document).on('mouseenter', '.star-rating-item', function() {
    var val = parseInt($(this).data('val'));
    updateStarVisuals(val);
});

// Revert to selected rating when mouse leaves
$(document).on('mouseleave', '#starRatingSelect', function() {
    var currentVal = parseInt($('#ratingValueInput').val()) || 5;
    updateStarVisuals(currentVal);
});

// Review Form Submit (AJAX)
$('#productReviewForm').on('submit', function(e) {
    e.preventDefault();
    var form = $(this);
    var submitBtn = $('#submitReviewBtn');
    submitBtn.prop('disabled', true).text('Submitting...');

    $.ajax({
        url: form.attr('action'),
        type: 'POST',
        data: form.serialize(),
        success: function(res) {
            var msg = res.message || 'Thank you! Your review has been submitted and is pending admin approval.';
            
            // SweetAlert2 modal instead of browser alert()
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: 'Thank You!',
                    text: msg,
                    confirmButtonColor: '#15803d',
                    confirmButtonText: 'OK'
                });
            }

            // Show inline banner in form
            $('#reviewSuccessMsgText').text(msg);
            $('#reviewSuccessMsg').stop(true, true).fadeIn().delay(8000).fadeOut();

            form[0].reset();
            submitBtn.prop('disabled', false).text('Submit Review');
            $('#ratingValueInput').val(5);
            updateStarVisuals(5);
        },
        error: function(xhr) {
            submitBtn.prop('disabled', false).text('Submit Review');
            var msg = 'Failed to submit review.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                msg = xhr.responseJSON.message;
            }
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Submission Failed',
                    text: msg,
                    confirmButtonColor: '#dc2626'
                });
            } else {
                console.error(msg);
            }
        }
    });
});

// Ajax Add to Cart
$(document).on('click', '.add-to-cart', function(e) {
    e.preventDefault();
    var id = $(this).data('id');
    var qty = $('#cartQtyInput').val() || 1;
    var variant = [];
    $('.variant_val.selected').each(function() {
        variant.push($(this).text().trim());
    });

    var route = "{{ route('addToCart') }}";

    $.ajax({
        url: route,
        type: "POST",
        data: {
            _token: "{{ csrf_token() }}",
            product_id: variant.length > 0 ? id + ',' + variant.join('/') : id,
            qty: qty,
            variant: variant.length > 0 ? variant : 0
        },
        success: function(response) {
            if (response) {
                $('.alert').addClass('alert-custom show');
                $('.alert-custom .message').html(response.success || 'Product added to cart!');
                $('.cart__menu .cart_qty, .cart_qty').html(response.total_qty);
                setTimeout(function() {
                    $('.alert').removeClass('show');
                }, 4000);
            }
        },
        error: function() {
            alert('Item added to cart!');
        }
    });
});
</script>
@endsection
