<?php

/**
 * Copyright 2017 - Abbah Anoh
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated
 * documentation files (the "Software"), to deal in the Software without restriction, including without limitation the
 * rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the
 * Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
 * WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS
 * OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR
 * OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

namespace App\Repositories;


use App\Message;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as DbBuilder;

class ConversationRepository
{
    /**
     * @var User
     */
    private $user;
    /**
     * @var Message
     */
    private $message;

    /**
     * Create a new instance of ConversationRepository.
     *
     * @param User $user
     * @param Message $message
     */
    public function __construct(User $user, Message $message)
    {
        $this->user = $user;
        $this->message = $message;
    }

    /**
     * @param int $user
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getConversations(int $user)
    {
        return $this->user->newQuery()
            ->select('name', 'id')
            ->where('id', '!=', $user)
            ->get();
    }

    /**
     * @param string $content
     * @param int $from
     * @param int $to
     * @return mixed
     */
    public function createMessage(string $content, int $from, int $to)
    {
        return $this->message->newQuery()
            ->create([
                'content' => $content,
                'from_id' => $from,
                'to_id' => $to
            ]);
    }

    /**
     * @param int $from
     * @param int $to
     * @return Builder|DbBuilder
     */
    public function getMessagesFor(int $from, int $to)
    {
        return $this->message->newQuery()
            ->whereRaw("((from_id = $from AND to_id = $to)")
            ->orWhereRaw("(from_id = $to AND to_id = $from))")
            ->orderByDesc('created_at')
            ->with([
                'from' => function($q) {return $q->select('name', 'id');}
            ]);
    }

    /**
     * @param int $user
     * @return \Illuminate\Support\Collection
     */
    public function unreadCount(int $user)
    {
        return $this->message->newQuery()
            ->where('to_id', '=', $user)
            ->groupBy('from_id')
            ->selectRaw('from_id, count(id) as count')
            ->whereNull('read_at')
            ->pluck('count', 'from_id');
    }

    /**
     * Mark messages as read for a given user.
     *
     * @param int $from
     * @param int $to
     */
    public function markAsReadAll(int $from, int $to)
    {
        $this->message->newQuery()
            ->where('from_id', '=', $from)
            ->where('to_id', '=', $to)
            ->update(['read_at' => Carbon::now()]);
    }
}