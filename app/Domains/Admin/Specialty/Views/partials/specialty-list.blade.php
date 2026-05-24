@foreach ($specialties as $specialty)
    <div class="specialty_inner">
        <div class="align-items-center justify-content-around row specialty_main_row">
            <div class="col specialty_name">
                @if($specialty->childSpecialties()->count() > 0)
                    <a href="javascript:void(0)" class="btnGetChildSpecialty" data-href="{{ route('specialties.get-child-specialties', $specialty->uuid) }}" data-child_exist="no">
                        <i class="ri-arrow-down-s-line arrow_down_icon"></i>
                    </a>
                @endif
                <span class="specialty_text"> {{ $specialty->name_en}} </span>
            </div>
            <div class="col-auto specialty_actions text-md-end">
                @if(isset($specialtyLevel) && $specialtyLevel < 2)
                    <a href="javascript:void(0)" class="btn btn-outline-success btn-sm btnAddSpecialty" data-href="{{route('specialties.create', $specialty->uuid)}}" data-step="0"><i class="ri-add-line"></i></a>
                @endif

                <a href="javascript:void(0)" class="btn btn-outline-dark btn-sm btnEditSpecialty" data-href="{{route('specialties.edit', $specialty->uuid)}}" data-step="0"><i class="ri-pencil-line"></i></a>

                <a href="javascript:void(0)" class="btn btn-outline-danger btn-sm deleteSpecialtyBtn" data-href="{{route('specialties.destroy', $specialty->uuid)}}" data-step="0"><i class="ri-delete-bin-line"></i></a>
            </div>
        </div>
        <div class="child-specialty-main"></div>
    </div>
@endforeach