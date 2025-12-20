<?php

namespace App\Http\Controllers;

use App\Models\PickupProduct;
use App\Models\PickupSlot;
use Illuminate\Http\Request;

class PickupCatalogController extends Controller
{
    public function index(Request $request)
    {
        $query = PickupProduct::query()->where('status', true);

        // 検索機能（任意）
        if ($request->filled('q')) {
            $query->where('name', 'like', '%' . $request->q . '%');
        }

        // ソート機能（任意）
        if ($request->filled('sort')) {
            $sort = $request->sort;
            if ($sort === 'price_asc') {
                $query->orderBy('price', 'asc');
            } elseif ($sort === 'price_desc') {
                $query->orderBy('price', 'desc');
            } else {
                $query->latest();
            }
        } else {
            $query->latest();
        }

        $products = $query->paginate(12); // ページネーション
        // dd($products);

        return view('pickup.catalog.index', compact('products'));
    }

    public function show(PickupProduct $pickupProduct)
    {
        abort_unless($pickupProduct->status, 404);

        return view('pickup.catalog.show', compact('pickupProduct'));
    }
}

