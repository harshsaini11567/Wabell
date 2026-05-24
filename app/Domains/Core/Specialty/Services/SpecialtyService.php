<?php

namespace App\Domains\Core\Specialty\Services;

use App\Domains\Core\Specialty\Models\Specialty;

class SpecialtyService
{
    public function createSpecialty(array $data, $id): Specialty
    {
        $input = [
            'name_en' => $data['name_en'],
            'name_ar' => $data['name_ar'],
        ];
        if(isset($id) && !empty($id)){
            $specialty = Specialty::where('uuid', $id)->select('id', 'uuid')->first();
            $input['parent_specialty_id'] = $specialty->id;
        }
        return Specialty::create($input);
    }

    public function updateSpecialty(Specialty $specialty, $data)
    {
        
        $specialty->update($data);

        return $specialty;
    }
}
