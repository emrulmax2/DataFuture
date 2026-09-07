<?php

/**
 * College-wide defaults for the staff email signature (My HR -> Email Signature).
 *
 * These are the parts of the signature that are the same for every member of
 * staff. Everything else is resolved from the employee's own HR record and can
 * be overridden by the employee on the tab itself.
 */
return [

    /*
     * Host the signature's images are linked from.
     *
     * Gmail and Outlook do not carry the pictures with the message — they
     * fetch them from their own servers when the mail is read, so every src
     * has to be an address the public internet can resolve. Leave this null
     * and the URLs follow ASSET_URL/APP_URL, which is right in production but
     * points at localhost during development (and therefore shows broken
     * images once pasted). Set SIGNATURE_ASSET_URL to the live domain to
     * produce a working signature from a local machine.
     */
    'asset_base' => env('SIGNATURE_ASSET_URL'),

    'organisation' => 'London Churchill College',

    'website' => [
        'label' => 'lcc.ac.uk',
        'url' => 'https://lcc.ac.uk',
    ],

    'telephone' => '020 7377 1077',

    'address' => [
        'line_1' => 'Barclay Hall, 156B Green Street,',
        'line_2' => 'Upton Park, London E7 8JQ',
    ],

    /*
     * Rendered as image links in the signature footer. Set a URL to null (or
     * remove the entry) to drop that icon from every staff signature.
     */
    'socials' => [
        'facebook' => 'https://www.facebook.com/londonchurchillcollege',
        'instagram' => 'https://www.instagram.com/londonchurchillcollege',
        'linkedin' => 'https://www.linkedin.com/company/london-churchill-college-ltd',
        'youtube' => 'https://www.youtube.com/user/LCCUK1',
        'x' => 'https://x.com/LCCUK1',
    ],

    'disclaimer' => 'This electronic message contains information which may be privileged or confidential. The information is intended to be for the use of the individual or entity named above. If you are not the intended recipient be aware that any disclosure, copying, distribution or use of the contents of this information is prohibited. If you have received this electronic message in error, please notify us by telephone or email immediately and delete it from your system. Internet e-mails are not necessarily secure. You are advised to scan this message for viruses and cannot accept liability for any loss or damage which may be caused as a result of any computer virus.',

];
