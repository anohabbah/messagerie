<?php

namespace App\Http\Controllers;


use App\Http\Requests\StoreMessageRequest;
use App\Notifications\MessageReceived;
use App\Repositories\ConversationRepository;
use App\User;
use Illuminate\Auth\AuthManager;

class ConversationsController extends Controller
{
    /**
     * @var ConversationRepository
     */
    private $r;

    /**
     * @var AuthManager
     */
    private $auth;

    /**
     * ConversationsController constructor.
     *
     * @param ConversationRepository $r
     * @param AuthManager $auth
     */
    public function __construct(ConversationRepository $r, AuthManager $auth)
    {
        $this->middleware('auth');

        $this->r = $r;
        $this->auth = $auth;
    }

    /**
     * @return mixed
     */
    public function index()
    {
        return view('conversations.index');
    }

    /**
     * @param User $user
     * @return mixed
     */
    public function show(User $user)
    {
        $me = $this->auth->user();
        $messages = $this->r->getMessagesFor($me->id, $user->id)->paginate(10);
        $unread = $this->r->unreadCount($me->id);
        if (isset($unread[$user->id])) {
            $this->r->markAsReadAll($user->id, $me->id);
            unset($unread[$user->id]);
        }

        return view('conversations.show')
            ->withUser($user)
            ->withUnread($unread)
            ->withMessages($messages)
            ->withUsers($this->r->getConversations($me->id));
    }

    /**
     * @param User $user
     * @param StoreMessageRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(User $user, StoreMessageRequest $request)
    {
        $message = $this->r->createMessage($request->input('content'), $this->auth->user()->id, $user->id);

        $user->notify(new MessageReceived($message));

        return back();
    }

}
