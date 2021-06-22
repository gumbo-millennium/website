<?php

declare(strict_types=1);

namespace App\Bots\Models;

use DateTimeInterface;
use InvalidArgumentException;

/**
 * @method self question(string $question)
 * @method self options(string[] $options)
 * @method self isAnonymous(bool $isAnonymous)
 * @method self allowsMultipleAnswers(bool $allowsMultipleAnswers)
 * @method self openPeriod(int $openPeriod)
 * @method static self make(array|string $question = [])
 */
class Poll extends TelegramObject
{
    public function __construct($question = [], array $options = [])
    {
        if (is_string($question)) {
            $question = [
                'question' => $question,
                'options' => $options,
            ];
        }
        parent::__construct($question);
    }

    public function addOption(string $option): self
    {
        $this->options = array_merge($this->options ?? [], [$option]);

        return $this;
    }

    public function quiz(string $correct, ?string $explanation = null): self
    {
        $correct = array_search($correct, $this->options ?? [], true);

        if ($correct === false) {
            throw new InvalidArgumentException('Correct option does not exist in list of options');
        }

        $this->type = 'quiz';
        $this->correct_option_id = $correct;

        if ($explanation) {
            $this->explanation = $explanation;
        }

        return $this;
    }

    /**
     * @param DateTimeInterface|int $closeDate
     */
    public function closeDate($closeDate): self
    {
        $this->closeDate = $closeDate instanceof DateTimeInterface ? $closeDate->getTimestamp() : $closeDate;

        return $this;
    }
}
