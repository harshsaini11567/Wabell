<script src="https://cdn.datatables.net/2.0.8/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/responsive/3.0.0/js/dataTables.responsive.min.js"></script>

<script src="{{asset('admin-assets/vendor/select2/js/select2.min.js')}}"></script>
<script src="{{asset('admin-assets/vendor/bootstrap-datepicker/js/bootstrap-datepicker.min.js')}}"></script>

<script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.key') }}&libraries=places"></script>
{!! $dataTable->scripts() !!}

<script>
$(document).ready(function(e){
    $(document).on('shown.bs.modal', '#AddNeighborhood, #EditNeighborhood', function () {
        const $modal = $(this);
        $modal.find('.select2').each(function () {
            const $select = $(this);
            // Prevent double initialization
            if ($select.hasClass('select2-hidden-accessible')) {
                $select.select2('destroy');
            }
 
            $select.select2({
                width: '100%',
                dropdownParent: $('.modal-body'),
                dropdownPosition: 'below',
                selectOnClose: false,
            });
        });
    });
});

@can('city_create')
    $(document).on("click", ".btnAddNeighborhood", function() {
        pageLoader('show');
        var url = $(this).data('href');
        $.ajax({
            type: 'get',
            url: url,
            dataType: 'json',
            success: function (response) {
                if(response.success) {
                    $('.popup_render_div').html(response.htmlView);
                    $('#AddNeighborhood').modal('show');

                    setTimeout(() => { initMap(); }, 300);
                }
                else {
                    toasterAlert('error',response.error);
                }
            },
            error: function(res){
                toasterAlert('error',res.responseJSON.error);
            },
            complete: function(xhr){
                pageLoader('hide');
            }
        });
    });

    $(document).on('submit','#AddNeighborhoodForm', function(e) {
        e.preventDefault();
        pageLoader('show', true);

        $('.validation-error-block').remove();
        var formData = $(this).serialize();
        var url = $(this).data('href');
        $.ajax({
            type: 'POST',
            url: url,
            data: formData,
            dataType: 'json',
            success: function (response) {
                if(response.success) {
                    $('#AddNeighborhood').modal('hide');
                    $('#neighborhood-table').DataTable().ajax.reload(null, false);
                    toasterAlert('success',response.message);
                }
                else {
                    toasterAlert('error', response.error);
                }
            },
            error: function (response) {
                if(response.responseJSON.error_type == 'something_error'){
                    toasterAlert('error',response.responseJSON.error);
                } else {
                    var errorLabelTitle = '';
                    $.each(response.responseJSON.errors, function (key, item) {
                        errorLabelTitle = `<span class="validation-error-block">${item[0]}</span>`;

                        $("input[name='" + key + "']").after(errorLabelTitle);
                         $("#"+key).siblings('.select2').after(errorLabelTitle);
                    });
                }
            },
            complete: function(xhr){
                pageLoader('hide', true);
            }
        });
    }); 
@endcan

@can('city_show')
    $(document).on("click", ".btnViewNeighborhood", function() {
        pageLoader('show');
        var url = $(this).data('href');
        $.ajax({
            type: 'get',
            url: url,
            dataType: 'json',
            success: function (response) {
                if(response.success) {
                    $('.popup_render_div').html(response.htmlView);
                    $('#ViewNeighborhood').modal('show');
                }
                else {
                    toasterAlert('error',response.error);
                }
            },
            error: function(res){
                toasterAlert('error',res.responseJSON.error);
            },
            complete: function(xhr){
                pageLoader('hide');
            }
        });
    });
@endcan

@can('city_edit')
    $(document).on("click", ".btnEditNeighborhood", function() {
        pageLoader('show');
        var url = $(this).data('href');
        $.ajax({
            type: 'get',
            url: url,
            dataType: 'json',
            success: function (response) {
                if(response.success) {
                    $('.popup_render_div').html(response.htmlView);
                    $('#editNeighborhood').modal('show');

                    setTimeout(() => { initMap(); }, 300);
                }
                else {
                    toasterAlert('error',response.error);
                }
            },
            error: function(res){
                toasterAlert('error',res.responseJSON.error);
            },
            complete: function(xhr){
                pageLoader('hide');
            }
        });
    });

    $(document).on('submit','#editNeighborhoodForm', function(e) {
        e.preventDefault();
       pageLoader('show', true);

        $('.validation-error-block').remove();
        var formData = $(this).serialize();

        var url = $(this).data('href');
        $.ajax({
            type: 'POST',
            url: url,
            data: formData,
            dataType: 'json',
            success: function (response) {
                if(response.success) {
                    $('#editNeighborhood').modal('hide');
                    $('#neighborhood-table').DataTable().ajax.reload(null, false);
                    toasterAlert('success',response.message);
                }
                else {
                    toasterAlert('error', response.error);
                }
            },
            error: function (response) {
                if(response.responseJSON.error_type == 'something_error'){
                    toasterAlert('error',response.responseJSON.error);
                } else {
                    var errorLabelTitle = '';
                    $.each(response.responseJSON.errors, function (key, item) {
                        errorLabelTitle = '<span class="validation-error-block">'+item[0]+'</span>';

                        $("input[name='" + key + "']").after(errorLabelTitle);
                        $("#"+key).siblings('.select2').after(errorLabelTitle);
                    });
                }
            },
            complete: function(xhr){
                pageLoader('hide', true);
            }
        });
    }); 
@endcan

@can('city_delete')
    $(document).on("click",".deleteNeighborhoodBtn", function() {
        var url = $(this).data('href');
        Swal.fire({
            title: "{{ trans('global.areYouSure') }}",
            text: "{{ trans('global.onceClickedRecordDeleted') }}",
            icon: "warning",
            showDenyButton: true,  
            //   showCancelButton: true,  
            confirmButtonText: "{{ trans('global.swl_confirm_button_text') }}",  
            denyButtonText: "{{ trans('global.swl_deny_button_text') }}",
        })
        .then(function(result) {
            if (result.isConfirmed) {  
                pageLoader('show');
                $.ajax({
                    type: 'DELETE',
                    url: url,
                    dataType: 'json',
                    data: { _token: "{{ csrf_token() }}" },
                    success: function (response) {
                        if(response.success) {
                            $('#neighborhood-table').DataTable().ajax.reload(null, false);
                            toasterAlert('success',response.message);
                        }
                        else {
                            toasterAlert('error',response.error);
                        }
                    },
                    error: function(res){
                        toasterAlert('error',res.responseJSON.error);
                    },
                    complete: function(xhr){
                        pageLoader('hide');
                    }
                });
            }
        });
    });
@endcan

let map, marker, searchBox;

function initMap() {
    const latInput = document.getElementById("lat");
    const lngInput = document.getElementById("lng");
    
    const defaultLocation = {
        lat: parseFloat(latInput.value) || 24.7136, // Jaipur default
        lng: parseFloat(lngInput.value) || 46.6753
    };

    // Initialize map
    map = new google.maps.Map(document.getElementById("map"), {
        center: defaultLocation,
        zoom: 6
    });

    // Marker
    marker = new google.maps.Marker({
        position: defaultLocation,
        map: map,
        draggable: true
    });

    // Try to get user's current location if creating a new city
    if (!latInput.value || !lngInput.value) {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    const userLoc = {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude
                    };
                    map.setCenter(userLoc);
                    marker.setPosition(userLoc);
                    latInput.value = userLoc.lat.toFixed(6);
                    lngInput.value = userLoc.lng.toFixed(6);
                },
                function() {
                    console.warn("User denied Geolocation permission.");
                }
            );
        }
    }

    // Marker drag updates fields
    marker.addListener("dragend", (event) => {
        latInput.value = event.latLng.lat().toFixed(6);
        lngInput.value = event.latLng.lng().toFixed(6);
    });

    // Map click moves marker
    map.addListener("click", (event) => {
        marker.setPosition(event.latLng);
        latInput.value = event.latLng.lat().toFixed(6);
        lngInput.value = event.latLng.lng().toFixed(6);
    });

    // Create input element for search
    const input = document.createElement("input");
    input.id = "map-search";
    input.type = "text";
    input.placeholder = "Search a place...";
    map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);

    searchBox = new google.maps.places.SearchBox(input);

    // Bias search results to map viewport
    map.addListener("bounds_changed", () => {
        searchBox.setBounds(map.getBounds());
    });

    // When place is selected
    searchBox.addListener("places_changed", () => {
        const places = searchBox.getPlaces();
        if (!places || places.length === 0) return;

        const place = places[0];
        if (!place.geometry || !place.geometry.location) return;

        map.panTo(place.geometry.location);
        map.setZoom(14);
        marker.setPosition(place.geometry.location);

        latInput.value = place.geometry.location.lat().toFixed(6);
        lngInput.value = place.geometry.location.lng().toFixed(6);
    });

    // Manual input updates map
    function updateMapFromInputs() {
        const lat = parseFloat(latInput.value);
        const lng = parseFloat(lngInput.value);
        if (!isNaN(lat) && !isNaN(lng)) {
        const newPos = { lat, lng };
        marker.setPosition(newPos);
        map.setCenter(newPos);
        map.setZoom(14);
        }
    }

    latInput.addEventListener("change", updateMapFromInputs);
    lngInput.addEventListener("change", updateMapFromInputs);
}

</script>