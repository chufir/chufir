<div class="panel">
    <div class="panel-title"><strong>{{__("Locations")}}</strong></div>
    <div class="panel-body">
        @if(is_default_lang())
            <div class="form-group">
                <label class="control-label">{{__("Location")}}</label>
                @if(!empty($is_smart_search))
                    <div class="form-group-smart-search">
                        <div class="form-content">
                            <?php
                            $map_lat = ""; // Initialize the variable for selected location map_lat
                            $map_lng = ""; // Initialize the variable for selected location map_lng
                            $list_json = [];
                            $traverse = function ($locations, $prefix = '') use (&$traverse, &$list_json , &$map_lat, &$map_lng, $row) {
                                foreach ($locations as $location) {
                                    $translate = $location->translate();
                                    if ($row->location_id == $location->id){
                                        $map_lat = $location->map_lat; // Store the selected location map_lat
                                        $map_lng = $location->map_lng; // Store the selected location map_lng

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
                                <select id="locationSelect" class="smart-search-location parent_text form-control" placeholder="{{__("-- Please Select --")}}" value="{{ $map_lat }}" data-onLoad="{{__("Loading...")}}"
                                       data-default="{{ json_encode($list_json) }}">
                                    <option value="0">{{__("-- Please Select --")}}</option>
                                    <?php
                                    global $selectedMapLat, $selectedMapLng;
                                    $traverse = function ($locations, $prefix = '') use (&$traverse, $row) {
                                        foreach ($locations as $location) {
                                            $selected = '';
                                            if ($row->location_id == $location->id)
                                                $selected = 'selected';

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
                                    <div style="display: none"><strong>Selected Location:</strong> <span id="selectedLocationDiv"></span></div>
                                    <div style="display: none"><strong>Selected Location ID:</strong> <span id="selectedLocationIdDiv"></span></div>
                                    <div style="display: none"><strong>Selected Map Latitude:</strong> <span id="selectedMapLatDiv"></span></div>
                                    <div style="display: none"><strong>Selected Map Longitude:</strong> <span id="selectedMapLngDiv"></span></div>
                                </div>
                            </div>
                        </div>
                    </div>

                @else

                    <div class="">
                        <select name="location_id" class="form-control" id="locationSelect">
                            <option value="0">{{__("-- Please Select --")}}</option>
                            <?php
                                $traverse = function ($locations, $prefix = '') use (&$traverse, $row) {
                                    foreach ($locations as $location) {
                                        $selected = '';
                                        if ($row->location_id == $location->id)
                                            $selected = 'selected';

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
                            <div style="display: none"><strong>Selected Location:</strong> <span id="selectedLocationDiv"></span></div>
                            <div style="display: none"><strong>Selected Location ID:</strong> <span id="selectedLocationIdDiv"></span></div>
                            <div style="display: none"><strong>Selected Map Latitude:</strong> <span id="selectedMapLatDiv"></span></div>
                            <div style="display: none"><strong>Selected Map Longitude:</strong> <span id="selectedMapLngDiv"></span></div>
                        </div>
                    </div>
                @endif
            </div>
        @endif

        <div style="display: none" class="form-group">
            <label class="control-label">{{__("Real address")}}</label>
            <input type="text" name="address" id="customPlaceAddress" class="form-control" placeholder="{{__("Real address")}}" value="{{$translation->address}}">
        </div>
        @if(is_default_lang())
            <div  style="display: none"  class="form-group">
                <label class="control-label">{{__("The geographic coordinate")}}</label>
                <div class="control-map-group">
                    <div id="map_content"></div>
                    <input type="text" placeholder="{{__("Search by name...")}}" class="bravo_searchbox form-control" autocomplete="off" onkeydown="return event.key !== 'Enter';">
                    <div class="g-control">
                        <div class="form-group">
                            <label>{{__("Map Latitude")}}:</label>
                            <input type="text" name="map_lat" id="map_lat" class="form-control" value="{{$row->map_lat ?? "51.505"}}" onkeydown="return event.key !== 'Enter';">
                        </div>
                        <div class="form-group">
                            <label>{{__("Map Longitude")}}:</label>
                            <input type="text" name="map_lng" id="map_lng" class="form-control" value="{{$row->map_lng ?? "51.505"}}" onkeydown="return event.key !== 'Enter';">
                        </div>
                        <div class="form-group">
                            <label>{{__("Map Zoom")}}:</label>
                            <input type="text" name="map_zoom" class="form-control" value="{{$row->map_zoom ?? "8"}}" onkeydown="return event.key !== 'Enter';">
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

<script>

    var selectValue = document.getElementById('locationSelect');

    if(selectValue.value > 0 ){
         // Retrieve the selected location map_lat and map_lng
         var selectedMapLat = selectValue.options[selectValue.selectedIndex].getAttribute('data-map-lat');
        var selectedMapLng = selectValue.options[selectValue.selectedIndex].getAttribute('data-map-lng');

        // Set the selected location map_lat and map_lng to the respective divs
        document.getElementById('selectedLocationDiv').innerHTML = selectValue.options[selectValue.selectedIndex].text;
        document.getElementById('selectedLocationIdDiv').innerHTML = selectValue.value;
        document.getElementById('selectedMapLatDiv').innerHTML = selectedMapLat;
        document.getElementById('selectedMapLngDiv').innerHTML = selectedMapLng;

        // Save the selected location information to localStorage
        localStorage.setItem('selectedLocation', selectValue.options[selectValue.selectedIndex].text);
        localStorage.setItem('selectedLocationId', selectValue.value);
        localStorage.setItem('selectedMapLat', selectedMapLat);
        localStorage.setItem('selectedMapLng', selectedMapLng);
    }
    // Add an event listener to the location select element
    document.getElementById('locationSelect').addEventListener('change', function() {
        // Retrieve the selected location map_lat and map_lng
        var selectedMapLat = this.options[this.selectedIndex].getAttribute('data-map-lat');
        var selectedMapLng = this.options[this.selectedIndex].getAttribute('data-map-lng');

        // Set the selected location map_lat and map_lng to the respective divs
        document.getElementById('selectedLocationDiv').innerHTML = this.options[this.selectedIndex].text;
        document.getElementById('selectedLocationIdDiv').innerHTML = this.value;
        document.getElementById('selectedMapLatDiv').innerHTML = selectedMapLat;
        document.getElementById('selectedMapLngDiv').innerHTML = selectedMapLng;

        // Save the selected location information to localStorage
        localStorage.setItem('selectedLocation', this.options[this.selectedIndex].text);
        localStorage.setItem('selectedLocationId', this.value);
        localStorage.setItem('selectedMapLat', selectedMapLat);
        localStorage.setItem('selectedMapLng', selectedMapLng);


    });
</script>


