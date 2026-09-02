<?php

namespace Modules\Ecommerce\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Ecommerce\Entities\ProductReview;
use Stripe\Review;

class ProductReviewController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        $reviews = ProductReview::query()->latest()->get();
        return view('ecommerce::backend.review.index', compact('reviews'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('ecommerce::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'nullable|string|max:1000',
            'comment' => 'nullable|string|max:1000',
        ]);
        try{
            DB::beginTransaction();
            $customer_name = auth()->check() ? auth()->user()->name : ($request->customer_name ?? 'Verified Buyer');
            $customer_id = auth()->check() ? auth()->id() : 0;
            $review_text = $request->review ?? $request->comment;

            ProductReview::create([
                'product_id' => $request->product_id,
                'customer_id' => $customer_id,
                'customer_name' => $customer_name,
                'rating' => $request->rating,
                'review' => $review_text,
                'approved' => 0, // Pending by default until admin approves
            ]);

            DB::commit();
            return response()->json([
                'status' => 'pending',
                'message' => 'Thank you! Your review has been submitted and is pending admin approval.'
            ]);

        }catch(\Throwable $e){
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to submit review. Please try again.'
            ], 500);
        }
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('ecommerce::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('ecommerce::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        $review = ProductReview::query()->findOrFail($id);
        $review->delete();
        return redirect()->back()->with('message','Review deleted successfully!');
    }

    public function toggleStatus(Request $request)
    {
        $review = ProductReview::find($request->id);

        if (!$review) {
            return response()->json(['success' => false, 'message' => 'Review not found']);
        }

        // Toggle status
        $review->approved = $review->approved == 1 ? 0 : 1;
        $review->save();

        return response()->json([
            'success' => true,
            'new_status' => $review->approved,
            'status_label' => $review->approved ? 'Approved' : 'Pending',
            'status_badge' => $review->approved ? 'badge-success' : 'badge-warning',
            'status_icon' => $review->approved ? 'ti-circle-check' : 'ti-clock',
            'action_label' => $review->approved ? 'Set Pending' : 'Approve',
            'action_btn_class' => $review->approved ? 'btn-warning' : 'btn-success',
            'action_icon' => $review->approved ? 'ti-rotate' : 'ti-check'
        ]);
    }
}
