var searchQueryForm = document.querySelector('input#search-query');
var searchButton = document.querySelector('button#search');

searchButton.addEventListener('click', function (e) {
    var searchQuery = searchQueryForm.value.trim();
    if (searchQuery) {
        window.location.href =
            window.location.protocol + '//' + window.location.host + '/crosswords/search/' + encodeURI(searchQuery);
    }
});
