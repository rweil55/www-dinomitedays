<script>
    function addDateToEntryTitle() {
        entryTitleElement = document.querySelector('.entry-title');
            if (entryTitleElement) {
        currentDate = new Date();
    options = {year: 'numeric', month: 'long', day: 'numeric' };
    var formattedDate = currentDate.toLocaleDateString(undefined, options);
    entryTitleElement.innerHTML += ' - ' + formattedDate;
    }
}
</script>