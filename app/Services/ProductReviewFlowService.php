<?php

namespace App\Services;

use App\Models\ProductDraft;
use App\Models\ProductImageReview;
use App\Helpers\Audit;

class ProductReviewFlowService
{

	public function checkImageReviews(
	    ProductDraft $draft
	)
	{
		$reviews = ProductImageReview::where(

		    'draft_id',
		    $draft->id

		)->get();

		if ($reviews->isEmpty()) {
		    return;
		}

		$pending = $reviews
		    ->where('status', 'pending')
		    ->count();

		if ($pending > 0) {
		    return;
		}

		$totalCount = $reviews->count();

		$approvedCount = $reviews
		    ->where('status', 'approved')
		    ->count();

		$rejectedCount = $reviews
		    ->where('status', 'rejected')
		    ->count();

		$status = $rejectedCount > 0
		    ? 'warning'
		    : 'approved'; 
	    
	    $before = $draft->toArray();

		$draft->update([

		    'image_ai_status' => $status,

		    'image_ai_total_count' => $totalCount,

		    'image_ai_ok_count' => $approvedCount,

		    'image_ai_ng_count' => $rejectedCount,

		]);

		$this->updateOverallStatus($draft);

		$draft->refresh();

		$after = $draft->toArray();

		Audit::log(

		    action: 'image_ai_completed',

		    target: $draft,

		    before: $before,

		    after: $after,

		    description: '画像AI審査集約完了'

		); 

	}

	private function updateOverallStatus(
	    ProductDraft $draft
	): void
	{
		$overall = $draft->image_ai_status;

	    $draft->update([

	        'overall_ai_status' => $overall,

	    ]);

	}


}	