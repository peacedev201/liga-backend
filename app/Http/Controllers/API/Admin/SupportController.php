<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Thread;
use App\Models\Ticket;
use App\Models\Message;
use App\Http\Resources\TicketResource;

class SupportController extends Controller
{
    //
    private $thread;
    private $ticket;
    private $message;

    public function __construct(Thread $thread, Ticket $ticket, Message $message)
    {
        $this->thread = $thread;
        $this->ticket = $ticket;
        $this->message = $message;
    }

    public function ticketList()
    {
        $list = $this->ticket->with('user')->where('status', 'pending')->get()->toArray();
        return successResponse($list);
    }

    public function closeTicket($id)
    {
        $ticket = $this->ticket->where('id', $id)->first();
        if(!$ticket){
            $error = ValidationException::withMessages([
                'ticketrequest' => ['The selected ticket does not exist'],
            ]);
            throw $error;
        };
        $ticket->update(['status' => 'approved']);
        
        return successResponse($ticket);
    }

    public function openChat($id)
    {
        $messages = $this->message->where('thread_id', $id)->orderBy('created_at', 'asc');
        $messages->update(['status' => 'approved']);
        $length = $messages->get()->count();
        $messages = $messages->skip($length-50)->take(50)->get()->toArray();
        return successResponse($messages);
    }

    public function addMessage(Request $request)
    {
        $message = $request->validate([
            'message' => 'required',
            'from' => 'required',
            'to' => 'required',
            'thread_id' => 'required',
        ]);

        $result = $this->message->create($message);
        return successResponse($result);
    }

    public function approveMessage($id)
    {
        $result = $this->message->where('id', $id)->take(1);
        $result->update(['status' => 'approved']);
        $result = $result->get()->toArray();
        return successResponse($result);
    }

    public function getNotification($id)
    {
        $messages = $this->message->where('status', 'pending')->where('to', $id)->get()->toArray();
        $result = [];
        foreach ($messages as $message) {
            if(!empty($message['thread_id'])){
                $result[$message['thread_id']][] = $message;
            }
        }
        foreach ($result as $key => $value) {
            $result[$key] = count($value);
        }
        $result['total'] = count($messages);

        return successResponse($result);
    }
}
