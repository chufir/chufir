<?php $destination_name = ""; $destination_id = ''; $list_json_destination = [];
$traverse = function ($locations, $prefix = '') use (&$traverse, &$list_json_destination , &$destination_name, &$destination_id) {
    foreach ($locations as $location) {
        $translate = $location->translate();
        if (Request::query('destination_id') == $location->id){
            $destination_name = $translate->name;
            $destination_id = $location->id;
        }
        $list_json_destination[] = [
            'id' => $location->id,
            'title' => $prefix . ' ' . $translate->name,
        ];
        $traverse($location->children, $prefix . '-');
    }
};
$traverse($list_location ?? $tour_location);
if (empty($inputName)){
    $inputName = 'destination_id';
}
$type = $search_style ?? "normal";
// $type = "autocomplete";
?>
<div class="searchMenu-loc js-form-dd js-liverSearch item">
    <span class="clear-loc absolute bottom-0 text-12"><i class="icon-close"></i></span>
    <div data-x-dd-click="searchMenu-loc">
        <h4 class="text-15 fw-500 ls-2 lh-16">{{ $field['title'] }}</h4>
        <div class="text-15 text-light-1 ls-2 lh-16  @if( $type == "autocomplete") smart-search  @endif ">
            <input type="hidden" name="{{$inputName}}" class="js-search-get-id" value="{{ $destination_id ?? '' }}">
            <input type="text" autocomplete="on"
             {{-- @if( $type == "normal") readonly  @endif --}}
             class="smart-search-location parent_text js-search js-dd-focus" placeholder="{{__("Where are you going?")}}" value="{{ $destination_name }}" data-onLoad="{{__("Loading...")}}" data-default="{{ json_encode($list_json_destination) }}">
        </div>
    </div>
    <div class="searchMenu-loc__field shadow-2 js-popup-window @if($type!='normal') d-none @endif" style="max-height: 300px;
    overflow-y: scroll;" data-x-dd="searchMenu-loc" data-x-dd-toggle="-is-active">
        <div class="bg-white px-30 py-30 sm:px-0 sm:py-15 rounded-4">
            <div class="y-gap-5 js-results">
                @foreach($list_json_destination as $location)
                    <div class="-link d-block col-12 text-left rounded-4 px-20 py-15 js-search-option" data-id="{{ $location['id'] }}">
                        <div class="d-flex align-items-center">
                            <div class="icon-location-2 text-light-1 text-20 pt-4"></div>
                            <div class="ml-10">
                                <div class="text-15 lh-12 fw-500 js-search-option-target">{{ $location['title'] }}</div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
