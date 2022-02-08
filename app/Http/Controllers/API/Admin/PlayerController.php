<?php

namespace App\Http\Controllers\API\Admin;

use App\Models\User;
use App\Models\ClubMember;
use App\Models\ClubProfile;
use App\Http\Resources\UserResource;
use App\Http\Requests\Admin\Player\UpdateRequest;
use App\Http\Resources\ClubProfileResource;

class PlayerController extends UserController
{
    private $club;

    public function __construct(User $model, ClubProfile $club, ClubMember $member)
    {
        $this->club = $club;
        $this->model = $model;
        $this->member = $member;
        $this->type = 'player';
    }

    public function related()
    {
        $items = $this->club->latest()->select('id', 'name')->get();

        return successResponse(ClubProfileResource::collection($items));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $item = $this->model->whereProfileableType($this->type)->with(['profileable.membership.club', 'profileable.willing.club'])->findorFail($id);
        return successResponse(new UserResource($item));
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
        $data = $request->validated();
        $item = $this->model->whereProfileableType($this->type)->findorFail($id);
        $this->member->updateOrCreate([
            'player_id' => $item->profileable->id
        ], [
            'club_id' => $data['club'],
        ]);
        $item->load(['profileable.membership.club']);
        return successResponse(new UserResource($item));
    }
}
