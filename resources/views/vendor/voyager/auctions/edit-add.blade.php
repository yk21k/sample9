@php
    $edit = !is_null($dataTypeContent->getKey());
    $add  = is_null($dataTypeContent->getKey());
    use App\Models\Product;
@endphp

@extends('voyager::master')

@section('css')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@stop

@section('page_title', __('voyager::generic.'.($edit ? 'edit' : 'add')).' '.$dataType->getTranslatedAttribute('display_name_singular'))

@section('page_header')
    <h1 class="page-title">
        <i class="{{ $dataType->icon }}"></i>
        {{ __('voyager::generic.'.($edit ? 'edit' : 'add')).' '.$dataType->getTranslatedAttribute('display_name_singular') }}
    </h1>
    
    @include('voyager::multilingual.language-selector')
@stop

@section('content')
    <div class="page-content edit-add container-fluid">
        <div class="row">

            <!-- <h2>jfgia:@gaiog:a</h2> -->
            <div class="col-md-12">

                <div class="panel panel-bordered">
                    <!-- form start -->
                    <form role="form"
                            class="form-edit-add"
                            action="{{ $edit ? route('voyager.'.$dataType->slug.'.update', $dataTypeContent->getKey()) : route('voyager.'.$dataType->slug.'.store') }}"
                            method="POST" enctype="multipart/form-data">
                        <!-- PUT Method if we are editing -->
                        @if($edit)
                            {{ method_field("PUT") }}
                        @endif

                        <!-- CSRF TOKEN -->
                        {{ csrf_field() }}

                        <div class="panel-body">

                            @if (count($errors) > 0)
                                <div class="alert alert-danger">
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <!-- Adding / Editing -->
                            @php
                                $dataTypeRows = $dataType->{($edit ? 'editRows' : 'addRows' )};
                            @endphp
                            

                            @foreach($dataTypeRows as $row)
                                <!-- GET THE DISPLAY OPTIONS -->
                                @php
                                    $display_options = $row->details->display ?? NULL;
                                    if ($dataTypeContent->{$row->field.'_'.($edit ? 'edit' : 'add')}) {
                                        $dataTypeContent->{$row->field} = $dataTypeContent->{$row->field.'_'.($edit ? 'edit' : 'add')};
                                    }
                                @endphp
                                
                                @if (isset($row->details->legend) && isset($row->details->legend->text))
                                    <legend class="text-{{ $row->details->legend->align ?? 'center' }}" style="background-color: {{ $row->details->legend->bgcolor ?? '#f0f0f0' }};padding: 5px;">{{ $row->details->legend->text }}</legend>
                                @endif

                                <div class="form-group @if($row->type == 'hidden') hidden @endif col-md-{{ $display_options->width ?? 12 }} {{ $errors->has($row->field) ? 'has-error' : '' }}" @if(isset($display_options->id)){{ "id=$display_options->id" }}@endif>
                                    {{ $row->slugify }}
                                    <label class="control-label" for="name">{{ $row->getTranslatedAttribute('display_name') }}</label>
                                    @include('voyager::multilingual.input-hidden-bread-edit-add')
                                    @if ($add && isset($row->details->view_add))
                                        @include($row->details->view_add, ['row' => $row, 'dataType' => $dataType, 'dataTypeContent' => $dataTypeContent, 'content' => $dataTypeContent->{$row->field}, 'view' => 'add', 'options' => $row->details])
                                    @elseif ($edit && isset($row->details->view_edit))
                                        @include($row->details->view_edit, ['row' => $row, 'dataType' => $dataType, 'dataTypeContent' => $dataTypeContent, 'content' => $dataTypeContent->{$row->field}, 'view' => 'edit', 'options' => $row->details])
                                    @elseif (isset($row->details->view))
                                        @include($row->details->view, ['row' => $row, 'dataType' => $dataType, 'dataTypeContent' => $dataTypeContent, 'content' => $dataTypeContent->{$row->field}, 'action' => ($edit ? 'edit' : 'add'), 'view' => ($edit ? 'edit' : 'add'), 'options' => $row->details])
                                    @elseif ($row->type == 'relationship')
                                      

                                        @if($row->display_name == 'shops' && auth()->user()->hasRole('seller'))
                                            {{auth()->user()->shop->name ?? 'n/a'}}
                                            <input type="hidden" name="shop_id" value="{{auth()->user()->shop->id}}">
                                        @else
                                            @include('voyager::formfields.relationship', ['options' => $row->details])
                                        @endif
                            
                                    @else
                                        {!! app('voyager')->formField($row, $dataType, $dataTypeContent) !!}
                                    @endif
                                    
                                    @foreach (app('voyager')->afterFormFields($row, $dataType, $dataTypeContent) as $after)
                                        {!! $after->handle($row, $dataType, $dataTypeContent) !!}
                                    @endforeach

                                    @if ($errors->has($row->field))
                                        @foreach ($errors->get($row->field) as $error)
                                            <span class="help-block">{{ $error }}</span>
                                        @endforeach
                                    @endif

                                    {{-- 20250715hidden input で JS に渡す --}}
                                    @php
                                        $auction = \App\Models\Auction::with('bids')->find($dataTypeContent->id);

                                        $hasBids = $auction && $auction->bids->isNotEmpty();
                                    @endphp

                                    <input type="hidden" id="has-bids-flag" value="{{ $hasBids ? 1 : 0 }}">


                                </div>
                                
                            @endforeach
                            

                            
                            @php
                                $attributeOptions = \App\Models\Attribute::with('values')->get();
                            @endphp



                            @if( $attributeOptions->isEmpty() )
                                <p>No attribute</p>
                            @endif


                        </div><!-- panel-body -->

                        <div class="panel-footer">
                            @section('submit-buttons')
                                <button type="submit" class="btn btn-primary save">{{ __('voyager::generic.save') }}</button>
                            @stop
                            @yield('submit-buttons')
                        </div>
                    </form>
                    
                    <div style="display:none">
                        <input type="hidden" id="upload_url" value="{{ route('voyager.upload') }}">
                        <input type="hidden" id="upload_type_slug" value="{{ $dataType->slug }}">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade modal-danger" id="confirm_delete_modal">
        <div class="modal-dialog">
            <div class="modal-content">

                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"
                            aria-hidden="true">&times;</button>
                    <h4 class="modal-title"><i class="voyager-warning"></i> {{ __('voyager::generic.are_you_sure') }}</h4>
                </div>

                <div class="modal-body">
                    <h4>{{ __('voyager::generic.are_you_sure_delete') }} '<span class="confirm_delete_name"></span>'</h4>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">{{ __('voyager::generic.cancel') }}</button>
                    <button type="button" class="btn btn-danger" id="confirm_delete">{{ __('voyager::generic.delete_confirm') }}</button>
                </div>
            </div>
        </div>
    </div>
    <!-- End Delete File Modal -->




@stop

@section('javascript')
    <script>
        var params = {};
        var $file;

        function deleteHandler(tag, isMulti) {
          return function() {
            $file = $(this).siblings(tag);

            params = {
                slug:   '{{ $dataType->slug }}',
                filename:  $file.data('file-name'),
                id:     $file.data('id'),
                field:  $file.parent().data('field-name'),
                multi: isMulti,
                _token: '{{ csrf_token() }}'
            }

            $('.confirm_delete_name').text(params.filename);
            $('#confirm_delete_modal').modal('show');
          };
        }

        $('document').ready(function () {
            $('.toggleswitch').bootstrapToggle();

            //Init datepicker for date fields if data-datepicker attribute defined
            //or if browser does not handle date inputs
            $('.form-group input[type=date]').each(function (idx, elt) {
                if (elt.hasAttribute('data-datepicker')) {
                    elt.type = 'text';
                    $(elt).datetimepicker($(elt).data('datepicker'));
                } else if (elt.type != 'date') {
                    elt.type = 'text';
                    $(elt).datetimepicker({
                        format: 'L',
                        extraFormats: [ 'YYYY-MM-DD' ]
                    }).datetimepicker($(elt).data('datepicker'));
                }
            });

            @if ($isModelTranslatable)
                $('.side-body').multilingual({"editing": true});
            @endif

            $('.side-body input[data-slug-origin]').each(function(i, el) {
                $(el).slugify();
            });

            $('.form-group').on('click', '.remove-multi-image', deleteHandler('img', true));
            $('.form-group').on('click', '.remove-single-image', deleteHandler('img', false));
            $('.form-group').on('click', '.remove-multi-file', deleteHandler('a', true));
            $('.form-group').on('click', '.remove-single-file', deleteHandler('a', false));

            $('#confirm_delete').on('click', function(){
                $.post('{{ route('voyager.'.$dataType->slug.'.media.remove') }}', params, function (response) {
                    if ( response
                        && response.data
                        && response.data.status
                        && response.data.status == 200 ) {

                        toastr.success(response.data.message);
                        $file.parent().fadeOut(300, function() { $(this).remove(); })
                    } else {
                        toastr.error("Error removing file.");
                    }
                });

                $('#confirm_delete_modal').modal('hide');
            });
            $('[data-toggle="tooltip"]').tooltip();
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const suggestedInput = document.querySelector('input[name="suggested_price"]');
            const spotInput = document.querySelector('input[name="spot_price"]');

            function validatePriceDifference() {
                const suggested = parseFloat(suggestedInput.value);
                const spot = parseFloat(spotInput.value);

                if (!isNaN(suggested) && !isNaN(spot)) {
                    const diff = Math.abs(spot - suggested);
                    if (diff >= 2000) {
                        spotInput.setCustomValidity("初期価格と即決価格の差額は2000円未満にしてください。");
                    } else {
                        spotInput.setCustomValidity("");
                    }
                }
            }

            suggestedInput.addEventListener('input', validatePriceDifference);
            spotInput.addEventListener('input', validatePriceDifference);
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const radios = document.querySelectorAll('input[name="delivery_status"]');
            const selected = document.querySelector('input[name="delivery_status"]:checked');

            if (selected && selected.value === "3") {
                radios.forEach(radio => {
                    radio.disabled = true;
                });
            }
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // 配送ステータスのラジオボタン群
            const radios = document.querySelectorAll('input[name="delivery_status"]');

            // shipping_companyとreception_numberのinput要素
            const shippingCompanyInput = document.querySelector('input[name="shipping_company"]');
            const receptionNumberInput = document.querySelector('input[name="reception_number"]');

            // これらのinput要素の親フォームグループを取得
            const shippingCompanyGroup = shippingCompanyInput.closest('.form-group');
            const receptionNumberGroup = receptionNumberInput.closest('.form-group');

            function toggleShippingFields() {
                const checked = document.querySelector('input[name="delivery_status"]:checked');
                if (checked && checked.value === '2') {
                    shippingCompanyGroup.style.display = 'block';
                    receptionNumberGroup.style.display = 'block';
                } else {
                    shippingCompanyGroup.style.display = 'none';
                    receptionNumberGroup.style.display = 'none';
                }
            }

            toggleShippingFields();

            radios.forEach(radio => {
                radio.addEventListener('change', toggleShippingFields);
            });
        });

    </script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const radios = document.querySelectorAll('input[name="delivery_status"]');
            const shippingInput = document.querySelector('input[name="shipping_company"]');
            const receptionInput = document.querySelector('input[name="reception_number"]');

            function toggleRequired() {
                const checked = document.querySelector('input[name="delivery_status"]:checked');
                if (checked && checked.value === '2') {
                    shippingInput.required = true;
                    receptionInput.required = true;
                } else {
                    shippingInput.required = false;
                    receptionInput.required = false;
                }
            }

            toggleRequired();  // 初期判定

            radios.forEach(radio => radio.addEventListener('change', toggleRequired));
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const steps = ['0', '1', '2', '3'];
            const radios = document.querySelectorAll('input[name="delivery_status"]');

            // 初期選択状態を取得
            const selectedRadio = document.querySelector('input[name="delivery_status"]:checked');
            const currentIndex = steps.indexOf(selectedRadio?.value);

            radios.forEach(radio => {
                const index = steps.indexOf(radio.value);

                // 現在より前のステップを無効化
                if (index < currentIndex) {
                    radio.disabled = true;
                }
            });

            // 選択変更時に前へ戻れないよう再チェック（※必要なら有効化）
            radios.forEach(radio => {
                radio.addEventListener('change', function () {
                    const newIndex = steps.indexOf(this.value);
                    radios.forEach(r => {
                        const idx = steps.indexOf(r.value);
                        r.disabled = idx < newIndex;
                    });
                });
            });
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const hasBids = document.getElementById('has-bids-flag');

            if (hasBids && hasBids.value === '1') {
                // name属性で要素を取得してdisabledを付ける
                const startInput = document.querySelector('input[name="start"]');
                const endInput = document.querySelector('input[name="end"]');

                if (startInput) startInput.disabled = true;
                if (endInput) endInput.disabled = true;
            }
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const hasBids = document.getElementById('has-bids-flag').value === '1';

            if (hasBids) {
                // 入札がある場合は、すべての <input data-field> を無効にする
                document.querySelectorAll('input[data-field]').forEach(function (input) {
                    input.disabled = true;
                });
            }
        });
    </script>




@stop
