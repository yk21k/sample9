@extends('layouts.app')

@section('content')

<!-- Side navigation -->

<div class="sidenav shadow-sm">

    <h3>Multilevel dropdown</h3>
        <ul class="multilevel-dropdown-menu">
            @foreach($categories as $category)

            <li class="parent"><a href="{{ route('products.index', ['category_id' => $category->id]) }}">{{ $category->name }}</a>
                @php 

                    $children = TCG\Voyager\Models\Category::where('parent_id', $category->id)->get();

                @endphp
                @if($children->isNotEmpty())
                    @foreach($children as $child)
                    <ul class="child">
                        <li class="parent"><a href="{{ route('products.index', ['category_id' => $child->id]) }}">{{ $child->name }} <span class="expand">»</span></a>
                            @php 
                                $grandChild = TCG\Voyager\Models\Category::where('parent_id', $child->id)->get();
                            @endphp
                            @if($grandChild->isNotEmpty())
                                @foreach($grandChild as $c)
                                <ul class="child">
                                    <li class="parent"><a href="{{ route('products.index', ['category_id' => $c->id]) }}">{{ $c->name }}</a>
                                        @php 
                                            $greatGrandchild = TCG\Voyager\Models\Category::where('parent_id', $c->id)->get();
                                        @endphp
                                        @if($greatGrandchild->isNotEmpty())

                                            @foreach($greatGrandchild as $ch)
                                            <ul class="child">
                                                <li class="parent"><a href="{{ route('products.index', ['category_id' => $ch->id]) }}">{{ $ch->name }}</a></li>
                                            </ul>
                                            @endforeach
                                        @endif    
                                    </li>    
                                </ul>
                                @endforeach
                            @endif
                        </li>
                    </ul>
                    @endforeach
                @endif
            </li>

            @endforeach
        </ul>
</div>
 

<div class="container">

    <h2>Products test</h2>

    <div class="row">
        @foreach($allProducts as $product)
            
            <div class="col-4">
                <div class="card">
                    <a class="" href="{{ route('products.detail', ['id'=>$product->id]) }}">

                    @if(isset($product->cover_img) && !empty($product->cover_img))
                        <img class="card-img-top" src="{{ asset( 'storage/'.$product->cover_img ) }}" alt="Card image cap">               
                    @else
                        <img class="card-img-top" src="{{ asset('images/no_image.jpg') }}" alt="Card image cap">
                    @endif
                    </a>

                    <div class="card-body">

                        <h4 class="card-title">{{ $product->name }}</h4>
                        {{ $product->description }}
                        <h4 class="card-title"> ${{ $product->price }} </h4>
                        <h4 class="card-title" id="stockQty">
                         @if($product->stock<=0) 
                            <div class="ribbon">Sold out!! </div>
                             
                         @else  
                            Stock : {{ $product->stock }}
                         @endif   
                        </h4>
                        @foreach($product_attributes as $attr)
                            @foreach ($attr->values as $val)
                                @if((!empty(json_decode($product->product_attributes,true)[$attr->name]) && json_decode($product->product_attributes,true)[$attr->name] == $val->value))
                            
                                <h4>{{$attr->name}} : {{$val->value}}</h4>
                                @endif
                            @endforeach
                        @endforeach
                            
                        <h4 class="card-title"> {{ $product->shop->name }} </h4>

                        <a class="" href="{{ route('inquiries.create', ['id'=>$product->shop->id]) }}"><h4>Contact Shop Manager</h4></a>


                    </div>
                    <div class="card-body" id="addCart1">
                        <a href="{{ route('cart.add', $product->id) }}" class="card-link">Add to Cart</a>
                    </div>
                </div>
              </div>
        @endforeach
        

    </div>

</div>
@endsection
