<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use App\Models\Categories;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categoryId = request('category_id');
        $categoryName = null;
        

        if($categoryId){
            $category = Categories::find($categoryId);
            $categoryName = ucfirst($category->name);
            // $products = $category->products;
            $products = $category->allProducts();
            // dd($products);
            // dd($category);
        }else{
            $products = Product::take(10)->get();
        }

        return view('products.index', compact('products', 'categoryName'));
    }

    public function search(Request $request, $query=null)
    {
            // dd($request->query);
            // dd($request->input('query'));
            $query = $request->input('query');
            $products = Product::where('name', 'LIKE', "%$query%")->paginate(2);
            // dd($products); 
        
        
        return view('products.catalog', compact('products', 'query'));
    }

    public function detail($id)
    {
        $productDetails = Product::find($id)->toArray();
        // dd($productDetails);
        // dd($productDetails['cover_img']);
        return view('products.detail', compact('productDetails'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request, Product $product)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        //
    }
}
