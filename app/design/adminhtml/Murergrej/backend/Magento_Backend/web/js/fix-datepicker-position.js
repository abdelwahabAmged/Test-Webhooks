require(['jquery'], function ($) {
    $(document).ready(function () {
        $(document).on('focus', '.admin__control-text._has-datepicker', function () {
            var datepicker = document.getElementById('ui-datepicker-div');
            const input = document.querySelector('.admin__control-text._has-datepicker:focus');

            if (input) {
                const offset = input.getBoundingClientRect();
                const topPosition = offset.top + window.scrollY + input.offsetHeight;
                const leftPosition = offset.left + window.scrollX;

                datepicker.style.position = 'absolute';
                datepicker.style.top = topPosition + 'px';
                datepicker.style.left = leftPosition + 'px';
            }
        });
    });
});
