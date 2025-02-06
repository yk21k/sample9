@extends('layouts.app')

@section('content')

<!-- Side navigation -->
<style>
    @media screen and (max-width: 480px) {
        .sidenav {
            display: none;
        }
    }

</style>
<div class="sidenav shadow-sm">

    <div style="color: black;"><a><h3>Category Menu</h3></a></div>
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

    <div class="row">

        <h2>Products test</h2>
        <br><br><br>
        <div class="text-animation1">  
            @foreach($holidays as $holiday)
                    <p>Holiday Store: {{ $holiday['shop_name'] }}</p>
                &nbsp;&nbsp;&nbsp;           
            @endforeach
        </div>
        @foreach($extra_holidays as $ex_holiday)
            <div class="text-animation2"> 
                @if($ex_holiday['date_flag'] === 2) 
                
                    <p>Stores Temporarily Closed :: {{ $ex_holiday['shop_name'] }}</p>
                
                @endif
                </div> 
                <div class="text-animation3">     
                @if($ex_holiday['date_flag'] === 1)
                    
                    <p>📣📣📣　Temporary Store :: {{ $ex_holiday['shop_name'] }}　📣📣📣</p><br><br><br>
                    
                @endif 
            </div>          
        @endforeach
        <br><br><br><br><br><br>
        
        
             
            
            @foreach($norm_products_pres as $attr)
                @foreach($attr->fovo_dises as $n)
                    @foreach (json_decode($attr->movie, true) as $movie)
                        @if(null !== $attr->fovo_dises)
                        Name:{{ $attr->name }}
                        Price:{{ $attr->price }}
                        Score:{{ $n->norm_total }} 
                        <video controls width="60%" src="{{ asset('storage/'.$movie['download_link']) }}#t=0,2" muted class="contents_width"></video>
                        @else
                            none
                        @endif
                    @endforeach
                @endforeach    
            @endforeach 

            
        
        
       

        <br><br><br>
        @foreach($allProducts as $product)



            <div class="col-4">
                @if($product->status==0)
                    
                    <div class="card card-skin change-border01 container10">
                        <div class="card-body change-border01__inner">
                            @if(isset($product->cover_img) && !empty($product->cover_img))
                                <img class="card-img-top" src="{{ asset( 'storage/'.$product->cover_img ) }}" alt="Card image cap">               
                            @else
                                <img class="card-img-top" src="{{ asset('images/no_image.jpg') }}" alt="Card image cap">
                            @endif
                            <h4 class="card-title overlay10">Inactive</h4>
                            <span class="change-border01__inner"><h4 class="card-title">Coming Soon !!</h4></span>
                            
                            
                            <h4 class="card-title">Coming Soon !!</h4>
                            <h4 class="card-title">Coming Soon !!</h4>
                            <h4 class="card-title">Coming Soon !!</h4>
                            <h4 class="card-title">Coming Soon !!</h4>
                        </div>
                    </div>
                    <br>   
                @else
                <div class="card card-skin change-border01">
                    <div class="card-body change-border01__inner">
                        <a class="" href="{{ route('products.detail', ['id'=>$product->id]) }}">

                            @if(isset($product->cover_img) && !empty($product->cover_img))
                                <img class="card-img-top" src="{{ asset( 'storage/'.$product->cover_img ) }}" alt="Card image cap">               
                            @else
                                <img class="card-img-top" src="{{ asset('images/no_image.jpg') }}" alt="Card image cap">
                            @endif
                        </a>

                        <div class="card-body change-border01__inner">

                            <h4 class="card-title">{{ $product->name }}</h4>
                            {{ $product->description }}

                                @foreach($capaign_objs as $obj) 

                                    @if($product->shop->id == $obj['shop_id'])
                                        <h4 class="card-title">${{ $product->price*(1-$obj['dicount_rate1']) }}</h4>
                                        <div class="ribbon1"> Campaign !! </div>
                                        <div class="ribbon2"> Up to: {{  Carbon\Carbon::parse($obj['end_date'])->format('Y/m/d')}}</div>
                                    @endif

                                @endforeach
                                
                            <h4 class="card-title">${{ $product->price }}</h4>
                            <h4 class="card-title" id="stockQty">
                             @if($product->stock<=0) 
                                <div class="ribbon">Sold out!!</div>
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
                            <h4 class="card-title">{{ $product->shop->name }}</h4>

                            <a class="" href="{{ route('inquiries.create', ['id'=>$product->shop->id]) }}"><h4>Contact Shop Manager</h4></a>
                            <div class="card-body change-border01__inner" id="addCart1">
                                <a href="{{ route('cart.add', $product->id) }}" class="card-link">Add to Cart</a>
                            </div>
                            
                               
                        </div>                      
                        
                    </div>
                </div>     
                @endif
            </div>
            <br>
        @endforeach
        
    </div><br>
    

</div><br>

@endsection
