<?php
$selectedValue = '';
$list_options = [
    'economy' => __('Economy'),
    'vip' => __('VIP'),
];

// if (Request::query('car_type')) {
//     $selectedValue = Request::query('car_type');
// }

// if (empty($inputName)) {
//     $inputName = 'car_type';
// }


$list_json[] = [
            // 'id' => $location->id,
            // 'title' => $prefix . ' ' . $translate->name,
            'title1' => __('Economy'),
    'title2' => __('VIP'),
        ];

$type = $search_style ?? 'normal';
$value = '';

?>
{{-- <div class="">
    <span class="clear-dest absolute bottom-0 text-12"></span>
    <div data-x-dd-click="searchMenu-dest">
        <h4 class="text-15 fw-500 ls-2 lh-16">{{ $field['title'] }}</h4>
        <div class="text-15 text-light-1 ls-2 lh-16 @if( $type == "autocomplete") smart-search @endif">
            <select name="{{ $inputName }}">
                <option  style="margin-top: 10rem" value="" disabled selected>{{ __('Choose Car Type') }}</option>
                @foreach($list_options as $value => $label)
                    <option value="{{ $value }}" @if($selectedValue == $value) selected @endif>{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </div>
</div> --}}

<!-------------- The design like the theme -------------->
<div class="searchMenu-loc js-form-dd item">
    <span class="clear-loc absolute bottom-0 text-12"><i class="icon-close"></i></span>
    <div data-x-dd-click="searchMenu-loc" class="CarType">
        <h4 class="text-15 fw-500 ls-2 lh-16">{{ $field['title'] }}</h4>
        <div class="text-15 text-light-1 ls-2 lh-16  @if( $type == "autocomplete") smart-search  @endif ">
            <input type="hidden" name="car_type" value="{{ $selectedValue }}" id="valueSent" class="js-search-get-id">
            <input type="text" autocomplete="off" @if( $type == "normal") readonly
             @endif class="smart-search-car_type parent_text js-search js-dd-focus"
              placeholder="{{__("Choose Car Type")}}" value="{{ $selectedValue }}" data-onLoad="{{__("Loading...")}}"
               data-default="{{ json_encode($list_json) }}"
               >
        </div>
    </div>
    <div class="searchMenu-loc__field shadow-2 js-popup-window  @if($type!='normal') d-none @endif" data-x-dd="searchMenu-loc" data-x-dd-toggle="-is-active">
        <div class="bg-white px-30 py-30 sm:px-0 sm:py-15 rounded-4">
            <div class="y-gap-5 js-results">
                @foreach($list_options as $value => $label)
                    <div class="-link d-block col-12 text-left rounded-4 px-20 py-15 js-search-option">
                        <div class="d-flex align-items-center">
                            <div class="icon-location-2 text-light-1 text-20 pt-4"></div>
                            <div class="ml-10">

                                 <div class="text-15 lh-12 fw-500 js-search-option-target"
                                  onclick="selectOption('{{ $value }}', '{{ $label }}')"
                                  >{{ $label }}</div>
                            </div>
                        </div>
                    </div>
                    @endforeach
            </div>
        </div>
    </div>
</div>

{{-- <script>
    function logSelectedValue(value) {
        console.log('Selected value:', value);
    }
</script> --}}
{{-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script> --}}
<script>
    function selectOption(value, label) {

        document.getElementById('valueSent').value = value;
        document.querySelector('.smart-search-car_type').value = label;

        const dropdownToggle = document.querySelector('[class="CarType"]');
if (dropdownToggle) {
    dropdownToggle.click();
}

        }
</script>

{{-- <script>
    var value = document.getElementById('inputValue');
    console.log("Value is: ", value);
</script> --}}

{{-- <select name="{{ $inputName }}"> --}}
{{-- <div class="searchMenu-loc js-form-dd js-liverSearch item">
    <span class="clear-loc absolute bottom-0 text-12"><i class="icon-close"></i></span>
    <div data-x-dd-click="searchMenu-loc">
        <h4 class="text-15 fw-500 ls-2 lh-16">{{ $field['title'] }}</h4>
        <div value="" disabled selected>{{ __('Choose Car Type') }}</div>
        <div class="text-15 text-light-1 ls-2 lh-16  @if( $type == "autocomplete") smart-search  @endif "> --}}
            {{-- <input type="hidden" name="{{$inputName}}" class="js-search-get-id" value="{{ $location_id ?? '' }}"> --}}
            {{-- <input type="text" autocomplete="off" @if( $type == "normal") readonly  @endif class="smart-search-location parent_text js-search js-dd-focus" placeholder="{{__("Where are you going?")}}" value="{{ $location_name }}" data-onLoad="{{__("Loading...")}}" data-default="{{ json_encode($list_json) }}"> --}}
        {{-- </div>
    </div> --}}

    {{-- <div class="searchMenu-loc__field shadow-2 js-popup-window  @if($type!='normal') d-none @endif" style="max-height: 300px;
    overflow-y: scroll;" data-x-dd="searchMenu-loc" data-x-dd-toggle="-is-active">
        <div class="bg-white px-30 py-30 sm:px-0 sm:py-15 rounded-4">

            <div class="y-gap-5 js-results"> --}}
                {{-- @foreach($list_json as $location) --}}
                {{-- @foreach($list_options as $value => $label) --}}
                    {{-- <div class="-link d-block col-12 text-left rounded-4 px-20 py-15 js-search-option" data-id="{{ $location['id'] }}"> --}}
                        {{-- <div class="d-flex align-items-center">

                            <div class="icon-location-2 text-light-1 text-20 pt-4"></div>
                            <div class="ml-10"> --}}
                                {{-- <div class="text-15 lh-12 fw-500 js-search-option-target">{{ $location['title'] }}</div> --}}
                                {{-- <div value="{{ $value }}" @if($selectedValue == $value) selected @endif>{{ $label }}</div>
                            </div>
                        </div>
                    </div>
                    @endforeach --}}
                    {{-- @endforeach --}}
            {{-- </div>
        </div>
    </div>
</div> --}}
{{-- </select> --}}





<!----------------- With Style Css ------------------------->
{{-- <div class="custom-select-container">
    <span class="clear-dest absolute bottom-0 text-12"></span>
    <div data-x-dd-click="searchMenu-dest">
        <h4 class="text-15 fw-500 ls-2 lh-16">{{ $field['title'] }}</h4>
        <div class="custom-select-wrapper text-15 text-light-1 ls-2 lh-16 @if( $type == "autocomplete") smart-search @endif">
            <details class="custom-select">
                <summary class="radios">
                    <input type="radio" name="{{ $inputName }}" id="default" title="{{ __('Choose Car Type') }}" @if(empty($selectedValue)) checked @endif>
                    @foreach($list_options as $value => $label)
                        <input type="radio" name="{{ $inputName }}" id="{{ $value }}" title="{{ $label }}" @if($selectedValue == $value) checked @endif>
                    @endforeach
                </summary>
                <ul class="list">
                    @foreach($list_options as $value => $label)
                        <li>
                            <label for="{{ $value }}">
                                {{ $label }}
                                <span></span>
                            </label>
                        </li>
                    @endforeach
                </ul>
            </details>
        </div>
    </div>
</div> --}}



