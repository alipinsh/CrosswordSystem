var STRINGS = {
    'cancel': 'Cancel',
    'ok': 'OK',
    'edit': 'Edit',
    'edited': 'Edited',
    'editedOn': 'edited on',
    'delete': 'Delete',
    'saved': 'Saved!',
    'noQuestionSelected': 'No question selected',
    'overlap': 'Overlap! You can\'t add a word here in the current orientation!',
    'nothingToEdit': 'Nothing to edit...',
    'noEmptyFields': 'No empty fields!',
    'onlyAZ': 'Use only a-z letters in answers',
    'answerOutOfBounds': 'Answer out of bounds!',
    'wordOverlap': 'Word overlap in the same orientation!',
    'numericMoveValues': 'Move values should be numeric!',
    'notBlankTitle': 'Title can not be blank!',
    'tooLongTitle': 'Title too long!',
    'tooLongQuestion': 'Question too long!',
    'notEmptyTags': 'Tags cannot be empty',
    'lettersAndCommaInTags': 'Tags should contain only alphanumeric characters, divided by comma',
    'wayTooManyTags': 'Way too many tags!',
    'noQuestions': 'Crossword is empty!',
    'imageWrongType': 'Wrong file type',
    'imageTooLarge': 'Image too large',
    'notEmptyMessage': 'Message can\'t be empty',
    'tooLongMessage': 'Message too long!',
    'show': 'Show',
    'hide': 'Hide'
}

function lang(string) {
    return STRINGS[string] ? STRINGS[string] : string;
}