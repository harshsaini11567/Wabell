<?php

namespace App\Domains\Api\PageContent\Controllers;

use App\Domains\Core\ContentManagement\Models\Page;
use App\Domains\Core\ContentManagement\Models\Section;
use App\Domains\Core\ContentManagement\Models\SectionMeta;
use App\Domains\Core\Faq\Models\Faq;
use App\Domains\Core\Setting\Models\Setting;
use App\Domains\Core\Specialty\Models\Specialty;
use App\Domains\Core\User\Models\User;
use App\Http\Controllers\APIController;
use Illuminate\Http\Request;

class PageContentController extends APIController
{
    public function index(Request $request, $slug)
    {
        $language = in_array($request->query('language'), ['en', 'ar']) ? $request->query('language') : 'en';        
        
        try {            
            $data = $this->defaultPageContent($slug, $language, $request);

            return $this->apiSuccess($data);
        } catch (\Throwable $th) {
            return $this->apiError($th->getMessage());
        }
    }

    // Default Page Content
    private function defaultPageContent($slug, $language, $request){
        $page = Page::with(['sections', 'sections.sectionMetas'])->where('slug', $slug)->first();
        $data = [];
        foreach ($page->sections as $section) {
            $filteredMetas = [];
            foreach ($section->sectionMetas as $meta) {
                if (str_ends_with($meta->meta_key, "_$language")) {
                    $baseKey = str_replace("_$language", '', $meta->meta_key);
                    $filteredMetas[$baseKey] = $meta->meta_value;
                } elseif (!preg_match('/_(en|ar)$/', $meta->meta_key)) {
                    if($meta->field_type == 'image'){
                        $metaValue = $meta->meta_value ? asset($meta->meta_value) : '';
                    } else {
                        $metaValue = $meta->meta_value;                        
                    }
                    $filteredMetas[$meta->meta_key] = $metaValue;
                }
            }
            $data[$section->section_key] = $filteredMetas;
            switch ($slug) {
                case 'home':
                    $faqs = Faq::select('question_'.$language, 'answer_'.$language)->where('faq_status','active')->where('faq_type', 'web')->whereNull('deleted_at')->get();
                    $data['faq_section']['faqs'] = $faqs;

                    $data = $this->homeData($data, $language, $section);
                    break;
                    
                default:
                    break;
            }
        }
        return $data;
    }

    // Home Page Data
    private function homeData($data,  $language, $section){
        if($section->section_key == 'banner_section'){
            // Master Count
            $masterCount = User::whereHas('roles', function($q) {
                $q->where('id', config('constant.roles.master'));
            }) ->where('user_status', 'active')->count();

            if($masterCount < 2500){
                $masterCount = $masterCount + config('constant.fake_count_for_web.masters');
            }
            $data[$section->section_key]['master_count'] = (int) $masterCount;

            // Learner Count
            $learnerCount = User::whereHas('roles', function($q) {
                $q->where('id', config('constant.roles.customer'));
            })
            ->where('user_status', 'active')->count();

            if($learnerCount < 2500){
                $learnerCount = $learnerCount + config('constant.fake_count_for_web.learners');
            }
            $data[$section->section_key]['learner_count'] = (int) $learnerCount;

            // Speciality Count
            $specialityCount = Specialty::where('specialty_status', 'active')->count();
            $data[$section->section_key]['speciality_count'] = $specialityCount;

            $learnerWelcomeVideo = getSetting('learner_welcome_video');
            $masterWelcomeVideo = getSetting('master_welcome_video');

            $data['wabell_videos']['for_master'] = !empty($masterWelcomeVideo)
                ? $masterWelcomeVideo
                : asset(config('constant.default.master_welcome_video'));

            $data['wabell_videos']['for_learner'] = !empty($learnerWelcomeVideo)
                ? $learnerWelcomeVideo
                : asset(config('constant.default.learner_welcome_video'));
        }
        return $data;
    }

    // Footer Data
    public function getFooterData(Request $request){
        try {
            $language = $request->query('language');

            $settings = Setting::whereIn('group', ['social_link', 'support'])->select("group", 'value', 'key')->get();

            $contactDetails = [];
            $socialMediaDetails = [];
            foreach ($settings as $setting) {
                if (str_ends_with($setting->key, "_{$language}")) {
                    if($setting->group == 'social_link'){
                        $baseKey = substr($setting->key, 0, -(strlen($language) + 1));
                        $socialMediaDetails[$baseKey] = $setting->value;    
                    }                
                } elseif (!preg_match('/_(en|ar)$/', $setting->key)) {
                    if($setting->group == 'support' && $setting->key != 'support_location' && $setting->key != 'support_whatsapp_number'){
                        $contactDetails[$setting->key] = $setting->value;
                    } else if($setting->group == 'social_link'){
                        $socialMediaDetails[$setting->key] = $setting->value;
                    }
                }
            }

            // Get App QR and Link
            $sectionId = Section::where('section_key', 'banner_section')
                ->value('id'); // fetch single column directly (faster than first()->id)

            $appQrdata = SectionMeta::where('section_id', $sectionId)
                ->whereIn('meta_key', ['google_qr_image', 'apple_qr_image', 'google_app_url', 'apple_app_url'])
                ->pluck('meta_value', 'meta_key') // key-value directly
                 ->map(function ($value, $key) {
                    // Only add full link for image paths
                    if (str_contains($key, '_qr_image') && $value) {
                        return asset($value);
                    }
                    return $value;
                })
                ->toArray();

            $data = [
                'socail_media_links' => $socialMediaDetails,
                'contact_details' => $contactDetails,
                'site_info' => [
                    "basic_site_info" => getSetting("basic_site_info_".$language) ?? '',
                ],
                'support_whatsapp_number' => getSetting('support_whatsapp_number'),
                'app_qr_url_data' => $appQrdata
            ];
            return $this->apiSuccess($data);
        } catch (\Throwable $th) {
            return $this->apiError($th->getMessage());
        }
    }
}