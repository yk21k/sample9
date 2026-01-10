
@extends('layouts.app')


@section('content')
<style>
    .hidden {
      display: none !important;
    }

    .form-check-input:checked + .form-check-label {
        font-weight: bold;
        background-color: #27627d;
        padding: 4px 8px;
        border-radius: 4px;
    }
</style>
<link rel="stylesheet" href="{{ asset('front/css/custom103.css') }}">


<h2>Submit Your Shop </h2>
@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="container mt-3">
  <form class="h-adr" id="shopForm" action="{{route('shops.store')}}" method="post" enctype="multipart/form-data">@csrf
    {{-- {{ csrf_field() }} --}}

    <div class="form-group">
      <label><h3>登録区分 *</h3></label>
      <div class="form-check">
        <input class="form-check-input" type="radio" name="registration_type" id="individual" value="個人">
        <label class="form-check-label" for="individual">個人</label>
      </div>
      <div class="form-check">
        <input class="form-check-input" type="radio" name="registration_type" id="sole_proprietor" value="個人事業主">
        <label class="form-check-label" for="sole_proprietor">個人事業主</label>
      </div>
      <div class="form-check">
        <input class="form-check-input" type="radio" name="registration_type" id="corporation" value="法人">
        <label class="form-check-label" for="corporation">法人</label>
      </div>
      <div class="form-check">
        <input class="form-check-input" type="radio" name="registration_type" id="outsourcing" value="業務請負">
        <label class="form-check-label" for="outsourcing">業務請負</label>
      </div>
    </div>
    <br>

    <div class="form-group">
        <label for="name"><h3>店名 * 上記の登録区分を必ず選択して下さい</h3></label>
        <input type="text" class="form-control" name="name" id="name" aria-describedby="helpId" value="{{ old('name', $shop_sets->name ?? '') }}" required>
    </div>
    &nbsp;<br><br>

    <a style="color:tomato;" href="https://www.invoice-kohyo.nta.go.jp/">国税庁インボイス制度適格請求書発行事業者公表サイト</a><small>必要時は検索し確認してください</small><br><br>

    <div class="form-group">
        <label for="invoice_number"><h3>インボイス番号：</h3></label>
        <input type="text" id="invoiceFormField" name="invoice_number" placeholder="T1234567890123"
       pattern="^[A-Za-z]\d{13}$"
       title="先頭は英字1文字、その後に数字13桁で入力してください。">
    </div><br><br>

    <div class="form-group">
        <label for="description"><h3>店舗概要 *</h3></label>
        <textarea class="form-control" name="description" id="description" rows="3" required>{{ old('description', $shop_sets->description ?? '') }}</textarea>
    </div>
    &nbsp;

    <div class="form-group">
        <label for="representative"> <h3>代表 *</h3></label>
        <input type="text" class="form-control" name="representative" id="representative" aria-describedby="helpId" value="{{ old('representative', $shop_sets->representative ?? '') }}" required>
    </div>
    <br>

    <div class="form-group">
        <label for="location_1"> <h3>所在地 * </h3><small>郵便番号で検索して下さい</small></label><br>
        <span class="p-country-name" style="display:none;">Japan</span>
        <label for="post-code">郵便番号:</label>
        <input type="text" class="p-postal-code" size="8" maxlength="8"><br>
        <input type="text" class="form-control p-region p-locality p-street-address p-extended-address" name="location_1" id="location_1" aria-describedby="helpId" value="{{ old('location_1', $shop_sets->location_1 ?? '') }}" required>
    </div>
    &nbsp;

    <div class="form-group">
        <label for="location_2"> <h3>配送先 </h3></label>
        <input type="text" class="form-control" name="location_2" id="location_2" aria-describedby="helpId" placeholder="Please enter the address if different from the address above." value="{{ old('location_2', $shop_sets->location_2 ?? '') }}">
    </div>
    &nbsp;

    <div class="form-group">
        <label for="telephone"> <h3>電話番号 *</h3></label>
        <input type="text" class="form-control" id="telephone" name="telephone" aria-describedby="helpId" placeholder="You can accept numbers with HYPHEN from both mobile and landline phones." value="{{ old('telephone', $shop_sets->telephone ?? '') }}" required>
    </div>
    &nbsp;

    <div class="form-group">
        <label for="email"> <h3>Email *</h3></label>
        <input type="email" class="form-control" name="email" id="email" aria-describedby="helpId" value="{{ old('email', $shop_sets->email ?? '') }}" required>
    </div>
    <br>
    <div id="idGroup1" style="display: none;">
        <label for="identification_1" class="form-label"><h3>ID card (運転免許証　or パスポート)</h3></label>
        <input class="form-control" list="identification1" name="identification_1" id="identification_1" value="{{ old('identification_1', $shop_sets->identification_1 ?? '') }}">
          <datalist id="identification1">
              <option value="運転免許証">
              <option value="パスポート">
          </datalist>
     
        <br>

        &nbsp;
        <div id="fileGroup1" class="form-group">
            <div class="form-group">
                <label for="file_1">  <div id="output1" style="font-size:20pt">上記の内容をアップロード</div></label>
                <input type="file" class="form-control" id="file_1" name="file_1" class="form-control" multiple>
            </div>
        </div>
        <br>
        <div id="fileGroup5" class="form-group">
            <div class="form-group">
                <label for="photo_4">  <div id="" style="font-size:20pt">上記の内容をアップロード(運転免許証の場合の裏面）</div></label>
                <input type="file" class="form-control" id="photo_4" name="photo_4" class="form-control" multiple>
            </div>
        </div>
        <br>

        <label for="license_expiry" class="form-label"><h3>上記の運転免許証/パスポートの有効期限</h3></label>
        <input class="form-control" type="date" name="license_expiry" id="license_expiry" value="{{ old('license_expiry', optional($shop_sets)->license_expiry ? \Carbon\Carbon::parse($shop_sets->license_expiry)->format('Y-m-d') : '') }}"
>
        <div id="fileGroup1" class="form-group">
            <div class="form-group">
                <label for="file_1">  <div id="output1" style="font-size:20pt">クレジットカードの請求書（当月を含む最新）</div></label>
                <input
                    type="file"
                    class="form-control"
                    id="identification_3"
                    name="identification_3"
                    accept="application/pdf"
                    required
                >
            </div>
        </div>
    </div> 
    &nbsp;  
    <div id="identificationGroup1" style="display: none;">
      <label for="identification_2_1" class="form-label">
        <h3>個人事業開始届出証明書
          <small>※請負の場合は発注者ではなく、受注者のもの</small>
        </h3>
      </label><br>
      
      <!-- identificationGroup1 -->
    　<input class="form-control" list="identification_list_1" name="identification_2_1" id="identification_2_1" value="個人事業開始届出証明書" readonly required>
      
      <datalist id="identification_list_1">
        <option value="個人事業開始届出証明書">
      </datalist>
    </div>
    &nbsp;

    &nbsp;
    <div id="identificationGroup2" style="display: none;">
      <label for="identification_2_2" class="form-label">
        <h3>商業・法人登記簿（履歴事項全部証明書）
          <small>※請負の場合は発注者ではなく、受注者のもの</small>
        </h3>
      </label><br>
      
    　<!-- identificationGroup2 -->
    　<input class="form-control" list="identification_list_2" name="identification_2_2" id="identification_2_2" value="商業・法人登記簿（履歴事項全部証明書）" readonly required>
      
      <datalist id="identification_list_2">
        <option value="商業・法人登記簿（履歴事項全部証明書）">
      </datalist>
    </div> 
    <br>

    <div id="fileGroup2" style="display: none;">
        <div class="form-group">
            <label for="file_2">  <div id="output2" style="font-size:20pt">上記の内容のアップロード</div></label>
            <input type="file" class="form-control" id="file_2" name="file_2" class="form-control" multiple>
        </div>
    </div>
    <br>

    <div class="form-group">
        <label for="person_1">  <h3>(担当者氏名１) *</h3></label>
        <input type="text" class="form-control" id="person_1" name="person_1" class="form-control" value="{{ old('person_1', $shop_sets->person_1 ?? '') }}" multiple>
        <br>
        <label for="id_1_1" class="form-label"><h3>担当者1の証明</h3></label>
        <select class="form-control" name="id_1_1" id="id_1_1" required>
            <option value="選択して下さい">選択して下さい</option>
            <option value="運転免許証">運転免許証</option>
            <option value="パスポート">パスポート</option>
            <option value="社員証">社員証</option>
            <option value="名刺">名刺</option>
            <option value="労働契約書">労働契約書</option>
        </select>
        <br>
        <label for="photo_1">  <h3>(上記内容のアップロード) *</h3></label>
        <input type="file" class="form-control" id="photo_1" name="photo_1" class="form-control" multiple>
        <br>

        <label for="photo_5">  <h3>上記の内容をアップロード(運転免許証の場合の裏面） *</h3></label>
        <input type="file" class="form-control" id="photo_5" name="photo_5" class="form-control" multiple>
    </div>
    

    <div class="form-group">
        <label for="person_2">  <h3>(担当者氏名２) *</h3></label>
        <input type="text" class="form-control" id="person_2" name="person_2" class="form-control" value="{{ old('person_2', $shop_sets->person_2 ?? '') }}" multiple>
        <label for="id_2_1" class="form-label"><h3>担当者2の証明</h3></label>
        <select class="form-control" name="id_2_1" id="id_2_1" required>
            <option value="選択して下さい">選択して下さい</option>
            <option value="運転免許証">運転免許証</option>
            <option value="パスポート">パスポート</option>
            <option value="社員証">社員証</option>
            <option value="名刺">名刺</option>
            <option value="労働契約書">労働契約書</option>
        </select>
        <br>
        <label for="photo_2">  <h3>(上記内容のアップロード) *</h3></label>
        <input type="file" class="form-control" id="photo_2" name="photo_2" class="form-control" multiple>
        <br>
        <label for="photo_6">  <h3>上記の内容をアップロード(運転免許証の場合の裏面） *</h3></label>
        <input type="file" class="form-control" id="photo_6" name="photo_6" class="form-control" multiple>
        <br>
    </div>
    &nbsp;

    <div class="form-group">
        <label for="person_3">  <h3>(担当者氏名３) *</h3></label>
        <input type="text" class="form-control" id="person_3" name="person_3" class="form-control" value="{{ old('person_3', $shop_sets->person_3 ?? '') }}" multiple>
        <label for="id_3_1" class="form-label"><h3>担当者3の証明</h3></label>
        <select class="form-control" name="id_3_1" id="id_3_1" required>
            <option value="選択して下さい">選択して下さい</option>
            <option value="運転免許証">運転免許証</option>
            <option value="パスポート">パスポート</option>
            <option value="社員証">社員証</option>
            <option value="名刺">名刺</option>
        </select>
        <br>
        <label for="photo_3">  <h3>(上記内容のアップロード)*</h3></label>
        <input type="file" class="form-control" id="photo_3" name="photo_3" class="form-control" multiple>
        <br>
        <label for="photo_7">  <h3>上記の内容をアップロード(運転免許証の場合の裏面） *</h3></label>
        <input type="file" class="form-control" id="photo_7" name="photo_7" class="form-control" multiple>
        <br>
    </div>

    <div id="fileGroup3" style="display: none;">
        <div class="form-group">
            <label for="file_3"> <div id="output3" style="font-size:20pt">file_3 * (Compatible with jpg, jpeg, png, pdf)</div></label>
            <input type="file" class="form-control" id="" name="" class="form-control" multiple>
        </div>
    </div>
    <br>

    <div id="fileGroup4" style="display: none;">
        <div class="form-group">
            <label for="file_4">  <h3>file_4 (Compatible with PDF only)　例：代表以外の担当者がいる場合は、担当者全員の集合写真など</h3></label>
            <input type="file" class="form-control" id="" name="" class="form-control" multiple>
        </div>
    </div> 
 
    <div id="extraFields" style="display: none;">
        <div class="form-group">
            <label for="manager">  <h3>manager *</h3></label>
            <input type="text" class="form-control" name="manager" id="manager" aria-describedby="helpId" value="{{ old('manager', $shop_sets->manager ?? '') }}" >
        </div>
        &nbsp;

        <div class="form-group">
            <label for="product_type">  <h3>product_type *</h3></label>
            <input type="text" class="form-control" name="product_type" id="product_type" aria-describedby="helpId" value="{{ old('product_type', $shop_sets->product_type ?? '') }}" >
        </div>
        &nbsp;

        <div class="form-group">
            <label for="volume">  <h3>volume *</h3></label>
            <input type="text" class="form-control" name="volume" id="volume" aria-describedby="helpId" value="{{ old('volume', $shop_sets->volume ?? '') }}">
        </div>
        &nbsp;

        <div class="form-group">
            <label for="note">  <h3>note</h3></label>
            <input type="text" class="form-control" name="note" id="" aria-describedby="helpId" value="{{ old('note', $shop_sets->note ?? '') }}">
        </div>
        &nbsp;
        

        <button type="submit" name="save_type" value="submit" class="btn btn-primary">申請する</button>
        <button type="button" id="saveDraftBtn" class="btn btn-secondary">下書き保存</button>
    </div>    
  </form>
</div>
&nbsp;

<script>
  document.addEventListener("DOMContentLoaded", function () {
    const registrationRadios = {
      individual: document.getElementById('individual'),
      soleProprietor: document.getElementById('sole_proprietor'),
      corporation: document.getElementById('corporation'),
      outsourcing: document.getElementById('outsourcing'),
    };

    const idGroup1 = document.getElementById('idGroup1');
    const identificationGroup1 = document.getElementById('identificationGroup1');
    const identificationGroup2 = document.getElementById('identificationGroup2');

    const fileGroup2 = document.getElementById('fileGroup2');
    const fileGroup3 = document.getElementById('fileGroup3');
    const fileGroup4 = document.getElementById('fileGroup4');
    const extraFields = document.getElementById('extraFields');

    const id1Select = document.getElementById('id_1_1');
    const id2Select = document.getElementById('id_2_1');
    const id3Select = document.getElementById('id_3_1');

    const identificationInput1 = document.getElementById('identification_2_1');
    const identificationInput2 = document.getElementById('identification_2_2');

    function updateSelectOptions(selectElement, options) {
      selectElement.innerHTML = '';

      const defaultOption = document.createElement('option');
      defaultOption.value = "選択して下さい";
      defaultOption.textContent = "選択して下さい";
      selectElement.appendChild(defaultOption);

      options.forEach(value => {
        const opt = document.createElement('option');
        opt.value = value;
        opt.textContent = value;
        selectElement.appendChild(opt);
      });
    }

    function updateForm() {
      const selectedType = document.querySelector('input[name="registration_type"]:checked')?.value;

      // 初期化
      idGroup1.style.display = 'none';
      identificationGroup1.style.display = 'none';
      identificationGroup2.style.display = 'none';
      fileGroup2.style.display = 'none';
      fileGroup3.style.display = 'none';
      fileGroup4.style.display = 'none';
      extraFields.style.display = 'block';

      // requiredも初期化
      if (identificationInput1) identificationInput1.required = false;
      if (identificationInput2) identificationInput2.required = false;

      // 個人
      if (selectedType === "個人") {
        idGroup1.style.display = 'block';
        extraFields.style.display = 'block';
        updateSelectOptions(id1Select, ['運転免許証', 'パスポート']);
        updateSelectOptions(id2Select, ['運転免許証', 'パスポート']);
        updateSelectOptions(id3Select, ['運転免許証', 'パスポート']);
      }

      // 個人事業主
      else if (selectedType === "個人事業主") {
        idGroup1.style.display = 'block';
        identificationGroup1.style.display = 'block';
        fileGroup2.style.display = 'block';
        extraFields.style.display = 'block';
        identificationInput1.required = true;
        updateSelectOptions(id1Select, ['運転免許証', 'パスポート']);
        updateSelectOptions(id2Select, ['運転免許証', 'パスポート']);
        updateSelectOptions(id3Select, ['運転免許証', 'パスポート']);
      }

      // 法人 or 業務請負（同じ扱い）
      else if (selectedType === "法人" || selectedType === "業務請負") {
        identificationGroup2.style.display = 'block';
        fileGroup2.style.display = 'block';
        extraFields.style.display = 'block';
        identificationInput2.required = true;
        updateSelectOptions(id1Select, ['社員証', '名刺', '労働契約書']);
        updateSelectOptions(id2Select, ['社員証', '名刺', '労働契約書']);
        updateSelectOptions(id3Select, ['社員証', '名刺', '労働契約書']);
      }
    }

    // イベントリスナー登録
    Object.values(registrationRadios).forEach(radio => {
      radio.addEventListener('change', updateForm);
    });

    // 初期表示
    updateForm();
  });
</script>

<script>
document.getElementById('saveDraftBtn').addEventListener('click', function () {
    // フォーム内の全ての required 属性を一時的に無効に
    document.querySelectorAll('#shopForm [required]').forEach(el => {
        el.removeAttribute('required');
    });

    // 下書き保存用に hidden を追加
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'save_type';
    input.value = 'draft';
    document.getElementById('shopForm').appendChild(input);

    document.getElementById('shopForm').submit();
});
</script>


<!-- インボイスPOST -->





@endsection
