<div class="panel">
    <div class="panel-title"><strong>{{__("Destinations")}}</strong></div>
    <div class="panel-body">
        @if(is_default_lang())
            <div class="form-group">
                <label class="control-label">{{__("Destination")}}</label>
                @if(!empty($is_smart_search))
                    <div class="form-group-smart-search">
                        <div class="form-content">
                            <?php
                            $map_lat = ""; // Initialize the variable for selected destination map_lat
                            $map_lng = ""; // Initialize the variable for selected destination map_lng
                            $list_json = [];
                            $traverse = function ($locations, $prefix = '') use (&$traverse, &$list_json , &$map_lat, &$map_lng, $row) {
                                foreach ($locations as $location) {
                                    $translate = $location->translate();
                                    if ($row->destination_id == $location->id){
                                        $map_lat = $location->map_lat; // Store the selected destination map_lat
                                        $map_lng = $location->map_lng; // Store the selected destination map_lng
                                    }
                                    $list_json[] = [
                                        'id' => $location->id,
                                        'title' => $prefix . ' ' . $translate->name,
                                        'map_lat' => $location->map_lat,
                                        'map_lng' => $location->map_lng,
                                    ];
                                    $traverse($location->children, $prefix . '-');
                                }
                            };
                            $traverse($car_location);
                            ?>
                            <div class="smart-search">
                                <input type="text" class="smart-search-location parent_text form-control" placeholder="{{__("-- Please Select --")}}" value="{{ $map_lat }}" data-onLoad="{{__("Loading...")}}"
                                       data-default="{{ json_encode($list_json) }}">
                                <input type="hidden" class="child_id" name="destination_id" value="{{$row->destination_id ?? Request::query('destination_id')}}">
                                <div style="display: none">
                                    <div><strong>Selected Destination:</strong> <span id="selectedDestinationDiv"></span></div>
                                    <div><strong>Selected Destination ID:</strong> <span id="selectedDestinationIdDiv"></span></div>
                                    <div><strong>Selected Destination Map Latitude:</strong> <span id="selectedDestinationMapLatDiv"></span></div>
                                    <div><strong>Selected Destination Map Longitude:</strong> <span id="selectedDestinationMapLngDiv"></span></div>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="">
                        <select name="destination_id" class="form-control" id="destinationSelect">
                            <option value="">{{__("-- Please Select --")}}</option>
                            <?php
                            global $selectedDestinationMapLat, $selectedDestinationMapLng;
                            $traverse = function ($locations, $prefix = '') use (&$traverse, $row) {
                                foreach ($locations as $location) {
                                    $selected = '';
                                    if ($row->destination_id == $location->id) {
                                        $selected = 'selected';
                                        global $selectedDestinationMapLat, $selectedDestinationMapLng;
                                        $selectedDestinationMapLat = $location->map_lat;
                                        $selectedDestinationMapLng = $location->map_lng;
                                    }

                                    printf("<option value='%s' %s data-map-lat='%s' data-map-lng='%s'>%s</option>",
                                        $location->id,
                                        $selected,
                                        $location->map_lat,
                                        $location->map_lng,
                                        $prefix . ' ' . $location->name
                                    );

                                    $traverse($location->children, $prefix . '-');
                                }
                            };
                            $traverse($car_location);
                            ?>
                        </select>
                        <div style="display: none">
                            <div><strong>Selected Destination:</strong> <span id="selectedDestinationDiv"></span></div>
                            <div><strong>Selected Destination ID:</strong> <span id="selectedDestinationIdDiv"></span></div>
                            <div><strong>Selected Destination Map Latitude:</strong> <span id="selectedDestinationMapLatDiv"></span></div>
                            <div><strong>Selected Destination Map Longitude:</strong> <span id="selectedDestinationMapLngDiv"></span></div>
                        </div>
                    </div>
                @endif
            </div>
        @endif
        {{-- <div style="display: none" class="form-group">
            <label class="control-label">{{__("Real address")}}</label>
            <input type="text" name="address" id="customPlaceAddress" class="form-control" placeholder="{{__("Real address")}}" value="{{$translation->address}}">
        </div> --}}
        {{-- @if(is_default_lang())
            <div style="display: none" class="form-group">
                <label class="control-label">{{__("The geographic coordinate")}}</label>
                <div class="control-map-group">
                    <div id="destination_map_content"></div>
                    <input type="text" placeholder="{{__("Search by name...")}}" class="bravo_searchbox form-control" autocomplete="off" onkeydown="return event.key !== 'Enter';">
                    <div class="g-control">
                        <div class="form-group">
                            <label>{{__("Map Latitude")}}:</label>
                            <input type="text" name="map_lat" class="form-control" value="{{$row->map_lat}}" onkeydown="return event.key !== 'Enter';">
                        </div>
                        <div class="form-group">
                            <label>{{__("Map Longitude")}}:</label>
                            <input type="text" name="map_lng" class="form-control" value="{{$row->map_lng}}" onkeydown="return event.key !== 'Enter';">
                        </div>
                        <div class="form-group">
                            <label>{{__("Map Zoom")}}:</label>
                            <input type="text" name="map_zoom" class="form-control" value="{{$row->map_zoom ?? "8"}}" onkeydown="return event.key !== 'Enter';">
                        </div>
                    </div>
                </div>
            </div>
        @endif --}}
    </div>
</div>

<script>

    var selectDestinationValue = document.getElementById('destinationSelect');

    if(selectDestinationValue.value > 0 ){
        // Retrieve the selected destination map_lat and map_lng
        var selectedDestinationMapLat = selectDestinationValue.options[selectDestinationValue.selectedIndex].getAttribute('data-map-lat');
        var selectedDestinationMapLng = selectDestinationValue.options[selectDestinationValue.selectedIndex].getAttribute('data-map-lng');

        // Set the selected destination map_lat and map_lng to the respective divs
        document.getElementById('selectedDestinationDiv').innerHTML = selectDestinationValue.options[selectDestinationValue.selectedIndex].text;
        document.getElementById('selectedDestinationIdDiv').innerHTML = selectDestinationValue.value;
        document.getElementById('selectedDestinationMapLatDiv').innerHTML = selectedDestinationMapLat;
        document.getElementById('selectedDestinationMapLngDiv').innerHTML = selectedDestinationMapLng;

        // Store the selected destination information in local storage
        localStorage.setItem('selectedDestinationName', selectDestinationValue.options[selectDestinationValue.selectedIndex].text);
        localStorage.setItem('selectedDestinationId', selectDestinationValue.value);
        localStorage.setItem('selectedDestinationMapLat', selectedDestinationMapLat);
        localStorage.setItem('selectedDestinationMapLng', selectedDestinationMapLng);
    }


    // Add an event listener to the destination select element
    document.getElementById('destinationSelect').addEventListener('change', function() {
        // Retrieve the selected destination map_lat and map_lng
        var selectedDestinationMapLat = this.options[this.selectedIndex].getAttribute('data-map-lat');
        var selectedDestinationMapLng = this.options[this.selectedIndex].getAttribute('data-map-lng');

        // Set the selected destination map_lat and map_lng to the respective divs
        document.getElementById('selectedDestinationDiv').innerHTML = this.options[this.selectedIndex].text;
        document.getElementById('selectedDestinationIdDiv').innerHTML = this.value;
        document.getElementById('selectedDestinationMapLatDiv').innerHTML = selectedDestinationMapLat;
        document.getElementById('selectedDestinationMapLngDiv').innerHTML = selectedDestinationMapLng;

        // Store the selected destination information in local storage
        localStorage.setItem('selectedDestinationName', this.options[this.selectedIndex].text);
        localStorage.setItem('selectedDestinationId', this.value);
        localStorage.setItem('selectedDestinationMapLat', selectedDestinationMapLat);
        localStorage.setItem('selectedDestinationMapLng', selectedDestinationMapLng);

    });
</script>
