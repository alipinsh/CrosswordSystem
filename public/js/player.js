var gridContainer = document.querySelector('.grid-container');
var grid = document.querySelector('.grid');
var letterInput = document.querySelector('input.dummy');

var revealLetterButton = document.querySelector('button#reveal-letter');
var revealWordButton = document.querySelector('button#reveal-word');
var revealGridButton = document.querySelector('button#reveal-grid');
var checkLetterButton = document.querySelector('button#check-letter');
var checkWordButton = document.querySelector('button#check-word');
var checkGridButton = document.querySelector('button#check-grid');

var questionPreview = document.querySelector('.question-preview');
var horizontalQuestionsElement = document.querySelector('.horizontal-questions');
var verticalQuestionsElement = document.querySelector('.vertical-questions');

var language = document.querySelector('.flag-icon img').alt;

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
var LETTER = 0;
var WORD = 1;
var GRID = 2;

var currentDirection = HORIZONTAL;
var selectedCell = [0, 0];

var crossword = {
    size: [10, 10],
    positions: [],
    questions: [{}, {}]
}

var cellOnClick = function (e) {
    letterInput.focus();
    var x = Number(e.currentTarget.getAttribute('x'));
    var y = Number(e.currentTarget.getAttribute('y'));

    clearWordHighlight();
    clearWholeLineHighlight();
    if (e.currentTarget.classList.contains("selected")) {
        currentDirection = 1 - currentDirection;
    } else {
        selectCell(e.currentTarget);
    }
    highlightWholeLine();
    highlightWord();
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

function renderBlanks() {
    for (var q in crossword.positions) {
        q = parseInt(q);
        var qx = crossword.positions[q][X];
        var qy = crossword.positions[q][Y];
        var qs = (q+1).toString();

        if (crossword.questions[HORIZONTAL][qs]) {
            var answer = crossword.questions[HORIZONTAL][qs][ANSWER];
            for (var ai = 0; ai < answer.length; ++ai) {
                var c = getCell(qx+ai, qy);
                c.classList.remove('blocked');
                c.addEventListener('click', cellOnClick);
            }
        }
        if (crossword.questions[VERTICAL][qs]) {
            var answer = crossword.questions[VERTICAL][qs][ANSWER];
            for (var ai = 0; ai < answer.length; ++ai) {
                var c = getCell(qx, qy+ai);
                c.classList.remove('blocked');
                c.addEventListener('click', cellOnClick);
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

    letterInput.focus();
    clearWordHighlight();
    clearWholeLineHighlight();
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

        question.appendChild(questionText);

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
        questionPreview.innerText = wordNumber + '. ' + crossword.questions[currentDirection][wordNumber][QUESTION];
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
    } else {
        questionPreview.innerText = lang('noQuestionSelected');
    }
}

function clearWholeLineHighlight() {
    var highlightedCells = document.querySelectorAll('.highlighted');
    for (var i = 0; i < highlightedCells.length; ++i) {
        highlightedCells[i].classList.remove('highlighted');
    }
}

function clearWordHighlight() {
    var highlightedCells = document.querySelectorAll('.word-selected');
    for (var i = 0; i < highlightedCells.length; ++i) {
        highlightedCells[i].classList.remove('word-selected');
    }
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

function inputLetter(cell, letter) {
    var gridIndex = cell.querySelector('span.grid-index');
    cell.classList.remove('wrong');
    cell.innerText = letter;
    if (gridIndex) {
        cell.insertBefore(gridIndex, cell.firstChild);
    }
}

function removeLetter(cell) {
    var gridIndex = cell.querySelector('span.grid-index');
    cell.classList.remove('wrong');
    cell.innerText = '';
    if (gridIndex) {
        cell.appendChild(gridIndex);
    }
}

function checkLetter(cell, letter) {
    if (cell.textContent.slice(-1).toLowerCase() !== letter) {
        cell.classList.add('wrong');
    }
}

letterInput.addEventListener('keydown', function(e) {
    var x = selectedCell[X];
    var y = selectedCell[Y];
    var cellToSelect = null;
    var letter = e.key.toLowerCase();

    if (e.keyCode === 37) {
        if (currentDirection !== HORIZONTAL) {
            currentDirection = HORIZONTAL;
            clearWholeLineHighlight();
            clearWordHighlight();
            highlightWholeLine();
            highlightWord();
        } else {
            do {
                x--;
                if (x === -1) {
                    x = crossword.size[WIDTH] - 1;
                }
                cellToSelect = getCell(x, y);
            } while (cellToSelect.classList.contains('blocked'));
            selectCell(cellToSelect);
            clearWordHighlight();
            highlightWord();
        }
    } else if (e.keyCode === 38) {
        if (currentDirection !== VERTICAL) {
            currentDirection = VERTICAL;
            clearWholeLineHighlight();
            clearWordHighlight();
            highlightWholeLine();
            highlightWord();
        } else {
            do {
                y--;
                if (y === -1) {
                    y = crossword.size[HEIGHT] - 1;
                }
                cellToSelect = getCell(x, y);
            } while (cellToSelect.classList.contains('blocked'));
            selectCell(cellToSelect);
            clearWordHighlight();
            highlightWord();
        }
    } else if (e.keyCode === 39) {
        if (currentDirection !== HORIZONTAL) {
            currentDirection = HORIZONTAL;
            clearWholeLineHighlight();
            clearWordHighlight();
            highlightWholeLine();
            highlightWord();
        } else {
            do {
                x++;
                if (x === crossword.size[WIDTH]) {
                    x = 0;
                }
                cellToSelect = getCell(x, y);
            } while (cellToSelect.classList.contains('blocked'));
            selectCell(cellToSelect);
            clearWordHighlight();
            highlightWord();
        }
    } else if (e.keyCode === 40) {
        if (currentDirection !== VERTICAL) {
            currentDirection = VERTICAL;
            clearWholeLineHighlight();
            clearWordHighlight();
            highlightWholeLine();
            highlightWord();
        } else {
            do {
                y++;
                if (y === crossword.size[HEIGHT]) {
                    y = 0;
                }
                cellToSelect = getCell(x, y);
            } while (cellToSelect.classList.contains('blocked'));
            selectCell(cellToSelect);
            clearWordHighlight();
            highlightWord();
        }
    } else if (e.keyCode === 8) {
        removeLetter(getCell(x, y));
        switch (currentDirection) {
            case HORIZONTAL:
                do {
                    x--;
                    if (x === -1) {
                        x = crossword.size[WIDTH] - 1;
                    }
                    cellToSelect = getCell(x, y);
                } while (cellToSelect.classList.contains('blocked'));
                selectCell(cellToSelect);
                clearWordHighlight();
                highlightWord();
                break;
            case VERTICAL:
                do {
                    y--;
                    if (y === -1) {
                        y = crossword.size[HEIGHT] - 1;
                    }
                    cellToSelect = getCell(x, y);
                } while (cellToSelect.classList.contains('blocked'));
                selectCell(cellToSelect);
                clearWordHighlight();
                highlightWord();
                break;
        }
    } else if (ALLOWED_LETTERS.indexOf(letter) > -1) {
        inputLetter(getCell(x, y), letter);
        switch (currentDirection) {
            case HORIZONTAL:
                do {
                    x++;
                    if (x === crossword.size[WIDTH]) {
                        x = 0
                    }
                    cellToSelect = getCell(x, y);
                } while (cellToSelect.classList.contains('blocked'));
                selectCell(cellToSelect);
                clearWordHighlight();
                highlightWord();
                break;
            case VERTICAL:
                do {
                    y++;
                    if (y === crossword.size[HEIGHT]) {
                        y = 0
                    }
                    cellToSelect = getCell(x, y);
                } while (cellToSelect.classList.contains('blocked'));
                selectCell(cellToSelect);
                clearWordHighlight();
                highlightWord();
                break;
        }
    } 
});

function reveal(what) {
    switch (what) {
        case LETTER:
            var foundAnswer = findWordByPoint(selectedCell[X], selectedCell[Y]);
            var answer = crossword.questions[foundAnswer.direction][foundAnswer.questionIndex][ANSWER];
            var answerPosition = crossword.positions[foundAnswer.questionIndex-1];
            inputLetter(
                getCell(selectedCell[X], selectedCell[Y]),
                answer[selectedCell[foundAnswer.direction] - answerPosition[foundAnswer.direction]]
            );
            break;
        case WORD:
            var answerId = findWordToHighlight();
            if (answerId) {
                var answer = crossword.questions[currentDirection][answerId][ANSWER];
                var answerPosition = crossword.positions[answerId-1];
                switch (currentDirection) {
                    case HORIZONTAL:
                        for (var l in answer) {
                            l = parseInt(l);
                            inputLetter(getCell(answerPosition[X]+l, answerPosition[Y]), answer[l]);
                        }
                        break;
                    case VERTICAL:
                        for (var l in answer) {
                            l = parseInt(l);
                            inputLetter(getCell(answerPosition[X], answerPosition[Y]+l), answer[l]);
                        }
                        break;
                }
            }
            break;
        case GRID:
            for (var q in crossword.positions) {
                q = parseInt(q);
                var qx = crossword.positions[q][X];
                var qy = crossword.positions[q][Y];
                var qs = (q+1).toString();

                if (crossword.questions[HORIZONTAL][qs]) {
                    var answer = crossword.questions[HORIZONTAL][qs][ANSWER];
                    for (var ai = 0; ai < answer.length; ++ai) {
                        inputLetter(getCell(qx+ai, qy), answer[ai]);
                    }
                }
                if (crossword.questions[VERTICAL][qs]) {
                    var answer = crossword.questions[VERTICAL][qs][ANSWER];
                    for (var ai = 0; ai < answer.length; ++ai) {
                        inputLetter(getCell(qx, qy+ai), answer[ai]);
                    }
                }
            }
            break;
    }
}

function check(what) {
    switch (what) {
        case LETTER:
            var foundAnswer = findWordByPoint(selectedCell[X], selectedCell[Y]);
            var answer = crossword.questions[foundAnswer.direction][foundAnswer.questionIndex][ANSWER];
            var answerPosition = crossword.positions[foundAnswer.questionIndex-1];
            checkLetter(
                getCell(selectedCell[X], selectedCell[Y]),
                answer[selectedCell[foundAnswer.direction] - answerPosition[foundAnswer.direction]]
            );
            break;
        case WORD:
            var answerId = findWordToHighlight();
            if (answerId) {
                var answer = crossword.questions[currentDirection][answerId][ANSWER];
                var answerPosition = crossword.positions[answerId-1];
                switch (currentDirection) {
                    case HORIZONTAL:
                        for (var l in answer) {
                            l = parseInt(l);
                            checkLetter(getCell(answerPosition[X]+l, answerPosition[Y]), answer[l]);
                        }
                        break;
                    case VERTICAL:
                        for (var l in answer) {
                            l = parseInt(l);
                            checkLetter(getCell(answerPosition[X], answerPosition[Y]+l), answer[l]);
                        }
                        break;
                }
            }
            break;
        case GRID:
            for (var q in crossword.positions) {
                q = parseInt(q);
                var qx = crossword.positions[q][X];
                var qy = crossword.positions[q][Y];
                var qs = (q+1).toString();

                if (crossword.questions[HORIZONTAL][qs]) {
                    var answer = crossword.questions[HORIZONTAL][qs][ANSWER];
                    for (var ai = 0; ai < answer.length; ++ai) {
                        checkLetter(getCell(qx+ai, qy), answer[ai]);
                    }
                }
                if (crossword.questions[VERTICAL][qs]) {
                    var answer = crossword.questions[VERTICAL][qs][ANSWER];
                    for (var ai = 0; ai < answer.length; ++ai) {
                        checkLetter(getCell(qx, qy+ai), answer[ai]);
                    }
                }
            }
            break;
    }
}

function selectFirstQuestion() {
    var cell = getCell(crossword.positions[0][X], crossword.positions[0][Y]);
    if (crossword.questions[HORIZONTAL]['1']) {
        currentDirection = HORIZONTAL;
    } else {
        currentDirection = VERTICAL;
    }
    cell.click();
}

revealLetterButton.addEventListener('click', function(e) {
    reveal(LETTER);
});

revealWordButton.addEventListener('click', function (e) {
    reveal(WORD);
});

revealGridButton.addEventListener('click', function (e) {
    reveal(GRID);
});

checkLetterButton.addEventListener('click', function(e) {
    check(LETTER);
});

checkWordButton.addEventListener('click', function (e) {
    check(WORD);
});

checkGridButton.addEventListener('click', function (e) {
    check(GRID);
});

grid.addEventListener('click', function (e) {
    letterInput.focus();
});

gridContainer.addEventListener('click', function (e) {
    grid.click();
})

document.addEventListener("DOMContentLoaded", function (e) {
    var crosswordDataHolder = document.querySelector('#crossword-data')
    crossword = JSON.parse(crosswordDataHolder.innerHTML);
    crosswordDataHolder.parentElement.removeChild(crosswordDataHolder);

    renderGrid();
    renderBlanks();
    renderQuestions(HORIZONTAL);
    renderQuestions(VERTICAL);
    selectFirstQuestion();
    highlightWholeLine();
    highlightWord();
});