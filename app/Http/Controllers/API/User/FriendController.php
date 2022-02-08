<?php

namespace App\Http\Controllers\API\User;

use App\Http\Controllers\Controller;
use App\Models\Friends;
use App\Models\User;
use App\Models\Thread;
use App\Models\Ticket;
use App\Models\Message;
use App\Models\PlayerProfile;
use Illuminate\Http\Request;
use App\Http\Resources\FriendsResource;
use Illuminate\Validation\ValidationException;

class FriendController extends Controller
{
    //
    private $friends;
    private $users;
    private $thread;
    private $ticket;
    private $message;
    private $playerprofile;

    public function __construct(Friends $friends, User $users, Thread $thread, Ticket $ticket, Message $message, PlayerProfile $playerprofile)
    {
        $this->friends = $friends;
        $this->users = $users;
        $this->thread = $thread;
        $this->ticket = $ticket;
        $this->message = $message;
        $this->playerprofile = $playerprofile;
    }

    public function friendList($id)
    {
        $list1 = $this->friends->where('player1', $id)->where('status', 'approved')->pluck('player2', 'id')->toArray();
        $list2 = $this->friends->where('player2', $id)->where('status', 'approved')->pluck('player1', 'id')->toArray();
        $list = array_merge($list1, $list2);
        $result = [];
        foreach ($list as $key => $value) {
            $result = array_merge($result, $this->users->where('profileable_id', $value)->get()->toArray());
        }
        return successResponse($result);
    }

    public function supportRequest($id)
    {
        $exist_ticket = $this->ticket->where('player', $id)->where('status', 'pending')->get()->toArray();
        if($exist_ticket){
            return successResponse($exist_ticket[0]['thread_id']);
        } else {
            $thread = [
                'connection1' => -1,
                'connection2' => $id,
            ];
            $thread = $this->thread->create($thread);
    
            $ticket = [
                'thread_id' => $thread->id,
                'player' => $id,
            ];

            $ticket = $this->ticket->create($ticket);
            return successResponse($thread->id);
        }
    }

    public function requestList($id)
    {
        // $list1 = $this->friends->where('player1', $id)->where('status', 'pending')->pluck('player2', 'id')->toArray();
        $list = $this->friends->where('player2', $id)->where('status', 'pending')->pluck('player1', 'id')->toArray();
        // $list = array_merge($list1, $list2);
        $result = [];
        foreach ($list as $key => $value) {
            $result = array_merge($result, $this->users->where('profileable_id', $value)->get()->toArray());
        }
        return successResponse($result);
    }

    public function friendRequest($from, $to)
    {
        $tt = $this->friends->where('player1', $from)->where('player2', $to)->where('status','!=', 'cancelled')->get()->toArray();
        $ttt = $this->friends->where('player2', $from)->where('player1', $to)->where('status','!=', 'cancelled')->get()->toArray();
        if($tt || $ttt) {
            $error = ValidationException::withMessages([
                'friendrequest' => ['current request already exisiting'],
            ]);
            throw $error;
        }
        $friend = [
            'player1' => $from,
            'player2' => $to,
        ];
        $friend = $this->friends->create($friend);
        return successResponse(new FriendsResource($friend));
    }

    public function approveRequest($from, $to)
    {
        $friend = $this->friends->where('player1', $from)->where('player2', $to)->first();
        if(!$friend){
            $error = ValidationException::withMessages([
                'friendrequest' => ['The selected friend does not exist'],
            ]);
            throw $error;
        };
        $friend->update(['status' => 'approved']);
        
        return successResponse($friend);
    }

    public function cancelRequest($from, $to)
    {
        $friend = $this->friends->where('player1', $from)->where('player2', $to)->first();
        if(!$friend){
            $error = ValidationException::withMessages([
                'friendrequest' => ['The selected friend does not exist'],
            ]);
            throw $error;
        };
        $friend->update(['status' => 'cancelled']);
        return successResponse($friend);
    }

    public function createThread($from, $to)
    {
        $exist_thread1 = $this->thread->where('connection1', $from)->where('connection2', $to)->get()->toArray();
        $exist_thread2 = $this->thread->where('connection2', $from)->where('connection1', $to)->get()->toArray();
        if($exist_thread1){
            return successResponse($exist_thread1[0]['id']);
        } else if ($exist_thread2) {
            return successResponse($exist_thread2[0]['id']);
        } else {
            $thread = [
                'connection1' => $from,
                'connection2' => $to,
            ];
            $thread = $this->thread->create($thread);

            return successResponse($thread->id);
        }
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

    public function getThreads($id)
    {
        $threads = [];
        $openticket = $this->ticket->where([
            ['status', 'pending'],
            ['player', $id]
            ])->first();

        if(!empty($openticket)){
            $threads[] = $this->thread->where('id', $openticket['thread_id'])->get()->toArray()[0];
            $threads = array_merge($threads, $this->thread
            ->where([
                ['connection1', '!=', -1],
                ['connection1', $id]
                ])
            ->orWhere([
                ['connection1', '!=', -1],
                ['connection2', $id]
                ])
            ->orderBy('id', 'desc')->get()->toArray());
        } else {
            $threads = $this->thread
            ->where([
                ['connection1', '!=', -1],
                ['connection1', $id]
                ])
            ->orWhere([
                ['connection1', '!=', -1],
                ['connection2', $id]
                ])
            ->orderBy('id', 'desc')->get()->toArray();
        }
        
        foreach ($threads as $key => $value) {
            if($value['connection1'] == $id){
                $threads[$key]['user_info'] = $this->playerprofile->where('id', $value['connection2'])->get()->toArray();
            } else if ($value['connection2']) {
                $threads[$key]['user_info'] = $this->playerprofile->where('id', $value['connection1'])->get()->toArray();
            }
        }

        return successResponse($threads);
    }
}
