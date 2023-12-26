<?php
$selectedValue = '';
$list_options = [
    'economy' => __('Economy'),
    'vip' => __('VIP'),
];

if (Request::query('car_type')) {
    $selectedValue = Request::query('car_type');
}

if (empty($inputName)) {
    $inputName = 'car_type';
}

$type = $search_style ?? 'normal';
?>
<div class="">
    <span class="clear-dest absolute bottom-0 text-12"></span>
    <div data-x-dd-click="searchMenu-dest">
        <h4 class="text-15 fw-500 ls-2 lh-16">{{ $field['title'] }}</h4>
        <div class="text-15 text-light-1 ls-2 lh-16 @if( $type == "autocomplete") smart-search @endif">
            <select name="{{ $inputName }}" class="form-select">
                <option value="" disabled selected>{{ __('Choose Car Type') }}</option>
                @foreach($list_options as $value => $label)
                    <option value="{{ $value }}" @if($selectedValue == $value) selected @endif>{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>
