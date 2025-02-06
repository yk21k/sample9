<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use App\Models\Categories;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\SubOrder;
use App\Models\SubOrderItem;
use App\Models\Fovorite;

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

        $product_movies = [];

        $product_movies = $productDetails['movie'];
        // dd($product_movies);

        // $product_movies = Product::with('movie', json_decode($productDetails['movie'], true))->get();

        if(isset(auth()->user()->id)){
            $search_order_ids = Order::where('user_id', auth()->user()->id)->first('id');

            $search_products = OrderItem::where('order_id', $search_order_ids)->first();

            $ableFavos = SubOrderItem::where('user_id', auth()->user()->id)->orderBy('id', 'DESC')->first();
            // dd($ableFavos);

            return response()->view('products.detail', ['productDetails' => $productDetails, 'product_movies' => $product_movies, 'search_order_ids' => $search_order_ids, 'id' => $id, 'ableFavos' => $ableFavos]);
        }
        return response()->view('products.detail', ['productDetails' => $productDetails, 'product_movies' => $product_movies, 'search_order_ids' => " ", 'id' => $id]);

    }

    public function productFavo(Request $request, $id)
    {
        if($request->isMethod('post')){
            $data = $request->all();
            // dd($data);
            $search_products = SubOrderItem::where('product_id', $data['product_id'])->where('user_id', auth()->user()->id)->first();
            // dd($search_products);
            
            $rules = [
                'review' => 'required|string|max:100'
            ];

            $customMessages = [

                'review' => 'Too long, less than 100 characters'
            ];

            $this->validate($request, $rules, $customMessages);

            $data = $request->all();
            $favorite = new Fovorite;

            $favorite->user_id = $request->user_id;
            $favorite->shop_id = $request->shop_id;
            $favorite->product_id = $request->product_id;
            $favorite->wants = $request->wants;
            $favorite->store_personnel = $request->store_personnel;
            $favorite->agree = $request->agree;
            $favorite->review = $request->review;
            $favorite->save();
              
        }
        $favorite->genarateFavoRates();
        
        return back()->withMessage('Thank you!!');
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
