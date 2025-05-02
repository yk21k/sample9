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

<form action="{{ route('customer.inquiries', ['shopId' => $id]) }}" method="post" enctype="multipart/form-data">@csrf
  <div class="form-group">
    <label for="exampleFormControlInput1"><h3>出品者の方や当サイトのショップへのお問い合わせはこちらからお願いいたします。<br><strong>返品・返金につきましては出品者の方や各ショップの判断となりますので予めご了承ください。</strong></h3></label>
    <input class="" type="hidden" id="user_id" name="user_id" value="{{ Auth::user()->id }}" readonly>
    <input class="" type="hidden" id="shop_id" name="shop_id" value="{{ $id }}" readonly>
  </div>

  <div class="form-group">
    <label for="exampleFormControlSelect1">お問い合わせの件名の種別</label>
    <select class="form-control" id="exampleFormControlSelect1" name="inq_subject" required>
      <option value="" disabled selected>Select--</option>
      <option value="1">1: このお問い合わせの回答や問題解決と購入とは別のものです。</option>
      <option value="2">2: このお問い合わせの回答や問題解決が解決されなくても購入します。</option>
      <option value="3">3: このお問い合わせの回答や問題解決が解決したら購入します。</option>
      <option value="4">4: この問題が解決されれば、購入を積極的に検討します。</option>
      <option value="5">5: ご購入・お受け取りいただいた商品についてです。</option>
      <option value="6">6: キャンセルしたい</option>
    </select>
  </div>

  <div class="form-group" id="cancel-reason-group" style="display: none;">
    <label for="inq_reason">「6.キャンセルしたい」を選択された方へ</label>
    <select class="form-control" id="inq_reason" name="inq_reason" required>
      <option value="" disabled selected>Select--</option>
      <option value="1">1: 違う商品が届きました。</option>
      <option value="2">2: 商品に満足できませんでした。</option>
      <option value="3">3: 配達が遅れました/間に合わないことが確定しました。</option>
      <option value="4">4: 注文を間違えました。</option>
      <option value="5">5: 価格に不満があります。</option>
      <option value="6">6: 購入後に価格が変わったのでキャンセルしたいです。</option>
      <option value="7">7: 強引な勧誘により本商品を購入したため困っています。こちらを選択する際は、消費者センター等へのご相談も検討ください。</option>
    </select>
  </div>

  <div class="form-group">
    <label for="inquiry_details">Inquiry Detail</label>
    <textarea class="form-control" name="inquiry_details" id="inquiry_details" rows="3" placeholder="Detail" required></textarea>
  </div>

  <div class="form-group">
    <h4>写真・ファイル送信に関する注意事項</h4>
    <small>配送を受けた方や第三者が本フォームを通じて送信する写真やファイルに関して、以下の行為を禁止いたします：<br>
      商品の内容に対する虚偽の加工や改変<br>
      商品の作り手、販売者、配送業者を含む、その他の不特定または特定の人物、団体に対する誹謗中傷や不正確な表現を含む加工や改変<br>
      送信される写真やファイルは、配送を受けた商品の状態を正確に反映したものとしてください。虚偽の内容や不正確な表現を含む場合、法的措置を取る場合がありますので、ご注意ください。
    </small><br>    
    <label for="exampleFormControlFile1"></label>
    <input type="file" class="form-control-file" name="inq_file" id="exampleFormControlFile1" accept="image/*,application/pdf"><br><br>
  </div>

  <button type="submit" class="btn btn-primary mb-2 mr-2">Submit</button>
  <br>
  <a href="{{ route('customer.answers', ['shopId' => $id]) }}"><h6>Shop Manager Answers</h6></a> 
</form>
   

<script>
  document.addEventListener('DOMContentLoaded', function () {
    const subjectSelect = document.getElementById('exampleFormControlSelect1');
    const cancelReasonGroup = document.getElementById('cancel-reason-group');
    const cancelReasonSelect = document.getElementById('inq_reason');

    subjectSelect.addEventListener('change', function () {
      if (this.value === '6') {
        cancelReasonGroup.style.display = 'block';
        cancelReasonSelect.setAttribute('required', 'required');
      } else {
        cancelReasonGroup.style.display = 'none';
        cancelReasonSelect.removeAttribute('required');
        cancelReasonSelect.value = ''; // 初期化（オプション）
      }
    });
  });
</script>


@endsection