<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Quotation (PDF) settings
|--------------------------------------------------------------------------
|
| Static company data printed on the order quotation PDF that does not live
| in the database. Seeded from the current printed quotation; edit freely.
| Only the currency symbol is used in code — everything else is display text.
|
*/

return [
    // Letterhead shown at the top of every quotation.
    'company_name' => 'SHIVDHARA MARBO GRANITO',
    'tagline'      => 'MARBO GRANITO',

    // Suffix for money amounts. Kept as "Rs" (not the ₹ glyph) so it renders in
    // dompdf's default font, matching the printed quotation.
    'currency' => 'Rs',

    // Terms & Conditions printed at the foot of the quotation, in order.
    'terms' => [
        'Billing Name BANSARY CERAMIC, Kotak Mahindra Bank Ltd A/C No: 1013615076, IFSC: KKBK0002864, G-4, Blue Sky, Bamroli Road, Surat - 395007',
        'Unloading Charges will be borne by you at actuals.',
        'Payment Terms: 100% Advance against Confirmed Order.',
        'Rate Validity: The Above Rates Are Subjected To change If Incase There Is Any Changes In to Government Levise.',
        'Delivery: Within 10-12 Days After Receipt Of Confirmed Order And Payment & Subject Availablity Of The Stock.',
        'Goods Will Be Delivered Till The Ground Floor.',
        'Above Rates Will Be Valid Till 5 days.',
        'As Per Company Norms 2% Breakages Should Be Consider.',
        'G.S.T No: 24AAPFB2862C1ZO',
        'The Above Rates Are Inclusive Of G.S.T',
        'Imported Item Will Not Be Taken Back, Once Sold.',
    ],
];
