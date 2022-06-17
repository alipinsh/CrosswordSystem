var freeButtons = document.querySelectorAll('.reported-comment .free-button');
var deleteButtons = document.querySelectorAll('.reported-comment .delete-button');

var freeComment = function (e) {
    e.preventDefault();
    e.currentTarget.disabled = true;

    var request = new XMLHttpRequest();
    var form = new FormData();
    form.append('comment_id', e.currentTarget.parentElement.getAttribute('data-comment'));
    request.open('POST', '/moderation/comment/free', true);

    var comment = e.currentTarget.parentElement;

    request.onload = function() {
        if (this.status >= 200 && this.status < 400) {
            comment.parentElement.removeChild(comment);
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

    var comment = e.currentTarget.parentElement;

    request.onload = function() {
        if (this.status >= 200 && this.status < 400) {
            comment.parentElement.removeChild(comment);
        } else {
            console.log('error');
        }
    };

    request.send(form);
};

freeButtons.forEach(function(button) {
    button.addEventListener('click', freeComment);
});

deleteButtons.forEach(function(button) {
    button.addEventListener('click', deleteComment);
});
