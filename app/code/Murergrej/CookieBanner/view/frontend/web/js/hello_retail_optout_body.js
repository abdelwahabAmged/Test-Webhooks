/**
 * @category    Murergrej
 * @package     Murergrej_CookieBanner
 * @author      Abanoub Youssef <info@scandiweb.com>
 * @copyright   Copyright (c) 2024 Scandiweb, Inc (https://scandiweb.com)
 *
 * This script listens for the 'CookieInformationConsentGiven' event and updates the tracking opt-out status
 * based on the user's consent for marketing cookies.
 */
window.addEventListener('CookieInformationConsentGiven', function (event) {
    var hrq = window.hrq || [];
    hrq.push(["setTrackingOptOut", !CookieInformation.getConsentGivenFor('cookie_cat_marketing')]);
});
