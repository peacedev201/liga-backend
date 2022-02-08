<?php

namespace App\Http\Controllers\API\Admin;

use App\Models\Tournament;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\TournamentResource;
use App\Http\Requests\Admin\Tournament\RoundRequest;
use App\Http\Requests\Admin\Tournament\StoreRequest;
use App\Http\Requests\Admin\Tournament\UpdateRequest;
use App\Http\Requests\Admin\Tournament\ScheduleRequest;

class TournamentController extends Controller
{
    private $model;

    public function __construct(Tournament $model)
    {
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
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRequest $request)
    {
        $data = $request->validated();
        if (isset($data['open_for_registration']) && $data['open_for_registration']) {
            $data['status'] = 'opened';
        }
        $item = $this->model->create($data);
        return successResponse(new TournamentResource($item));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $item = $this->model->with(['rounds.tournamentRoundMatches', 'participants', 'participants.membership' => function($query){
            $query->with('club:id,name')->whereStatus(1);
        }])->findorFail($id);
        return successResponse(new TournamentResource($item));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateRequest $request, $id)
    {
        // $d = \App\Models\ClubProfile::latest()->take(8)->get()->modelKeys();
        // $item = $this->model->withCount('participants')->findorFail($id);
        // $item->participants()->attach($d);
        // dd('fdsf');
        $data = $request->validated();
        $item = $this->model->findorFail($id);

        if ($item->status == 'drafted' || $item->status == 'opened' || $item->status == 'closed') {
            if (($item->status == 'drafted' || $item->status == 'closed') && isset($data['open_for_registration']) && $data['open_for_registration']) {
                $data['status'] = 'opened';
            }
            if ($item->status == 'opened' && isset($data['close_for_registration']) && $data['close_for_registration']) {
                $data['status'] = 'closed';
            }
            $item->update($data);
            if ($data['only_for_clubs'] == 'true') {
                $item->only_for_clubs = 1;
            } else {
                $item->only_for_clubs = 0;
            }
            $item->save();
            return successResponse(new TournamentResource($item));
        } else {
            return errorResponse(100, 'The tournament has been scheduled');
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
        $item->delete();
        return successResponse(new TournamentResource($item));
    }

    /**
     * Store rounds of a resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function round(RoundRequest $request, $id)
    {
        $data = $request->validated();
        $item = $this->model->findorFail($id);

        if ($item->status == 'closed' || $item->status == 'rounded') {
            $rounds = [];
            foreach ($data['rounds'] as $round) {
                $roundData = [
                    'id' => null
                ];
                if (isset($round['id'])) {
                    $roundData['id'] = $round['id'];
                }
                $round = $item->rounds()->updateOrCreate($roundData, $round);
                array_push($rounds, $round->id);
            }

            $item->rounds()->whereNotIn('id', $rounds)->delete();
            $item->update(['status' => 'rounded']);
            $item->load('rounds');
            return successResponse(new TournamentResource($item));
        } else {
            return errorResponse(100, 'The tournament has been scheduled');
        }
    }

    /**
     * Store matches of a resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function plan(ScheduleRequest $request, $id)
    {
        $data = $request->validated();
        $item = $this->model->findorFail($id);

        if ($item->status == 'rounded' || $item->status == 'scheduled' || $item->status == 'started') {
            foreach ($data['rounds'] as $roundData) {
                $round = $item->rounds()->findorFail($roundData['id']);
                $matches = [];
                foreach ($roundData['matches'] as $match) {
                    $matchData = [
                        'id' => null
                    ];
                    if (isset($match['id'])) {
                        $matchData['id'] = $match['id'];
                    }
                    $match = $round->tournamentRoundMatches()->updateOrCreate($matchData, $match);
                    array_push($matches, $match->id);
                }
                $round->tournamentRoundMatches()->whereNotIn('id', $matches)->delete();
            }
            if ($item->status == 'rounded') {
                $item->update(['status' => 'scheduled']);
            }

            return successResponse(new TournamentResource($item));
        } else {
            return errorResponse(100, 'The tournament has been scheduled');
        }
    }

    /**
     * Start the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function start(Request $request, $id)
    {
        $item = $this->model->findorFail($id);

        if ($item->status == 'scheduled' || $item->status == 'completed') {
            $item->update(['status' => 'started']);

            return successResponse(new TournamentResource($item));
        } else {
            return errorResponse(100, 'The tournament has been scheduled');
        }
    }

    /**
     * Schedule the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function schedule(Request $request, $id)
    {
        $item = $this->model->findorFail($id);

        if ($item->status == 'started') {
            $item->update(['status' => 'scheduled']);

            return successResponse(new TournamentResource($item));
        } else {
            return errorResponse(100, 'The tournament has been scheduled');
        }
    }

    /**
     * Complete the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function complete(Request $request, $id)
    {
        $item = $this->model->findorFail($id);

        if ($item->status == 'started') {
            $item->update(['status' => 'completed']);

            return successResponse(new TournamentResource($item));
        } else {
            return errorResponse(100, 'The tournament has been completed');
        }
    }
}
