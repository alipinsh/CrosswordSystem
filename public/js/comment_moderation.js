var freeButtons = document.querySelectorAll('.reported-comment .free-button');
var deleteButtons = document.querySelectorAll('.reported-comment .delete-button');

var freeComment = function (e) {
    e.preventDefault();
    e.currentTarget.disabled = true;

    var request = new XMLHttpRequest();
    var form = new FormData();
    form.append('comment_id', e.currentTarget.parentElement.getAttribute('data-comment'));
    request.open('POST', '/moderation/comment/free', true);

    request.onload = function() {
        if (this.status >= 200 && this.status < 400) {
            var commentToRemove = e.currentTarget.parentElement;
            e.currentTarget.parentElement.parentElement.removeChild(commentToRemove);
        } else {
            console.log('error');
        }
    };

    request.send(form);
};

var deleteComment = function (e) {
    e.preventDefault();
    e.currentTarget.disabled = true;

    var request = new XMLHttpRequest();
    var form = new FormData();
    form.append('comment_id', e.currentTarget.parentElement.getAttribute('data-comment'));
    request.open('POST', '/moderation/comment/action', true);

    request.onload = function() {
        if (this.status >= 200 && this.status < 400) {
            var commentToRemove = e.currentTarget.parentElement;
            e.currentTarget.parentElement.parentElement.removeChild(commentToRemove);
        } else {
            console.log('error');
        }
    };

    request.send(form);
};

freeButtons.forEach(function(button) {
    button.addEventListener(freeComment);
});

deleteButtons.forEach(function(button) {
    button.addEventListener(deleteComment);
});
