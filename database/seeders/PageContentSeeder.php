<?php

namespace Database\Seeders;

use App\Domains\Core\ContentManagement\Models\Page;
use App\Domains\Core\ContentManagement\Models\Section;
use App\Domains\Core\ContentManagement\Models\SectionMeta;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PageContentSeeder extends Seeder
{
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        // Empty the table
        DB::table('pages')->truncate();
        DB::table('sections')->truncate();
        DB::table('section_metas')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Create Pages
        $pages = [
            // Home page
                [
                    'name_en' => 'Home',
                    'name_ar' => 'الرئيسية',
                    'slug' => 'home',
                    'sections' => [
                        [
                            'name_en' => 'Banner Section',
                            'name_ar' => 'نبذة عن الشركة',
                            'section_key' => 'banner_section',
                            'position' => 1,
                            'metas' => [
                                /* // Titles
                                [
                                    'display_name_en' => 'Title English',
                                    'display_name_ar' => 'عنوان باللغة الإنجليزية',
                                    'meta_key' => 'title_en',
                                    'meta_value' => 'Event Management Company',
                                    'field_type' => 'text',
                                ],
                                [
                                    'display_name_en' => 'Title Arabic',
                                    'display_name_ar' => 'عنوان باللغة العربية',
                                    'meta_key' => 'title_ar',
                                    'meta_value' => 'شركة إدارة الفعاليات',
                                    'field_type' => 'text',
                                ],
                                // Description
                                [
                                    'display_name_en' => 'Description English',
                                    'display_name_ar' => 'الوصف باللغة الإنجليزية',
                                    'meta_key' => 'description_en',
                                    'meta_value' => 'Find expert Masters  near you. Wabell instantly connect you with specialized Masters  near you.',
                                    'field_type' => 'textarea',
                                ],
                                [
                                    'display_name_en' => 'Description Arabic',
                                    'display_name_ar' => 'الوصف باللغة العربية',
                                    'meta_key' => 'description_ar',
                                    'meta_value' => 'ابحث عن خبراء بالقرب منك. يوصلك Wabell فورًا بخبراء متخصصين بالقرب منك.',
                                    'field_type' => 'textarea',
                                ], */
                                // Image
                                [
                                    'display_name_en' => "Image",
                                    'display_name_ar' => "صورة",
                                    'meta_key' => 'about_image',
                                    'meta_value' => null,
                                    'field_type' => 'image',
                                ],
                                // Play Store QR Image
                                [
                                    'display_name_en' => "Google Play Store QR Image",
                                    'display_name_ar' => "صورة QR لمتجر جوجل بلاي",
                                    'meta_key' => 'google_qr_image',
                                    'meta_value' => null,
                                    'field_type' => 'image',
                                ],
                                // Apple Store QR Image
                                [
                                    'display_name_en' => "App Store QR Image",
                                    'display_name_ar' => "صورة QR لمتجر التطبيقات",
                                    'meta_key' => 'apple_qr_image',
                                    'meta_value' => null,
                                    'field_type' => 'image',
                                ],

                                // Please store url
                                [
                                    'display_name_en' => 'App URL ( Google Play Store )',
                                    'display_name_ar' => 'الوصف باللغة الإنجليزية',
                                    'meta_key' => 'google_app_url',
                                    'meta_value' => '',
                                    'field_type' => 'url',
                                ],

                                // App store url
                                [
                                    'display_name_en' => 'App URL ( App Store )',
                                    'display_name_ar' => 'الوصف باللغة الإنجليزية',
                                    'meta_key' => 'apple_app_url',
                                    'meta_value' => '',
                                    'field_type' => 'url',
                                ],
                            ],
                        ],
                        /* [
                            'name_en' => 'Features',
                            'name_ar' => 'الخدمات المميزة',
                            'section_key' => 'features',
                            'position' => 2,
                            'metas' => [
                                // Feature 1
                                    [
                                        'display_name_en' => 'Feature 1 Title English',
                                        'display_name_ar' => 'عنوان ماجستير الجودة باللغة الإنجليزية',
                                        'meta_key' => 'feature_1_heading_en',
                                        'meta_value' => 'Find qualified Masters near you',
                                        'field_type' => 'text',
                                    ],
                                    [
                                        'display_name_en' => 'Feature 1 Title Arabic',
                                        'display_name_ar' => 'عنوان ماجستير الجودة باللغة العربية',
                                        'meta_key' => 'feature_1_heading_ar',
                                        'meta_value' => 'ابحث عن الماجستير المؤهلين بالقرب منك',
                                        'field_type' => 'text',
                                    ],

                                    // Description
                                    [
                                        'display_name_en' => 'Feature 1 Description English',
                                        'display_name_ar' => 'الوصف باللغة الإنجليزية',
                                        'meta_key' => 'feature_1_description_en',
                                        'meta_value' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
                                        'field_type' => 'textarea',
                                    ],
                                    [
                                        'display_name_en' => 'Feature 1 Description Arabic',
                                        'display_name_ar' => 'الوصف باللغة العربية',
                                        'meta_key' => 'feature_1_description_ar',
                                        'meta_value' => 'لوريم إيبسوم دولور سيت أميت، consectetur adipiscing إيليت.',
                                        'field_type' => 'textarea',
                                    ],

                                // Feature 2
                                    [
                                        'display_name_en' => 'Feature 2 Title English',
                                        'display_name_ar' => 'عنوان ماجستير الجودة باللغة الإنجليزية',
                                        'meta_key' => 'feature_2_heading_en',
                                        'meta_value' => 'Secure in-app chat',
                                        'field_type' => 'text',
                                    ],
                                    [
                                        'display_name_en' => 'Feature 2 Title Arabic',
                                        'display_name_ar' => 'عنوان ماجستير الجودة باللغة العربية',
                                        'meta_key' => 'feature_2_heading_ar',
                                        'meta_value' => 'دردشة آمنة داخل التطبيق',
                                        'field_type' => 'text',
                                    ],

                                    // Description
                                    [
                                        'display_name_en' => 'Feature 2 Description English',
                                        'display_name_ar' => 'الوصف باللغة الإنجليزية',
                                        'meta_key' => 'feature_2_description_en',
                                        'meta_value' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
                                        'field_type' => 'textarea',
                                    ],
                                    [
                                        'display_name_en' => 'Feature 2 Description Arabic',
                                        'display_name_ar' => 'الوصف باللغة العربية',
                                        'meta_key' => 'feature_2_description_ar',
                                        'meta_value' => 'لوريم إيبسوم دولور سيت لوريم إيبسوم دولور سيت أميت، consectetur adipiscing إيليت.',
                                        'field_type' => 'textarea',
                                    ],

                                // Feature 3
                                    [
                                        'display_name_en' => 'Feature 3 Title English',
                                        'display_name_ar' => 'عنوان ماجستير الجودة باللغة الإنجليزية',
                                        'meta_key' => 'feature_3_heading_en',
                                        'meta_value' => 'Subject-wise expert matching',
                                        'field_type' => 'text',
                                    ],
                                    [
                                        'display_name_en' => 'Feature 3 Title Arabic',
                                        'display_name_ar' => 'عنوان ماجستير الجودة باللغة العربية',
                                        'meta_key' => 'feature_3_heading_ar',
                                        'meta_value' => 'مطابقة الخبراء حسب الموضوع',
                                        'field_type' => 'text',
                                    ],

                                    // Description
                                    [
                                        'display_name_en' => 'Feature 3 Description English',
                                        'display_name_ar' => 'الوصف باللغة الإنجليزية',
                                        'meta_key' => 'feature_3_description_en',
                                        'meta_value' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
                                        'field_type' => 'textarea',
                                    ],
                                    [
                                        'display_name_en' => 'Feature 3 Description Arabic',
                                        'display_name_ar' => 'الوصف باللغة العربية',
                                        'meta_key' => 'feature_3_description_ar',
                                        'meta_value' => 'لوريم إيبسوم دولور سيت لوريم إيبسوم دولور سيت أميت، consectetur adipiscing إيليت.',
                                        'field_type' => 'textarea',
                                    ],
                            ],
                        ], */
                        /* [
                            'name_en' => 'About Wabell',
                            'name_ar' => 'حدثك، شغفنا',
                            'section_key' => 'about_wabell',
                            'position' => 3,
                            'metas' => [
                                // Titles
                                [
                                    'display_name_en' => 'Title English',
                                    'display_name_ar' => 'عنوان باللغة الإنجليزية',
                                    'meta_key' => 'title_en',
                                    'meta_value' => 'What is Wabell?',
                                    'field_type' => 'text',
                                ],
                                [
                                    'display_name_en' => 'Title Arabic',
                                    'display_name_ar' => 'عنوان باللغة العربية',
                                    'meta_key' => 'title_ar',
                                    'meta_value' => 'ما هو Wabell؟',
                                    'field_type' => 'text',
                                ],
                                // Description
                                [
                                    'display_name_en' => 'Description English',
                                    'display_name_ar' => 'الوصف باللغة الإنجليزية',
                                    'meta_key' => 'description_en',
                                    'meta_value' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nunc vulputate libero et velit interdum, ac aliquet odio mattis.',
                                    'field_type' => 'textarea',
                                ],
                                [
                                    'display_name_en' => 'Description Arabic',
                                    'display_name_ar' => 'الوصف باللغة العربية',
                                    'meta_key' => 'description_ar',
                                    'meta_value' => 'لوريم إيبسوم دولور سيت أميت، consectetur adipiscing إيليت. Nunc vulputate libero et velit interdum، ac aliquet odio mattis.',
                                    'field_type' => 'textarea',
                                ],
                                // Video
                                [
                                    'display_name_en' => "Video",
                                    'display_name_ar' => "Video",
                                    'meta_key' => 'video',
                                    'meta_value' => null,
                                    'field_type' => 'video',
                                ],
                            ],
                        ], */
                        /* [
                            'name_en' => 'Why Wabell Use',
                            'name_ar' => 'الشركاء الرائجون',
                            'section_key' => 'why_wabell',
                            'position' => 3,
                            'metas' => [
                                // Titles
                                [
                                    'display_name_en' => 'Title English',
                                    'display_name_ar' => 'عنوان باللغة الإنجليزية',
                                    'meta_key' => 'title_en',
                                    'meta_value' => 'Why Use Wabell?',
                                    'field_type' => 'text',
                                ],
                                [
                                    'display_name_en' => 'Title Arabic',
                                    'display_name_ar' => 'عنوان باللغة العربية',
                                    'meta_key' => 'title_ar',
                                    'meta_value' => 'لماذا تستخدم Wabell؟',
                                    'field_type' => 'text',
                                ],
                                // Description
                                [
                                    'display_name_en' => 'Description English',
                                    'display_name_ar' => 'الوصف باللغة الإنجليزية',
                                    'meta_key' => 'description_en',
                                    'meta_value' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nunc vulputate libero et velit interdum, ac aliquet odio mattis.',
                                    'field_type' => 'textarea',
                                ],
                                [
                                    'display_name_en' => 'Description Arabic',
                                    'display_name_ar' => 'الوصف باللغة العربية',
                                    'meta_key' => 'description_ar',
                                    'meta_value' => 'لوريم إيبسوم دولور سيت أميت، consectetur adipiscing إيليت. Nunc vulputate libero et velit interdum، ac aliquet odio mattis.',
                                    'field_type' => 'textarea',
                                ],

                                // For Learner
                                // Titles
                                [
                                    'display_name_en' => 'For Learner Title English',
                                    'display_name_ar' => 'عنوان باللغة الإنجليزية',
                                    'meta_key' => 'for_learner_heading_en',
                                    'meta_value' => 'For Learners',
                                    'field_type' => 'text',
                                ],
                                [
                                    'display_name_en' => 'For Learner Title Arabic',
                                    'display_name_ar' => 'عنوان باللغة العربية',
                                    'meta_key' => 'for_learner_heading_ar',
                                    'meta_value' => 'للمتعلمين',
                                    'field_type' => 'text',
                                ],
                                // Description
                                [
                                    'display_name_en' => 'For Learner Description English',
                                    'display_name_ar' => 'الوصف باللغة الإنجليزية',
                                    'meta_key' => 'for_learner_description_en',
                                    'meta_value' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nunc vulputate libero et velit interdum, ac aliquet odio mattis.',
                                    'field_type' => 'textarea',
                                ],
                                [
                                    'display_name_en' => 'For Learner Description Arabic',
                                    'display_name_ar' => 'الوصف باللغة العربية',
                                    'meta_key' => 'for_learner_description_ar',
                                    'meta_value' => 'لوريم إيبسوم دولور سيت أميت، consectetur adipiscing إيليت. Nunc vulputate libero et velit interdum، ac aliquet odio mattis.',
                                    'field_type' => 'textarea',
                                ],
                                // Image
                                [
                                    'display_name_en' => "For Learner Image",
                                    'display_name_ar' => "صورة",
                                    'meta_key' => 'for_learner_image',
                                    'meta_value' => null,
                                    'field_type' => 'image',
                                ],

                                // For Master
                                // Titles
                                [
                                    'display_name_en' => 'For Master Title English',
                                    'display_name_ar' => 'عنوان باللغة الإنجليزية',
                                    'meta_key' => 'for_master_heading_en',
                                    'meta_value' => 'For Masters',
                                    'field_type' => 'text',
                                ],
                                [
                                    'display_name_en' => 'For Master Title Arabic',
                                    'display_name_ar' => 'عنوان باللغة العربية',
                                    'meta_key' => 'for_master_heading_ar',
                                    'meta_value' => 'للمتعلمين',
                                    'field_type' => 'text',
                                ],
                                // Description
                                [
                                    'display_name_en' => 'For Master Description English',
                                    'display_name_ar' => 'الوصف باللغة الإنجليزية',
                                    'meta_key' => 'for_master_description_en',
                                    'meta_value' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nunc vulputate libero et velit interdum, ac aliquet odio mattis.',
                                    'field_type' => 'textarea',
                                ],
                                [
                                    'display_name_en' => 'For Master Description Arabic',
                                    'display_name_ar' => 'الوصف باللغة العربية',
                                    'meta_key' => 'for_master_description_ar',
                                    'meta_value' => 'لوريم إيبسوم دولور سيت أميت، consectetur adipiscing إيليت. Nunc vulputate libero et velit interdum، ac aliquet odio mattis.',
                                    'field_type' => 'textarea',
                                ],
                                // Image
                                [
                                    'display_name_en' => "For Master Image",
                                    'display_name_ar' => "صورة",
                                    'meta_key' => 'for_master_image',
                                    'meta_value' => null,
                                    'field_type' => 'image',
                                ],
                            ],
                        ], */
                        /* [
                            'name_en' => 'Frequently Asked Questions',
                            'name_ar' => 'انضم إلى دليل شبكتنا',
                            'section_key' => 'faq',
                            'position' => 4,
                            'metas' => [
                                [
                                    'display_name_en' => 'Title English',
                                    'display_name_ar' => 'العنوان باللغة الإنجليزية',
                                    'meta_key' => 'title_en',
                                    'meta_value' => 'Frequently Asked Questions',
                                    'field_type' => 'text',
                                ],
                                [
                                    'display_name_en' => 'Title Arabic',
                                    'display_name_ar' => 'العنوان باللغة العربية',
                                    'meta_key' => 'title_ar',
                                    'meta_value' => 'الأسئلة الشائعة',
                                    'field_type' => 'text',
                                ]
                            ],
                        ], */
                        /* [
                            'name_en' => 'App disply section',
                            'name_ar' => 'انضم إلى دليل شبكتنا',
                            'section_key' => 'last_section',
                            'position' => 4,
                            'metas' => [
                                [
                                    'display_name_en' => 'Title English',
                                    'display_name_ar' => 'العنوان باللغة الإنجليزية',
                                    'meta_key' => 'title_en',
                                    'meta_value' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
                                    'field_type' => 'text',
                                ],
                                [
                                    'display_name_en' => 'Title Arabic',
                                    'display_name_ar' => 'العنوان باللغة العربية',
                                    'meta_key' => 'title_ar',
                                    'meta_value' => 'لوريم إيبسوم دولور سيت أميت، consectetur adipiscing إيليت.',
                                    'field_type' => 'text',
                                ],
                                [
                                    'display_name_en' => 'Description English',
                                    'display_name_ar' => 'الوصف باللغة الإنجليزية',
                                    'meta_key' => 'description_en',
                                    'meta_value' => 'Find expert Masters near you. Wabell instantly connect you with specialized Masters near you.',
                                    'field_type' => 'textarea',
                                ],
                                [
                                    'display_name_en' => 'Description Arabic',
                                    'display_name_ar' => 'الوصف باللغة العربية',
                                    'meta_key' => 'description_ar',
                                    'meta_value' => 'ابحث عن خبراء بالقرب منك. يوصلك Wabell فورًا بخبراء متخصصين بالقرب منك.',
                                    'field_type' => 'textarea',
                                ],

                                // Mobile Left Image
                                [
                                    'display_name_en' => "Mobile Left Image",
                                    'display_name_ar' => "صورة",
                                    'meta_key' => 'left_mobile_image',
                                    'meta_value' => null,
                                    'field_type' => 'image',
                                ],
                                // Mobile Center Image
                                [
                                    'display_name_en' => "Mobile Center Image",
                                    'display_name_ar' => "صورة",
                                    'meta_key' => 'center_mobile_image',
                                    'meta_value' => null,
                                    'field_type' => 'image',
                                ],
                                // Mobile Right Image
                                [
                                    'display_name_en' => "Mobile Right Image",
                                    'display_name_ar' => "صورة",
                                    'meta_key' => 'right_mobile_image',
                                    'meta_value' => null,
                                    'field_type' => 'image',
                                ],
                            ],
                        ], */
                    ],
                ],
        ];

        foreach ($pages as $pageData) {
            $page = Page::create([
                'name_en' => $pageData['name_en'],
                'name_ar' => $pageData['name_ar'],
                'slug' => $pageData['slug'],
            ]);

            if(isset($pageData['sections'])){
                foreach ($pageData['sections'] as $sectionData) {
                    $section = Section::create([
                            'page_id'       => $page->id,
                            'name_en'       => $sectionData['name_en'],
                            'name_ar'       => $sectionData['name_ar'],
                            'section_key'   => $sectionData['section_key'],
                            'position'      => $sectionData['position']
                        ]);

                    foreach ($sectionData['metas'] as $metaData) {
                        SectionMeta::updateOrCreate([
                            'section_id'    => $section->id,
                            'display_name_en'  => $metaData['display_name_en'],
                            'display_name_ar'  => $metaData['display_name_ar'],
                            'meta_key'      => $metaData['meta_key'],
                            'meta_value'    => $metaData['meta_value'],
                            'field_type'    => $metaData['field_type']
                        ]);
                    }
                }
            }
        }
    }
}