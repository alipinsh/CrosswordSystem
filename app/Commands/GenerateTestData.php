<?php

namespace App\Commands;

use App\Models\CommentModel;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Faker\Factory as FakerFactory;
use App\Models\UserModel;
use App\Models\CrosswordModel;
use App\Models\TagModel;

class GenerateTestData extends BaseCommand
{
    protected $group = 'Database';
    protected $name = 'generate:test';
    protected $usage = 'generate:test <users> <crosswords>';
    protected $arguments = [
        'users' => 'Amount of users',
        'crosswords' => 'Amount of crosswords'
    ];
    protected $description = 'Generates test data';

    private $dicts = [
        'en' => [],
        'lv' => [],
        'ru' => []
    ];

    private $arr = [];
    private $sortedArr = [];

    public function run(array $params)
    {
        $this->dicts['en'] = file_get_contents(ROOTPATH . '/data/en_wordlist.json');
        $this->dicts['en'] = json_decode($this->dicts['en'], true);

        $this->dicts['lv'] = file_get_contents(ROOTPATH . '/data/lv_wordlist.json');
        $this->dicts['lv'] = json_decode($this->dicts['lv'], true);

        $this->dicts['ru'] = file_get_contents(ROOTPATH . '/data/ru_wordlist.json');
        $this->dicts['ru'] = json_decode($this->dicts['ru'], true);

        $faker = FakerFactory::create();

        $userModel = new UserModel();
        $crosswordModel = new CrosswordModel();
        $tagModel = new TagModel();
        $commentModel = new CommentModel();

        // Generate users
        CLI::write('Generating users');
        $userIds = [];
        for ($i = 0; $i < 100; $i++) {
            $userData = [
                'username' => $faker->unique()->userName(),
                'email' => $faker->unique()->email(),
                'password' => '12345678',
                'password_confirm' => '12345678',
                'image' => 'default.png',
                'role' => UserModel::USER_ROLE,
                'registered_on' => date('Y-m-d H:i:s', time()),
                'auth_code' => null,
                'email_confirmed' => true
            ];
            $userIds[] = $userModel->insert($userData, true);
        }

        shuffle($userIds);
        $crosswordUserIds = array_slice($userIds, 0, 30);

        $tags = ['special', 'funky', 'funny', 'interesting', 'complicated', 'smart', 'crazy', 'groovy', 'awesome', 'world'];

        CLI::write('Generating crosswords');
        $crosswordIds = [];
        foreach (['en', 'ru', 'lv'] as $lang) {
            CLI::write('Generating for ' . $lang);
            for ($i = 0; $i < 100; $i++) {
                shuffle($tags);
                $tagsText = implode(',', array_slice($tags, 0, random_int(0, 5)));
                $cData = $this->generateCrosswordData($lang);
                $crosswordData = [
                    'title' => '[G] ' . $faker->text(50),
                    'width' => $cData['size'][CrosswordModel::WIDTH],
                    'height' => $cData['size'][CrosswordModel::HEIGHT],
                    'questions' => sizeof($cData['questions'][CrosswordModel::HORIZONTAL]) +
                        sizeof($cData['questions'][CrosswordModel::VERTICAL]),
                    'data' => json_encode($cData),
                    'user_id' => $crosswordUserIds[array_rand($crosswordUserIds)],
                    'is_public' => 1,
                    'tags' => $tagsText,
                    'language' => $lang,
                    'published_at' => date('Y-m-d H:i:s', time())
                ];

                $crosswordId = $crosswordModel->insert($crosswordData, true);
                $crosswordIds[] = $crosswordId;

                $tagModel->updateTags($crosswordId);
                $userModel->updateCreatedCount($crosswordData['user_id']);
            }
        }

        // Attach favorites
        CLI::write('Generating favorites');
        foreach ($userIds as $userId) {
            shuffle($crosswordIds);
            $favCrosswordIds = array_slice($crosswordIds, 0, random_int(10, 100));

            foreach ($favCrosswordIds as $favCrosswordId) {
                $crosswordModel->getFavorited($favCrosswordId, $userId);
                $userModel->updateFavoritedCount($userId);
            }
        }

        // Attach comments
        CLI::write('Generating comments');
        foreach ($userIds as $userId) {
            shuffle($crosswordIds);
            $favCrosswordIds = array_slice($crosswordIds, 0, random_int(5, 50));

            foreach ($favCrosswordIds as $favCrosswordId) {
                $comment = [
                    'user_id' => $userId,
                    'crossword_id' => $favCrosswordId,
                    'text' => $faker->text()
                ];
                $commentModel->insert($comment);
            }
        }

        return 0;
    }

    // ---------------------- Crossword generation --------------------------
    // This code is basically a port of https://github.com/gaoryrt/crossword-generator

    private function generateCrosswordData(string $lang) {
        do {
            $words = array_rand($this->dicts[$lang], random_int(5, 10));
            $this->arr = $words;
            $this->sortedArr = $words;
            usort($this->sortedArr, function ($a, $b) {
                return mb_strlen($a) - mb_strlen($b);
            });

            $raw = $this->draw([[
                'wordStr' => array_pop($this->sortedArr),
                'xNum' => 0,
                'yNum' => 0,
                'isHorizon' => true
            ]], array_pop($this->sortedArr));
        } while ($raw == false);

        $formatted = $this->format($raw, $lang);

        return $formatted;
    }

    private function format($raw, $lang) {
        $formatted = [
            'size' => [$raw['width'], $raw['height']],
            'positions' => [],
            'questions' => [[], []]
        ];

        foreach ($raw['positionObjArr'] as $positionObj) {
            $search = [$positionObj['xNum'], $positionObj['yNum']];
            $foundIdx = array_search($search, $formatted['positions']);

            if ($foundIdx == false) {
                $foundIdx = count($formatted['positions']);
                $formatted['positions'][] = $search;
            }

            $formatted['questions'][$positionObj['isHorizon'] ? 0 : 1][$foundIdx + 1] = 
                [$this->dicts[$lang][$positionObj['wordStr']], mb_strtolower($positionObj['wordStr'])];
        }

        return $formatted;
    }

    private function letterMapOfPositionObjArr($positionObjArr) {
        $rtn = [];

        foreach ($positionObjArr as $positionObj) {
            $len = mb_strlen($positionObj['wordStr']);
            for ($i = 0; $i < $len; $i++) {
                $letter = mb_substr($positionObj['wordStr'], $i, 1);

                if (!isset($rtn[$letter])) {
                    $rtn[$letter] = [];
                }

                $rtn[$letter][] = [
                    'x' => $positionObj['xNum'] + ($positionObj['isHorizon'] ? $i : 0),
                    'y' => $positionObj['yNum'] + ($positionObj['isHorizon'] ? 0 : $i)
                ];
            }
        }

        return $rtn;
    }

    private function findPosition($letterMapWordStr) {
        $letterMap = $letterMapWordStr['letterMap'];
        $wordStr = $letterMapWordStr['wordStr'];

        $matrixObj = $this->letterMapToMatrix($letterMap);

        if (!$wordStr) {
            return [];
        }

        $available = [];
        $len = mb_strlen($wordStr);
        for ($i = 0; $i < $len; $i++) {
            $letter = mb_substr($wordStr, $i, 1);

            if (!isset($letterMap[$letter])) {
                continue;
            }

            foreach ($letterMap[$letter] as $xyObj) {
                $xNum = $xyObj['x'];
                $yNum = $xyObj['y'];
                $isHorizon = !isset($matrixObj[$yNum][$xNum + 1]);

                if ($isHorizon) {
                    if (isset($matrixObj[$yNum][$xNum - $i - 1])) {
                        continue;
                    }
                    if (isset($matrixObj[$yNum][$xNum - $i + $len])) {
                        continue;
                    }
                    for ($j = 0; $j < $len; $j++) {
                        if ($i == $j) {
                            continue;
                        }
                        if (isset($matrixObj[$yNum - 1]) && isset($matrixObj[$yNum - 1][$xNum - $i + $j])) {
                            continue 2;
                        }
                        if (isset($matrixObj[$yNum][$xNum - $i + $j])) {
                            continue 2;
                        }
                        if (isset($matrixObj[$yNum + 1]) && isset($matrixObj[$yNum + 1][$xNum - $i + $j])) {
                            continue 2;
                        }
                    }
                } else {
                    if (isset($matrixObj[$yNum - $i - 1]) && isset($matrixObj[$yNum - $i - 1][$xNum])) {
                        continue;
                    }
                    if (isset($matrixObj[$yNum - $i + $len]) && isset($matrixObj[$yNum - $i + $len][$xNum])) {
                        continue;
                    }
                    for ($j = 0; $j < $len; $j++) {
                        if ($i == $j || !isset($matrixObj[$yNum - $i + $j])) {
                            continue;
                        }
                        if (isset($matrixObj[$yNum - $i + $j][$xNum - 1])) {
                            continue 2;
                        }
                        if (isset($matrixObj[$yNum - $i + $j][$xNum])) {
                            continue 2;
                        }
                        if (isset($matrixObj[$yNum - $i + $j][$xNum + 1])) {
                            continue 2;
                        }
                    }
                }

                $available[] = [
                    'wordStr' => $wordStr,
                    'xNum' => $xyObj['x'] - ($isHorizon ? $i : 0),
                    'yNum' => $xyObj['y'] - ($isHorizon ? 0 : $i),
                    'isHorizon' => $isHorizon
                ];
            }
        }

        return $available;
    }

    private function letterMapToMatrix($letterMap) {
        $matrix = [];

        foreach ($letterMap as $letter => $a) {
            foreach ($letterMap[$letter] as $letterObj) {
                $y = $letterObj['y'];
                $x = $letterObj['x'];
                if (!isset($matrix[$y])) {
                    $matrix[$y] = [];
                }
                $matrix[$y][$x] = $letter;
            }
        }

        return $matrix;
    }

    private function draw($positionObjArr, $wordStr) {
        $letterMap = $this->letterMapOfPositionObjArr($positionObjArr);

        if (!$wordStr) {
            return $this->output($positionObjArr);
        }

        $nextObjArr = $this->findPosition([
            'wordStr' => $wordStr,
            'letterMap' => $letterMap
        ]);

        if (count($nextObjArr)) {
            $arr = $nextObjArr;
            // shuffle($arr);
            $theWordStr = array_pop($this->sortedArr);
            for ($i = 0; $i < count($nextObjArr); $i++) {
                $nextObj = $arr[$i];
                $ans = $this->draw(array_merge($positionObjArr, [$nextObj]), $theWordStr);
                if ($ans) {
                    $positionObjArr[] = $nextObj;
                    $this->sortedArr[] = $theWordStr;
                    return $ans;
                }
            }
            $this->sortedArr[] = $theWordStr;
            return false;
        } else {
            return false;
        }
    }

    private function output($positionObjArr) {
        $translateX = 0;
        $translateY = 0;
        $maxX = 0;
        $maxY = 0;

        foreach ($positionObjArr as $positionObj) {
            $wordLen = mb_strlen($positionObj['wordStr']);
            $isHorizon = $positionObj['isHorizon'];
            $currentX = $positionObj['xNum'];
            $currentY = $positionObj['yNum'];
            $tailX = $currentX + $wordLen * ($isHorizon ? 1 : 0);
            $tailY = $currentY + $wordLen * ($isHorizon ? 0 : 1);

            if ($tailX > $maxX) {
                $maxX = $tailX;
            }
            if ($tailY > $maxY) {
                $maxY = $tailY;
            }
            if ($currentX < $translateX) {
                $translateX = $currentX;
            }
            if ($currentY < $translateY) {
                $translateY = $currentY;
            }
        }

        $idx = 0;
        $order = array_reduce($this->arr, function ($iter, $val) use (&$idx) {
            $iter[$val] = $idx;
            $idx += 1;
            return $iter;
        }, []);

        $newPositionObjArr = array_map(function ($positionObj) use ($translateX, $translateY) {
            $rtn = $positionObj;
            $rtn['xNum'] -= $translateX;
            $rtn['yNum'] -= $translateY;
            return $rtn;
        }, $positionObjArr);
        usort($newPositionObjArr, function ($a, $b) use ($order) {
            return $order[$a['wordStr']] - $order[$b['wordStr']];
        });

        $height = $maxY - $translateY;
        $width = $maxX - $translateX;

        return [
            'height' => $height,
            'width' => $width,
            'positionObjArr' => $newPositionObjArr
        ];
    }
}
