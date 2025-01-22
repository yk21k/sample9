@extends('layouts.app')


@section('content')

@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="" method="post">@csrf
  <div class="form-group">
    <label for="exampleFormControlInput1"><h3>To contact the seller or shop manager, please enter here.<br><strong>Please note that returns and refunds will be determined by each shop.</strong></h3></label>
    <input class="" type="hidden" id="user_id" name="user_id" value="{{ Auth::user()->id }}" readonly="">
    <input class="" type="hidden" id="shop_id" name="shop_id" value="{{ $id }}" readonly="">
    <br>
    <label for="inq_subject">Subject</label>
    <input type="text" class="form-control" name="inq_subject" id="" aria-describedby="helpId" placeholder="Subject: About...">

  </div>
  <div class="form-group">
    <label for="exampleFormControlSelect1">Example select</label>
    <select class="form-control" id="exampleFormControlSelect1" required>
      <option>Select--</option>
      <option value="1">1: This issue and purchasing are two different things.</option>
      <option value="2">2: I will purchase it even if this problem is not resolved.</option>
      <option value="3">3: I will buy it when this problem is resolved.</option>
      <option value="4">4: If this problem is resolved, I will positively consider purchasing.</option>
      <option value="5">5: This is about the product you purchased and received.</option>
      <option value="6">6: I would like to cancel.</option>
    </select>
  </div>
  <div class="form-group">
    <label for="exampleFormControlSelect1">For those who selected "6. I would like to cancel."</label>
    <select class="form-control" id="exampleFormControlSelect1" required>
      <option >Select--</option>
      <option value="1">1: I received a different product.</option>
      <option value="2">2: I was not satisfied with the product------</option>
      <option value="3">3: The delivery was delayed/I was worried about the delivery.</option>
      <option value="4">4: I made a mistake in my order-------</option>
      <option value="5">5: I'm dissatisfied with the price------</option>
      <option value="6">6: There was a price change after I made the purchase and I wanted to cancel.------</option>
      <option value="7">7: I am in trouble because I purchased this product due to forceful solicitation.Please consider consulting with the consumer center etc.------</option>
    </select>
  </div>
  <br>

    <div class="form-group">
        <label for="inquiry_details">Inquiry Detail</label>
        <textarea class="form-control" name="exampleFormControlTextarea1" id="" rows="3" placeholder="Detail"></textarea>
    </div><br>

    <div class="form-group">
    <h4>写真・ファイル送信に関する注意事項</h4>

    <small>配送を受けた方や第三者が本フォームを通じて送信する写真やファイルに関して、以下の行為を禁止いたします：<br>

        商品の内容に対する虚偽の加工や改変<br>
        商品の作り手、販売者、配送業者を含む、その他の不特定または特定の人物、団体に対する誹謗中傷や不正確な表現を含む加工や改変<br>
        送信される写真やファイルは、配送を受けた商品の状態を正確に反映したものとしてください。虚偽の内容や不正確な表現を含む場合、法的措置を取る場合がありますので、ご注意ください。
    </small><br>    
    <label for="exampleFormControlFile1">Example file input</label>
    <input type="file" class="form-control-file" id="exampleFormControlFile1"><br><br>
  </div>
  <button type="submit" class="btn btn-primary mb-2 mr-2">Submit</button>
  <br>
  <a href="{{ route('inquiries.answers', ['id'=>$id]) }}"><h6>Shop Manager Answers</h6></a> 


</form>

<form action="" method="post">
    @csrf
    <h3>This is an inquiry form for the seller or store manager.</h3>
    <input class="" type="hidden" id="user_id" name="user_id" value="{{ Auth::user()->id }}" readonly="">
    <input class="" type="hidden" id="shop_id" name="shop_id" value="{{ $id }}" readonly="">

    <div class="form-group">
        <label for="inq_subject">Subject</label>
        <input type="text" class="form-control" name="inq_subject" id="" aria-describedby="helpId" placeholder="Subject: About...">
    </div><br>

    <div class="form-group">
        <label for="inquiry_details">Inquiry Detail</label>
        <textarea class="form-control" name="inquiry_details" id="" rows="3" placeholder="Detail"></textarea>
    </div><br>

    <div class="form-group">
    	<button class="btn btn-primary" type="submit">Submit</button>
    </div><br>

</form>

<a href="{{ route('inquiries.answers', ['id'=>$id]) }}">Shop Manager Answers</a>    


@endsection