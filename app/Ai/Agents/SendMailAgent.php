<?php

namespace App\Ai\Agents;

use App\Ai\Tools\SendMail;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Messages\Message;
use Laravel\Ai\Promptable;
use Stringable;

class SendMailAgent implements Agent, Conversational, HasTools
{
    use Promptable;

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        return 'You are an email assistant. When the user asks you to send an email, compose an appropriate subject and body based on their request, then use the send_mail tool with the recipient address (extract it from the prompt if given), subject, and body. Always use the tool to actually send the email when the user wants to send one. If the user does not provide an email address, ask for it. Reply briefly to confirm what you did.';
    }

    /**
     * Get the list of messages comprising the conversation so far.
     *
     * @return Message[]
     */
    public function messages(): iterable
    {
        return [];
    }

    /**
     * Get the tools available to the agent.
     *
     * @return Tool[]
     */
    public function tools(): iterable
    {
        return [
            new SendMail,
        ];
    }
}
