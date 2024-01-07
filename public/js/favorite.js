var b = document.querySelector('button#favorite');
b.addEventListener('click', function (e) {
    e.target.disabled = true;

    var request = new XMLHttpRequest();
    var formData = new FormData();
    var crosswordId = document.querySelector('input[name="crossword_id"]').value;
    formData.append("crossword_id", crosswordId);

    request.open('POST', '/favorite', true);

    request.onload = function() {
        if (this.status >= 200 && this.status < 400) {
            var data = JSON.parse(this.response);
            console.log(data);
            var favoritesCount = document.querySelector('.favorites-count');
            var count = favoritesCount.innerText.match(/\d+/)[0];
            count = parseInt(count) + 1;
            favoritesCount.innerText = favoritesCount.innerText.replace(/\d+/, count);
        } else {
            console.log('error');
        }
    };

    request.send(formData);
});
