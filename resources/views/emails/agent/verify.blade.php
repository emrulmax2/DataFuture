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
.btn.btn-primary a{
	
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
}
/*LOGO*/

.logo h1{
	margin: 0;
}
.logo h1 a{
	color: #30e3ca;
	font-size: 24px;
	font-weight: 700;
	font-family: 'Lato', sans-serif;
}

/*HERO*/
.hero{
	position: relative;
	z-index: 0;
}

.hero .text{
	color: rgba(0,0,0,.3);
}
.hero .text h2{
	color: #000;
	font-size: 40px;
	margin-bottom: 0;
	font-weight: 400;
	line-height: 1.4;
}
.hero .text h3{
	font-size: 18px;
	font-weight: 300;
}
.hero .text h2 span{
	font-weight: 600;
	color: #30e3ca;
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


    </style>


</head>

<body width="100%" style="margin: 0; padding: 0 !important; mso-line-height-rule: exactly; background-color: #f1f1f1;">
	<center style="width: 100%; background-color: #f1f1f1;">
    <div style="display: none; font-size: 1px;max-height: 0px; max-width: 0px; opacity: 0; overflow: hidden; mso-hide: all; font-family: sans-serif;">
      &zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;
    </div>
    <div style="max-width: 600px; margin: 0 auto;" class="email-container">
    	<!-- BEGIN BODY -->
      <table align="center" role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin: auto;">
      	<tr>
          <td valign="top" class="bg_white" style="padding: 1em 2.5em 0 2.5em;">
          	<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
          		<tr>
          			<td class="logo" style="text-align: center;">
			            <a style="display: block; width:190px; margin:0 auto;" href="https://lcc.ac.uk">
							@include("emails.logo")
						</a>
			          </td>
          		</tr>
          	</table>
          </td>
	      </tr><!-- end tr -->
	      
			<tr>
          <td valign="middle" class="hero bg_white" style="padding: 2em 0 4em 0;">
            <table>
            	<tr>
            		<td>
            			<div class="text" style="padding: 0 2.5em; text-align: center;">
            				<h2>Please verify your email</h2>
            				<h3>All Agent needs to verify email in order to apply</h3>
            				<div class="btn btn-primary"><a href="{{ $url }}" >Click Here to Verify</a></div>
            			</div>
            		</td>
            	</tr>
            </table>
          </td>
	      </tr><!-- end tr -->
      <!-- 1 Column Text + Button : END -->
      </table>
      {{-- <table align="center" role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin: auto;">
      	<tr>
          <td valign="middle" class="bg_light footer email-section">
            <table>
            	<tr>
                <td valign="top" width="33.333%" style="padding-top: 20px;">
                  <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                    <tr>
                      <td style="text-align: left; padding-right: 10px;">
                      	<h3 class="heading">About</h3>
                      	<p>London Churchill College is a medium sized College situated in East London.</p>
						
                      </td>
                    </tr>
                  </table>
                </td>
                <td valign="top" width="33.333%" style="padding-top: 20px;">
                  <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                    <tr>
                      <td style="text-align: left; padding-left: 5px; padding-right: 5px;">
                      	<h3 class="heading">Contact Info</h3>
                      	<ul>
					                <li><span class="text">Barclay Hall, 156 Green Street, London, E78JQ</span></li>
					                <li><span class="text">+44 (0) 0207 377 1077</span></a></li>
					              </ul>
                      </td>
                    </tr>
                  </table>
                </td>
                <td valign="top" width="33.333%" style="padding-top: 20px;">
                  <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                    <tr>
                      <td style="text-align: left; padding-left: 10px;">
                      	<h3 class="heading">Useful Links</h3>
                      	<ul>
					                <li><a href="https://lcc.ac.uk/">Home</a></li>
					                <li><a href="https://lcc.ac.uk/about-us/">About</a></li>
					                <li><a href="https://lcc.ac.uk/why-us/">Why Us</a></li>
					              </ul>
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>
            </table>
          </td>
        </tr><!-- end: tr -->
        <tr>
          <td class="bg_light" style="text-align: center;">
          	<p>No longer want to receive these email? You can <a href="#" style="color: rgba(0,0,0,.8);">Unsubscribe here</a></p>
          </td>
        </tr>
      </table> --}}
	  <table cellpadding="0" class="hero bg_white" cellspacing="0" style="width: 100%; margin: auto; vertical-align: -webkit-baseline-middle; font-size: medium; font-family: sans-serif;">
		<tbody>
		   <tr>
			  <td>
				 <table cellpadding="0" cellspacing="0" style="vertical-align: -webkit-baseline-middle; font-size: medium; font-family: sans-serif; width: 100%;">
					<tbody>
					   <tr>
						  <td height="30">&nbsp;</td>
					   </tr>
					   <tr>
						  <td color="#f2547d" direction="horizontal" height="1" style="width: 100%; border-bottom-width: 1px; border-bottom-style: solid; border-bottom-color: rgb(201, 12, 16); border-left-style: none; display: block;"></td>
					   </tr>
					   <tr>
						  <td height="30">&nbsp;</td>
					   </tr>
					</tbody>
				 </table>
			  </td>
		   </tr>
		   <tr>
			  <td>
				 <table cellpadding="0" cellspacing="0" style="vertical-align: -webkit-baseline-middle; font-size: medium; font-family: sans-serif; width: 95%;">
					<tbody>
					   <tr>
						  <td style="vertical-align: top;">
							<table cellpadding="0" cellspacing="0" style="vertical-align: -webkit-baseline-middle; font-size: medium; font-family: sans-serif; width: 100%;">
								<tr height="25" style="vertical-align: middle;">
									<td width="30" style="vertical-align: middle;">
									   <table cellpadding="0" cellspacing="0" style="vertical-align: -webkit-baseline-middle; font-size: medium; font-family: sans-serif;">
										  <tbody>
											 <tr>
												<td style="vertical-align: bottom;"> <span color="#f2547d" width="11" style="display: block; background-color: rgb(201, 12, 16);"> <img src="http://churchill.ac/img/i1.png" color="#f2547d" width="13" class="sc-iRbamj blSEcj" style="display: block; background-color: rgb(201, 12, 16);"> </span> </td>
											 </tr>
										  </tbody>
									   </table>
									</td>
									<td style="padding: 0px; color: rgb(0, 0, 0);" class="extension"> <a href="tel:02073771077" style="text-decoration: none; color: rgb(0, 0, 0); font-size: 12px;"> <span>02073771077</span> </a><span style="text-decoration: none; color: rgb(0, 0, 0); font-size: 12px;"></span> </td>
								 </tr>
								 <tr height="25" style="vertical-align: middle;" class="emailAddress">
									<td width="30" style="vertical-align: middle;">
									   <table cellpadding="0" cellspacing="0" style="vertical-align: -webkit-baseline-middle; font-size: medium; font-family: sans-serif;">
										  <tbody>
											 <tr>
												<td style="vertical-align: bottom;"> <span color="#f2547d" width="11" style="display: block; background-color: rgb(201, 12, 16);"> <img src="http://churchill.ac/img/i3.png" color="#f2547d" width="13" style="display: block; background-color: rgb(201, 12, 16);"> </span> </td>
											 </tr>
										  </tbody>
									   </table>
									</td>
									<td style="padding: 0px;" class="emailAddressHtml"><span style="color: rgb(0, 0, 0); font-size: 12px;">info@lcc.ac.uk</span></td>
								 </tr>
								 <tr height="25" style="vertical-align: middle;">
									<td width="30" style="vertical-align: top; padding-top:10px">
									   <table cellpadding="0" cellspacing="0" style="vertical-align: -webkit-baseline-middle; font-size: medium; font-family: sans-serif;">
										  <tbody>
											 <tr>
												<td style="vertical-align: bottom;"> <span color="#f2547d" width="11" style="display: block; background-color: rgb(201, 12, 16);"> <img src="http://churchill.ac/img/address.png" color="#f2547d" width="13" style="display: block; background-color: rgb(201, 12, 16);"> </span> </td>
											 </tr>
										  </tbody>
									   </table>
									</td>
									<td style="padding: 0px; line-height:13px; padding-top:5px;"> <span style="font-size: 12px; color: rgb(0, 0, 0);"> <span>Barclay Hall, 156B Green Street,<br>London, E7 8JQ</span> </span> </td>
								 </tr>
							</table>
						  </td>
						  <td style="text-align: right; vertical-align: top;">
							 <table cellpadding="0" cellspacing="0" style="vertical-align: -webkit-baseline-middle; font-size: medium; font-family: sans-serif; display: inline-block;">
								<tbody>
								   <tr style="text-align: right;">
									   
									  <td> <a href="https://www.facebook.com/londonchurchillcollege" color="#6a78d1" style="display: inline-block; padding: 0px;"> <img src="http://churchill.ac/img/facebook_new.png" alt="facebook" color="#6a78d1" height="30" style="max-width: 135px; display: block;"> </a> </td>
									  <td width="5">
										 <div></div>
									  </td>
										
									  <td> <a href="https://instagram.com/londonchurchillcollege" color="#6a78d1" style="display: inline-block; padding: 0px;"> <img src="http://churchill.ac/img/instagram.png" alt="facebook" color="#6a78d1" height="30" style="max-width: 135px; display: block;"> </a> </td>
									  <td width="5">
										 <div></div>
									  </td>
										
									  <td> <a href="https://www.linkedin.com/company/london-churchill-college-ltd" color="#6a78d1" style="display: inline-block; padding: 0px;"> <img src="http://churchill.ac/img/linkedin_new.png" alt="linkedin" color="#6a78d1" height="30" style="max-width: 135px; display: block;"> </a> </td>
									  <td width="5">
										 <div></div>
									  </td>
										
									  <td> <a href="https://www.youtube.com/user/LCCUK1" color="#6a78d1" style="display: inline-block; padding: 0px;"> <img src="http://churchill.ac/img/youtube.png" alt="instagram" color="#6a78d1" height="30" style="max-width: 135px; display: block;"> </a> </td>
									  <td width="5">
										 <div></div>
									  </td>
										
									  <td> <a href="https://twitter.com/LCC_Welfare" color="#6a78d1" style="display: inline-block; padding: 0px;"> <img src="http://churchill.ac/img/twitter_new.png" alt="twitter" color="#6a78d1" height="30" style="max-width: 135px; display: block;"> </a> </td>
									  <td width="5">
										 <div></div>
									  </td>
																							</tr>
								</tbody>
							 </table>
						  </td>
					   </tr>
					</tbody>
				 </table>
			  </td>
		   </tr>
			
		   <tr>
			  <td height="30">&nbsp;</td>
		   </tr>
		   <tr>
			  <td>
				 <p style=" color: rgb(191,191,191); font-size: 10px; font-family: sans-serif; width:95%; margin-left:15px"> <strong>Disclaimer:</strong><br> This electronic message contains information which may be privileged or confidential. The information is intended to be for the use of the individual or entity named above. If you are not the intended recipient be aware that any disclosure, copying, distribution or use of the contents of this information is prohibited. If you have received this electronic message in error, please notify us by telephone or email immediately and delete it from your system. Internet e-mails are not necessarily secure. You are advised to scan this message for viruses and cannot accept liability for any loss or damage which may be caused as a result of any computer virus. </p>
			  </td>
		   </tr>
		   <tr>
			  <td height="30">&nbsp;</td>
		   </tr>
			
		</tbody>
	 </table>
    </div>
  </center>
</body>
</html>