/**
 * @category    Murergrej
 * @package     Murergrej_CookieBanner
 * @author      Abanoub Youssef <info@scandiweb.com>
 * @copyright   Copyright (c) 2024 Scandiweb, Inc (https://scandiweb.com)
 *
 * This script initializes the `hrq` array and pushes a function to it. The function checks if consent
 * for marketing cookies has not been given and sets the tracking opt-out status accordingly.
 */
var hrq = window.hrq || [];
hrq.push(function (sdk) {
    if (!CookieInformation.getConsentGivenFor('cookie_cat_marketing')) {
        sdk.setTrackingOptOut(true);
    }
});
