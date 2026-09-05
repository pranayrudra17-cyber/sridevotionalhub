<div class="row">
    <div class="col-md-3">
        <label>{{ translate('Street / House No.')}}</label>
    </div>
    <div class="col-md-9">
        <input type="text" class="form-control mb-3" placeholder="{{ translate('Enter street / house no.')}}" name="street_house_no" value="{{ $street_house_no ?? '' }}" required>
    </div>
</div>
<div class="row">
    <div class="col-md-3">
        <label>{{ translate('Nearby / Landmark')}}</label>
    </div>
    <div class="col-md-9">
        <input type="text" class="form-control mb-3" placeholder="{{ translate('Enter nearby landmark')}}" name="nearby_landmark" value="{{ $nearby_landmark ?? '' }}">
    </div>
</div>
<div class="row">
    <div class="col-md-3">
        <label>{{ translate('Area / Locality')}}</label>
    </div>
    <div class="col-md-9">
        <input type="text" class="form-control mb-3" placeholder="{{ translate('Enter area / locality')}}" name="area_locality" value="{{ $area_locality ?? '' }}">
    </div>
</div>
<div class="row">
    <div class="col-md-3">
        <label>{{ translate('Additional Address Details')}}</label>
    </div>
    <div class="col-md-9">
        <textarea class="form-control mb-3" placeholder="{{ translate('Apartment, floor, building, etc.')}}" rows="2" name="additional_address_details">{{ $additional_address_details ?? '' }}</textarea>
    </div>
</div>
