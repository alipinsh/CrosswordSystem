var STRINGS = {
    'cancel': 'Atcelt',
    'ok': 'OK',
    'edit': 'Rediģēt',
    'edited': 'rediģēts',
    'editedOn': 'edited on',
    "delete": "Delete",
    "saved": "Saglabāts!",
    "noQuestionSelected": "No question selected",
    "overlap": "Overlap! Jūs nevarat šeit pievienot vārdu pašreizējā orientācijā!",
    'nothingToEdit': 'Nav ko rediģēt...',
    "noEmptyFields": "Nav tukšu lauku!",
    'onlyAZ': 'Atbildēs izmantot tikai valodas mazus burtus',
    'answerOutOutOfBounds': "Atbilde ārpus robežām!",
    "wordOverlap": "Vārdi pārklājas vienā un tajā pašā orientācijā!",
    "numericMoveValues": "Pārvietojuma vērtībām jābūt skaitliskajām!",
    "notBlankTitle": "Nosaukums nedrīkst būt tukšs!",
    "tooLongTitle": "Nosaukums ir pārāk garš!",
    "tooLongQuestion": "Jautājums ir pārāk garš!",
    "notEmptyTags": "Tagi nedrīkst būt tukšas",
    "lettersAndCommaInTags": "Tags drīkst saturēt tikai burtu un ciparu zīmes, atdalītas ar komatu",
    'wayTooManyTags': "Pārāk daudz tagu!",
    'noQuestions': 'Krustvārdu mīkla ir tukša!',
    "imageWrongType": "Nepareizs faila tips",
    "imageTooLarge": "Pārāk liels attēls",
    "notEmptyMessage": "Ziņojums nevar būt tukšs",
    "tooLongMessage": "Ziņojums ir pārāk garš!",
    "show": "Rādīt",
    "hide": "Paslēpt",
    'report': 'Ziņot'
}

function lang(string) {
    return STRINGS[string] ? STRINGS[string] : string;
}
