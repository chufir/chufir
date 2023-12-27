@extends('admin.layouts.app')

@section('content')
    <form action="{{route('car.admin.store',['id'=>($row->id) ? $row->id : '-1','lang'=>request()->query('lang')])}}" method="post">
        @csrf
        <div class="container-fluid">
            <div class="d-flex justify-content-between mb20">
                <div class="">
                    <h1 class="title-bar">{{$row->id ? __('Edit: ').$row->title : __('Add new car')}}</h1>
                    @if($row->slug)
                        <p class="item-url-demo">{{__("Permalink")}}: {{ url('car' ) }}/<a href="#" class="open-edit-input" data-name="slug">{{$row->slug}}</a>
                        </p>
                    @endif
                </div>
                <div class="">
                    @if($row->slug)
                        <a class="btn btn-primary btn-sm" href="{{$row->getDetailUrl(request()->query('lang'))}}" target="_blank">{{__("View Car")}}</a>
                    @endif
                </div>
            </div>
            @include('admin.message')
            @if($row->id)
                @include('Language::admin.navigation')
            @endif
            <div class="lang-content-box">
                <div class="row">
                    <div class="col-md-9">
                        @include('Car::admin.car.content')
                        @include('Car::admin.car.car_type')
                        @include('Car::admin.car.location')
                        @include('Car::admin.car.destination')
                        @include('Car::admin.car.pricing')
                        @if(is_default_lang())
                            {{-- @include('Car::admin.car.availability') --}}
                        @endif
                        @include('Core::admin/seo-meta/seo-meta')
                    </div>
                    <div class="col-md-3">
                        <div class="panel">
                            <div class="panel-title"><strong>{{__('Publish')}}</strong></div>
                            <div class="panel-body">
                                @if(is_default_lang())
                                    <div>
                                        <label><input @if($row->status=='publish') checked @endif type="radio" name="status" value="publish"> {{__("Publish")}}
                                        </label></div>
                                    <div>
                                        <label><input @if($row->status=='draft') checked @endif type="radio" name="status" value="draft"> {{__("Draft")}}
                                        </label></div>
                                @endif
                                <div class="text-right">
                                    <button class="btn btn-primary" type="submit"><i class="fa fa-save"></i> {{__('Save Changes')}}</button>
                                </div>
                            </div>
                        </div>
                        @if(is_default_lang())
                        <div class="panel">
                            <div class="panel-title"><strong>{{__("Author Setting")}}</strong></div>
                            <div class="panel-body">
                                <div class="form-group">
                                    <?php
                                    $user = $row->author;
                                    \App\Helpers\AdminForm::select2('author_id', [
                                        'configs' => [
                                            'ajax'        => [
                                                'url' => route('user.admin.getForSelect2'),
                                                'dataType' => 'json'
                                            ],
                                            'allowClear'  => true,
                                            'placeholder' => __('-- Select User --')
                                        ]
                                    ], !empty($user->id) ? [
                                        $user->id,
                                        $user->getDisplayName() . ' (#' . $user->id . ')'
                                    ] : false)
                                    ?>
                                </div>
                            </div>
                        </div>
                        @endif
                        @if(is_default_lang())
                            <div class="panel">
                                <div class="panel-title"><strong>{{__("Availability")}}</strong></div>
                                <div class="panel-body">
                                    <div class="form-group">
                                        <label>{{__('Car Featured')}}</label>
                                        <br>
                                        <label>
                                            <input type="checkbox" name="is_featured" @if($row->is_featured) checked @endif value="1"> {{__("Enable featured")}}
                                        </label>
                                    </div>
                                    <div class="form-group d-none">
                                        <label>{{__('Is Instant Booking?')}}</label>
                                        <br>
                                        <label>
                                            <input type="checkbox" name="is_instant" @if($row->is_instant) checked @endif value="1"> {{__("Enable instant booking")}}
                                        </label>
                                    </div>
                                    <div class="form-group">
                                        <label >{{__('Default State')}}</label>
                                        <br>
                                        <select name="default_state" class="custom-select">
                                            <option value="">{{__('-- Please select --')}}</option>
                                            <option value="1" @if(old('default_state',$row->default_state ?? 0) == 1) selected @endif>{{__("Always available")}}</option>
                                            <option value="0" @if(old('default_state',$row->default_state ?? 0) == 0) selected @endif>{{__("Only available on specific dates")}}</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            @include('Car::admin.car.attributes')
                            @include('Car::admin.car.ical')
                            <div class="panel">
                                <div class="panel-title"><strong>{{__('Feature Image')}}</strong></div>
                                <div class="panel-body">
                                    <div class="form-group">
                                        {!! \Modules\Media\Helpers\FileHelper::fieldUpload('image_id',$row->image_id) !!}
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection
@push('js')
    {{-- {!! App\Helpers\MapEngine::scripts() !!} --}}
    <script>
        jQuery(function ($) {
            new BravoMapEngine('map_content', {
                disableScripts: true,
                fitBounds: true,
                center: [{{$row->map_lat ?? setting_item('map_lat_default',51.505 ) }}, {{$row->map_lng ?? setting_item('map_lng_default',0.09 ) }}],
                zoom:{{$row->map_zoom ?? "8"}},
                ready: function (engineMap) {
                    @if($row->map_lat && $row->map_lng)
                    engineMap.addMarker([{{$row->map_lat}}, {{$row->map_lng}}], {
                        icon_options: {}
                    });
                    @endif
                    engineMap.on('click', function (dataLatLng) {
                        engineMap.clearMarkers();
                        engineMap.addMarker(dataLatLng, {
                            icon_options: {}
                        });
                        $("input[name=map_lat]").attr("value", dataLatLng[0]);
                        $("input[name=map_lng]").attr("value", dataLatLng[1]);
                    });
                    engineMap.on('zoom_changed', function (zoom) {
                        $("input[name=map_zoom]").attr("value", zoom);
                    });
                    if(bookingCore.map_provider === "gmap"){
                        engineMap.searchBox($('#customPlaceAddress'),function (dataLatLng) {
                            engineMap.clearMarkers();
                            engineMap.addMarker(dataLatLng, {
                                icon_options: {}
                            });
                            $("input[name=map_lat]").attr("value", dataLatLng[0]);
                            $("input[name=map_lng]").attr("value", dataLatLng[1]);
                        });
                    }
                    engineMap.searchBox($('.bravo_searchbox'),function (dataLatLng) {
                        engineMap.clearMarkers();
                        engineMap.addMarker(dataLatLng, {
                            icon_options: {}
                        });
                        $("input[name=map_lat]").attr("value", dataLatLng[0]);
                        $("input[name=map_lng]").attr("value", dataLatLng[1]);
                    });
                }
            });
        })



    // In case I have already the location and destination (edit car)

    var selectValue = document.getElementById('locationSelect');
    var selectDestinationValue = document.getElementById('destinationSelect');

    if(selectValue.value > 0 && selectDestinationValue.value > 0 ){
        var locationLat = localStorage.getItem('selectedMapLat');
        var locationLng = localStorage.getItem('selectedMapLng');
        var destinationLat = localStorage.getItem('selectedDestinationMapLat');
        var destinationLng = localStorage.getItem('selectedDestinationMapLng');


        if (locationLat > 0 && locationLng > 0 && destinationLat > 0 && destinationLng > 0) {
            // Update the HTML elements with the location data
            // document.getElementById('selectedLocationMapLatInPricing').innerHTML = locationLat;
            // document.getElementById('selectedLocationMapLngInPricing').innerHTML = locationLng;
            // document.getElementById('selectedDestinationMapLatInPricing').innerHTML = destinationLat;
            // document.getElementById('selectedDestinationMapLngInPricing').innerHTML = destinationLng;


        }
    }else{
    // Update the HTML elements with the location data
                // document.getElementById('selectedLocationMapLatInPricing').innerHTML = '';
                // document.getElementById('selectedLocationMapLngInPricing').innerHTML = '';
                // document.getElementById('selectedDestinationMapLatInPricing').innerHTML = '';
                // document.getElementById('selectedDestinationMapLngInPricing').innerHTML = '';
            }

        document.addEventListener('DOMContentLoaded', function() {
        // Select the #distanceDisplay element
        var distanceDisplay = document.getElementById('distanceDisplay');

        // Function to log the value and perform other actions
        function handleDistanceChange() {
            // Log the current value
            console.log("Distance:", distanceDisplay.innerText);
            // Add other actions as needed
        }

        // Attach an event listener to the distanceDisplay element
        distanceDisplay.addEventListener('DOMSubtreeModified', handleDistanceChange);

        // Log the initial value
        handleDistanceChange();
    });

    document.getElementById('price_per_km').addEventListener('input', function() {
            var pricePerKm = parseFloat(this.value);
            console.log("Price per km:", pricePerKm);

            var result = parseFloat(distanceDisplay.innerText) * pricePerKm;
            console.log(result);
            document.getElementById('fullPrice').value = result;

        });


    ///////////////////// Location ////////////////////


            document.getElementById('locationSelect').addEventListener('change', function() {
            var selectedLocationMapLat = this.options[this.selectedIndex].getAttribute('data-map-lat');
            var selectedLocationMapLng = this.options[this.selectedIndex].getAttribute('data-map-lng');
            updateLocationData(selectedLocationMapLat, selectedLocationMapLng);
        });

        function updateLocationData(mapLat, mapLng) {
            // Update fields in pricing.blade.php using the captured data
            document.getElementById('selectedLocationMapLatInPricing').innerHTML = mapLat;
            document.getElementById('selectedLocationMapLngInPricing').innerHTML = mapLng;
        }



        ///////////////////// Destination ////////////////////
            document.getElementById('destinationSelect').addEventListener('change', function() {
            var selectedDestinationMapLat = this.options[this.selectedIndex].getAttribute('data-map-lat');
            var selectedDestinationMapLng = this.options[this.selectedIndex].getAttribute('data-map-lng');
            updateDestinationData(selectedDestinationMapLat, selectedDestinationMapLng);

        });
        function updateDestinationData(mapLat, mapLng) {
            // Update fields in pricing.blade.php using the captured data
            document.getElementById('selectedDestinationMapLatInPricing').innerHTML = mapLat;
            document.getElementById('selectedDestinationMapLngInPricing').innerHTML = mapLng;
        }




    // Function to calculate the distance and cost between two points using Haversine formula
    function calculateDistanceAndCost(lat1, lon1, lat2, lon2, pricePerKm) {
        var R = 6371; // Radius of the Earth in kilometers
        var dLat = (lat2 - lat1) * (Math.PI / 180);
        var dLon = (lon2 - lon1) * (Math.PI / 180);
        var a =
            Math.sin(dLat / 2) * Math.sin(dLat / 2) +
            Math.cos(lat1 * (Math.PI / 180)) * Math.cos(lat2 * (Math.PI / 180)) *
            Math.sin(dLon / 2) * Math.sin(dLon / 2);
        var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        var distance = R * c; // Distance in kilometers

        // Calculate cost based on distance and price per km
        var cost = distance * pricePerKm;

        return { distance: distance, cost: cost };
    }

    // Function to update the distance and cost in the HTML elements
    function updateDistanceAndCost() {
        // Get the latitude, longitude, and price per km values
        var locationLat = parseFloat(document.getElementById('selectedLocationMapLatInPricing').innerText);
        var locationLng = parseFloat(document.getElementById('selectedLocationMapLngInPricing').innerText);
        var destinationLat = parseFloat(document.getElementById('selectedDestinationMapLatInPricing').innerText);
        var destinationLng = parseFloat(document.getElementById('selectedDestinationMapLngInPricing').innerText);
        var pricePerKm = parseFloat(document.getElementsByName('price_per_km')[0].value);

        // Check if values are not empty and pricePerKm is a valid number
        if (!isNaN(locationLat) && !isNaN(locationLng) && !isNaN(destinationLat) && !isNaN(destinationLng) && !isNaN(pricePerKm)) {
            // Calculate distance and cost
            var result = calculateDistanceAndCost(locationLat, locationLng, destinationLat, destinationLng, pricePerKm);

            // Update the HTML elements with the calculated distance and cost
            document.getElementById('distanceDisplay').innerText =  result.distance.toFixed(2) + ' km';

            // var price = result.distance.toFixed(2) * 5;
            // console.log(price);
            // document.getElementById('costDisplay').innerText = 'Cost: ' + result.cost.toFixed(2);
        }
    }

    // Attach event listeners to location and destination selects
    document.getElementById('locationSelect').addEventListener('change', function () {
        // Trigger update when location changes
        updateDistanceAndCost();
    });

    document.getElementById('destinationSelect').addEventListener('change', function () {
        // Trigger update when destination changes
        updateDistanceAndCost();
    });

    // Attach event listener to price per km input
    document.getElementsByName('price_per_km')[0].addEventListener('input', function () {
        // Trigger update when price per km changes
        updateDistanceAndCost();

    });


    </script>

@endpush
