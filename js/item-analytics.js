(function () {
    /**
    * Activate the Popover utility.
    */
    [].slice.call(document.querySelectorAll('.block-asu-item-analytics-item-block span[data-toggle="popover"]')).map(function (el) {
        return new bootstrap.Popover(el)
    })
}())
