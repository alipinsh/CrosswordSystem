var reportForm = document.querySelector('form#report');
var reportInput = reportForm.querySelector('textarea[name="report_text"]')
var reportButton = reportForm.querySelector('button[name="postReport"]');
var reportError = document.querySelector('.report-error');
var showReportForm = document.querySelector('button#show-report-form');

var reportFormVisible = false;

function lengthInUtf8Bytes(str) {
    var m = encodeURIComponent(str).match(/%[89ABab]/g);
    return str.length + (m ? m.length : 0);
}

reportForm.addEventListener('submit', function (e) {
    e.preventDefault();

    var form = new FormData(reportForm);
    reportInput.value = reportInput.value.trim();

    if (!reportInput.value.length) {
        reportError.innerText = lang('notEmptyMessage');
        return;
    }

    if (lengthInUtf8Bytes(reportInput.value) > 65535) {
        reportError.innerText = lang('tooLongMessage');
        return;
    }

    var request = new XMLHttpRequest();
    request.open('POST', '/moderation/report', true);

    request.onload = function() {
        if (this.status >= 200 && this.status < 400) {
            var data = JSON.parse(this.response);

            reportError.innerText = '';
            reportButton.disabled = true;
            showReportForm.click();
        } else {
            console.log('error');
        }
    };

    request.send(form);
});

showReportForm.addEventListener('click', function (e) {
    reportForm.parentElement.style.display = reportFormVisible ? 'none' : 'block';
    reportFormVisible = !reportFormVisible;
});