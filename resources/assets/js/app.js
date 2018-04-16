
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

$(document).keyup(function(e) {
    if (e.which === 115 || e.which === 83) {
        $('input[name="search"]').get(0).focus();
    }
});
