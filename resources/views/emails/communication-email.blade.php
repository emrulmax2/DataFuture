<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
    <meta charset="utf-8"> <!-- utf-8 works for most cases -->
    <meta name="viewport" content="width=device-width"> <!-- Forcing initial-scale shouldn't be necessary -->
    <meta http-equiv="X-UA-Compatible" content="IE=edge"> <!-- Use the latest (edge) version of IE rendering engine -->
    <meta name="x-apple-disable-message-reformatting">  <!-- Disable auto-scale in iOS 10 Mail entirely -->
    <title></title> <!-- The title tag shows in email notifications, like Android 4.4. -->

    <link href="https://fonts.googleapis.com/css?family=Lato:300,400,700" rel="stylesheet">

    <!-- CSS Reset : BEGIN -->
    <style>
        /* What it does: Remove spaces around the email design added by some email clients. */
        /* Beware: It can remove the padding / margin and add a background color to the compose a reply window. */
        html,
		body {
			margin: 0 auto !important;
			padding: 0 !important;
			height: 100% !important;
			width: 100% !important;
			background: #f1f1f1;
		}

		/* What it does: Stops email clients resizing small text. */
		* {
			-ms-text-size-adjust: 100%;
			-webkit-text-size-adjust: 100%;
		}

		/* What it does: Centers email on Android 4.4 */
		div[style*="margin: 16px 0"] {
			margin: 0 !important;
		}

		/* What it does: Stops Outlook from adding extra spacing to tables. */
		table,
		td {
			mso-table-lspace: 0pt !important;
			mso-table-rspace: 0pt !important;
		}

		/* What it does: Fixes webkit padding issue. */
		table {
			border-spacing: 0 !important;
			border-collapse: collapse !important;
			table-layout: fixed !important;
			margin: 0 auto !important;
		}

		/* What it does: Uses a better rendering method when resizing images in IE. */
		img {
			-ms-interpolation-mode:bicubic;
		}

		/* What it does: Prevents Windows 10 Mail from underlining links despite inline CSS. Styles for underlined links should be inline. */
		a {
			text-decoration: none;
		}

		/* What it does: A work-around for email clients meddling in triggered links. */
		*[x-apple-data-detectors],  /* iOS */
		.unstyle-auto-detected-links *,
		.aBn {
			border-bottom: 0 !important;
			cursor: default !important;
			color: inherit !important;
			text-decoration: none !important;
			font-size: inherit !important;
			font-family: inherit !important;
			font-weight: inherit !important;
			line-height: inherit !important;
		}

		/* What it does: Prevents Gmail from displaying a download button on large, non-linked images. */
		.a6S {
			display: none !important;
			opacity: 0.01 !important;
		}

		/* What it does: Prevents Gmail from changing the text color in conversation threads. */
		.im {
			color: inherit !important;
		}

		/* If the above doesn't work, add a .g-img class to any image in question. */
		img.g-img + div {
			display: none !important;
		}

		/* What it does: Removes right gutter in Gmail iOS app: https://github.com/TedGoas/Cerberus/issues/89  */
		/* Create one of these media queries for each additional viewport size you'd like to fix */

		/* iPhone 4, 4S, 5, 5S, 5C, and 5SE */
		@media only screen and (min-device-width: 320px) and (max-device-width: 374px) {
			u ~ div .email-container {
				min-width: 320px !important;
			}
		}
		/* iPhone 6, 6S, 7, 8, and X */
		@media only screen and (min-device-width: 375px) and (max-device-width: 413px) {
			u ~ div .email-container {
				min-width: 375px !important;
			}
		}
		/* iPhone 6+, 7+, and 8+ */
		@media only screen and (min-device-width: 414px) {
			u ~ div .email-container {
				min-width: 414px !important;
			}
		}
    </style>

    <!-- CSS Reset : END -->

    <!-- Progressive Enhancements : BEGIN -->
    <style>
	    .primary{
			background: #30e3ca;
		}
		.bg_white{
			background: #ffffff;
		}
		.bg_light{
			background: #fafafa;
		}
		.bg_black{
			background: #000000;
		}
		.bg_dark{
			background: rgba(0,0,0,.8);
		}
		.email-section{
			padding:2.5em;
		}

		/*BUTTON*/
		.btn{
			padding: 10px 15px;
			display: inline-block;
		}
		.btn.btn-primary{
			border-radius: 5px;
			background: rgb(13,148,136);
			color: #ffffff;
		}
		.btn.btn-white{
			border-radius: 5px;
			background: #ffffff;
			color: #000000;
		}
		.btn.btn-white-outline{
			border-radius: 5px;
			background: transparent;
			border: 1px solid #fff;
			color: #fff;
		}
		.btn.btn-black-outline{
			border-radius: 0px;
			background: transparent;
			border: 2px solid #000;
			color: #000;
			font-weight: 700;
		}

		h1,h2,h3,h4,h5,h6{
			font-family: 'Lato', sans-serif;
			color: #000000;
			margin-top: 0;
			font-weight: 400;
		}

		body{
			font-family: 'Lato', sans-serif;
			font-weight: 400;
			font-size: 15px;
			line-height: 1.8;
			color: rgba(0,0,0,.4);
		}

		a{
			color: #30e3ca;
		}

		table{
			border-spacing: 0;
    		border-collapse: collapse;
			border: none;
		}
		

		/*HEADING SECTION*/
		.heading-section{
		}
		.heading-section h2{
			color: #000000;
			font-size: 28px;
			margin-top: 0;
			line-height: 1.4;
			font-weight: 400;
		}
		.heading-section .subheading{
			margin-bottom: 20px !important;
			display: inline-block;
			font-size: 13px;
			text-transform: uppercase;
			letter-spacing: 2px;
			color: rgba(0,0,0,.4);
			position: relative;
		}
		.heading-section .subheading::after{
			position: absolute;
			left: 0;
			right: 0;
			bottom: -10px;
			content: '';
			width: 100%;
			height: 2px;
			background: #30e3ca;
			margin: 0 auto;
		}

		.heading-section-white{
			color: rgba(255,255,255,.8);
		}
		.heading-section-white h2{
			font-family: 
			line-height: 1;
			padding-bottom: 0;
		}
		.heading-section-white h2{
			color: #ffffff;
		}
		.heading-section-white .subheading{
			margin-bottom: 0;
			display: inline-block;
			font-size: 13px;
			text-transform: uppercase;
			letter-spacing: 2px;
			color: rgba(255,255,255,.4);
		}

		ul.social{
			padding: 0;
		}
		ul.social li{
			display: inline-block;
			margin-right: 10px;
		}

		/*FOOTER*/

		.footer{
			border-top: 1px solid rgba(0,0,0,.05);
			color: rgba(0,0,0,.5);
		}
		.footer .heading{
			color: #000;
			font-size: 20px;
		}
		.footer ul{
			margin: 0;
			padding: 0;
		}
		.footer ul li{
			list-style: none;
			margin-bottom: 10px;
		}
		.footer ul li a{
			color: rgba(0,0,0,1);
		}

		@media screen and (max-width: 500px) {
		}


		/* New CSS */
		.logoText{
			color: #FFF;
			font-size: 32px;
			font-weight: 700;
			font-family: 'Lato', sans-serif;
			text-decoration: none;
		}
		.logoText:hover{
			color: #FFF;
		}
	</style>
</head>
	@php
		$opt = App\Models\Option::where('category', 'SITE_SETTINGS')->where('name','site_logo')->pluck('value', 'name')->toArray();
		$logoUrl = (isset($opt['site_logo']) && !empty($opt['site_logo']) && Storage::disk('local')->exists('public/'.$opt['site_logo']) ? Storage::disk('local')->url('public/'.$opt['site_logo']) : '');
	@endphp
	<body width="100%" style="margin: 0; padding: 15px 0 !important; background-color: #f1f1f1;">
		<div style="max-width: 600px; margin: 0 auto; background: #FFF; border-radius: 5px;" class="email-container">
			<!-- BEGIN BODY -->
			<table role="presentation" width="100%" style="margin: auto; border-radius: 5px 5px 5px 5px;">
				<tr>
					<td style="text-align: center; background: rgb(22, 78, 99); padding: 40px 0; border-radius: 5px 5px 0 0;">
						@if(!empty($logoUrl))
							<a href="https://lcc.ac.uk"><img style="max-width: 150px; height: auto;" src="{{ $logoUrl }}" /></a>
						@else 
							<a href="https://lcc.ac.uk"  style="color: #FFF; font-size: 22px; font-weight: bold; text-decoration: none; font-family: 'Lato', sans-serif;">London Churchill College</a>
						@endif
					</td>
				</tr>
				<tr>
					<td style="padding: 30px 30px; text-align: justify;">
						{!! $content !!}
					</td>
				</tr>
				<tr>
					<td style="padding: 20px 30px; border-top: 1px dotted #ddd; border-bottom: 1px dotted #ddd;">
						<p style="text-align: justify; color: #888; font-size: 12px; line-height: normal; margin: 0;">
							This e-mail and its attachments are intended for the above named only and may be confidential. If they have
							come to you in error you must take no action based on them, nor must you copy or show them to anyone; please
							reply to this e-mail and highlight the error. Although this email and any attachments are believed to be free of any
							virus or another defect which might affect any computer or IT system onto which it is received and opened, it is
							the responsibility of the recipient to ensure that it is virus free, and no responsibility is accepted by London
							Churchill College for any loss or damage arising from its use.
						</p>
					</td>
				</tr>
				<tr>
					<td style="text-align: center;">
						<p style="margin: 0; font-size: 13px; line-height: 20px; color: #777; padding: 10px 0;">
							<span class="text">Barclay Hall, 156B Green Street, London, E7 8JQ</span><br/>
						    <span class="text">+44 (0) 207 377 1077</span>
						</p>
					</td>
				</tr>
			</table>
		</div>
	</body>
</html>