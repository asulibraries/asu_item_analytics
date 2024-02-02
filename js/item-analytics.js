(function() {
    /**
     * We should only be seeing paths that end in a node id,
     * e.g. 'items/1111`, 'node/1111', or perhaps 'collections/1111',
     * but we check and skip if not.
     * Also, use pathname to omit query strings and page fragments.
     */
    let matches = window.location.pathname.match(/\d+$/);
    if (!matches) {
        return;
    }
    let nid = matches[0];

    fetch(`/asu-item-analytics/${nid}/monthly`).then(response => response.json()).then(data => {
        let download_count = Object.values(data).reduce((a, b) => parseInt(a) + parseInt(b), 0);
        // Hide if we don't have data for it.
        if (download_count < 1) {
            return;
        }
        // Display the download count
        let block = document.getElementById("asu-item-analytics");
        if (!block) {return;}
        block.innerHTML = `
<span data-toggle="tooltip" data-delay="0" title="Downloads since the beginning of 2024.">
<i class="fas fa-info-circle"></i></span>
Download count: ${download_count.toLocaleString()}
`
        block.style.display = "block";
    });
}())
