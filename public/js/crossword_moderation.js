var reasonForm = document.querySelector('form#reason');
var reasonInput = reasonForm.querySelector('textarea[name="reason_text"]');
var reasonButton = reasonForm.querySelector('button[name="postReason"]');
var reasonError = document.querySelector('.reason-error');
var freeButton = document.querySelector('button#free-crossword');
var showReasonForm = document.querySelector('button#show-reason-form');

var reasonFormVisible = false;

reasonForm.addEventListener('submit', function (e) {
    e.preventDefault();

    var form = new FormData(reasonForm);

    reasonInput.value = reasonInput.value.trim();
    if (!reasonInput.value.length || !form.get('moderation_action').length) {
        reasonError.innerText = lang('notEmptyMessage');
        return;
    }

    var request = new XMLHttpRequest();
    request.open('POST', '/moderation/action', true);

    request.onload = function() {
        if (this.status >= 200 && this.status < 400) {
            var data = JSON.parse(this.response);

            window.location.href = window.location.protocol + '//' + window.location.host + '/moderation';
        } else {
            console.log('error');
        }
    };

    request.send(form);
});

if (freeButton) {
    freeButton.addEventListener('click', function (e) {
        e.preventDefault();

        var form = new FormData();
        form.append('crossword_id', freeButton.getAttribute('data-cid'));

        var request = new XMLHttpRequest();
        request.open('POST', '/moderation/free', true);

        request.onload = function() {
            if (this.status >= 200 && this.status < 400) {
                var data = JSON.parse(this.response);

                window.location.href = window.location.protocol + '//' + window.location.host + '/moderation';
            } else {
                console.log('error');
            }
        };

        request.send(form);
    });
}

showReasonForm.addEventListener('click', function (e) {
    reasonForm.parentElement.style.display = reasonFormVisible ? 'none' : 'block';
    reasonFormVisible = !reasonFormVisible;
});
