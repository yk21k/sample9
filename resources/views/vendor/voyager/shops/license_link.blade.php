@if($dataTypeContent->{$row->field} && auth()->user()->hasRole('admin'))
    <a class="btn btn-sm btn-primary" 
       href="{{ route('admin.shop-license.show', [$dataTypeContent->id, $dataTypeContent->{$row->field}]) }}" 
       target="_blank">
        ファイル確認
    </a>
@else
    <span class="text-muted">非表示</span>
@endif
