<div class="form-group">
    <label>{{ $row->display_name }}</label>

    {{-- 現在の画像 --}}
    @if(!empty($dataTypeContent->{$row->field}))
        <div style="margin-bottom:10px;">
            <img src="{{ Storage::disk('s3')->url($dataTypeContent->{$row->field}) }}" width="150">
        </div>
    @endif

    {{-- アップロード --}}
    <input type="file" name="{{ $row->field }}">

</div>