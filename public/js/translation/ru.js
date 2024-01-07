var STRINGS = {
    'cancel': 'Отмена',
    'ok': 'OK',
    'edit': 'Редактировать',
    'edited': 'Редактировано',
    'editedOn': 'Редактировано ',
    'delete': 'Удалить',
    'saved': 'Сохранено!',
    'noQuestionSelected': 'Вопрос не выбран',
    'overlap': 'Перекрытие! Вы не можете добавить сюда слово в текущей ориентации!',
    'nothingToEdit': 'Нечего редактировать...',
    'noEmptyFields': 'Никаких пустых полей!',
    'onlyAZ': 'Использовать в ответах только маленькие буквы языка',
    'answerOutOfBounds': 'Ответ выходит за рамки!',
    'wordOverlap': 'Перекрытие слов в одной ориентации!',
    'numericMoveValues': 'Значения перемещения должны быть числовыми!',
    'notBlankTitle': 'Заголовок не может быть пустым!',
    'tooLongTitle': 'Заголовок слишком длинный!',
    'tooLongQuestion': 'Слишком длинный вопрос!',
    'notEmptyTags': 'Теги не могут быть пустыми',
    'lettersAndCommaInTags': 'Теги должны содержать только буквенно-цифровые символы, разделённые запятой',
    'wayTooManyTags': 'Слишком много тегов!',
    'noQuestions': 'Кроссворд пуст!',
    'imageWrongType': 'Неправильный тип файла',
    'imageTooLarge': 'Слишком большое изображение',
    'notEmptyMessage': 'Сообщение не может быть пустым',
    'tooLongMessage': 'Сообщение слишком длинное!',
    'show': 'показать',
    'hide': 'спрятать',
    'report': 'Жаловаться'
}

function lang(string) {
    return STRINGS[string] ? STRINGS[string] : string;
}
