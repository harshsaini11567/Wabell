<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title></title>

	<!-- FONT FAMILY -->
	<link href="https://fonts.googleapis.com/css2?family=Barlow:wght@400;500;600;700;800&family=Nunito+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @yield('styles')
</head>
<body style="margin: 0;background-color: #f2f5fc;padding: 20px; text-align: {{ $language == 'ar' ? 'right' : 'left' }} !important;" dir="{{ $language === 'ar' ? 'rtl' : 'ltr' }}">
	<div class="mail-template" style="max-width: 100%; margin: 0 auto;">
		<table cellpadding="0" cellspacing="0" width="100%" style="max-width:600px; margin:0 auto;background-color: #fff;padding: 30px;border-radius: 14px;">
			<thead>
				<tr style="width:100%;">
					<th style="text-align: center;">
						<!-- {{ getSetting('site_title') ? getSetting('site_title') : config('app.name') }} -->
                        <img style="width:200px; box-sizing: content-box;" src="{{ asset(config('constant.default.email_logo')) }}" alt="Wabell Logo" title="Wabell" /> 
                    </th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td style="padding:15px 0 30px;font-family:'Nunito Sans',sans-serif;color: #000;font-size: 15px;">
                        @yield('email-content') 
					</td>
				</tr>
			</tbody>
			<tfoot>
				<tr>
					<td style="border-top: 1px solid #dddddd;padding-top: 24px;padding-bottom: 14px;text-align: center;color: #6d7081;font-family:'Nunito Sans',sans-serif;">
						{{-- <img src="{{ asset(config('constant.default.email_logo')) }}" alt="logo" style="width: 200px;" /> --}}
						<p style="padding-top: 10px;text-align: center;margin: 0;font-size: 14px;">{{ trans('emails.footer_data', [], $language ?? 'ar') }}</p>
					</td>
				</tr>
				<tr>
					<td style="padding-bottom: 26px;text-align: center;color: #6d7081;font-family:'Nunito Sans',sans-serif;font-size: 14px;">
						<a href="tel:{{ getSetting('support_contact') }}" style="display: inline-block;color: #6d7081;text-decoration: none;vertical-align: middle;"><img src="{{ asset('default/social-icons/phone.png') }}" alt="Phone" style="vertical-align: sub;" /> {{ getSetting('support_contact') }}</a>
						<span style="margin: 0 6px;display: inline-block;">|</span>
						<a href="mailto:{{ getSetting('support_email') }}" style="display: inline-block;color: #6d7081;"><img src="{{ asset('default/social-icons/email.png') }}" alt="Email" style="vertical-align: sub;" /> {{ getSetting('support_email') }}</a>
						<span style="margin: 0 6px;display: inline-block;">|</span>
						<span style="vertical-align: middle;display: inline-block;"><img src="{{ asset('default/social-icons/map.png') }}" alt="Location" style="vertical-align: sub;" /> {{ getSetting('support_location_' . ($language ?? 'en')) }}</span>
					</td>
				</tr>
				<tr>
					<td style="text-align: center;padding-bottom: 20px;font-family:'Nunito Sans',sans-serif;">
						<a href="{{ getSetting('social_link_whatsapp') }}" title="WhatsApp" style="display: inline-block;margin: 0 5px;">
							<img src="{{ asset('default/social-icons/whatsapp.png') }}" alt="WhatsApp" />
						</a>
						<a href="{{ getSetting('social_link_facebook') }}" title="Facebook" style="display: inline-block;margin: 0 5px;">
							<img src="{{ asset('default/social-icons/facebook.png') }}" alt="Facebook" />
						</a>
						<a href="{{ getSetting('social_link_twitter') }}" title="Twitter" style="display: inline-block;margin: 0 5px;">
							<img src="{{ asset('default/social-icons/twitter.png') }}" alt="Twitter" />
						</a>
						<a href="{{ getSetting('social_link_snapchat') }}" title="Snapchat" style="display: inline-block;margin: 0 5px;">
							<img src="{{ asset('default/social-icons/snapchat.png') }}" alt="Snapchat" />
						</a>
						<a href="{{ getSetting('social_link_linkedin') }}" title="Linkedin" style="display: inline-block;margin: 0 5px;">
							<img src="{{ asset('default/social-icons/linkedin.png') }}" alt="Linkedin" />
						</a>
						<a href="{{ getSetting('social_link_instagram') }}" title="Instagram" style="display: inline-block;margin: 0 5px;">
							<img src="{{ asset('default/social-icons/instagram.png') }}" alt="Instagram" />
						</a>
						<a href="{{ getSetting('social_link_tiktok') }}" title="TikTok" style="display: inline-block;margin: 0 5px;">
							<img src="{{ asset('default/social-icons/tiktok.png') }}" alt="TikTok" />
						</a>
						<a href="{{ getSetting('social_link_youtube') }}" title="Youtube" style="display: inline-block;margin: 0 5px;">
							<img src="{{ asset('default/social-icons/youtube.png') }}" alt="Youtube" />
						</a>
					</td>
				</tr>
				<tr>
					<td style="text-align: center;color: #6d7081;font-family:'Nunito Sans',sans-serif;padding-bottom: 10px;font-size: 14px;">
						<a href="{{ config('app.frontend_url') . '/' . ($language ?? 'ar') . '/privacy-policy' }}" style="text-decoration: underline;color: #6d7081;">{{ trans('emails.privacy_policy', [], $language ?? 'ar') }}</a>
						<span style="margin: 0 6px;">|</span>
						<a href="{{ config('app.frontend_url') . '/' . ($language ?? 'ar') . '/terms-conditions' }}" style="text-decoration: underline;color: #6d7081;">{{ trans('emails.terms_of_service', [], $language ?? 'ar') }}</a>
						<p style="padding-top: 12px;margin: 0;text-align: center;">{{ trans('emails.copyright', ['year' => date('Y')], $language ?? 'ar') }}</p>
					</td>
				</tr>
			</tfoot>
		</table>
	</div>
	
</body>
</html>