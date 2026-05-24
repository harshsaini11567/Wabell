<?php

namespace App\Domains\Api\Common\Controllers;

use App\Http\Controllers\APIController;
use App\Domains\Core\Setting\Models\Setting;
use Illuminate\Http\Request;

class ContentController extends APIController
{
    public function privacyPolicy(Request $request){
        try {
            $locale = $request->query('language');
            $column = 'privacy_policy_' . $locale;
            $pp = Setting::select('id','value')->where('key',$column)->whereNull('deleted_at')->first();
             if($pp){
                $pp->value = $pp->value ?? '';
                $data = $pp;
                return $this->apiSuccess(['privacy_policy' => $data]);
            }
            else{
                return $this->apiError(trans('messages.not_found'));
            }
        } catch (\Throwable $th) {
            // dd($th);
            return $this->apiError(trans('messages.error_message'));
        }
    }

    public function termCondition(Request $request){
        try {
            $locale = $request->query('language');
            $column = 'term_condition_' . $locale;
            $tnc = Setting::select('id','value')->where('key',$column)->whereNull('deleted_at')->first();
            if($tnc){
                $tnc->value = $tnc->value ?? '';
                $data = $tnc;
                return $this->apiSuccess(['term_condition' => $data]);
            }
            else{
                return $this->apiError(trans('messages.not_found'));
            }
        } catch (\Throwable $th) {
            // dd($th);
            return $this->apiError(trans('messages.error_message'));
        }
    }
}

?>