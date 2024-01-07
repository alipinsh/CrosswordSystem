var commentForm = document.querySelector('form#comment');
var commentInput = commentForm.querySelector('textarea[name="comment_text"]');
var commentError = document.querySelector('.comment-error');
var getCommentsButton = document.querySelector('button#get-comments');
var moreCommentsButton = document.querySelector('button#more-comments');

var loadedCommentsPage = 0;
var totalPages = 0;

commentForm.addEventListener('submit', function (e) {
    e.preventDefault();

    var form = new FormData(commentForm);
    commentInput.value = commentInput.value.trim();

    if (!commentInput.value.length) {
        commentError.innerText = lang('notEmptyMessage');
        return;
    }

    if (commentInput.value.length > 65535) {
        commentError.innerText = lang('tooLongMessage');
        return;
    }

    var request = new XMLHttpRequest();
    request.open('POST', '/comment', true);

    request.onload = function() {
        if (this.status >= 200 && this.status < 400) {
            var data = JSON.parse(this.response);

            document.querySelector('.comments-list').insertBefore(
                createCommentElement(data),
                document.querySelector('.comments-list').firstChild
            );

            commentForm.querySelector('button').disabled = true;
        } else {
            console.log('error');
        }
    };

    request.send(form);
});

function renderEditForm(text) {
    var editForm = document.createElement('form');
    var editCommentError = document.createElement('div');
    editCommentError.classList.add('edit-comment-error');
    var textEdit = document.createElement('textarea');
    textEdit.setAttribute('name', 'edited_text');
    textEdit.value = text;
    var editConfirm = document.createElement('button');
    editConfirm.setAttribute('type', 'submit');
    editConfirm.innerText = lang('ok');
    var cancel = document.createElement('button');
    cancel.innerText = lang('cancel');
    cancel.addEventListener('click', function (e) {
        e.preventDefault();
        e.currentTarget.parentElement.parentElement.classList.remove('editing');
        e.currentTarget.parentElement.parentElement.removeChild(e.currentTarget.parentElement);
    });

    editForm.appendChild(editCommentError);
    editForm.appendChild(textEdit);
    editForm.appendChild(cancel);
    editForm.appendChild(editConfirm);

    editForm.addEventListener('submit', function (e) {
        e.preventDefault();

        var currentForm = e.currentTarget;
        var editCommentInput = currentForm.querySelector('textarea[name="edited_text"]');
        editCommentInput.value = editCommentInput.value.trim();

        if (!editCommentInput.value.length) {
            currentForm.querySelector('.edit-comment-error').innerText = lang('notEmptyMessage');
            return;
        }

        var form = new FormData(e.currentTarget);
        form.append('id', e.currentTarget.parentElement.getAttribute('data-comment'));

        var request = new XMLHttpRequest();
        request.open('POST', '/comment/edit', true);

        request.onload = function() {
            if (this.status >= 200 && this.status < 400) {
                var data = JSON.parse(this.response);

                currentForm.parentElement.querySelector('.comment-text').innerHTML = data.text;
                currentForm.parentElement.classList.remove('editing');
                currentForm.parentElement.querySelector('.comment-date').innerText = data.posted_at +
                    ' ('+lang('editedOn')+': '+data.edited_at+')';
                currentForm.parentElement.removeChild(currentForm);
            } else {
                console.log('error');
            }
        };

        request.send(form);
    });

    return editForm;
}

var reportComment = function(e) {
    e.currentTarget.disabled = true;

    var form = new FormData();
    form.append('comment_id', e.currentTarget.parentElement.parentElement.getAttribute('data-comment'));

    var request = new XMLHttpRequest();
    request.open('POST', '/moderation/comment/report', true);

    request.onload = function() {
        if (this.status >= 200 && this.status < 400) {

        } else {
            console.log('error');
        }
    };

    request.send(form);
};

var editComment = function(e) {
    e.preventDefault();

    var comment = e.currentTarget.parentElement.parentElement;
    comment.classList.add('editing');
    var text = comment.querySelector('.comment-text').innerText;
    var editForm = renderEditForm(text);
    comment.appendChild(editForm);
};

var deleteComment = function(e) {
    e.preventDefault();

    var request = new XMLHttpRequest();
    var form = new FormData();
    form.append('id', e.currentTarget.parentElement.parentElement.getAttribute('data-comment'));
    request.open('POST', '/comment/delete', true);

    request.onload = function() {
        if (this.status >= 200 && this.status < 400) {
            var data = JSON.parse(this.response);
            var commentToRemove = document.querySelector('div[data-comment="' + data['deleted_id'] + '"]');
            commentToRemove.parentElement.removeChild(commentToRemove);
        } else {
            console.log('error');
        }
    };

    request.send(form);
};

function createCommentElement(data) {
    var comment = document.createElement("div");
    comment.classList.add('comment');
    comment.setAttribute('data-comment', data.id);

    comment.innerHTML =
        '<div class="comment-date">'+data.posted_at+
        ((data.posted_at == data.edited_at) ? '' : ' ('+lang('editedOn')+': '+data.edited_at+')') +
        '</div>' +
        '<div class="comment-body">' +
        '<div class="comment-image-col"><img src="/img/avatar/min/'+data.image+'"></div>' +
        '<div class="comment-text-col">' +
        '<div class="comment-username">'+data.username+'</div>' +
        '<div class="comment-text">'+data.text+'</div>' +
        '</div>' +
        '</div>';

    var commentActions = document.createElement('div');
    commentActions.classList.add('comment-actions');
    var reportButton = document.createElement('button');
    reportButton.classList.add('report-button');
    reportButton.addEventListener('click', reportComment);
    reportButton.innerText = lang('report');
    commentActions.appendChild(reportButton);

    if (data.editable) {
        var editButton = document.createElement('button');
        editButton.classList.add('edit-button');
        editButton.addEventListener('click', editComment);
        editButton.innerText = lang('edit');
        commentActions.appendChild(editButton);
        var deleteButton = document.createElement('button');
        deleteButton.classList.add('delete-button');
        deleteButton.addEventListener('click', deleteComment);
        deleteButton.innerText = lang('delete');
        commentActions.appendChild(deleteButton);
    }

    comment.appendChild(commentActions);

    return comment;
}

getCommentsButton.addEventListener('click', function(e) {
    e.preventDefault();

    var request = new XMLHttpRequest();
    var cid = window.location.href.substring(window.location.href.lastIndexOf('/') + 1);
    request.open('GET', '/comments?cid=' + cid + '&p=' + (loadedCommentsPage + 1), true);

    request.onload = function() {
        if (this.status >= 200 && this.status < 400) {
            var data = JSON.parse(this.response);
            loadedCommentsPage++;
            totalPages = data.totalPages;

            var fragment = document.createDocumentFragment();
            for (var i = 0; i < data.comments.length; ++i) {
                fragment.appendChild(createCommentElement(data.comments[i]));
            }

            getCommentsButton.disabled = true;
            document.querySelector('.comments').style.display = 'block';
            document.querySelector('.comments-list').appendChild(fragment);

            if (loadedCommentsPage == totalPages) {
                moreCommentsButton.disabled = true;
            }
        } else {
            console.log('error');
        }
    };

    request.send();
});

moreCommentsButton.addEventListener('click', function (e) {
    e.preventDefault();

    var request = new XMLHttpRequest();
    var cid = window.location.href.substring(window.location.href.lastIndexOf('/') + 1);
    request.open('GET', '/comments?cid=' + cid + '&p=' + (loadedCommentsPage + 1), true);

    request.onload = function() {
        if (this.status >= 200 && this.status < 400) {
            var data = JSON.parse(this.response);
            loadedCommentsPage++;
            totalPages = data.totalPages;

            var fragment = document.createDocumentFragment();
            for (var i = 0; i < data.comments.length; ++i) {
                fragment.appendChild(createCommentElement(data.comments[i]));
            }

            document.querySelector('.comments').style.display = 'block';
            document.querySelector('.comments-list').appendChild(fragment);

            if (loadedCommentsPage == totalPages) {
                moreCommentsButton.disabled = true;
            }
        } else {
            console.log('error');
        }
    };

    request.send();
});
