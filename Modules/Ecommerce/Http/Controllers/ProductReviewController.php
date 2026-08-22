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
            'review' => 'required|string|max:1000',
        ]);
        try{
            DB::beginTransaction();
            ProductReview::create([
                'product_id' => $request->product_id,
                'customer_id' => auth()->id(),
                'customer_name' => auth()->user()->name,
                'rating' => $request->rating,
                'review' => $request->review,
                'approved' => 0,
            ]);

            DB::commit();
            return response()->json(['message' => 'Review submitted successfully!']);

        }catch(\Throwable $e){
            DB::rollBack();
            return response()->json(['message' => 'Review submitted successfully!']);
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
            'label' => $review->approved ? 'Approved' : 'Pending',
            'btn_class' => $review->approved ? 'btn-success' : 'btn-warning'
        ]);
    }

}
