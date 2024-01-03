<div class="form-group">
    <label>{{__("Name")}}</label>
    <input type="text" value="{{$translation->name}}" placeholder="{{__("Location name")}}" id="locationName" name="name" class="form-control">
</div>
@if(is_default_lang())
    <div class="form-group">
        <label>{{__("Parent")}}</label>
        <select name="parent_id" class="form-control">
            <option value="">{{__("-- Please Select --")}}</option>
            <?php
            $traverse = function ($categories, $prefix = '') use (&$traverse, $row) {
                foreach ($categories as $category) {
                    if ($category->id == $row->id) {
                        continue;
                    }
                    $selected = '';
                    if ($row->parent_id == $category->id)
                        $selected = 'selected';
                    printf("<option value='%s' %s>%s</option>", $category->id, $selected, $prefix . ' ' . $category->name);
                    $traverse($category->children, $prefix . '-');
                }
            };
            $traverse($parents);
            ?>
        </select>
    </div>
@endif
<div class="form-group">
    <label class="control-label">{{__("Description")}}</label>
    <div class="">
        <textarea name="content" class="d-none has-ckeditor" cols="30" rows="10">{{$translation->content}}</textarea>
    </div>
</div>

<!-- Include the Google Maps API script -->
{{-- <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAm_ZEJZ58IDAn9IjtGr3a9Y0UKKjOcWI0&libraries=places"></script> --}}

<script>
document.addEventListener("DOMContentLoaded", function() {
    // Get the input element by its ID
    var locationNameInput = document.getElementById("locationName");

    // Add an event listener for the input's focus out event
    locationNameInput.addEventListener("input", function() {
        // Get the location name value
        var locationName = locationNameInput.value;

        // Perform geocoding using the Google Maps Geocoding API
        var geocoder = new google.maps.Geocoder();
        geocoder.geocode({ address: locationName }, function(results, status) {
            if (status === "OK" && results.length > 0) {
                // Get latitude and longitude
                var latitude = results[0].geometry.location.lat();
                var longitude = results[0].geometry.location.lng();

                // Log the latitude and longitude to the console
                console.log("Latitude: " + latitude + ", Longitude: " + longitude);
            } else {
                console.error("Geocoding failed. Status: " + status);
            }
        });
    });
});
</script>
