<?php

namespace App\Http\Controllers\API\User;

use App\Models\Tournament;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\TournamentResource;
use App\Http\Requests\Admin\Tournament\RoundRequest;
use App\Http\Requests\Admin\Tournament\StoreRequest;
use App\Http\Requests\Admin\Tournament\StoreRequest as UpdateRequest;

class TournamentController extends Controller
{
    private $user;
    private $model;

    public function __construct(Tournament $model)
    {
        $this->user = auth()->user();
        $this->model = $model;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $items = $this->model->latest()->get();
        return successResponse(TournamentResource::collection($items));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $item = $this->model->with('rounds')->findorFail($id);
        return successResponse(new TournamentResource($item));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // $d = $this->user->getModel()->profileable()->getModel()->latest()->take(8)->get()->modelKeys();
        // $item = $this->model->withCount('participants')->findorFail($id);
        // $item->participants()->attach($d);

        // $item = $this->model->whereHas('participants', function ($query) {
        //     $query->where('id', $this->user->club);
        // })->find($id);
        $item = $this->model->findorFail($id);

        if ($item->status == 'opened') {
            $players = [];
            if ($this->user->profileable_type == 'player') {
                if ($item->only_for_clubs && !$this->user->profileable->membership) {
                    return errorResponse(100, 'Only player with club can register in this tournament.');
                }
                $players = [$this->user->profileable->id];
            }

            if ($this->user->profileable_type == 'club') {
                $this->user->profileable->load(['members' => function ($query) {
                    $query->with(['player' => function ($query) {
                        $query->select('id');
                    }]);
                }]);
                foreach ($this->user->profileable->members as $member) {
                    array_push($players, $member->player->id);
                }
            }

            $item->participants()->syncWithoutDetaching($players);
            return successResponse();
        } else {
            return errorResponse(100, 'The tournament has been closed for registration');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $item = $this->model->findorFail($id);
        if ($item->status == 'drafted' || $item->status == 'opened') {
            $item->participants()->detach($this->user->club);
        }
        return successResponse();
    }
}
