<?php

namespace App\Http\Controllers\API;

use App\Events\NewMessage;
use App\Http\Requests\StoreMessageRequest;
use App\Repositories\ConversationRepository;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ConversationsController extends Controller
{
    /**
     * @var ConversationRepository
     */
    private $r;

    /**
     * ConversationsController constructor.
     * @param ConversationRepository $r
     */
    public function __construct(ConversationRepository $r)
    {
        $this->r = $r;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request) {
        $conversations = $this->r->getConversations($request->user()->id);
        $unread = $this->r->unreadCount($request->user()->id);
        foreach ($conversations as $conversation) {
            if (isset($unread[$conversation->id])) {
                $conversation->unread = $unread[$conversation->id];
            } else {
                $conversation->unread = 0;
            }
        }

        return response()->json([
            'conversations' => $conversations
        ]);
    }

    /**
     * @param Request $request
     * @param User $user
     * @return JsonResponse
     */
    public function show(Request $request, User $user)
    {
        $count = null;

        $messagesQuery = $this->r->getMessagesFor($request->user()->id, $user->id);

        if ($request->has('before')) {
            $messagesQuery->where('created_at', '<', $request->get('before'));
        } else {
            $count = $messagesQuery->count();
        }

        $messages = $messagesQuery->limit(10)->get();
        $update = false;
        foreach ($messages as $message) {
            if ($message->read_at === null && $message->to_id === $request->user()->id) {
                $message->read_at = Carbon::now();

                if (!$update) {
                    $this->r->markAsReadAll($message->from_id, $message->to_id);
                }

                $update = true;
            }
        }
        return new JsonResponse([
            'messages' => array_reverse($messages->toArray()),
            'count' => $count
        ]);
    }

    public function store(StoreMessageRequest $request, User $user)
    {
        $message = $this->r->createMessage($request->get('content'), $request->user()->id, $user->id);

        broadcast(new NewMessage($message));

        return new JsonResponse(['message' => $message]);
    }
}
