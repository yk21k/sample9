@extends('layouts.seller')

@section('content')

<h3>Shop Setting</h3>

<div class="container">
    <h2>書類提出フォーム</h2>

    @foreach($shop_settings as $shop_setting)
        <form action="" method="POST" enctype="multipart/form-data">
            @csrf

            <!-- ラジオ選択 -->
            <div class="mb-3">
                <label class="form-label">本人確認書類の種類を選択してください：</label><br>
                <div>
                    <input type="radio" name="id_type" value="license" id="id_license" checked>
                    <label for="id_license">運転免許証</label>

                    <input type="radio" name="id_type" value="passport" id="id_passport">
                    <label for="id_passport">パスポート</label>
                </div>
            </div>

            <!-- 法人登記など -->
            <div class="mb-3">
                <label for="corporate_registration" class="form-label">法人登記（PDFまたは画像)/個人事業主証明書</label><br>
                <label>登録日：{{ $shop_setting->updated_at->format('Y年m月d日') }} 最新のものをアップして下さい</label>
                <input type="file" name="corporate_registration" class="form-control">
            </div>

            <!-- 運転免許証セクション -->
            <div id="license_section">
                <div class="mb-3">
                    <label class="form-label">運転免許証（表面）</label>
                    <input type="file" name="license_front" class="form-control">
                </div>
                <div class="mb-3">
                    <label class="form-label">運転免許証（裏面）</label>
                    <input type="file" name="license_back" class="form-control">
                </div>
                <div class="mb-3">
                    <label for="license_expiry" class="form-label">運転免許証の有効期限</label>
                    <input type="date" name="license_expiry" class="form-control">
                </div>
            </div>

            <!-- パスポートセクション -->
            <div id="passport_section" style="display:none;">
                <div class="mb-3">
                    <label for="passport_photos" class="form-label">パスポート写真（画像/PDF）</label>
                    <input type="file" name="passport_photos" class="form-control">
                </div>
                <div class="mb-3">
                    <label for="passport_expiry" class="form-label">パスポートの有効期限</label>
                    <input type="date" name="passport_expiry" class="form-control">
                </div>
            </div>

			<!-- 共通入力 -->
			<div class="mb-3">
			    <label for="responsible_person" class="form-label">責任者氏名</label>
			    <input type="text" name="responsible_person" class="form-control">

			    <label for="responsible_person_photo" class="form-label mt-2">責任者の写真（画像）</label>
			    <input type="file" name="responsible_person_photo" class="form-control">
			</div>

			<div class="mb-3">
			    <label for="staff_1" class="form-label">担当者1氏名</label>
			    <input type="text" name="staff_1" class="form-control">

			    <label for="staff_1_photo" class="form-label mt-2">担当者1の写真（画像）</label>
			    <input type="file" name="staff_1_photo" class="form-control">
			</div>

			<div class="mb-3">
			    <label for="staff_2" class="form-label">担当者2氏名</label>
			    <input type="text" name="staff_2" class="form-control">

			    <label for="staff_2_photo" class="form-label mt-2">担当者2の写真（画像）</label>
			    <input type="file" name="staff_2_photo" class="form-control">
			</div>

			<div class="mb-3">
			    <label for="staff_3" class="form-label">担当者3氏名</label>
			    <input type="text" name="staff_3" class="form-control">

			    <label for="staff_3_photo" class="form-label mt-2">担当者3の写真（画像）</label>
			    <input type="file" name="staff_3_photo" class="form-control">
			</div>


            <button type="submit" class="btn btn-primary">提出する</button>
        </form>
    @endforeach
</div>

<!-- JavaScriptで切り替え -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const licenseRadio = document.getElementById('id_license');
        const passportRadio = document.getElementById('id_passport');
        const licenseSection = document.getElementById('license_section');
        const passportSection = document.getElementById('passport_section');

        function toggleSections() {
            if (licenseRadio.checked) {
                licenseSection.style.display = 'block';
                passportSection.style.display = 'none';
            } else if (passportRadio.checked) {
                licenseSection.style.display = 'none';
                passportSection.style.display = 'block';
            }
        }

        licenseRadio.addEventListener('change', toggleSections);
        passportRadio.addEventListener('change', toggleSections);

        // 初期化
        toggleSections();
    });
</script>

@endsection
