var saveButton = document.querySelector('input#save');
var deleteCrosswordButton = document.querySelector('input#delete');
var yesDeleteButton = document.querySelector('input#yes-delete');
var noDeleteButton = document.querySelector('input#no-delete');
var titleForm = document.querySelector('input#title');
var languageForm = document.querySelector('select#language');
var tagsForm = document.querySelector('textarea#tags');
var publicCheckbox = document.querySelector('input#is_public')
var widthForm = document.querySelector('input#width');
var heightForm = document.querySelector('input#height');
var resizeButton = document.querySelector('input#resize');
var gridContainer = document.querySelector('.grid-container');
var grid = document.querySelector('.grid');
var preview = document.querySelector('.word-preview');

var horizontalQuestionsElement = document.querySelector('.horizontal-questions');
var verticalQuestionsElement = document.querySelector('.vertical-questions');

var addButton = document.querySelector('.tool-button#add');
var editButton = document.querySelector('.tool-button#edit');
var deleteButton = document.querySelector('.tool-button#delete');
var moveButton = document.querySelector('.tool-button#move');

var error = document.querySelector('.error');
var infoError = document.querySelector('.info-error');
var infoError = document.querySelector('.info');

var editModal = document.querySelector('.edit-modal');
var editModalQuestion = editModal.querySelector('textarea');
var editModalAnswer = editModal.querySelector('input');
var editModalLetterCount = editModal.querySelector('.letter-count');
var editModalCancelButton = editModal.querySelector('button.cancel-button');
var editModalOKButton = editModal.querySelector('button.ok-button');

var moveModal = document.querySelector('.move-modal');
var moveModalLeft = moveModal.querySelector('input#left');
var moveModalTop = moveModal.querySelector('input#top');
var moveModalCancelButton = moveModal.querySelector('button.cancel-button');
var moveModalOKButton = moveModal.querySelector('button.ok-button');

var crosswordId = null;
var overlaps = [];
var modalVisible = false;

var ALLOWED_LETTERS = {
    en: 'abcdefghijklmnopqrstuvwxyz',
    ru: 'абвгдеёжзийклмнопрстуфхцчшщьыъэюя',
    lv: 'aābcčdeēfgģhiījkķlļmnņoprsštuūvzž'
};

var CELL_WIDTH = 24;
var CELL_HEIGHT = 24;

var X = 0;
var Y = 1;
var HORIZONTAL = 0;
var VERTICAL = 1;
var WIDTH = 0;
var HEIGHT = 1;
var QUESTION = 0;
var ANSWER = 1;
var MIN = 0;
var MAX = 1;

var currentDirection = HORIZONTAL;
var selectedCell = [0, 0];

var crossword = {
    size: [10, 10],
    positions: [],
    questions: [{}, {}]
}

let answerRegex = null;
let tagsRegex = new RegExp('^[' + ALLOWED_LETTERS.en + ALLOWED_LETTERS.ru + ALLOWED_LETTERS.lv + ',]+$');

function isNumeric(str) {
    if (typeof str != "string") {
        return false;
    }
    return !isNaN(str) && !isNaN(parseFloat(str));
}

function clamp(number, min, max) {
    return Math.min(Math.max(number, min), max);
}

function replaceLetter(word, letter, pos) {
    return word.substring(0, pos) + letter + word.substring(pos + letter.length);
}

function updateAnswerRegex() {
    answerRegex = new RegExp('^[' + ALLOWED_LETTERS[languageForm.value] + ']+$', 'i');
}

var cellOnClick = function (e) {
    if (!modalVisible) {
        var x = Number(e.currentTarget.getAttribute('x'));
        var y = Number(e.currentTarget.getAttribute('y'));

        clearHighlight();
        if (e.currentTarget.classList.contains('selected')) {
            currentDirection = 1 - currentDirection;
        } else {
            selectCell(e.currentTarget);
        }
        highlightWholeLine();
        highlightWord();
    }
};

function renderGrid() {
    var fragment = document.createDocumentFragment();
    var x = 0;
    var y = 0;
    var area = crossword.size[WIDTH] * crossword.size[HEIGHT];
    for (var i = 0; i < area; ++i) {
        var e = document.createElement('div');
        e.classList.add('cell');
        e.classList.add('blocked');
        e.setAttribute('x', x);
        e.setAttribute('y', y);
        e.addEventListener('click', cellOnClick);
        fragment.appendChild(e);
        x++;
        if (x === crossword.size[WIDTH]) {
            x = 0;
            y++;
            fragment.appendChild(document.createElement('br'));
        }
    }
    grid.appendChild(fragment);
    grid.style.width = crossword.size[WIDTH] * CELL_WIDTH + 'px';
    grid.style.height = crossword.size[HEIGHT] * CELL_HEIGHT + 'px';
}

function renderAnswers() {
    for (var q in crossword.positions) {
        q = parseInt(q);
        var qx = crossword.positions[q][X];
        var qy = crossword.positions[q][Y];
        var qs = (q+1).toString();

        if (crossword.questions[HORIZONTAL][qs]) {
            var answer = crossword.questions[HORIZONTAL][qs][ANSWER];
            for (var ai = 0; ai < answer.length; ++ai) {
                var c = getCell(qx+ai, qy);
                c.innerText = answer[ai];
                c.classList.remove('blocked');
            }
        }
        if (crossword.questions[VERTICAL][qs]) {
            var answer = crossword.questions[VERTICAL][qs][ANSWER];
            for (var ai = 0; ai < answer.length; ++ai) {
                var c = getCell(qx, qy+ai);
                c.innerText = answer[ai];
                c.classList.remove('blocked');
            }
        }
    }

    for (var q in crossword.positions) {
        q = parseInt(q);
        var qx = crossword.positions[q][X];
        var qy = crossword.positions[q][Y];

        var gridIndex = document.createElement('span');
        gridIndex.classList.add('grid-index');
        gridIndex.innerText = q+1;

        var indexCell = getCell(qx, qy);
        indexCell.insertBefore(gridIndex, indexCell.firstChild);
    }
}

function clearAnswers() {
    var notBlockedCells = document.querySelectorAll('.cell:not(.blocked)');
    for (var i = 0; i < notBlockedCells.length; ++i) {
        notBlockedCells[i].classList.add('blocked');
        notBlockedCells[i].innerHTML = '';
    }
}

function onQuestionClick(e) {
    var answerId = e.currentTarget.getAttribute('data-question');
    var answerDirection = e.currentTarget.parentElement.classList.contains('horizontal-questions') ?
        HORIZONTAL : VERTICAL;
    var x = crossword.positions[answerId-1][X];
    var y = crossword.positions[answerId-1][Y];

    currentDirection = answerDirection;

    clearHighlight();
    selectCell(getCell(x, y));
    highlightWholeLine();
    highlightWord();

    gridContainer.scrollTo(x * CELL_WIDTH, y * CELL_HEIGHT);
}

function renderQuestions(questionsDirection) {
    var fragment = document.createDocumentFragment();

    for (var k in crossword.questions[questionsDirection]) {
        var question = document.createElement('div');
        question.classList.add('question');
        question.setAttribute('data-question', k);
        question.addEventListener('click', onQuestionClick);
        var questionNumber = document.createElement('span');
        questionNumber.classList.add('question-number');
        questionNumber.innerText = k + '. ';
        var questionText = document.createElement('div');
        questionText.classList.add('question-text');
        questionText.appendChild(questionNumber);
        questionText.innerHTML += crossword.questions[questionsDirection][k][QUESTION];
        var questionAnswer = document.createElement('div');
        questionAnswer.classList.add('question-answer');
        questionAnswer.innerText = crossword.questions[questionsDirection][k][ANSWER];

        question.appendChild(questionText);
        question.appendChild(questionAnswer);

        fragment.appendChild(question);
    }
    switch (questionsDirection) {
        case HORIZONTAL:
            horizontalQuestionsElement.innerHTML = '';
            horizontalQuestionsElement.appendChild(fragment);
            break;
        case VERTICAL:
            verticalQuestionsElement.innerHTML = '';
            verticalQuestionsElement.appendChild(fragment);
            break;
    }
}

function getCell(x, y) {
    return grid.querySelector('.cell[x="'+x+'"][y="'+y+'"]');
}

function selectCell(cell) {
    clearSelected();
    selectedCell[X] = parseInt(cell.getAttribute('x'));
    selectedCell[Y] = parseInt(cell.getAttribute('y'));
    cell.classList.add('selected');
}

function clearSelected() {
    var s = grid.querySelector('.cell.selected');
    if (s) {
        s.classList.remove('selected');
    }
}

function highlightWholeLine() {
    switch (currentDirection) {
        case HORIZONTAL:
            for (var ix = 0; ix < crossword.size[WIDTH]; ++ix) {
                getCell(ix, selectedCell[Y]).classList.add('highlighted');
            }
            break;
        case VERTICAL:
            for (var iy = 0; iy < crossword.size[HEIGHT]; ++iy) {
                getCell(selectedCell[X], iy).classList.add('highlighted');
            }
            break;
    }
}

function highlightWord() {
    var wordNumber = findWordToHighlight();
    if (wordNumber) {
        switch (currentDirection) {
            case HORIZONTAL:
                var startX = crossword.positions[wordNumber-1][X];
                var endX = startX + crossword.questions[HORIZONTAL][wordNumber][ANSWER].length - 1;
                for (var wx = startX; wx <= endX; ++wx) {
                    getCell(wx, selectedCell[Y]).classList.add('word-selected');
                }
                break;
            case VERTICAL:
                var startY = crossword.positions[wordNumber-1][Y];
                var endY = startY + crossword.questions[VERTICAL][wordNumber][ANSWER].length - 1;
                for (var wy = startY; wy <= endY; ++wy) {
                    getCell(selectedCell[X], wy).classList.add('word-selected');
                }
                break;
        }
    }
}

function clearHighlight() {
    var highlightedCells = document.querySelectorAll('.highlighted, .word-selected');
    for (var i = 0; i < highlightedCells.length; ++i) {
        highlightedCells[i].classList.remove('highlighted');
        highlightedCells[i].classList.remove('word-selected');
    }
}

function findWordByPoint(x, y) {
    for (var k in crossword.questions[HORIZONTAL]) {
        if (crossword.positions[k-1][X] <= x
            && (crossword.positions[k-1][X] + crossword.questions[HORIZONTAL][k][ANSWER].length) > x
            && crossword.positions[k-1][Y] === y) {
            return {questionIndex: k, direction: HORIZONTAL};
        }
    }
    for (var k in crossword.questions[VERTICAL]) {
        if (crossword.positions[k-1][Y] <= y
            && (crossword.positions[k-1][Y] + crossword.questions[VERTICAL][k][ANSWER].length) > y
            && crossword.positions[k-1][X] === x) {
            return {questionIndex: k, direction: VERTICAL};
        }
    }

    return null;
}

function findWordToHighlight() {
    switch (currentDirection) {
        case HORIZONTAL:
            for (var k in crossword.questions[HORIZONTAL]) {
                if (crossword.positions[k-1][X] <= selectedCell[X]
                    && (crossword.positions[k-1][X] + crossword.questions[HORIZONTAL][k][ANSWER].length) > selectedCell[X]
                    && crossword.positions[k-1][Y] === selectedCell[Y]) {
                    return k;
                }
            }
            break;
        case VERTICAL:
            for (var k in crossword.questions[VERTICAL]) {
                if (crossword.positions[k-1][Y] <= selectedCell[Y]
                    && (crossword.positions[k-1][Y] + crossword.questions[VERTICAL][k][ANSWER].length) > selectedCell[Y]
                    && crossword.positions[k-1][X] === selectedCell[X]) {
                    return k;
                }
            }
            break;
    }

    return null;
}

function findCoordinateValue(type, coord) {
    switch (type) {
        case MIN:
            var smallest = [];
            for (var c in crossword.positions) {
                smallest.push(crossword.positions[c][coord]);
            }
            return Math.min.apply(Math, smallest);
        case MAX:
            var biggest = [];
            for (var c in crossword.questions[coord]) {
                biggest.push(
                    crossword.positions[c-1][coord] + crossword.questions[coord][c][ANSWER].length - 1
                );
            }
            return Math.max.apply(Math, biggest);
    }
}

function checkIfGridFits(newWidth, newHeight) {
    return findCoordinateValue(MAX, X) < newWidth && findCoordinateValue(MAX, Y) < newHeight;
}

function onResizeButtonClick(e) {
    if (modalVisible) {
        return;
    }

    var errored = false;
    error.innerText = '';
    if (!isNumeric(widthForm.value) || !isNumeric(heightForm.value)) {
        error.innerText = 'Size values should be numeric!';
        errored = true;
    }
    widthForm.value = clamp(parseInt(widthForm.value), 1, 100);
    heightForm.value = clamp(parseInt(heightForm.value), 1, 100);

    if (!checkIfGridFits(widthForm.value, heightForm.value)) {
        error.innerText = 'Current puzzle doesn\'t fit!';
        errored = true;
    }

    if (!errored) {
        crossword.size[WIDTH] = parseInt(widthForm.value);
        crossword.size[HEIGHT] = parseInt(heightForm.value);
        grid.innerHTML = '';
        renderGrid();
        renderAnswers();
    }
}

resizeButton.addEventListener('click', onResizeButtonClick);

addButton.addEventListener('click', function (e) {
    error.innerText = '';
    if (!modalVisible) {
        if (!findWordToHighlight()) {
            modalVisible = true;
            editModal.style.display = 'block';
            preview.style.left = selectedCell[X] * CELL_WIDTH + 'px';
            preview.style.top = selectedCell[Y] * CELL_HEIGHT + 'px';
            editModalAnswer.focus();
        } else {
            error.innerText = lang('overlap');
        }
    }
});

editButton.addEventListener('click', function (e) {
    error.innerText = '';
    if (!modalVisible) {
        var selectedAnswer = findWordToHighlight();
        if (selectedAnswer) {
            modalVisible = true;
            editModal.style.display = 'block';
            editModal.setAttribute('data-edit', selectedAnswer);
            selectCell(getCell(crossword.positions[selectedAnswer-1][X], crossword.positions[selectedAnswer-1][Y]));
            preview.style.left = selectedCell[X] * CELL_WIDTH + 'px';
            preview.style.top = selectedCell[Y] * CELL_HEIGHT + 'px';
            editModalQuestion.value = crossword.questions[currentDirection][selectedAnswer][QUESTION];
            editModalAnswer.value = crossword.questions[currentDirection][selectedAnswer][ANSWER];
            editModalLetterCount.innerText = editModalAnswer.value.length;
            setPreview(editModalAnswer.value);
            editModalAnswer.focus();
        } else {
            error.innerText = lang('nothingToEdit');
        }
    }
});

deleteButton.addEventListener('click', function (e) {
    if (!modalVisible) {
        var questionToDelete = findWordToHighlight();
        if (crossword.questions[1-currentDirection][questionToDelete]) {
            delete crossword.questions[currentDirection][questionToDelete];
        } else {
            crossword.positions.splice(questionToDelete-1, 1);
            delete crossword.questions[currentDirection][questionToDelete];
            for (var q in crossword.questions[HORIZONTAL]) {
                if (q > questionToDelete) {
                    crossword.questions[HORIZONTAL][q-1] =  crossword.questions[HORIZONTAL][q];
                    delete crossword.questions[HORIZONTAL][q];
                }
            }
            for (var q in crossword.questions[VERTICAL]) {
                if (q > questionToDelete) {
                    crossword.questions[VERTICAL][q-1] =  crossword.questions[VERTICAL][q];
                    delete crossword.questions[VERTICAL][q];
                }
            }
        }

        clearAnswers();
        clearHighlight();
        highlightWholeLine();
        renderAnswers();
        renderQuestions(HORIZONTAL);
        renderQuestions(VERTICAL);
    }
});

moveButton.addEventListener('click', function (e) {
    error.innerText = '';
    if (!modalVisible) {
        modalVisible = true;
        moveModal.style.display = 'block';
        moveModalLeft.min = -findCoordinateValue(MIN, X);
        moveModalLeft.max = crossword.size[WIDTH] - findCoordinateValue(MAX, X) - 1;
        moveModalTop.min = -findCoordinateValue(MIN, Y);
        moveModalTop.max = crossword.size[HEIGHT] - findCoordinateValue(MAX, Y) - 1;
        moveModalLeft.value = 0;
        moveModalTop.value = 0;
    }
});

function setPreview(previewWord) {
    preview.innerHTML = '';
    overlaps = [];
    var fragment = document.createDocumentFragment();
    var x = selectedCell[X];
    var y = selectedCell[Y];
    for (var l in previewWord) {
        var previewCell = document.createElement('div');
        previewCell.classList.add('cell');
        previewCell.classList.add('preview');
        previewCell.setAttribute('x', x);
        previewCell.setAttribute('y', y);
        previewCell.setAttribute('style', 'left: '+(x * CELL_WIDTH)+'px; top: '+(y * CELL_HEIGHT)+'px;');
        previewCell.innerText = previewWord[l];

        var overlappingCell = getCell(x, y);
        if (overlappingCell.innerText !== '' && overlappingCell.textContent.slice(-1) !== previewWord[l]) {
            previewCell.classList.add('warning');
            overlaps.push({x: x, y: y, letter: previewWord[l]});
        }

        fragment.appendChild(previewCell);
        switch (currentDirection) {
            case HORIZONTAL:
                x++;
                break;
            case VERTICAL:
                y++;
                fragment.appendChild(document.createElement('br'));
                break;
        }
    }
    preview.appendChild(fragment);
}

editModalAnswer.addEventListener('input', function (e) {
    var currentLength = e.currentTarget.value.length;
    editModalLetterCount.innerText = currentLength;
    if (currentDirection === HORIZONTAL && (selectedCell[X] + currentLength <= crossword.size[WIDTH])
        || currentDirection === VERTICAL && (selectedCell[Y] + currentLength) <= crossword.size[HEIGHT]) {
        setPreview(e.currentTarget.value.toLowerCase());
    }

})

editModalCancelButton.addEventListener('click', function (e) {
    error.innerText = ''
    modalVisible = false;
    preview.innerHTML = '';
    editModal.removeAttribute('data-edit');
    editModal.style.display = 'none';
    editModalQuestion.value = '';
    editModalAnswer.value = '';
    editModalLetterCount.innerText = '0';
});

function updateOverlappedAnswers() {
    for (var o in overlaps) {
        var foundAnswer = findWordByPoint(overlaps[o].x, overlaps[o].y);
        var oldAnswer = crossword.questions[foundAnswer.direction][foundAnswer.questionIndex][ANSWER];
        crossword.questions[foundAnswer.direction][foundAnswer.questionIndex][ANSWER] = replaceLetter(
            oldAnswer,
            overlaps[o].letter,
            (foundAnswer.direction ? overlaps[o].y : overlaps[o].x) -
                crossword.positions[foundAnswer.questionIndex-1][foundAnswer.direction]
        );
    }
}

function indexNumberInCell(x, y) {
    var q = 0;
    for (q; q < crossword.positions.length; ++q) {
        if (crossword.positions[q][X] === x && crossword.positions[q][Y] === y) {
            return q+1;
        }
    }
    crossword.positions.push([x, y]);
    return q+1;
}

function findCoordinateInPositions(x, y, positionDirection) {
    for (var c in crossword.positions) {
        c = parseInt(c);
        if (crossword.positions[c][X] === x && crossword.positions[c][Y] === y) {
            if (crossword.questions[positionDirection][c+1]) {
                return true;
            }
        }
    }
    return false;
}

function checkForAnswerBeginnings(startX, startY, wordLength, wordDirection) {
    switch (wordDirection) {
        case HORIZONTAL:
            for (var wx = startX; wx < startX + wordLength; ++wx) {
                if (findCoordinateInPositions(wx, startY, wordDirection)) {
                    return true;
                }
            }
            break;
        case VERTICAL:
            for (var wy = startY; wy < startY + wordLength; ++wy) {
                if (findCoordinateInPositions(startX, wy, wordDirection)) {
                    return true;
                }
            }
            break;
    }
    return false;
}

editModalOKButton.addEventListener('click', function (e) {
    var errored = false;
    error.innerText = '';
    var currentLength = editModalAnswer.value.length;

    if (editModalQuestion.value === '' || editModalQuestion.value === '') {
        error.innerText = lang('noEmptyFields');
        errored = true;
    } else if (editModalQuestion.value.length > 2000) {
        error.innerText = lang('tooLongQuestion');
        errored = true;
    } else if (!answerRegex.test(editModalAnswer.value)) {
        error.innerText = lang('onlyAZ');
        errored = true;
    } else if (currentDirection === HORIZONTAL && (selectedCell[X] + currentLength) > crossword.size[WIDTH]
               || currentDirection === VERTICAL && (selectedCell[Y] + currentLength) > crossword.size[HEIGHT]) {
        error.innerText = lang('answerOutOfBounds');
        errored = true;
    } else if (!editModal.getAttribute('data-edit')
               && checkForAnswerBeginnings(selectedCell[X], selectedCell[Y], currentLength, currentDirection)) {
        error.innerText = lang('wordOverlap');
        errored = true;
    }

    if (!errored) {
        var questionNumber = editModal.getAttribute('data-edit');
        if (!questionNumber) {
            questionNumber = indexNumberInCell(selectedCell[X], selectedCell[Y]);
        }
        updateOverlappedAnswers();
        crossword.questions[currentDirection][questionNumber] =
            [editModalQuestion.value.trim(), editModalAnswer.value.toLowerCase()];

        if (editModal.getAttribute('data-edit')) {
            clearAnswers();
            clearHighlight();
            highlightWholeLine();
        }
        renderAnswers();
        renderQuestions(HORIZONTAL);
        renderQuestions(VERTICAL);
        highlightWord();

        modalVisible = false;
        preview.innerHTML = '';
        editModal.style.display = 'none';
        editModal.removeAttribute('data-edit');
        editModalQuestion.value = '';
        editModalAnswer.value = '';
        editModalLetterCount.innerText = '0';
    }
});

moveModalCancelButton.addEventListener('click', function (e) {
    modalVisible = false;
    moveModal.style.display = 'none';
    moveModalLeft.value = '';
    moveModalTop.value = '';
});

moveModalOKButton.addEventListener('click', function (e) {
    var errored = false;
    error.innerText = '';
    if (!isNumeric(moveModalLeft.value) || !isNumeric(moveModalTop.value)) {
        error.innerText = lang('numericMoveValues');
        errored = true;
    }

    if (!errored) {
        moveModalLeft.value = clamp(parseInt(moveModalLeft.value), parseInt(moveModalLeft.min), parseInt(moveModalLeft.max));
        moveModalTop.value = clamp(parseInt(moveModalTop.value), parseInt(moveModalTop.min), parseInt(moveModalTop.max));
        var moveX = parseInt(moveModalLeft.value);
        var moveY = parseInt(moveModalTop.value);
        for (var c in crossword.positions) {
            crossword.positions[c][X] += moveX;
            crossword.positions[c][Y] += moveY;
        }
        clearAnswers();
        clearHighlight();
        highlightWholeLine();
        renderAnswers();
        highlightWord();

        modalVisible = false;
        moveModal.style.display = 'none';
    }
});

saveButton.addEventListener('click', function (e) {
    var errored = false
    infoError.innerText = '';
    titleForm.value = titleForm.value.trim();
    tagsForm.value = tagsForm.value.toLowerCase().trim();
    if (titleForm.value === '') {
        infoError.innerText = lang('notBlankTitle');
        errored = true;
    } else if (titleForm.value.length > 255) {
        infoError.innerText = lang('tooLongTitle');
        errored = true;
    } else if (tagsForm.value.length == 0) {
        infoError.innerText = lang('notEmptyTags');
        errored = true;
    } else if (!tagsRegex.test(tagsForm.value)) {
        infoError.innerText = lang('lettersAndCommaInTags');
        errored = true;
    } else if (tagsForm.value.length > 65535) {
        infoError.innerText = lang('wayTooManyTags');
        errored = true;
    } else if (!crossword.positions.length) {
        infoError.innerText = lang('noQuestions');
        errored = true;
    }

    if (!errored) {
        var request = new XMLHttpRequest();
        var form = new FormData();
        if (crosswordId) {
            form.append('id', crosswordId);
        }
        form.append('title', titleForm.value);
        form.append('language', languageForm.value);
        form.append('tags', JSON.stringify(tagsForm.value.split(',')));
        form.append('is_public', publicCheckbox.checked ? 1 : 0);
        form.append('crossword_data', JSON.stringify(crossword));
        request.open('POST', '/crossword/save', true);

        request.onload = function() {
            if (this.status >= 200 && this.status < 400) {
                var data = JSON.parse(this.response);
                if (data.error) {
                    infoError.innerText = data.error;
                } else {
                    infoError.innerText = lang('saved');
                    if (!crosswordId) {
                        window.location.href =
                            window.location.protocol + '//' + window.location.host + '/crossword/edit/' + data.crossword_id;
                    }
                }

            } else {
                console.log('error');
            }
        };

        request.send(form);
    }
});

if (deleteCrosswordButton) {
    deleteCrosswordButton.addEventListener('click', function (e) {
        document.querySelector('.sure-delete').style.display = 'block';
        e.currentTarget.style.display = 'none';
    });

    yesDeleteButton.addEventListener('click', function (e) {
        var request = new XMLHttpRequest();
        request.open('POST', '/crossword/delete/' + crosswordId, true);

        request.onload = function() {
            if (this.status >= 200 && this.status < 400) {
                var data = JSON.parse(this.response);
                if (data.error) {
                    infoError.innerText = data.error;
                } else {
                    window.location.href = window.location.protocol + '//' + window.location.host + '/account';
                }
            } else {
                console.log('error');
            }
        };

        request.send();
    });

    noDeleteButton.addEventListener('click', function (e) {
        document.querySelector('.sure-delete').style.display = 'none';
        deleteCrosswordButton.style.display = 'block';
    });
}

languageForm.addEventListener('change', function (e) {
    updateAnswerRegex();
})

document.addEventListener('DOMContentLoaded', function (e) {
    var crosswordDataHolder = document.querySelector('#crossword-data');
    if (crosswordDataHolder) {
        var parsedData = JSON.parse(crosswordDataHolder.innerHTML);
        crosswordId = parseInt(parsedData.id);
        titleForm.value = parsedData.title;
        languageForm.value = parsedData.language;
        tagsForm.value = parsedData.tags;
        if (parseInt(parsedData.is_public)) {
            publicCheckbox.checked = true;
        }
        crossword = JSON.parse(parsedData.data);
        crosswordDataHolder.parentElement.removeChild(crosswordDataHolder);
    }

    updateAnswerRegex();

    widthForm.value = crossword.size[WIDTH];
    heightForm.value = crossword.size[HEIGHT];
    renderGrid();
    renderAnswers();
    renderQuestions(HORIZONTAL);
    renderQuestions(VERTICAL);
});
