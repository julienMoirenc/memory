<?php

namespace App\Models;

use DateTime;
use PDO;

class Game
{

    /**
     * The PDO Object
     *
     * @var PDO
     */
    private $pdo;

    /**
     * available cards
     */
    public const CARDS = [
        "redapple",
        "banana",
        "orange",
        "greenlime",
        "pomegranate",
        "apricot",
        "lime",
        "strawberry",
        "greenapple",
        "peach",
        "grape",
        "watermelon",
        "plum",
        "pear",
        "cherry",
        "rasperry",
        "mango",
        "whitecherry"
    ];

    public const TIMEOUT = 180000;

    public const NB_PAIRS = 14;

    function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    function getPDO()
    {
        return $this->pdo;
    }

    /**
     * Generate a problem
     *
     * @return array
     */
    private function generateProblem(): array
    {
        $cards = self::CARDS;

        $problem = [];

        for ($i = 0; $i < self::NB_PAIRS; $i++) {

            //pick an unused card
            $ok = false;
            while (!$ok) {
                $indexCard = rand(0, sizeof(self::CARDS) - 1);
                if (!in_array(self::CARDS[$indexCard], $problem)) {
                    $ok = true;
                }
            }

            //add it twice in the problem array
            $problem[] = self::CARDS[$indexCard];
            $problem[] = self::CARDS[$indexCard];
        }

        //suffle shuffle shuffle shuffeling !
        shuffle($problem);

        return $problem;
    }

    /**
     * Verify a solution
     * @todo Need to be a bit rewritten as no pair difference test is done. i.e pass when providing ten x the same good pair...
     * @return array
     */
    private function verifySolution($problem, $solution): bool
    {
        $pairCount = 0;

        foreach ($solution as $pair) {
            if ($problem[$pair['item1']] == $problem[$pair['item2']]) {
                $pairCount++;
            }
        }

        if ($pairCount  == self::NB_PAIRS) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Create a new game
     *
     * @return void the new game line
     */
    public function new()
    {
        $key = uniqid();

        $problem = [];

        $cards = self::CARDS;

        $problem = $this->generateProblem();

        $problem = json_encode($problem);

        $startTime = new DateTime();
        $start  = $startTime->format('Y-m-d H:i:s');

        $stmt = $this
            ->pdo
            ->prepare('insert into `games` (`key`, `problem`, `start`)
                        values (:key, :problem, :start)
            ');

        $stmt->bindValue('key', $key);
        $stmt->bindValue('start', $start);
        $stmt->bindValue('problem', $problem);

        $stmt->execute();

        $lastInsertId = $this
            ->pdo->lastInsertId();

        $stmt = $this
            ->pdo
            ->prepare('select * from  games where id = :id');

        $stmt->execute([':id' => $lastInsertId]);

        return $stmt->fetch();
    }

    /**
     * Check if a solution is valid for the game problem, save it and calculate time
     *
     * @return boolean
     */
    public function verify(string $key, array $solution): bool
    {

        //get the game row in the database
        $loadStmt = $this
            ->pdo
            ->prepare('select * from `games`
                            where `key` = :key
                            and solvingtime is NULL
                ');

        $loadStmt->execute([':key' => $key]);
        $game = $loadStmt->fetch();

        $start = $game['start'];

        $problem = json_decode($game['problem']);

        $ok = $this->verifySolution($problem, $solution);

        if ($ok) {

            //get current time for end time
            $endTime = new DateTime();
            $end = $endTime->format('Y-m-d H:i:s');

            //calculate solving time (end - start)
            $solvingtime = strtotime($end) - strtotime($start);

            //prepare data
            $data = [
                ':solvingtime' => $solvingtime,
                ':solution' => json_encode($solution),
                ':end' => $end,
                ':key' => $key,
            ];

            //prepare statement
            $stmt = $this
                ->pdo
                ->prepare('update `games`
                            set `solvingtime` = :solvingtime,
                            `solution` = :solution,
                            `end` = :end
                            where `key` = :key
                            and `solvingtime` is NULL
                ');

            //bind data and execute statement
            $stmt->execute($data);

            return true;
        } else {
            return false;
        }
    }

    /**
     * Get best scores
     *
     * @param integer $count
     * @return array
     */
    public function getBests(int $count = 3): array
    {

        $stmt = $this
            ->pdo
            ->prepare('select solvingtime from games where solvingtime is not null order by solvingtime asc limit :count');

        $stmt->bindValue('count', $count, PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->fetchAll();
    }
}
