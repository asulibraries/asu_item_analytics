(function () {
    /**
    * Activate the Popover utility.
    */
    [].slice.call(document.querySelectorAll('.asu-item-analytics-popover span[data-bs-toggle="popover"]')).map(function (el) {
        return new bootstrap.Popover(el)
    })
}());
