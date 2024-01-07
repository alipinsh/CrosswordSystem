var saveProgressButton = document.querySelector('button#save-progress');
var saveProgressHolder = document.querySelector('div#save-data');

saveProgressButton.addEventListener('click', function (e) {
    e.target.disabled = true;
    var progress = [{}, {}];
    for (var q in crossword.positions) {
        q = parseInt(q);
        var qx = crossword.positions[q][X];
        var qy = crossword.positions[q][Y];
        var qs = (q+1).toString();

        if (crossword.questions[HORIZONTAL][qs]) {
            progress[HORIZONTAL][qs] = '';
            var answer = crossword.questions[HORIZONTAL][qs][ANSWER];
            for (var ai = 0; ai < answer.length; ++ai) {
                var lastLetter = getCell(qx+ai, qy).textContent.slice(-1).toLowerCase();
                if (lastLetter === '' || !isNaN(lastLetter)) {
                    progress[HORIZONTAL][qs] += '*';
                } else {
                    progress[HORIZONTAL][qs] += lastLetter;
                }
            }
        }
        if (crossword.questions[VERTICAL][qs]) {
            progress[VERTICAL][qs] = '';
            var answer = crossword.questions[VERTICAL][qs][ANSWER];
            for (var ai = 0; ai < answer.length; ++ai) {
                var lastLetter = getCell(qx, qy+ai).textContent.slice(-1).toLowerCase();
                if (lastLetter === '' || !isNaN(lastLetter)) {
                    progress[VERTICAL][qs] += '*';
                } else {
                    progress[VERTICAL][qs] += lastLetter;
                }
            }
        }
    }

    var request = new XMLHttpRequest();
    var formData = new FormData();
    var crosswordId = document.querySelector('input[name="crossword_id"]').value;
    formData.append("crosswordId", crosswordId);
    formData.append('progress', JSON.stringify(progress));

    request.open('POST', '/saves/save', true);

    request.onload = function() {
        if (this.status >= 200 && this.status < 400) {
            var data = JSON.parse(this.response);
            e.target.disabled = false;
        } else {
            console.log('error');
        }
    };

    request.send(formData);
});

document.addEventListener("DOMContentLoaded", function (e) {
    if (saveProgressHolder) {
        var saveProgress = JSON.parse(saveProgressHolder.innerText);

        for (var q in crossword.positions) {
            q = parseInt(q);
            var qx = crossword.positions[q][X];
            var qy = crossword.positions[q][Y];
            var qs = (q+1).toString();

            if (crossword.questions[HORIZONTAL][qs]) {
                var answer = crossword.questions[HORIZONTAL][qs][ANSWER];
                for (var ai = 0; ai < answer.length; ++ai) {
                    if (saveProgress[HORIZONTAL][qs][ai] !== '*') {
                        inputLetter(getCell(qx+ai, qy), saveProgress[HORIZONTAL][qs][ai]);
                    }
                }
            }
            if (crossword.questions[VERTICAL][qs]) {
                var answer = crossword.questions[VERTICAL][qs][ANSWER];
                for (var ai = 0; ai < answer.length; ++ai) {
                    if (saveProgress[VERTICAL][qs][ai] !== '*') {
                        inputLetter(getCell(qx, qy+ai), saveProgress[VERTICAL][qs][ai]);
                    }
                }
            }
        }

        saveProgressHolder.parentElement.removeChild(saveProgressHolder);
    }
});
