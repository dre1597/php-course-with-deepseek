<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/Quiz.php';

class QuizTest extends TestCase
{
    public function testConstructorStartsWithNoAnswers(): void
    {
        $quiz = new Quiz();

        $this->assertEmpty($quiz->getAnswers());
        $this->assertFalse($quiz->isFinished());
    }

    public function testConstructorAcceptsPreloadedAnswers(): void
    {
        $allCorrect = array_map(fn($q) => $q['answer'], Quiz::QUESTIONS);
        $quiz = new Quiz($allCorrect, Quiz::QUESTIONS);

        $this->assertSame($allCorrect, $quiz->getAnswers());
        $this->assertTrue($quiz->isFinished());
    }

    public function testGetCurrentQuestionIndexStartsAtZero(): void
    {
        $quiz = new Quiz();

        $this->assertSame(0, $quiz->getCurrentQuestionIndex());
    }

    public function testGetCurrentQuestionIndexAdvancesAfterAnswers(): void
    {
        $quiz = new Quiz();
        $quiz->answer(0);
        $quiz->answer(1);

        $this->assertSame(2, $quiz->getCurrentQuestionIndex());
    }

    public function testGetCurrentQuestionReturnsFirstQuestion(): void
    {
        $quiz = new Quiz([], Quiz::QUESTIONS);

        $question = $quiz->getCurrentQuestion();

        $this->assertIsArray($question);
        $this->assertArrayHasKey('question', $question);
        $this->assertArrayHasKey('options', $question);
        $this->assertArrayHasKey('answer', $question);
    }

    public function testGetCurrentQuestionReturnsNextAfterAnswering(): void
    {
        $quiz = new Quiz([], Quiz::QUESTIONS);
        $quiz->answer(0);

        $question = $quiz->getCurrentQuestion();

        $this->assertSame(Quiz::QUESTIONS[1], $question);
    }

    public function testGetCurrentQuestionReturnsNullWhenFinished(): void
    {
        $answers = array_fill(0, 5, 0);
        $quiz = new Quiz($answers, Quiz::QUESTIONS);

        $this->assertNull($quiz->getCurrentQuestion());
    }

    public function testIsFinishedAfterAllAnswers(): void
    {
        $quiz = new Quiz([0, 1, 2, 1, 2], Quiz::QUESTIONS);

        $this->assertTrue($quiz->isFinished());
    }

    public function testIsNotFinishedWithPartialAnswers(): void
    {
        $quiz = new Quiz([0, 1], Quiz::QUESTIONS);

        $this->assertFalse($quiz->isFinished());
    }

    public function testAnswerSavesOption(): void
    {
        $quiz = new Quiz([], Quiz::QUESTIONS);
        $quiz->answer(2);

        $this->assertSame([2], $quiz->getAnswers());
    }

    public function testAnswerAdvancesToNextQuestion(): void
    {
        $quiz = new Quiz();
        $quiz->answer(0);

        $this->assertSame(1, $quiz->getCurrentQuestionIndex());
    }

    public function testAnswerDoesNothingAfterFinished(): void
    {
        $quiz = new Quiz([0, 1, 2, 1, 2], Quiz::QUESTIONS);
        $quiz->answer(0);

        $this->assertSame([0, 1, 2, 1, 2], $quiz->getAnswers());
    }

    public function testGetScoreAllCorrect(): void
    {
        $answers = array_map(fn($q) => $q['answer'], Quiz::QUESTIONS);
        $quiz    = new Quiz($answers, Quiz::QUESTIONS);

        $score = $quiz->getScore();

        $this->assertSame(5, $score['correct']);
        $this->assertSame(5, $score['total']);
    }

    public function testGetScoreAllWrong(): void
    {
        $quiz = new Quiz([9, 9, 9, 9, 9], Quiz::QUESTIONS);

        $score = $quiz->getScore();

        $this->assertSame(0, $score['correct']);
        $this->assertSame(5, $score['total']);
    }

    public function testGetScorePartial(): void
    {
        $wrong  = [9, 9, 2, 9, 2];

        $quiz  = new Quiz($wrong, Quiz::QUESTIONS);
        $score = $quiz->getScore();

        $this->assertSame(2, $score['correct']);
        $this->assertSame(5, $score['total']);
    }

    public function testGetScoreMidQuiz(): void
    {
        $quiz  = new Quiz([1, 9], Quiz::QUESTIONS);
        $score = $quiz->getScore();

        $this->assertSame(5, $score['total']);
        $this->assertGreaterThanOrEqual(0, $score['correct']);
    }

    public function testAllQuestionsHaveAnswerIndexWithinBounds(): void
    {
        foreach (Quiz::QUESTIONS as $question) {
            $this->assertGreaterThanOrEqual(0, $question['answer']);
            $this->assertLessThan(count($question['options']), $question['answer']);
        }
    }

    public function testAllQuestionsHaveFourOptions(): void
    {
        foreach (Quiz::QUESTIONS as $question) {
            $this->assertCount(4, $question['options']);
        }
    }

    public function testGetAnswersReturnsEmptyInitially(): void
    {
        $quiz = new Quiz();

        $this->assertSame([], $quiz->getAnswers());
    }

    public function testAnswerAllFiveQuestions(): void
    {
        $quiz = new Quiz([], Quiz::QUESTIONS);

        for ($i = 0; $i < 5; $i++) {
            $this->assertFalse($quiz->isFinished());
            $quiz->answer(0);
        }

        $this->assertTrue($quiz->isFinished());
        $this->assertNull($quiz->getCurrentQuestion());
    }

    public function testShuffledQuizHasSameNumberOfQuestions(): void
    {
        $quiz = new Quiz();

        $count = 0;
        while (!$quiz->isFinished()) {
            $quiz->answer(0);
            $count++;
        }

        $this->assertSame(5, $count);
    }

    public function testShuffledQuizPreservesCorrectAnswerMapping(): void
    {
        $quiz = new Quiz([], Quiz::QUESTIONS);

        $question = $quiz->getCurrentQuestion();
        $correct  = $question['options'][$question['answer']];
        $expected = Quiz::QUESTIONS[0]['options'][Quiz::QUESTIONS[0]['answer']];

        $this->assertSame($expected, $correct);
    }

    public function testShuffledOptionsContainAllOriginals(): void
    {
        $questions = [
            [
                'question' => 'Q1',
                'options'  => ['A', 'B', 'C', 'D'],
                'answer'   => 2,
            ],
        ];

        $quiz  = new Quiz([], $questions);
        $first = $quiz->getCurrentQuestion();

        sort($first['options']);
        $this->assertSame(['A', 'B', 'C', 'D'], $first['options']);
    }

    public function testShuffledAnswerRemappedToCorrectOption(): void
    {
        $questions = [
            [
                'question' => 'Who?',
                'options'  => ['Wrong', 'Wrong', 'Correct', 'Wrong'],
                'answer'   => 2,
            ],
        ];

        $quiz = new Quiz([], $questions);
        $q    = $quiz->getCurrentQuestion();
        $quiz->answer($q['answer']);

        $score = $quiz->getScore();

        $this->assertSame(1, $score['correct']);
    }

    public function testScoreWorksWithShuffledQuestions(): void
    {
        $quiz = new Quiz([], Quiz::QUESTIONS);

        for ($i = 0; $i < 5; $i++) {
            $q = $quiz->getCurrentQuestion();
            $quiz->answer($q['answer']);
        }

        $score = $quiz->getScore();

        $this->assertSame(5, $score['correct']);
    }

    public function testShuffledQuestionsAreConsistentWithinInstance(): void
    {
        $quiz = new Quiz([], Quiz::QUESTIONS);

        $first = $quiz->getCurrentQuestion();
        $quiz->answer(0);
        $again = $quiz->getCurrentQuestion();

        $this->assertNotSame($first['question'], $again['question']);
    }
}
