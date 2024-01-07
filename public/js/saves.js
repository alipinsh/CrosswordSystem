var deleteButtons = document.querySelectorAll('button.delete-save-button');

var deleteButtonOnClick = function (e) {
    e.preventDefault();

    var request = new XMLHttpRequest();
    var form = new FormData();
    form.append('save_id', e.currentTarget.parentElement.parentElement.getAttribute('data-sid'));
    request.open('POST', '/saves/delete', true);

    request.onload = function() {
        if (this.status >= 200 && this.status < 400) {
            var data = JSON.parse(this.response);
            var saveToRemove = document.querySelector('div[data-sid="' + data['deleted_id'] + '"]');
            saveToRemove.parentElement.removeChild(saveToRemove);
        } else {
            console.log('error');
        }
    };

    request.send(form);
};

deleteButtons.forEach(function (e) {
    e.addEventListener('click', deleteButtonOnClick);
});
