<?php

declare(strict_types=1);

namespace App\BotMan\Conversations;

use App\Models\User;
use BotMan\BotMan\BotMan;
use BotMan\BotMan\Messages\Conversations\Conversation;

abstract class InvokableConversation extends Conversation
{
    private ?User $user = null;
    protected function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * Returns a name.
     */
    protected function getName(): string
    {
        $bot = $this->getBot();
        return (string) (
            optional($this->getUser())->first_name ??
            $bot->getUser()->getFirstName() ??
            $bot->getUser()->getUsername() ??
            $bot->getMessage()->getSender()
        );
    }

    /**
     * Starts the conversation by calling the `run` function
     * @param IncomingMessage $message
     * @param BotMan $bot
     * @return void
     * @throws BindingResolutionException
     */
    public function __invoke(BotMan $bot): void
    {
        // Assign bot
        $this->setBot($bot);

        // Get message
        $message = $bot->getMessage();

        // Set user if available
        if ($message->getExtras('user') instanceof User) {
            $this->user = $message->getExtras('user');
        }

        // Forward call
        $this->run($bot);
    }
}
