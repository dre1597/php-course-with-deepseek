<?php

class Quiz
{
    public const array QUESTIONS = [
        [
            'question' => 'Qual superglobal contém dados de formulários enviados via POST?',
            'options' => ['$_GET', '$_POST', '$_REQUEST', '$_SERVER'],
            'answer' => 1,
        ],
        [
            'question' => 'Qual função é usada para iniciar uma sessão PHP?',
            'options' => ['session_start()', 'session_init()', 'start_session()', 'session_begin()'],
            'answer' => 0,
        ],
        [
            'question' => 'Qual o operador de concatenação em PHP?',
            'options' => ['+', '&', '.', ','],
            'answer' => 2,
        ],
        [
            'question' => 'Como declarar uma variável em PHP?',
            'options' => ['var name;', '$name = valor;', 'let name = valor;', 'name := valor;'],
            'answer' => 1,
        ],
        [
            'question' => 'Qual extensão é usada para conectar ao SQLite?',
            'options' => ['mysql', 'pgsql', 'sqlite3', 'mysqli'],
            'answer' => 2,
        ],
    ];

    private array $questions;
    private array $answers;
    private int $total;

    public function __construct(array $answers = [], ?array $questions = null)
    {
        $this->total = count(self::QUESTIONS);
        $this->answers = $answers;
        $this->questions = $questions ?? $this->shuffleQuestions();
    }

    public function getCurrentQuestionIndex(): int
    {
        return count($this->answers);
    }

    public function getCurrentQuestion(): ?array
    {
        $index = $this->getCurrentQuestionIndex();

        if ($index >= $this->total) {
            return null;
        }

        return $this->questions[$index];
    }

    public function isFinished(): bool
    {
        return count($this->answers) >= $this->total;
    }

    public function answer(int $optionIndex): void
    {
        if ($this->isFinished()) {
            return;
        }

        $this->answers[] = $optionIndex;
    }

    public function getScore(): array
    {
        $correct = 0;
        foreach ($this->answers as $i => $answer) {
            if ($answer === $this->questions[$i]['answer']) {
                $correct++;
            }
        }

        return ['correct' => $correct, 'total' => $this->total];
    }

    public function getAnswers(): array
    {
        return $this->answers;
    }

    public function __serialize(): array
    {
        return [
            'questions' => $this->questions,
            'answers'   => $this->answers,
            'total'     => $this->total,
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->questions = $data['questions'] ?? $this->shuffleQuestions();
        $this->answers   = $data['answers']   ?? [];
        $this->total     = $data['total']     ?? count(self::QUESTIONS);
    }

    private function shuffleQuestions(): array
    {
        $shuffled = [];
        $keys     = array_keys(self::QUESTIONS);
        shuffle($keys);

        foreach ($keys as $i => $key) {
            $question = self::QUESTIONS[$key];
            $shuffled[$i] = $this->shuffleOptions($question);
        }

        return $shuffled;
    }

    private function shuffleOptions(array $question): array
    {
        $options = $question['options'];
        $correct = $options[$question['answer']];
        $shuffled = $options;
        shuffle($shuffled);
        $newAnswer = array_search($correct, $shuffled, true);

        $question['options'] = $shuffled;
        $question['answer'] = $newAnswer;

        return $question;
    }
}
