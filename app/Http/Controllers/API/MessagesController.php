<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Message;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MessagesController extends Controller
{
    public function read(Request $request, Message $message)
    {
        $message->update(['read_at' => Carbon::now()]);

        return new JsonResponse(['success' => true]);
    }
}
